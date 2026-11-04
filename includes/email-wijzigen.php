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
 * Gebruikersnaam bij het nieuwe adres: zelfde opschoning als bij registratie
 * (lp_unieke_gebruikersnaam), maar de eigen huidige gebruikersnaam telt niet als bezet.
 */
function lp_email_wijzig_nieuwe_login( $nieuw, $user_id ) {
    $basis = substr( sanitize_user( $nieuw, true ), 0, 50 ) ?: 'lid';
    $login = $basis;
    for ( $i = 2; ( $eigenaar = username_exists( $login ) ) && (int) $eigenaar !== (int) $user_id; $i++ ) {
        $login = $basis . '-' . $i;
    }
    return $login;
}

/**
 * Controleer één paar en voer het uit als $uitvoeren true is.
 *
 * Statussen: klaar, bijgewerkt, ongewijzigd, niet_gevonden, meerdere, conflict, fout.
 */
function lp_email_wijzig_verwerk( array $paar, $uitvoeren = false ) {
    global $wpdb;

    $oud      = $paar['oud'];
    $nieuw    = $paar['nieuw'];
    $resultaat = [ 'oud' => $oud, 'nieuw' => $nieuw, 'user_id' => null, 'status' => '', 'bericht' => '' ];

    // Gebruiker zoeken op e-mailadres én op gebruikersnaam (oude accounts hebben het adres als gebruikersnaam)
    $id_via_login = (int) username_exists( $oud );
    $via_email    = get_user_by( 'email', $oud );
    $id_via_email = $via_email ? (int) $via_email->ID : 0;
    $ids          = array_values( array_unique( array_filter( [ $id_via_email, $id_via_login ] ) ) );

    if ( ! $ids ) {
        $resultaat['status']  = 'niet_gevonden';
        $resultaat['bericht'] = __( 'Geen gebruiker met dit e-mailadres of deze gebruikersnaam.', 'mijn-ledenportaal' );
        return $resultaat;
    }
    if ( count( $ids ) > 1 ) {
        $resultaat['status']  = 'meerdere';
        $resultaat['bericht'] = sprintf( __( 'Past bij meerdere gebruikers (ID %s). Niet gewijzigd; doe dit handmatig.', 'mijn-ledenportaal' ), implode( ', ', $ids ) );
        return $resultaat;
    }

    $user_id   = $ids[0];
    $gebruiker = get_userdata( $user_id );
    $resultaat['user_id'] = $user_id;

    if ( ! $gebruiker ) {
        $resultaat['status']  = 'fout';
        $resultaat['bericht'] = __( 'Gebruiker kon niet worden geladen.', 'mijn-ledenportaal' );
        return $resultaat;
    }

    // Is het nieuwe adres al van iemand anders?
    $eigenaar = get_user_by( 'email', $nieuw );
    if ( $eigenaar && (int) $eigenaar->ID !== $user_id ) {
        $resultaat['status']  = 'conflict';
        $resultaat['bericht'] = sprintf( __( 'Het nieuwe adres is al in gebruik bij gebruiker ID %d.', 'mijn-ledenportaal' ), (int) $eigenaar->ID );
        return $resultaat;
    }

    $nieuwe_login = lp_email_wijzig_nieuwe_login( $nieuw, $user_id );

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

    // wp_update_user() kan de gebruikersnaam niet wijzigen, dus rechtstreeks in de tabel
    $gelukt = $wpdb->update(
        $wpdb->users,
        [ 'user_login' => $nieuwe_login, 'user_email' => $nieuw ],
        [ 'ID' => $user_id ],
        [ '%s', '%s' ],
        [ '%d' ]
    );

    if ( $gelukt === false ) {
        $resultaat['status']  = 'fout';
        $resultaat['bericht'] = sprintf( __( 'Databasefout: %s', 'mijn-ledenportaal' ), $wpdb->last_error ?: 'onbekend' );
        return $resultaat;
    }

    // Cache wissen met het oude object, zodat ook de oude login/e-mail-sleutels verdwijnen
    clean_user_cache( $gebruiker );
    // Overal uitloggen: de inlogcookie hoort bij de oude gebruikersnaam
    WP_Session_Tokens::get_instance( $user_id )->destroy_all();

    do_action( 'lp_email_gewijzigd', $user_id, $gebruiker->user_email, $nieuw );

    $resultaat['status']  = 'bijgewerkt';
    $resultaat['bericht'] = sprintf( __( 'Bijgewerkt (gebruikersnaam %s). Het lid is overal uitgelogd.', 'mijn-ledenportaal' ), $nieuwe_login );
    return $resultaat;
}

function lp_email_wijzig_log() {
    // Bestaand logboek van de losse plugin overnemen zolang er nog geen eigen logboek is
    $log = get_option( LP_EMAIL_WIJZIG_LOG, null ) ?? get_option( 'wys_ueu_log', [] );
    return is_array( $log ) ? $log : [];
}

function lp_email_wijzig_log_toevoegen( array $regels ) {
    $log = array_merge( array_reverse( $regels ), lp_email_wijzig_log() );
    update_option( LP_EMAIL_WIJZIG_LOG, array_slice( $log, 0, LP_EMAIL_WIJZIG_LOG_MAX ), false );
}

/**
 * Formulier verwerken: voorbeeld, uitvoeren of logboek wissen. Daarna terug naar de pagina,
 * zodat vernieuwen het formulier niet opnieuw verstuurt.
 */
add_action( 'admin_post_lp_email_wijzigen', function() {
    if ( ! current_user_can( 'manage_options' ) ) wp_die( esc_html__( 'Geen toegang.', 'mijn-ledenportaal' ), 403 );
    check_admin_referer( 'lp_email_wijzigen' );

    $stap  = sanitize_key( $_POST['lp_stap'] ?? '' );
    $tekst = wp_unslash( $_POST['lp_paren'] ?? '' );
    $flash = [];

    if ( $stap === 'log_wissen' ) {
        update_option( LP_EMAIL_WIJZIG_LOG, [], false );
    } elseif ( in_array( $stap, [ 'voorbeeld', 'uitvoeren' ], true ) ) {
        $uitvoeren = $stap === 'uitvoeren';
        $invoer    = lp_email_wijzig_lees_invoer( $tekst );
        $uitkomst  = array_map( fn( $paar ) => lp_email_wijzig_verwerk( $paar, $uitvoeren ), $invoer['paren'] );

        if ( $uitvoeren ) {
            $door = wp_get_current_user();
            lp_email_wijzig_log_toevoegen( array_map( fn( $r ) => $r + [
                'tijd' => current_time( 'mysql' ),
                'door' => $door->user_login . ' (#' . $door->ID . ')',
            ], $uitkomst ) );
        }

        $flash = [
            'stap'     => $stap,
            // Na uitvoeren het tekstvak leegmaken, zodat niemand per ongeluk twee keer uitvoert
            'tekst'    => $uitvoeren ? '' : $tekst,
            'uitkomst' => $uitkomst,
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
        'meerdere'      => [ __( 'Meerdere gebruikers', 'mijn-ledenportaal' ), '#dba617' ],
        'conflict'      => [ __( 'Conflict', 'mijn-ledenportaal' ), '#d63638' ],
        'fout'          => [ __( 'Fout', 'mijn-ledenportaal' ), '#d63638' ],
        // Statussen uit het logboek van de losse plugin
        'updated'       => [ __( 'Bijgewerkt', 'mijn-ledenportaal' ), '#00a32a' ],
        'not_found'     => [ __( 'Niet gevonden', 'mijn-ledenportaal' ), '#646970' ],
        'ambiguous'     => [ __( 'Meerdere gebruikers', 'mijn-ledenportaal' ), '#dba617' ],
        'error'         => [ __( 'Fout', 'mijn-ledenportaal' ), '#d63638' ],
    ];
    [ $tekst, $kleur ] = $labels[ $status ] ?? [ $status, '#646970' ];
    return sprintf(
        '<span style="display:inline-block;padding:2px 8px;border-radius:3px;background:%s;color:#fff;font-size:11px;font-weight:600;">%s</span>',
        esc_attr( $kleur ),
        esc_html( $tekst )
    );
}

function lp_admin_email_wijzigen_pagina() {
    if ( ! current_user_can( 'manage_options' ) ) return;

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
                            <?php // Oude regels uit de losse plugin gebruiken Engelse sleutels ?>
                            <td><?php echo esc_html( $r['tijd'] ?? $r['time'] ?? '' ); ?></td>
                            <td><?php echo esc_html( $r['door'] ?? $r['actor'] ?? '' ); ?></td>
                            <td><code><?php echo esc_html( $r['oud'] ?? $r['old'] ?? '' ); ?></code></td>
                            <td><code><?php echo esc_html( $r['nieuw'] ?? $r['new'] ?? '' ); ?></code></td>
                            <td><?php echo (int) ( $r['user_id'] ?? 0 ) ?: '—'; ?></td>
                            <td><?php echo lp_email_wijzig_status_label( $r['status'] ?? '' ); ?></td>
                            <td><?php echo esc_html( $r['bericht'] ?? $r['message'] ?? '' ); ?></td>
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
