<?php
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * LP-velden die gemigreerd kunnen worden vanuit UM
 */
function lp_migratie_velden() {
    return [
        'lp_account_status'      => __( 'Accountstatus', 'mijn-ledenportaal' ),
        'lp_geslacht'            => __( 'Geslacht', 'mijn-ledenportaal' ),
        'lp_geboortedatum'       => __( 'Geboortedatum', 'mijn-ledenportaal' ),
        'lp_telefoonnummer'      => __( 'Telefoonnummer', 'mijn-ledenportaal' ),
        'lp_mobiel'              => __( 'Mobiel', 'mijn-ledenportaal' ),
        'lp_straatnaam'          => __( 'Straatnaam', 'mijn-ledenportaal' ),
        'lp_huisnummer'          => __( 'Huisnummer', 'mijn-ledenportaal' ),
        'lp_huisnummer_toevoeging' => __( 'Huisnummer toevoeging', 'mijn-ledenportaal' ),
        'lp_postcode'            => __( 'Postcode', 'mijn-ledenportaal' ),
        'lp_plaats'              => __( 'Plaats', 'mijn-ledenportaal' ),
        'lp_land'                => __( 'Land', 'mijn-ledenportaal' ),
        'lp_afdeling'            => __( 'Afdeling', 'mijn-ledenportaal' ),
        'lp_soort_pensioen'      => __( 'Soort pensioen', 'mijn-ledenportaal' ),
        'lp_verenigingsfunctie'  => __( 'Verenigingsfunctie', 'mijn-ledenportaal' ),
        'lp_iban'                => __( 'IBAN', 'mijn-ledenportaal' ),
        'lp_iban_ten_name_van'   => __( 'IBAN ten name van', 'mijn-ledenportaal' ),
        'lp_incasso_toestemming' => __( 'Incasso toestemming', 'mijn-ledenportaal' ),
    ];
}

/**
 * Bekende standaardkoppeling op basis van de actuele UM-veldnamen
 */
function lp_migratie_standaard_mapping() {
    return [
        'lp_account_status'        => 'account_status',
        'lp_geslacht'              => 'gender_custom',
        'lp_geboortedatum'         => 'birth_date',
        'lp_telefoonnummer'        => 'phone_number',
        'lp_mobiel'                => 'mobile_number',
        'lp_straatnaam'            => 'straatnaam',
        'lp_huisnummer'            => 'huisnummer',
        'lp_huisnummer_toevoeging' => 'toevoeging_huisnummer',
        'lp_postcode'              => 'postcode',
        'lp_plaats'                => 'plaats',
        'lp_land'                  => 'land',
        'lp_afdeling'              => 'laatste_afdeling',
        'lp_soort_pensioen'        => 'soort_pensioen',
        'lp_verenigingsfunctie'    => 'inzetbaar_vereniging',
        'lp_iban'                  => 'iban',
        'lp_iban_ten_name_van'     => 'ten_name_van',
        'lp_incasso_toestemming'   => 'toestemming_incasso_checkbox',
    ];
}

/**
 * Status-waarden omzetten van UM naar LP
 */
function lp_migratie_status( $um_status ) {
    $map = [
        'approved'                    => 'approved',
        'awaiting_admin_review'       => 'pending',
        'awaiting_email_confirmation' => 'pending',
        'rejected'                    => 'rejected',
        'inactive'                    => 'rejected',
    ];
    return $map[ $um_status ] ?? 'pending';
}

/**
 * Haal alle gebruikers op die UM-data hebben (account_status meta key)
 */
function lp_migratie_um_gebruikers() {
    return get_users( [
        'meta_key'     => 'account_status',
        'meta_compare' => 'EXISTS',
        'number'       => -1,
        'fields'       => 'all',
    ] );
}

/**
 * Verzamel alle unieke meta-sleutels van UM-gebruikers
 */
function lp_migratie_detecteer_sleutels() {
    global $wpdb;

    $gebruikers = lp_migratie_um_gebruikers();
    if ( empty( $gebruikers ) ) return [];

    $ids = array_map( fn( $u ) => $u->ID, $gebruikers );
    $placeholders = implode( ',', array_fill( 0, count( $ids ), '%d' ) );

    // Systeemsleutels die we buiten beschouwing laten
    $skip = [
        'session_tokens', 'wp_capabilities', 'wp_user_level', 'wp_dashboard_quick_press_last_post_id',
        'dismissed_wp_pointers', 'show_welcome_panel', 'closedpostboxes_dashboard',
        'metaboxhidden_dashboard', 'community-events-location', 'show_admin_bar_front',
        'wp_user-settings', 'wp_user-settings-time', 'wp_media_library_mode',
        'managenav-menuscolumnshidden', 'nav_menu_recently_edited',
    ];

    $skip_like = array_map( fn( $s ) => "meta_key NOT LIKE '%" . esc_sql( $s ) . "%'", $skip );

    $query = $wpdb->prepare(
        "SELECT DISTINCT meta_key FROM {$wpdb->usermeta}
         WHERE user_id IN ($placeholders)
         AND meta_key NOT LIKE 'wp_%'
         AND meta_key NOT LIKE '_um_%'
         AND meta_key NOT LIKE 'lp_%'
         ORDER BY meta_key",
        ...$ids
    );

    return $wpdb->get_col( $query );
}

/**
 * Admin submenu registratie
 */
add_action( 'admin_menu', function() {
    add_submenu_page(
        'ledenportaal',
        __( 'Migratie van Ultimate Member', 'mijn-ledenportaal' ),
        __( 'Migratie', 'mijn-ledenportaal' ),
        'manage_options',
        'lp-migratie',
        'lp_admin_migratie_pagina'
    );
} );

/**
 * Admin pagina: Migratie
 */
function lp_admin_migratie_pagina() {
    if ( ! current_user_can( 'manage_options' ) ) return;

    $stap        = sanitize_key( $_GET['stap'] ?? '1' );
    $basis_url   = admin_url( 'admin.php?page=lp-migratie' );
    $lp_velden   = lp_migratie_velden();
    $um_sleutels = lp_migratie_detecteer_sleutels();
    // Gebruik de standaardkoppeling als er nog niets opgeslagen is
    $mapping     = get_option( 'lp_migratie_mapping', lp_migratie_standaard_mapping() );
    $melding     = '';
    $melding_type = 'success';

    // --- Stap 1: Koppeling opslaan ---
    if ( $stap === '1' && isset( $_POST['lp_migratie_mapping_submit'] ) ) {
        check_admin_referer( 'lp_migratie_mapping' );
        $nieuw = [];
        foreach ( $lp_velden as $lp_sleutel => $_ ) {
            $um_val = sanitize_key( $_POST['mapping'][ $lp_sleutel ] ?? '' );
            if ( $um_val !== '' ) {
                $nieuw[ $lp_sleutel ] = $um_val;
            }
        }
        update_option( 'lp_migratie_mapping', $nieuw );
        $mapping = $nieuw;
        $melding = __( 'Koppeling opgeslagen.', 'mijn-ledenportaal' );
    }

    // --- Stap 2: CSV exporteren ---
    if ( $stap === '2' && isset( $_POST['lp_migratie_export_submit'] ) ) {
        check_admin_referer( 'lp_migratie_export' );
        lp_migratie_export_csv( $mapping );
        exit;
    }

    // --- Stap 3: CSV importeren ---
    $import_resultaat = null;
    if ( $stap === '3' && isset( $_POST['lp_migratie_import_submit'] ) ) {
        check_admin_referer( 'lp_migratie_import' );
        $import_resultaat = lp_migratie_import_csv();
        if ( is_wp_error( $import_resultaat ) ) {
            $melding      = $import_resultaat->get_error_message();
            $melding_type = 'error';
        }
    }

    $um_count = count( lp_migratie_um_gebruikers() );
    ?>
    <div class="wrap">
        <h1><?php esc_html_e( 'Ledenportaal — Migratie vanuit Ultimate Member', 'mijn-ledenportaal' ); ?></h1>

        <?php if ( $melding ) : ?>
            <div class="notice notice-<?php echo esc_attr( $melding_type ); ?> is-dismissible"><p><?php echo esc_html( $melding ); ?></p></div>
        <?php endif; ?>

        <?php if ( $um_count === 0 ) : ?>
            <div class="notice notice-warning"><p><?php esc_html_e( 'Geen gebruikers gevonden met Ultimate Member-data (geen account_status meta-sleutel). Zorg dat deze plugin op de live site staat waar UM actief is.', 'mijn-ledenportaal' ); ?></p></div>
        <?php else : ?>
            <p><?php printf( esc_html__( '%d gebruiker(s) met Ultimate Member-data gevonden.', 'mijn-ledenportaal' ), $um_count ); ?></p>
        <?php endif; ?>

        <!-- Stap-navigatie -->
        <nav style="display: flex; gap: 0; margin: 20px 0; border-bottom: 2px solid #c3c4c7;">
            <?php foreach ( [ '1' => 'Stap 1 — Koppel velden', '2' => 'Stap 2 — Exporteer CSV', '3' => 'Stap 3 — Importeer CSV' ] as $nr => $titel ) :
                $actief = $stap === $nr;
            ?>
                <a href="<?php echo esc_url( add_query_arg( 'stap', $nr, $basis_url ) ); ?>"
                   style="padding: 10px 20px; font-weight: <?php echo $actief ? 'bold' : 'normal'; ?>; border-bottom: <?php echo $actief ? '3px solid #0091D5; margin-bottom: -2px;' : 'none;'; ?> text-decoration: none; color: <?php echo $actief ? '#0091D5' : '#3c434a'; ?>;">
                    <?php echo esc_html( $titel ); ?>
                </a>
            <?php endforeach; ?>
        </nav>

        <?php if ( $stap === '1' ) : ?>
        <!-- ===== STAP 1: KOPPELING ===== -->
        <p><?php esc_html_e( 'Koppel hier de Ultimate Member-veldnamen aan de velden van het Ledenportaal. De sleutels zijn automatisch gedetecteerd uit de database.', 'mijn-ledenportaal' ); ?></p>

        <?php if ( empty( $um_sleutels ) && $um_count > 0 ) : ?>
            <p style="color: #666;"><em><?php esc_html_e( 'Geen extra meta-sleutels gevonden naast de standaardvelden.', 'mijn-ledenportaal' ); ?></em></p>
        <?php endif; ?>

        <form method="post">
            <?php wp_nonce_field( 'lp_migratie_mapping' ); ?>
            <input type="hidden" name="lp_migratie_mapping_submit" value="1">

            <table class="wp-list-table widefat fixed striped" style="margin-top: 12px;">
                <thead>
                    <tr>
                        <th style="width: 35%;"><?php esc_html_e( 'Ledenportaal-veld', 'mijn-ledenportaal' ); ?></th>
                        <th><?php esc_html_e( 'Ultimate Member-sleutel', 'mijn-ledenportaal' ); ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ( $lp_velden as $lp_sleutel => $label ) :
                        $huidige = $mapping[ $lp_sleutel ] ?? '';
                    ?>
                    <tr>
                        <td>
                            <strong><?php echo esc_html( $label ); ?></strong><br>
                            <code style="font-size: 11px; color: #666;"><?php echo esc_html( $lp_sleutel ); ?></code>
                        </td>
                        <td>
                            <select name="mapping[<?php echo esc_attr( $lp_sleutel ); ?>]" style="min-width: 220px;">
                                <option value=""><?php esc_html_e( '— niet koppelen —', 'mijn-ledenportaal' ); ?></option>
                                <?php foreach ( $um_sleutels as $um_sleutel ) : ?>
                                    <option value="<?php echo esc_attr( $um_sleutel ); ?>" <?php selected( $huidige, $um_sleutel ); ?>>
                                        <?php echo esc_html( $um_sleutel ); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>

            <p style="margin-top: 16px;">
                <button type="submit" class="button button-primary"><?php esc_html_e( 'Koppeling opslaan', 'mijn-ledenportaal' ); ?></button>
                <?php if ( ! empty( $mapping ) ) : ?>
                    <a href="<?php echo esc_url( add_query_arg( 'stap', '2', $basis_url ) ); ?>" class="button" style="margin-left: 8px;">
                        <?php esc_html_e( 'Doorgaan naar Stap 2 →', 'mijn-ledenportaal' ); ?>
                    </a>
                <?php endif; ?>
            </p>
        </form>

        <?php elseif ( $stap === '2' ) : ?>
        <!-- ===== STAP 2: EXPORT ===== -->
        <p><?php esc_html_e( 'Genereer een CSV-bestand op basis van de ingestelde koppeling. Controleer de data in Excel vóór je importeert.', 'mijn-ledenportaal' ); ?></p>

        <?php if ( empty( $mapping ) ) : ?>
            <div class="notice notice-warning"><p>
                <?php esc_html_e( 'Stel eerst een koppeling in bij Stap 1.', 'mijn-ledenportaal' ); ?>
                <a href="<?php echo esc_url( add_query_arg( 'stap', '1', $basis_url ) ); ?>"><?php esc_html_e( 'Ga naar Stap 1', 'mijn-ledenportaal' ); ?></a>
            </p></div>
        <?php else : ?>
            <p><?php esc_html_e( 'Gekoppelde velden:', 'mijn-ledenportaal' ); ?></p>
            <ul style="list-style: disc; margin-left: 20px;">
                <?php foreach ( $mapping as $lp => $um ) : ?>
                    <li><code><?php echo esc_html( $um ); ?></code> → <code><?php echo esc_html( $lp ); ?></code></li>
                <?php endforeach; ?>
            </ul>

            <form method="post" style="margin-top: 16px;">
                <?php wp_nonce_field( 'lp_migratie_export' ); ?>
                <input type="hidden" name="lp_migratie_export_submit" value="1">
                <button type="submit" class="button button-primary">
                    <?php esc_html_e( 'CSV downloaden', 'mijn-ledenportaal' ); ?>
                </button>
                <a href="<?php echo esc_url( add_query_arg( 'stap', '3', $basis_url ) ); ?>" class="button" style="margin-left: 8px;">
                    <?php esc_html_e( 'Doorgaan naar Stap 3 →', 'mijn-ledenportaal' ); ?>
                </a>
            </form>
        <?php endif; ?>

        <?php elseif ( $stap === '3' ) : ?>
        <!-- ===== STAP 3: IMPORT ===== -->
        <?php if ( is_array( $import_resultaat ) ) : ?>
            <div class="notice notice-success">
                <p><strong><?php esc_html_e( 'Import voltooid!', 'mijn-ledenportaal' ); ?></strong></p>
                <ul style="list-style: disc; margin-left: 20px;">
                    <li><?php printf( esc_html__( '%d leden bijgewerkt', 'mijn-ledenportaal' ), $import_resultaat['bijgewerkt'] ); ?></li>
                    <li><?php printf( esc_html__( '%d rijen overgeslagen (ongeldige user_id)', 'mijn-ledenportaal' ), $import_resultaat['overgeslagen'] ); ?></li>
                </ul>
            </div>
        <?php endif; ?>

        <p><?php esc_html_e( 'Upload de (gecontroleerde) CSV om de data te importeren in het Ledenportaal. Bestaande lp_*-velden worden overschreven.', 'mijn-ledenportaal' ); ?></p>
        <p><strong style="color: #d9534f;"><?php esc_html_e( 'Let op: maak eerst een database-backup voordat je importeert.', 'mijn-ledenportaal' ); ?></strong></p>

        <form method="post" enctype="multipart/form-data" style="margin-top: 16px;">
            <?php wp_nonce_field( 'lp_migratie_import' ); ?>
            <input type="hidden" name="lp_migratie_import_submit" value="1">

            <table class="form-table" style="margin-top: 0;">
                <tr>
                    <th><label for="lp_migratie_csv"><?php esc_html_e( 'CSV-bestand', 'mijn-ledenportaal' ); ?></label></th>
                    <td>
                        <input type="file" name="lp_migratie_csv" id="lp_migratie_csv" accept=".csv">
                        <p class="description"><?php esc_html_e( 'Gebruik de CSV die je in Stap 2 hebt gedownload.', 'mijn-ledenportaal' ); ?></p>
                    </td>
                </tr>
            </table>

            <button type="submit" class="button button-primary"
                onclick="return confirm('<?php esc_attr_e( 'Weet je zeker dat je de import wilt starten? Bestaande LP-velden worden overschreven.', 'mijn-ledenportaal' ); ?>')">
                <?php esc_html_e( 'Import starten', 'mijn-ledenportaal' ); ?>
            </button>
        </form>
        <?php endif; ?>

    </div>
    <?php
}

/**
 * Vertaal een UM-waarde naar de juiste LP-waarde.
 *
 * UM slaat keuzevelden op als geserialiseerde PHP-arrays met leesbare labels,
 * bijv. a:1:{i:0;s:21:"Communicatiecommissie";}
 * WordPress deserialiseert dit automatisch bij get_user_meta().
 * Deze functie zet de labels terug naar de LP-sleutels.
 */
function lp_migratie_vertaal_waarde( $lp_sleutel, $waarde ) {
    // Normaliseer: als array, gebruik de waarden
    $waarden = is_array( $waarde ) ? array_values( $waarde ) : [ (string) $waarde ];
    // Verwijder lege elementen
    $waarden = array_filter( $waarden, fn( $v ) => $v !== '' );

    switch ( $lp_sleutel ) {

        case 'lp_account_status':
            return lp_migratie_status( (string) ( $waarden[0] ?? '' ) );

        case 'lp_geslacht':
            // Reverse lookup: label → sleutel
            $omgekeerd = array_flip( lp_geslacht_opties() );
            return $omgekeerd[ $waarden[0] ?? '' ] ?? strtolower( $waarden[0] ?? '' );

        case 'lp_afdeling':
            $omgekeerd = array_flip( lp_afdeling_opties() );
            return $omgekeerd[ $waarden[0] ?? '' ] ?? '';

        case 'lp_soort_pensioen':
            $omgekeerd = array_flip( lp_pensioen_opties() );
            return $omgekeerd[ $waarden[0] ?? '' ] ?? '';

        case 'lp_verenigingsfunctie':
            // Meerdere waarden mogelijk; reverse lookup per label
            $omgekeerd = array_flip( lp_functie_opties() );
            $sleutels  = [];
            foreach ( $waarden as $label ) {
                $sleutel = $omgekeerd[ $label ] ?? null;
                if ( $sleutel ) $sleutels[] = $sleutel;
            }
            return implode( ',', $sleutels );

        case 'lp_incasso_toestemming':
            // Elke niet-lege waarde = toestemming gegeven
            return ! empty( $waarden ) ? '1' : '';

        default:
            // Enkelvoudige waarde: eerste element
            return (string) ( $waarden[0] ?? '' );
    }
}

/**
 * Genereer en stuur CSV naar browser
 */
function lp_migratie_export_csv( array $mapping ) {
    $gebruikers = lp_migratie_um_gebruikers();
    $lp_velden  = array_keys( $mapping );

    header( 'Content-Type: text/csv; charset=UTF-8' );
    header( 'Content-Disposition: attachment; filename="um-migratie-' . date( 'Y-m-d' ) . '.csv"' );
    header( 'Pragma: no-cache' );

    $out = fopen( 'php://output', 'w' );
    fputs( $out, "\xEF\xBB\xBF" ); // BOM voor Excel UTF-8

    fputcsv( $out, array_merge( [ 'user_id', 'user_login', 'display_name' ], $lp_velden ) );

    foreach ( $gebruikers as $gebruiker ) {
        $rij = [ $gebruiker->ID, $gebruiker->user_login, $gebruiker->display_name ];

        foreach ( $mapping as $lp_sleutel => $um_sleutel ) {
            // get_user_meta deserialiseert automatisch geserialiseerde arrays
            $raw    = get_user_meta( $gebruiker->ID, $um_sleutel, true );
            $rij[]  = lp_migratie_vertaal_waarde( $lp_sleutel, $raw );
        }

        fputcsv( $out, $rij );
    }

    fclose( $out );
}

/**
 * Verwerk geüpload CSV-bestand
 */
function lp_migratie_import_csv() {
    if ( empty( $_FILES['lp_migratie_csv']['tmp_name'] ) ) {
        return new WP_Error( 'geen_bestand', __( 'Geen bestand geüpload.', 'mijn-ledenportaal' ) );
    }

    $pad = $_FILES['lp_migratie_csv']['tmp_name'];
    $fh  = fopen( $pad, 'r' );
    if ( ! $fh ) {
        return new WP_Error( 'leesfout', __( 'Kon het bestand niet lezen.', 'mijn-ledenportaal' ) );
    }

    // BOM verwijderen als aanwezig
    $bom = fread( $fh, 3 );
    if ( $bom !== "\xEF\xBB\xBF" ) {
        rewind( $fh );
    }

    $koppen = fgetcsv( $fh );
    if ( ! $koppen ) {
        fclose( $fh );
        return new WP_Error( 'leeg_bestand', __( 'Het bestand is leeg of onleesbaar.', 'mijn-ledenportaal' ) );
    }

    $koppen      = array_map( 'trim', $koppen );
    $lp_velden   = lp_migratie_velden();
    $bijgewerkt  = 0;
    $overgeslagen = 0;

    while ( ( $rij = fgetcsv( $fh ) ) !== false ) {
        $data = array_combine( $koppen, $rij );
        if ( ! $data ) continue;

        $user_id = absint( $data['user_id'] ?? 0 );
        if ( ! $user_id || ! get_userdata( $user_id ) ) {
            $overgeslagen++;
            continue;
        }

        foreach ( $lp_velden as $lp_sleutel => $_ ) {
            if ( ! isset( $data[ $lp_sleutel ] ) ) continue;
            $waarde = sanitize_text_field( $data[ $lp_sleutel ] );

            if ( $lp_sleutel === 'lp_verenigingsfunctie' ) {
                // Sla op als meerdere rijen
                delete_user_meta( $user_id, 'lp_verenigingsfunctie' );
                if ( $waarde !== '' ) {
                    $functies      = array_map( 'sanitize_key', explode( ',', $waarde ) );
                    $geldige       = array_keys( lp_functie_opties() );
                    foreach ( $functies as $functie ) {
                        if ( in_array( trim( $functie ), $geldige, true ) ) {
                            add_user_meta( $user_id, 'lp_verenigingsfunctie', trim( $functie ) );
                        }
                    }
                }
            } else {
                update_user_meta( $user_id, $lp_sleutel, $waarde );
            }
        }

        $bijgewerkt++;
    }

    fclose( $fh );

    return [ 'bijgewerkt' => $bijgewerkt, 'overgeslagen' => $overgeslagen ];
}
