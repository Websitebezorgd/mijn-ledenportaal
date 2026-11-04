<?php
/**
 * E-mailadressen van leden in bulk wijzigen (voorheen de losse plugin "User Email Updater").
 *
 * Werkt e-mailadres én gebruikersnaam bij, met eerst een voorbeeld zonder wijzigingen en daarna
 * een logboek van wat er is gedaan. De gebruikersnaam volgt het nieuwe adres, zodat het oude
 * adres niet meer als gebruikersnaam werkt om in te loggen.
 */

if ( ! defined( 'ABSPATH' ) ) exit;

define( 'LP_EMAIL_WIJZIG_LOG', 'lp_email_wijzig_log' );
define( 'LP_EMAIL_WIJZIG_LOG_MAX', 200 );

/**
 * Lees de geplakte tekst: één paar per regel, "oud => nieuw" (ook ->, komma of spatie).
 *
 * Lege regels en regels die met --, # of // beginnen worden overgeslagen. Markdown-mailto-links
 * worden opgeschoond. Het nieuwe adres wordt opgeslagen zoals het is ingevoerd, met hoofdletters.
 *
 * @return array{paren: array, fouten: array}
 */
function lp_email_wijzig_lees_invoer( $tekst ) {
    $paren  = [];
    $fouten = [];

    foreach ( preg_split( "/\r\n|\n|\r/", (string) $tekst ) as $i => $regel ) {
        $regel_nr = $i + 1;
        $regel    = trim( $regel );

        if ( $regel === '' || preg_match( '~^(--|#|//)~', $regel ) ) continue;

        // [jan@x.nl](mailto:jan@x.nl) → jan@x.nl, en losse "mailto:" weg
        $schoon = preg_replace( '/\[([^\]]+)\]\(mailto:[^\)]+\)/i', '$1', $regel );
        $schoon = preg_replace( '/mailto:/i', '', $schoon );
        $delen  = preg_split( '/\s*(?:=>|->|,)\s*|\s+/', $schoon, -1, PREG_SPLIT_NO_EMPTY );

        if ( count( $delen ) !== 2 ) {
            $fouten[] = [ 'regel' => $regel_nr, 'bericht' => sprintf( __( 'Kan deze regel niet lezen (verwacht: oud@adres.nl => nieuw@adres.nl): %s', 'mijn-ledenportaal' ), $regel ) ];
            continue;
        }

        [ $oud, $nieuw ] = $delen;

        if ( ! is_email( $oud ) ) {
            $fouten[] = [ 'regel' => $regel_nr, 'bericht' => sprintf( __( 'Ongeldig oud e-mailadres: %s', 'mijn-ledenportaal' ), $oud ) ];
            continue;
        }
        if ( ! is_email( $nieuw ) ) {
            $fouten[] = [ 'regel' => $regel_nr, 'bericht' => sprintf( __( 'Ongeldig nieuw e-mailadres: %s', 'mijn-ledenportaal' ), $nieuw ) ];
            continue;
        }

        // Alleen hoofdletters anders (jan@x.nl => Jan@x.nl of andersom) mag: dan worden alleen die aangepast
        $paren[] = [ 'oud' => $oud, 'nieuw' => $nieuw, 'regel' => $regel_nr ];
    }

    return [ 'paren' => $paren, 'fouten' => $fouten ];
}

/**
 * Mag de huidige gebruiker deze pagina gebruiken? Zelfde eis als bij het opslaan van
 * profielvelden: beheerder van de instellingen én van gebruikers.
 */
function lp_email_wijzig_mag() {
    return current_user_can( 'manage_options' ) && current_user_can( 'edit_users' );
}

/**
 * Unieke user_nicename (gebruikt in auteur-URL's en het zoeken in Ledenbeheer) bij de nieuwe
 * gebruikersnaam, zodat het oude adres daar niet blijft staan. Zelfde opbouw als wp_insert_user.
 */
function lp_email_wijzig_nieuwe_nicename( $login, $user_id ) {
    global $wpdb;
    $basis    = mb_substr( sanitize_title( $login ), 0, 50 ) ?: 'lid';
    $nicename = $basis;
    for ( $i = 2; $wpdb->get_var( $wpdb->prepare( "SELECT ID FROM {$wpdb->users} WHERE user_nicename = %s AND ID != %d LIMIT 1", $nicename, $user_id ) ); $i++ ) {
        // Kolom is maximaal 50 tekens, inclusief het volgnummer
        $nicename = mb_substr( $basis, 0, 50 - strlen( "-$i" ) ) . "-$i";
    }
    return $nicename;
}

/**
 * Controleer één paar en voer het uit als $uitvoeren true is.
 *
 * Statussen: klaar, bijgewerkt, ongewijzigd, niet_gevonden, eigen_account, geen_rechten,
 * conflict, fout. (dubbel komt uit lp_email_wijzig_verwerk_alles.)
 */
function lp_email_wijzig_verwerk( array $paar, $uitvoeren = false ) {
    global $wpdb;

    $oud       = $paar['oud'];
    $nieuw     = $paar['nieuw'];
    $resultaat = [ 'oud' => $oud, 'nieuw' => $nieuw, 'user_id' => null, 'status' => '', 'bericht' => '' ];

    // Eerst op e-mailadres zoeken. Alleen als dat niets oplevert op gebruikersnaam: oudere accounts
    // hebben hun eerste adres als gebruikersnaam, ook als dat adres nu bij een ander lid hoort.
    $gebruiker = get_user_by( 'email', $oud );
    if ( ! $gebruiker ) {
        $id_via_login = (int) username_exists( $oud );
        $gebruiker    = $id_via_login ? get_userdata( $id_via_login ) : false;
    }

    if ( ! $gebruiker ) {
        $resultaat['status']  = 'niet_gevonden';
        $resultaat['bericht'] = __( 'Geen gebruiker met dit e-mailadres of deze gebruikersnaam.', 'mijn-ledenportaal' );
        return $resultaat;
    }

    $user_id = (int) $gebruiker->ID;
    $resultaat['user_id'] = $user_id;

    // Je eigen account: de wijziging logt je direct uit, midden in deze actie
    if ( $user_id === (int) get_current_user_id() ) {
        $resultaat['status']  = 'eigen_account';
        $resultaat['bericht'] = __( 'Dit is je eigen account. Wijzig je eigen adres via je profiel.', 'mijn-ledenportaal' );
        return $resultaat;
    }
    // Per account controleren, zodat bijvoorbeeld een super admin niet door een sitebeheerder te wijzigen is
    if ( ! current_user_can( 'edit_user', $user_id ) ) {
        $resultaat['status']  = 'geen_rechten';
        $resultaat['bericht'] = __( 'Je hebt geen rechten om dit account te wijzigen.', 'mijn-ledenportaal' );
        return $resultaat;
    }

    // Is het nieuwe adres al van iemand anders?
    $eigenaar = get_user_by( 'email', $nieuw );
    if ( $eigenaar && (int) $eigenaar->ID !== $user_id ) {
        $resultaat['status']  = 'conflict';
        $resultaat['bericht'] = sprintf( __( 'Het nieuwe adres is al in gebruik bij gebruiker ID %d.', 'mijn-ledenportaal' ), (int) $eigenaar->ID );
        return $resultaat;
    }

    $nieuwe_login = lp_unieke_gebruikersnaam( $nieuw, $user_id );

    if ( $gebruiker->user_email === $nieuw && $gebruiker->user_login === $nieuwe_login ) {
        $resultaat['status']  = 'ongewijzigd';
        $resultaat['bericht'] = __( 'Heeft dit adres en deze gebruikersnaam al.', 'mijn-ledenportaal' );
        return $resultaat;
    }

    if ( ! $uitvoeren ) {
        $resultaat['status']  = 'klaar';
        $resultaat['bericht'] = sprintf(
            __( 'Nu: %1$s (gebruikersnaam %2$s). Wordt: %3$s (gebruikersnaam %4$s).', 'mijn-ledenportaal' ),
            $gebruiker->user_email, $gebruiker->user_login, $nieuw, $nieuwe_login
        );
        return $resultaat;
    }

    $nieuwe_gegevens = [
        'user_login'    => $nieuwe_login,
        'user_email'    => $nieuw,
        'user_nicename' => lp_email_wijzig_nieuwe_nicename( $nieuwe_login, $user_id ),
    ];

    // wp_update_user() kan de gebruikersnaam niet wijzigen, dus rechtstreeks in de tabel
    $gelukt = $wpdb->update( $wpdb->users, $nieuwe_gegevens, [ 'ID' => $user_id ], [ '%s', '%s', '%s' ], [ '%d' ] );

    if ( $gelukt === false ) {
        $resultaat['status']  = 'fout';
        $resultaat['bericht'] = sprintf( __( 'Databasefout: %s', 'mijn-ledenportaal' ), $wpdb->last_error ?: 'onbekend' );
        return $resultaat;
    }

    // Cache wissen met het oude object, zodat ook de oude login/e-mail/nicename-sleutels verdwijnen
    clean_user_cache( $gebruiker );
    // Overal uitloggen: de inlogcookie hoort bij de oude gebruikersnaam
    WP_Session_Tokens::get_instance( $user_id )->destroy_all();
    update_user_meta( $user_id, 'lp_account_gewijzigd', current_time( 'Y-m-d H:i:s', true ) );

    // Andere plugins (mailinglijst, CRM) laten weten dat het profiel is gewijzigd, zoals wp_update_user doet
    do_action( 'profile_update', $user_id, $gebruiker, [ 'ID' => $user_id ] + $nieuwe_gegevens );
    do_action( 'lp_email_gewijzigd', $user_id, $gebruiker->user_email, $nieuw );

    $resultaat['status']  = 'bijgewerkt';
    $resultaat['bericht'] = sprintf( __( 'Bijgewerkt (gebruikersnaam %s). Het lid is overal uitgelogd.', 'mijn-ledenportaal' ), $nieuwe_login );
    return $resultaat;
}

/**
 * Verwerk alle paren. Bij uitvoeren komt elk resultaat meteen in het logboek, zodat ook na een
 * time-out halverwege te zien is welke accounts al zijn gewijzigd.
 *
 * Een adres dat in meer dan één regel voorkomt wordt niet verwerkt: zulke wijzigingen hangen van
 * elkaar af (b => c en a => b), en dan zou het voorbeeld iets anders laten zien dan het uitvoeren.
 */
function lp_email_wijzig_verwerk_alles( array $paren, $uitvoeren = false ) {
    $adressen_per_paar = array_map( fn( $p ) => array_unique( [ strtolower( $p['oud'] ), strtolower( $p['nieuw'] ) ] ), $paren );
    $aantal            = array_count_values( array_merge( [], ...$adressen_per_paar ) );
    $door              = $uitvoeren ? wp_get_current_user() : null;
    $uitkomst          = [];

    foreach ( $paren as $i => $paar ) {
        $dubbel = array_filter( $adressen_per_paar[ $i ], fn( $adres ) => $aantal[ $adres ] > 1 );

        $r = $dubbel
            ? [ 'oud' => $paar['oud'], 'nieuw' => $paar['nieuw'], 'user_id' => null, 'status' => 'dubbel',
                'bericht' => sprintf( __( '%s staat in meer dan één regel. Doe wijzigingen die van elkaar afhangen in aparte rondes.', 'mijn-ledenportaal' ), implode( ', ', $dubbel ) ) ]
            : lp_email_wijzig_verwerk( $paar, $uitvoeren );

        if ( $uitvoeren ) {
            lp_email_wijzig_log_toevoegen( $r + [
                'tijd' => current_time( 'mysql' ),
                'door' => $door->user_login . ' (#' . $door->ID . ')',
            ] );
        }
        $uitkomst[] = $r;
    }

    return $uitkomst;
}

function lp_email_wijzig_log() {
    $log = get_option( LP_EMAIL_WIJZIG_LOG, null );

    // Eenmalig het logboek van de losse plugin "User Email Updater" overnemen
    if ( $log === null ) {
        $statussen = [ 'updated' => 'bijgewerkt', 'not_found' => 'niet_gevonden', 'ambiguous' => 'meerdere', 'error' => 'fout', 'ready' => 'klaar' ];
        $log = [];
        foreach ( (array) get_option( 'wys_ueu_log', [] ) as $r ) {
            if ( ! is_array( $r ) ) continue;
            $log[] = [
                'tijd'    => $r['time'] ?? '',
                'door'    => $r['actor'] ?? '',
                'oud'     => $r['old'] ?? '',
                'nieuw'   => $r['new'] ?? '',
                'user_id' => $r['user_id'] ?? null,
                'status'  => $statussen[ $r['status'] ?? '' ] ?? ( $r['status'] ?? '' ),
                'bericht' => $r['message'] ?? '',
            ];
        }
        update_option( LP_EMAIL_WIJZIG_LOG, $log, false );
        delete_option( 'wys_ueu_log' );
    }

    return is_array( $log ) ? $log : [];
}

function lp_email_wijzig_log_toevoegen( array $regel ) {
    $log = lp_email_wijzig_log();
    array_unshift( $log, $regel );
    update_option( LP_EMAIL_WIJZIG_LOG, array_slice( $log, 0, LP_EMAIL_WIJZIG_LOG_MAX ), false );
}

/**
 * Formulier verwerken: voorbeeld, uitvoeren of logboek wissen. Daarna terug naar de pagina,
 * zodat vernieuwen het formulier niet opnieuw verstuurt.
 */
add_action( 'admin_post_lp_email_wijzigen', function() {
    if ( ! lp_email_wijzig_mag() ) wp_die( esc_html__( 'Geen toegang.', 'mijn-ledenportaal' ), 403 );
    check_admin_referer( 'lp_email_wijzigen' );

    $stap  = sanitize_key( $_POST['lp_stap'] ?? '' );
    $tekst = wp_unslash( $_POST['lp_paren'] ?? '' );
    $flash = [];

    if ( $stap === 'log_wissen' ) {
        update_option( LP_EMAIL_WIJZIG_LOG, [], false );
    } elseif ( in_array( $stap, [ 'voorbeeld', 'uitvoeren' ], true ) ) {
        $uitvoeren = $stap === 'uitvoeren';
        if ( $uitvoeren ) {
            // Een lange lijst kan even duren; voorkom dat PHP halverwege afbreekt
            wp_raise_memory_limit( 'admin' );
            if ( function_exists( 'set_time_limit' ) ) set_time_limit( 0 );
        }

        $invoer = lp_email_wijzig_lees_invoer( $tekst );
        $flash  = [
            'stap'     => $stap,
            // Na uitvoeren het tekstvak leegmaken, zodat niemand per ongeluk twee keer uitvoert
            'tekst'    => $uitvoeren ? '' : $tekst,
            'uitkomst' => lp_email_wijzig_verwerk_alles( $invoer['paren'], $uitvoeren ),
            'fouten'   => $invoer['fouten'],
        ];
    }

    set_transient( 'lp_email_wijzig_' . get_current_user_id(), $flash, 5 * MINUTE_IN_SECONDS );
    wp_safe_redirect( admin_url( 'admin.php?page=lp-email-wijzigen' ) );
    exit;
} );

function lp_email_wijzig_status_label( $status ) {
    $labels = [
        'klaar'         => [ __( 'Klaar', 'mijn-ledenportaal' ), '#2271b1' ],
        'bijgewerkt'    => [ __( 'Bijgewerkt', 'mijn-ledenportaal' ), '#00a32a' ],
        'ongewijzigd'   => [ __( 'Al goed', 'mijn-ledenportaal' ), '#646970' ],
        'niet_gevonden' => [ __( 'Niet gevonden', 'mijn-ledenportaal' ), '#646970' ],
        'dubbel'        => [ __( 'Dubbel in lijst', 'mijn-ledenportaal' ), '#dba617' ],
        'eigen_account' => [ __( 'Eigen account', 'mijn-ledenportaal' ), '#dba617' ],
        'geen_rechten'  => [ __( 'Geen rechten', 'mijn-ledenportaal' ), '#d63638' ],
        'conflict'      => [ __( 'Conflict', 'mijn-ledenportaal' ), '#d63638' ],
        'fout'          => [ __( 'Fout', 'mijn-ledenportaal' ), '#d63638' ],
        // Alleen nog in het overgenomen logboek van de losse plugin
        'meerdere'      => [ __( 'Meerdere gebruikers', 'mijn-ledenportaal' ), '#dba617' ],
    ];
    [ $tekst, $kleur ] = $labels[ $status ] ?? [ $status, '#646970' ];
    return sprintf(
        '<span style="display:inline-block;padding:2px 8px;border-radius:3px;background:%s;color:#fff;font-size:11px;font-weight:600;">%s</span>',
        esc_attr( $kleur ),
        esc_html( $tekst )
    );
}

function lp_admin_email_wijzigen_pagina() {
    if ( ! lp_email_wijzig_mag() ) return;

    $sleutel = 'lp_email_wijzig_' . get_current_user_id();
    $flash   = get_transient( $sleutel ) ?: [];
    delete_transient( $sleutel );

    $uitkomst = $flash['uitkomst'] ?? [];
    $fouten   = $flash['fouten'] ?? [];
    $is_voorbeeld = ( $flash['stap'] ?? '' ) === 'voorbeeld';
    $aantal_klaar = count( array_filter( $uitkomst, fn( $r ) => $r['status'] === 'klaar' ) );
    $log = lp_email_wijzig_log();
    ?>
    <div class="wrap">
        <h1><?php esc_html_e( 'Ledenportaal — E-mailadressen wijzigen', 'mijn-ledenportaal' ); ?></h1>

        <?php if ( ( $flash['stap'] ?? '' ) === 'uitvoeren' ) :
            $bijgewerkt = count( array_filter( $uitkomst, fn( $r ) => $r['status'] === 'bijgewerkt' ) );
            $mislukt    = count( $uitkomst ) - $bijgewerkt;
            ?>
            <div class="notice notice-<?php echo $mislukt ? 'warning' : 'success'; ?> is-dismissible">
                <p><?php printf( esc_html__( '%1$d gebruiker(s) bijgewerkt, %2$d niet bijgewerkt.', 'mijn-ledenportaal' ), $bijgewerkt, $mislukt ); ?></p>
            </div>
        <?php endif; ?>

        <p><?php esc_html_e( 'Plak één wijziging per regel: oud@adres.nl => nieuw@adres.nl. Klik eerst op "Voorbeeld bekijken"; er wordt dan nog niets gewijzigd. Het e-mailadres en de gebruikersnaam worden allebei bijgewerkt en het lid wordt overal uitgelogd.', 'mijn-ledenportaal' ); ?></p>

        <form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
            <?php wp_nonce_field( 'lp_email_wijzigen' ); ?>
            <input type="hidden" name="action" value="lp_email_wijzigen">
            <input type="hidden" name="lp_stap" value="voorbeeld">
            <textarea name="lp_paren" rows="10" class="large-text code" placeholder="oud@voorbeeld.nl => nieuw@voorbeeld.nl"><?php echo esc_textarea( $flash['tekst'] ?? '' ); ?></textarea>
            <p class="description"><?php esc_html_e( 'Scheidingsteken: =>, ->, komma of spatie. Regels die beginnen met --, # of // worden overgeslagen. Het nieuwe adres wordt opgeslagen zoals je het typt, dus ook alleen hoofdletters aanpassen (jan@x.nl => Jan@x.nl) kan.', 'mijn-ledenportaal' ); ?></p>
            <p><button type="submit" class="button button-primary"><?php esc_html_e( 'Voorbeeld bekijken', 'mijn-ledenportaal' ); ?></button></p>
        </form>

        <?php if ( $fouten ) : ?>
            <div class="notice notice-warning inline">
                <p><strong><?php esc_html_e( 'Deze regels zijn overgeslagen:', 'mijn-ledenportaal' ); ?></strong></p>
                <ul style="margin-left: 16px; list-style: disc;">
                    <?php foreach ( $fouten as $fout ) : ?>
                        <li><?php printf( esc_html__( 'Regel %1$d: %2$s', 'mijn-ledenportaal' ), (int) $fout['regel'], esc_html( $fout['bericht'] ) ); ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>

        <?php if ( $uitkomst ) : ?>
            <h2><?php echo $is_voorbeeld ? esc_html__( 'Voorbeeld', 'mijn-ledenportaal' ) : esc_html__( 'Resultaat', 'mijn-ledenportaal' ); ?></h2>
            <table class="widefat striped">
                <thead>
                    <tr>
                        <th><?php esc_html_e( 'Oud', 'mijn-ledenportaal' ); ?></th>
                        <th><?php esc_html_e( 'Nieuw', 'mijn-ledenportaal' ); ?></th>
                        <th><?php esc_html_e( 'ID', 'mijn-ledenportaal' ); ?></th>
                        <th><?php esc_html_e( 'Status', 'mijn-ledenportaal' ); ?></th>
                        <th><?php esc_html_e( 'Toelichting', 'mijn-ledenportaal' ); ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ( $uitkomst as $r ) : ?>
                        <tr>
                            <td><code><?php echo esc_html( $r['oud'] ); ?></code></td>
                            <td><code><?php echo esc_html( $r['nieuw'] ); ?></code></td>
                            <td><?php echo $r['user_id'] ? '<a href="' . esc_url( get_edit_user_link( $r['user_id'] ) ) . '">' . (int) $r['user_id'] . '</a>' : '—'; ?></td>
                            <td><?php echo lp_email_wijzig_status_label( $r['status'] ); ?></td>
                            <td><?php echo esc_html( $r['bericht'] ); ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>

            <?php if ( $is_voorbeeld && $aantal_klaar ) : ?>
                <form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" style="margin-top: 16px;"
                      onsubmit="return confirm('<?php echo esc_js( sprintf( __( 'Weet je zeker dat je %d gebruiker(s) wilt bijwerken? Dit kan niet ongedaan worden gemaakt.', 'mijn-ledenportaal' ), $aantal_klaar ) ); ?>');">
                    <?php wp_nonce_field( 'lp_email_wijzigen' ); ?>
                    <input type="hidden" name="action" value="lp_email_wijzigen">
                    <input type="hidden" name="lp_stap" value="uitvoeren">
                    <input type="hidden" name="lp_paren" value="<?php echo esc_attr( $flash['tekst'] ?? '' ); ?>">
                    <button type="submit" class="button button-primary button-large">
                        <?php printf( esc_html__( 'Bevestigen en %d gebruiker(s) bijwerken', 'mijn-ledenportaal' ), $aantal_klaar ); ?>
                    </button>
                </form>
            <?php elseif ( $is_voorbeeld ) : ?>
                <p><em><?php esc_html_e( 'Er is niets om bij te werken.', 'mijn-ledenportaal' ); ?></em></p>
            <?php endif; ?>
        <?php endif; ?>

        <hr style="margin: 32px 0;">

        <h2><?php printf( esc_html__( 'Logboek (laatste %d wijzigingen)', 'mijn-ledenportaal' ), LP_EMAIL_WIJZIG_LOG_MAX ); ?></h2>
        <?php if ( ! $log ) : ?>
            <p><?php esc_html_e( 'Nog geen wijzigingen.', 'mijn-ledenportaal' ); ?></p>
        <?php else : ?>
            <table class="widefat striped">
                <thead>
                    <tr>
                        <th><?php esc_html_e( 'Tijdstip', 'mijn-ledenportaal' ); ?></th>
                        <th><?php esc_html_e( 'Door', 'mijn-ledenportaal' ); ?></th>
                        <th><?php esc_html_e( 'Oud', 'mijn-ledenportaal' ); ?></th>
                        <th><?php esc_html_e( 'Nieuw', 'mijn-ledenportaal' ); ?></th>
                        <th><?php esc_html_e( 'ID', 'mijn-ledenportaal' ); ?></th>
                        <th><?php esc_html_e( 'Status', 'mijn-ledenportaal' ); ?></th>
                        <th><?php esc_html_e( 'Toelichting', 'mijn-ledenportaal' ); ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ( $log as $r ) : ?>
                        <tr>
                            <td><?php echo esc_html( $r['tijd'] ?? '' ); ?></td>
                            <td><?php echo esc_html( $r['door'] ?? '' ); ?></td>
                            <td><code><?php echo esc_html( $r['oud'] ?? '' ); ?></code></td>
                            <td><code><?php echo esc_html( $r['nieuw'] ?? '' ); ?></code></td>
                            <td><?php echo (int) ( $r['user_id'] ?? 0 ) ?: '—'; ?></td>
                            <td><?php echo lp_email_wijzig_status_label( $r['status'] ?? '' ); ?></td>
                            <td><?php echo esc_html( $r['bericht'] ?? '' ); ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            <form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" style="margin-top: 12px;"
                  onsubmit="return confirm('<?php echo esc_js( __( 'Logboek helemaal wissen?', 'mijn-ledenportaal' ) ); ?>');">
                <?php wp_nonce_field( 'lp_email_wijzigen' ); ?>
                <input type="hidden" name="action" value="lp_email_wijzigen">
                <input type="hidden" name="lp_stap" value="log_wissen">
                <button type="submit" class="button"><?php esc_html_e( 'Logboek wissen', 'mijn-ledenportaal' ); ?></button>
            </form>
        <?php endif; ?>
    </div>
    <?php
}
