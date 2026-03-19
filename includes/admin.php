<?php
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Toon lp_ velden op de gebruikersprofiel pagina in de admin
 */
add_action( 'show_user_profile', 'lp_toon_profiel_velden' );
add_action( 'edit_user_profile', 'lp_toon_profiel_velden' );

function lp_toon_profiel_velden( $user ) {
    $meta = [
        'account_status'        => get_user_meta( $user->ID, 'lp_account_status', true ),
        'geslacht'              => get_user_meta( $user->ID, 'lp_geslacht', true ),
        'geboortedatum'         => get_user_meta( $user->ID, 'lp_geboortedatum', true ),
        'telefoonnummer'        => get_user_meta( $user->ID, 'lp_telefoonnummer', true ),
        'mobiel'                => get_user_meta( $user->ID, 'lp_mobiel', true ),
        'straatnaam'            => get_user_meta( $user->ID, 'lp_straatnaam', true ),
        'huisnummer'            => get_user_meta( $user->ID, 'lp_huisnummer', true ),
        'huisnummer_toevoeging' => get_user_meta( $user->ID, 'lp_huisnummer_toevoeging', true ),
        'postcode'              => get_user_meta( $user->ID, 'lp_postcode', true ),
        'plaats'                => get_user_meta( $user->ID, 'lp_plaats', true ),
        'land'                  => get_user_meta( $user->ID, 'lp_land', true ),
        'afdeling'              => get_user_meta( $user->ID, 'lp_afdeling', true ),
        'soort_pensioen'        => get_user_meta( $user->ID, 'lp_soort_pensioen', true ),
        'verenigingsfunctie'         => get_user_meta( $user->ID, 'lp_verenigingsfunctie' ),
        'iban'                       => get_user_meta( $user->ID, 'lp_iban', true ),
        'iban_ten_name_van'          => get_user_meta( $user->ID, 'lp_iban_ten_name_van', true ),
        'incasso_toestemming'        => get_user_meta( $user->ID, 'lp_incasso_toestemming', true ),
        'incasso_toestemming_datum'  => get_user_meta( $user->ID, 'lp_incasso_toestemming_datum', true ),
        'account_gewijzigd'          => get_user_meta( $user->ID, 'lp_account_gewijzigd', true ),
    ];

    $status_labels = [
        'pending'  => __( 'In afwachting', 'mijn-ledenportaal' ),
        'approved' => __( 'Goedgekeurd', 'mijn-ledenportaal' ),
        'rejected' => __( 'Afgewezen', 'mijn-ledenportaal' ),
    ];
    $status_kleuren = [
        'pending'  => '#f0ad4e',
        'approved' => '#5cb85c',
        'rejected' => '#d9534f',
    ];
    $status        = $meta['account_status'];
    $status_label  = $status_labels[ $status ] ?? esc_html( $status );
    $status_kleur  = $status_kleuren[ $status ] ?? '#999';
    ?>
    <h2><?php esc_html_e( 'Ledenportaal', 'mijn-ledenportaal' ); ?></h2>
    <?php wp_nonce_field( 'lp_opslaan_profiel_' . $user->ID, 'lp_profiel_nonce' ); ?>
    <table class="form-table">

        <tr>
            <th><label><?php esc_html_e( 'Accountstatus', 'mijn-ledenportaal' ); ?></label></th>
            <td>
                <select name="lp_account_status">
                    <option value=""><?php esc_html_e( '— Geen —', 'mijn-ledenportaal' ); ?></option>
                    <?php foreach ( $status_labels as $waarde => $label ) : ?>
                        <option value="<?php echo esc_attr( $waarde ); ?>" <?php selected( $status, $waarde ); ?>>
                            <?php echo esc_html( $label ); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <?php if ( $status ) : ?>
                    <span style="margin-left:8px; background:<?php echo esc_attr( $status_kleur ); ?>; color:#fff; padding:2px 8px; border-radius:3px; font-size:12px;">
                        <?php echo esc_html( $status_label ); ?>
                    </span>
                <?php endif; ?>
            </td>
        </tr>

        <tr>
            <th><label for="lp_geslacht"><?php esc_html_e( 'Geslacht', 'mijn-ledenportaal' ); ?></label></th>
            <td>
                <select name="lp_geslacht" id="lp_geslacht">
                    <option value=""><?php esc_html_e( '— Geen —', 'mijn-ledenportaal' ); ?></option>
                    <?php foreach ( lp_geslacht_opties() as $waarde => $label ) : ?>
                        <option value="<?php echo esc_attr( $waarde ); ?>" <?php selected( $meta['geslacht'], $waarde ); ?>>
                            <?php echo esc_html( $label ); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </td>
        </tr>

        <tr>
            <th><label for="lp_geboortedatum"><?php esc_html_e( 'Geboortedatum', 'mijn-ledenportaal' ); ?></label></th>
            <td><input type="date" name="lp_geboortedatum" id="lp_geboortedatum" value="<?php echo esc_attr( $meta['geboortedatum'] ); ?>" class="regular-text"></td>
        </tr>

        <tr>
            <th><label for="lp_telefoonnummer"><?php esc_html_e( 'Telefoonnummer', 'mijn-ledenportaal' ); ?></label></th>
            <td><input type="text" name="lp_telefoonnummer" id="lp_telefoonnummer" value="<?php echo esc_attr( $meta['telefoonnummer'] ); ?>" class="regular-text"></td>
        </tr>

        <tr>
            <th><label for="lp_mobiel"><?php esc_html_e( 'Mobiel', 'mijn-ledenportaal' ); ?></label></th>
            <td><input type="text" name="lp_mobiel" id="lp_mobiel" value="<?php echo esc_attr( $meta['mobiel'] ); ?>" class="regular-text"></td>
        </tr>

        <tr>
            <th><?php esc_html_e( 'Adres', 'mijn-ledenportaal' ); ?></th>
            <td>
                <input type="text" name="lp_straatnaam" placeholder="<?php esc_attr_e( 'Straat', 'mijn-ledenportaal' ); ?>"
                    value="<?php echo esc_attr( $meta['straatnaam'] ); ?>" style="width:200px">
                <input type="text" name="lp_huisnummer" placeholder="<?php esc_attr_e( 'Nr.', 'mijn-ledenportaal' ); ?>"
                    value="<?php echo esc_attr( $meta['huisnummer'] ); ?>" style="width:60px">
                <input type="text" name="lp_huisnummer_toevoeging" placeholder="<?php esc_attr_e( 'Toev.', 'mijn-ledenportaal' ); ?>"
                    value="<?php echo esc_attr( $meta['huisnummer_toevoeging'] ); ?>" style="width:60px">
                <br style="margin-bottom:6px">
                <input type="text" name="lp_postcode" placeholder="<?php esc_attr_e( 'Postcode', 'mijn-ledenportaal' ); ?>"
                    value="<?php echo esc_attr( $meta['postcode'] ); ?>" style="width:100px">
                <input type="text" name="lp_plaats" placeholder="<?php esc_attr_e( 'Plaats', 'mijn-ledenportaal' ); ?>"
                    value="<?php echo esc_attr( $meta['plaats'] ); ?>" style="width:160px">
                <br style="margin-bottom:6px">
                <select name="lp_land">
                    <?php foreach ( lp_land_opties() as $code => $naam ) : ?>
                        <option value="<?php echo esc_attr( $code ); ?>" <?php selected( $meta['land'] ?: 'NL', $code ); ?>>
                            <?php echo esc_html( $naam ); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </td>
        </tr>

        <tr>
            <th><label for="lp_afdeling"><?php esc_html_e( 'Afdeling', 'mijn-ledenportaal' ); ?></label></th>
            <td>
                <select name="lp_afdeling" id="lp_afdeling">
                    <option value=""><?php esc_html_e( '— Geen —', 'mijn-ledenportaal' ); ?></option>
                    <?php foreach ( lp_afdeling_opties() as $waarde => $label ) : ?>
                        <option value="<?php echo esc_attr( $waarde ); ?>" <?php selected( $meta['afdeling'], $waarde ); ?>>
                            <?php echo esc_html( $label ); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </td>
        </tr>

        <tr>
            <th><label for="lp_soort_pensioen"><?php esc_html_e( 'Soort pensioen', 'mijn-ledenportaal' ); ?></label></th>
            <td>
                <select name="lp_soort_pensioen" id="lp_soort_pensioen">
                    <option value=""><?php esc_html_e( '— Geen —', 'mijn-ledenportaal' ); ?></option>
                    <?php foreach ( lp_pensioen_opties() as $waarde => $label ) : ?>
                        <option value="<?php echo esc_attr( $waarde ); ?>" <?php selected( $meta['soort_pensioen'], $waarde ); ?>>
                            <?php echo esc_html( $label ); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </td>
        </tr>

        <tr>
            <th><?php esc_html_e( 'Verenigingsfunctie', 'mijn-ledenportaal' ); ?></th>
            <td>
                <?php foreach ( lp_functie_opties() as $waarde => $label ) : ?>
                    <label style="display:block; margin-bottom:4px;">
                        <input type="checkbox" name="lp_verenigingsfunctie[]"
                            value="<?php echo esc_attr( $waarde ); ?>"
                            <?php checked( in_array( $waarde, (array) $meta['verenigingsfunctie'], true ) ); ?>>
                        <?php echo esc_html( $label ); ?>
                    </label>
                <?php endforeach; ?>
            </td>
        </tr>

        <tr>
            <th><label for="lp_iban"><?php esc_html_e( 'IBAN', 'mijn-ledenportaal' ); ?></label></th>
            <td><input type="text" name="lp_iban" id="lp_iban" value="<?php echo esc_attr( $meta['iban'] ); ?>" class="regular-text"></td>
        </tr>

        <tr>
            <th><label for="lp_iban_ten_name_van"><?php esc_html_e( 'IBAN ten name van', 'mijn-ledenportaal' ); ?></label></th>
            <td><input type="text" name="lp_iban_ten_name_van" id="lp_iban_ten_name_van" value="<?php echo esc_attr( $meta['iban_ten_name_van'] ); ?>" class="regular-text"></td>
        </tr>

        <tr>
            <th><?php esc_html_e( 'Incasso toestemming', 'mijn-ledenportaal' ); ?></th>
            <td>
                <label>
                    <input type="checkbox" name="lp_incasso_toestemming" value="1" <?php checked( $meta['incasso_toestemming'], '1' ); ?>>
                    <?php esc_html_e( 'Toestemming gegeven', 'mijn-ledenportaal' ); ?>
                </label>
                <?php if ( $meta['incasso_toestemming_datum'] ) : ?>
                    <span style="margin-left:8px; color:#666; font-size:12px;">
                        <?php echo esc_html( date_i18n( 'd-m-Y', strtotime( $meta['incasso_toestemming_datum'] ) ) ); ?>
                    </span>
                <?php endif; ?>
            </td>
        </tr>

        <?php if ( $meta['account_gewijzigd'] ) : ?>
        <tr>
            <th><?php esc_html_e( 'Laatste wijziging', 'mijn-ledenportaal' ); ?></th>
            <td style="color:#666; padding-top:9px;">
                <?php echo esc_html( date_i18n( 'd-m-Y H:i', strtotime( $meta['account_gewijzigd'] ) ) ); ?>
            </td>
        </tr>
        <?php endif; ?>

    </table>
    <?php
}

/**
 * Sla lp_ velden op bij het bewaren van het gebruikersprofiel in de admin
 */
add_action( 'personal_options_update', 'lp_sla_profiel_velden_op' );
add_action( 'edit_user_profile_update', 'lp_sla_profiel_velden_op' );

function lp_sla_profiel_velden_op( $user_id ) {
    if ( ! current_user_can( 'edit_user', $user_id ) ) return;
    if ( ! isset( $_POST['lp_profiel_nonce'] ) || ! wp_verify_nonce( $_POST['lp_profiel_nonce'], 'lp_opslaan_profiel_' . $user_id ) ) return;

    $oude_status = get_user_meta( $user_id, 'lp_account_status', true );
    $nieuwe_status = sanitize_key( $_POST['lp_account_status'] ?? '' );

    update_user_meta( $user_id, 'lp_account_status',        $nieuwe_status );
    update_user_meta( $user_id, 'lp_geslacht',              sanitize_key( $_POST['lp_geslacht'] ?? '' ) );
    update_user_meta( $user_id, 'lp_geboortedatum',         sanitize_text_field( $_POST['lp_geboortedatum'] ?? '' ) );
    update_user_meta( $user_id, 'lp_telefoonnummer',        sanitize_text_field( $_POST['lp_telefoonnummer'] ?? '' ) );
    update_user_meta( $user_id, 'lp_mobiel',                sanitize_text_field( $_POST['lp_mobiel'] ?? '' ) );
    update_user_meta( $user_id, 'lp_straatnaam',            sanitize_text_field( $_POST['lp_straatnaam'] ?? '' ) );
    update_user_meta( $user_id, 'lp_huisnummer',            sanitize_text_field( $_POST['lp_huisnummer'] ?? '' ) );
    update_user_meta( $user_id, 'lp_huisnummer_toevoeging', sanitize_text_field( $_POST['lp_huisnummer_toevoeging'] ?? '' ) );
    update_user_meta( $user_id, 'lp_postcode',              sanitize_text_field( $_POST['lp_postcode'] ?? '' ) );
    update_user_meta( $user_id, 'lp_plaats',                sanitize_text_field( $_POST['lp_plaats'] ?? '' ) );
    update_user_meta( $user_id, 'lp_land',                  sanitize_text_field( $_POST['lp_land'] ?? 'NL' ) );
    update_user_meta( $user_id, 'lp_afdeling',              sanitize_key( $_POST['lp_afdeling'] ?? '' ) );
    update_user_meta( $user_id, 'lp_soort_pensioen',        sanitize_key( $_POST['lp_soort_pensioen'] ?? '' ) );

    delete_user_meta( $user_id, 'lp_verenigingsfunctie' );
    $functies = array_map( 'sanitize_key', (array) ( $_POST['lp_verenigingsfunctie'] ?? [] ) );
    $geldige  = array_keys( lp_functie_opties() );
    foreach ( $functies as $keuze ) {
        if ( in_array( $keuze, $geldige, true ) ) {
            add_user_meta( $user_id, 'lp_verenigingsfunctie', $keuze );
        }
    }

    // IBAN & incasso
    $huidig_iban = get_user_meta( $user_id, 'lp_iban', true );
    update_user_meta( $user_id, 'lp_iban',              sanitize_text_field( $_POST['lp_iban'] ?? '' ) );
    update_user_meta( $user_id, 'lp_iban_ten_name_van', sanitize_text_field( $_POST['lp_iban_ten_name_van'] ?? '' ) );
    $incasso = isset( $_POST['lp_incasso_toestemming'] ) ? '1' : '';
    $huidig_incasso = get_user_meta( $user_id, 'lp_incasso_toestemming', true );
    update_user_meta( $user_id, 'lp_incasso_toestemming', $incasso );
    if ( $incasso === '1' && $huidig_incasso !== '1' ) {
        update_user_meta( $user_id, 'lp_incasso_toestemming_datum', current_time( 'Y-m-d H:i:s', true ) );
    }
    update_user_meta( $user_id, 'lp_account_gewijzigd', current_time( 'Y-m-d H:i:s', true ) );

    // Trigger mail als status veranderd is
    if ( $nieuwe_status !== $oude_status ) {
        if ( $nieuwe_status === 'approved' ) {
            do_action( 'lp_account_goedgekeurd', $user_id );
        } elseif ( $nieuwe_status === 'rejected' ) {
            do_action( 'lp_account_afgewezen', $user_id );
        }
    }
}

/**
 * Admin menu registratie
 */
add_action( 'admin_menu', function() {
    add_menu_page(
        __( 'Ledenportaal', 'mijn-ledenportaal' ),
        __( 'Ledenportaal', 'mijn-ledenportaal' ),
        'manage_options',
        'ledenportaal',
        'lp_admin_instellingen_pagina',
        'dashicons-groups',
        30
    );

    add_submenu_page(
        'ledenportaal',
        __( 'Instellingen', 'mijn-ledenportaal' ),
        __( 'Instellingen', 'mijn-ledenportaal' ),
        'manage_options',
        'ledenportaal',
        'lp_admin_instellingen_pagina'
    );

    add_submenu_page(
        'ledenportaal',
        __( 'Mails', 'mijn-ledenportaal' ),
        __( 'Mails', 'mijn-ledenportaal' ),
        'manage_options',
        'lp-mails',
        'lp_admin_mails_pagina'
    );

    add_submenu_page(
        'ledenportaal',
        __( 'Formuliervelden', 'mijn-ledenportaal' ),
        __( 'Formuliervelden', 'mijn-ledenportaal' ),
        'manage_options',
        'lp-formuliervelden',
        'lp_admin_formuliervelden_pagina'
    );

    add_submenu_page(
        'ledenportaal',
        __( 'Export', 'mijn-ledenportaal' ),
        __( 'Export', 'mijn-ledenportaal' ),
        'manage_options',
        'lp-export',
        'lp_admin_export_pagina'
    );

    add_submenu_page(
        'ledenportaal',
        __( 'Import', 'mijn-ledenportaal' ),
        __( 'Import', 'mijn-ledenportaal' ),
        'manage_options',
        'lp-import',
        'lp_admin_import_pagina'
    );

    add_submenu_page(
        'ledenportaal',
        __( 'Ledenbeheer', 'mijn-ledenportaal' ),
        __( 'Ledenbeheer', 'mijn-ledenportaal' ),
        'manage_options',
        'lp-ledenbeheer',
        'lp_admin_ledenbeheer_pagina'
    );
} );

/**
 * Instellingen registreren
 */
add_action( 'admin_init', function() {
    register_setting( 'lp_instellingen', 'lp_login_pagina_id', [ 'sanitize_callback' => 'absint' ] );
    register_setting( 'lp_instellingen', 'lp_account_pagina_id', [ 'sanitize_callback' => 'absint' ] );
    register_setting( 'lp_instellingen', 'lp_registratie_pagina_id', [ 'sanitize_callback' => 'absint' ] );
    register_setting( 'lp_instellingen', 'lp_wachtwoord_vergeten_pagina_id', [ 'sanitize_callback' => 'absint' ] );
    register_setting( 'lp_instellingen', 'lp_nieuw_wachtwoord_pagina_id', [ 'sanitize_callback' => 'absint' ] );
    register_setting( 'lp_instellingen', 'lp_na_login_pagina_id',         [ 'sanitize_callback' => 'absint' ] );
    // Mail toggles
    $mail_sleutels = [
        'registratie_bevestiging',
        'admin_nieuw_lid',
        'account_goedgekeurd',
        'account_afgewezen',
        'account_bijgewerkt',
        'wachtwoord_reset',
    ];
    foreach ( $mail_sleutels as $sleutel ) {
        register_setting( 'lp_mails', 'lp_mail_actief_' . $sleutel, [
            'sanitize_callback' => function( $v ) { return $v ? '1' : '0'; },
        ] );
    }
    register_setting( 'lp_mails', 'lp_notificatie_email', [ 'sanitize_callback' => 'sanitize_email' ] );
    $mail_namen = [ 'registratie_bevestiging', 'admin_nieuw_lid', 'account_goedgekeurd', 'account_afgewezen', 'account_bijgewerkt', 'wachtwoord_reset' ];
    foreach ( $mail_namen as $naam ) {
        register_setting( 'lp_mails', 'lp_mail_onderwerp_' . $naam, [ 'sanitize_callback' => 'sanitize_text_field' ] );
        register_setting( 'lp_mails', 'lp_mail_inhoud_' . $naam,    [ 'sanitize_callback' => 'wp_kses_post' ] );
    }
    register_setting( 'lp_instellingen', 'lp_goedkeuring_flow', [
        'sanitize_callback' => function( $v ) {
            return in_array( $v, [ 'manual', 'automatic' ], true ) ? $v : 'manual';
        },
    ] );
    register_setting( 'lp_instellingen', 'lp_registratie_rol', [
        'sanitize_callback' => function( $v ) {
            $rollen = array_keys( get_editable_roles() );
            return in_array( $v, $rollen, true ) ? $v : 'subscriber';
        },
    ] );
    register_setting( 'lp_instellingen', 'lp_adminbar_uitschakelen', [
        'sanitize_callback' => function( $v ) {
            if ( ! is_array( $v ) ) return [];
            $rollen = array_keys( get_editable_roles() );
            return array_values( array_intersect( $v, $rollen ) );
        },
    ] );
    register_setting( 'lp_formuliervelden', 'lp_verplicht_velden', [
        'sanitize_callback' => function( $v ) {
            $geldig = array_keys( lp_configureerbare_velden() );
            return is_array( $v ) ? array_values( array_intersect( $v, $geldig ) ) : [];
        },
    ] );
    register_setting( 'lp_instellingen', 'lp_beveilig_alles', [
        'sanitize_callback' => function( $v ) { return $v ? '1' : ''; },
    ] );
    register_setting( 'lp_instellingen', 'lp_uitgesloten_urls', [
        'sanitize_callback' => function( $v ) {
            $regels = array_map( 'trim', explode( "\n", (string) $v ) );
            $regels = array_filter( $regels, fn( $r ) => $r !== '' );
            return implode( "\n", $regels );
        },
    ] );
    register_setting( 'lp_instellingen', 'lp_beveiligde_post_types', [
        'sanitize_callback' => function( $v ) { return is_array( $v ) ? array_map( 'sanitize_key', $v ) : []; },
    ] );
    register_setting( 'lp_instellingen', 'lp_beveiligde_taxonomieen', [
        'sanitize_callback' => function( $v ) { return is_array( $v ) ? array_map( 'sanitize_key', $v ) : []; },
    ] );
    register_setting( 'lp_instellingen', 'lp_beveiligde_paginas', [
        'sanitize_callback' => function( $waarde ) {
            if ( ! is_array( $waarde ) ) return [];
            return array_map( 'absint', $waarde );
        }
    ] );
} );

/**
 * Admin pagina: Instellingen
 */
function lp_admin_instellingen_pagina() {
    if ( ! current_user_can( 'manage_options' ) ) return;

    $alle_paginas = get_pages( [ 'post_status' => 'publish', 'sort_column' => 'post_title' ] );
    $login_id              = get_option( 'lp_login_pagina_id', 0 );
    $account_id            = get_option( 'lp_account_pagina_id', 0 );
    $registratie_id        = get_option( 'lp_registratie_pagina_id', 0 );
    $wachtwoord_vergeten_id = get_option( 'lp_wachtwoord_vergeten_pagina_id', 0 );
    $nieuw_wachtwoord_id   = get_option( 'lp_nieuw_wachtwoord_pagina_id', 0 );
    $na_login_id           = get_option( 'lp_na_login_pagina_id', 0 );
    $beveiligde            = get_option( 'lp_beveiligde_paginas', [] );
    if ( ! is_array( $beveiligde ) ) $beveiligde = [];
    $beveilig_alles        = get_option( 'lp_beveilig_alles', '' );
    $uitgesloten_urls      = get_option( 'lp_uitgesloten_urls', '' );
    $bev_post_types        = get_option( 'lp_beveiligde_post_types', [] );
    $bev_taxonomieen       = get_option( 'lp_beveiligde_taxonomieen', [] );
    $alle_post_types       = get_post_types( [ 'public' => true ], 'objects' );
    $alle_taxonomieen      = get_taxonomies( [ 'public' => true ], 'objects' );
    $goedkeuring_flow      = get_option( 'lp_goedkeuring_flow', 'manual' );
    $registratie_rol       = get_option( 'lp_registratie_rol', 'subscriber' );
    $adminbar_uitschakelen = (array) get_option( 'lp_adminbar_uitschakelen', [] );
    ?>
    <div class="wrap">
        <h1><?php esc_html_e( 'Ledenportaal — Instellingen', 'mijn-ledenportaal' ); ?></h1>

        <?php settings_errors( 'lp_instellingen' ); ?>

        <div style="background: #fff; border: 1px solid #c3c4c7; border-radius: 4px; padding: 16px 20px; margin-bottom: 20px; display: flex; gap: 32px; flex-wrap: wrap; align-items: center;">
            <strong style="color: #1d2327; white-space: nowrap;"><?php esc_html_e( 'Shortcodes:', 'mijn-ledenportaal' ); ?></strong>
            <?php
            $shortcodes = [
                '[ledenportaal_login]',
                '[ledenportaal_registratie]',
                '[ledenportaal_account]',
                '[ledenportaal_wachtwoord_vergeten]',
                '[ledenportaal_nieuw_wachtwoord]',
            ];
            foreach ( $shortcodes as $sc ) : ?>
                <code
                    style="background: #f0f0f1; padding: 6px 12px; border-radius: 3px; font-size: 13px; cursor: pointer; user-select: all; border: 1px solid #c3c4c7;"
                    title="<?php esc_attr_e( 'Klik om te kopiëren', 'mijn-ledenportaal' ); ?>"
                    onclick="navigator.clipboard.writeText('<?php echo esc_js( $sc ); ?>').then(function(){ var el = this; el.style.background='#d7f0d1'; setTimeout(function(){ el.style.background='#f0f0f1'; }, 1200); }.bind(this));"
                ><?php echo esc_html( $sc ); ?></code>
            <?php endforeach; ?>
        </div>

        <form method="post" action="options.php">
            <?php settings_fields( 'lp_instellingen' ); ?>

            <table class="form-table">
                <tr>
                    <th scope="row">
                        <label for="lp_login_pagina_id"><?php esc_html_e( 'Loginpagina', 'mijn-ledenportaal' ); ?></label>
                    </th>
                    <td>
                        <select name="lp_login_pagina_id" id="lp_login_pagina_id">
                            <option value="0"><?php esc_html_e( '— Selecteer pagina —', 'mijn-ledenportaal' ); ?></option>
                            <?php foreach ( $alle_paginas as $pagina ) : ?>
                                <option value="<?php echo esc_attr( $pagina->ID ); ?>" <?php selected( $login_id, $pagina->ID ); ?>>
                                    <?php echo esc_html( $pagina->post_title ); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <?php if ( $login_id ) : ?>
                            <a href="<?php echo esc_url( get_permalink( $login_id ) ); ?>" target="_blank" class="button button-small" style="margin-left: 8px;">
                                <?php esc_html_e( 'Bekijk', 'mijn-ledenportaal' ); ?>
                            </a>
                        <?php endif; ?>
                    </td>
                </tr>
                <tr>
                    <th scope="row">
                        <label for="lp_account_pagina_id"><?php esc_html_e( 'Accountpagina', 'mijn-ledenportaal' ); ?></label>
                    </th>
                    <td>
                        <select name="lp_account_pagina_id" id="lp_account_pagina_id">
                            <option value="0"><?php esc_html_e( '— Selecteer pagina —', 'mijn-ledenportaal' ); ?></option>
                            <?php foreach ( $alle_paginas as $pagina ) : ?>
                                <option value="<?php echo esc_attr( $pagina->ID ); ?>" <?php selected( $account_id, $pagina->ID ); ?>>
                                    <?php echo esc_html( $pagina->post_title ); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <?php if ( $account_id ) : ?>
                            <a href="<?php echo esc_url( get_permalink( $account_id ) ); ?>" target="_blank" class="button button-small" style="margin-left: 8px;">
                                <?php esc_html_e( 'Bekijk', 'mijn-ledenportaal' ); ?>
                            </a>
                        <?php endif; ?>
                    </td>
                </tr>
                <tr>
                    <th scope="row">
                        <label for="lp_na_login_pagina_id"><?php esc_html_e( 'Doorsturen na inloggen', 'mijn-ledenportaal' ); ?></label>
                    </th>
                    <td>
                        <select name="lp_na_login_pagina_id" id="lp_na_login_pagina_id">
                            <option value="0"><?php esc_html_e( '— Accountpagina (standaard) —', 'mijn-ledenportaal' ); ?></option>
                            <?php foreach ( $alle_paginas as $pagina ) : ?>
                                <option value="<?php echo esc_attr( $pagina->ID ); ?>" <?php selected( $na_login_id, $pagina->ID ); ?>>
                                    <?php echo esc_html( $pagina->post_title ); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <?php if ( $na_login_id ) : ?>
                            <a href="<?php echo esc_url( get_permalink( $na_login_id ) ); ?>" target="_blank" class="button button-small" style="margin-left: 8px;">
                                <?php esc_html_e( 'Bekijk', 'mijn-ledenportaal' ); ?>
                            </a>
                        <?php endif; ?>
                        <p class="description"><?php esc_html_e( 'Pagina waarnaar leden worden doorgestuurd na het inloggen. Standaard is dat de accountpagina.', 'mijn-ledenportaal' ); ?></p>
                    </td>
                </tr>
                <tr>
                    <th scope="row">
                        <label for="lp_registratie_pagina_id"><?php esc_html_e( 'Registratiepagina', 'mijn-ledenportaal' ); ?></label>
                    </th>
                    <td>
                        <select name="lp_registratie_pagina_id" id="lp_registratie_pagina_id">
                            <option value="0"><?php esc_html_e( '— Selecteer pagina —', 'mijn-ledenportaal' ); ?></option>
                            <?php foreach ( $alle_paginas as $pagina ) : ?>
                                <option value="<?php echo esc_attr( $pagina->ID ); ?>" <?php selected( $registratie_id, $pagina->ID ); ?>>
                                    <?php echo esc_html( $pagina->post_title ); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <?php if ( $registratie_id ) : ?>
                            <a href="<?php echo esc_url( get_permalink( $registratie_id ) ); ?>" target="_blank" class="button button-small" style="margin-left: 8px;">
                                <?php esc_html_e( 'Bekijk', 'mijn-ledenportaal' ); ?>
                            </a>
                        <?php endif; ?>
                    </td>
                </tr>
                <tr>
                    <th scope="row">
                        <label for="lp_wachtwoord_vergeten_pagina_id"><?php esc_html_e( 'Wachtwoord vergeten pagina', 'mijn-ledenportaal' ); ?></label>
                    </th>
                    <td>
                        <select name="lp_wachtwoord_vergeten_pagina_id" id="lp_wachtwoord_vergeten_pagina_id">
                            <option value="0"><?php esc_html_e( '— Selecteer pagina —', 'mijn-ledenportaal' ); ?></option>
                            <?php foreach ( $alle_paginas as $pagina ) : ?>
                                <option value="<?php echo esc_attr( $pagina->ID ); ?>" <?php selected( $wachtwoord_vergeten_id, $pagina->ID ); ?>>
                                    <?php echo esc_html( $pagina->post_title ); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <?php if ( $wachtwoord_vergeten_id ) : ?>
                            <a href="<?php echo esc_url( get_permalink( $wachtwoord_vergeten_id ) ); ?>" target="_blank" class="button button-small" style="margin-left: 8px;">
                                <?php esc_html_e( 'Bekijk', 'mijn-ledenportaal' ); ?>
                            </a>
                        <?php endif; ?>
                    </td>
                </tr>
                <tr>
                    <th scope="row">
                        <label for="lp_nieuw_wachtwoord_pagina_id"><?php esc_html_e( 'Nieuw wachtwoord pagina', 'mijn-ledenportaal' ); ?></label>
                    </th>
                    <td>
                        <select name="lp_nieuw_wachtwoord_pagina_id" id="lp_nieuw_wachtwoord_pagina_id">
                            <option value="0"><?php esc_html_e( '— Selecteer pagina —', 'mijn-ledenportaal' ); ?></option>
                            <?php foreach ( $alle_paginas as $pagina ) : ?>
                                <option value="<?php echo esc_attr( $pagina->ID ); ?>" <?php selected( $nieuw_wachtwoord_id, $pagina->ID ); ?>>
                                    <?php echo esc_html( $pagina->post_title ); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <?php if ( $nieuw_wachtwoord_id ) : ?>
                            <a href="<?php echo esc_url( get_permalink( $nieuw_wachtwoord_id ) ); ?>" target="_blank" class="button button-small" style="margin-left: 8px;">
                                <?php esc_html_e( 'Bekijk', 'mijn-ledenportaal' ); ?>
                            </a>
                        <?php endif; ?>
                    </td>
                </tr>
                <tr>
                    <th scope="row">
                        <?php esc_html_e( 'Goedkeuringsflow', 'mijn-ledenportaal' ); ?>
                    </th>
                    <td>
                        <fieldset>
                            <label style="display: block; margin-bottom: 6px;">
                                <input type="radio" name="lp_goedkeuring_flow" value="manual"
                                    <?php checked( $goedkeuring_flow, 'manual' ); ?>>
                                <?php esc_html_e( 'Handmatig — nieuwe leden moeten door een beheerder worden goedgekeurd', 'mijn-ledenportaal' ); ?>
                            </label>
                            <label style="display: block;">
                                <input type="radio" name="lp_goedkeuring_flow" value="automatic"
                                    <?php checked( $goedkeuring_flow, 'automatic' ); ?>>
                                <?php esc_html_e( 'Automatisch — nieuwe leden worden direct goedgekeurd na registratie', 'mijn-ledenportaal' ); ?>
                            </label>
                        </fieldset>
                    </td>
                </tr>
                <tr>
                    <th scope="row">
                        <?php esc_html_e( 'Rol bij registratie', 'mijn-ledenportaal' ); ?>
                    </th>
                    <td>
                        <select name="lp_registratie_rol" id="lp_registratie_rol">
                            <?php foreach ( get_editable_roles() as $rol_slug => $rol_info ) : ?>
                                <option value="<?php echo esc_attr( $rol_slug ); ?>" <?php selected( $registratie_rol, $rol_slug ); ?>>
                                    <?php echo esc_html( translate_user_role( $rol_info['name'] ) ); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <p class="description"><?php esc_html_e( 'De WordPress-rol die nieuwe leden krijgen na registratie.', 'mijn-ledenportaal' ); ?></p>
                    </td>
                </tr>
                <tr>
                    <th scope="row">
                        <?php esc_html_e( 'Admin balk verbergen', 'mijn-ledenportaal' ); ?>
                    </th>
                    <td>
                        <fieldset>
                            <?php foreach ( get_editable_roles() as $rol_slug => $rol_info ) : ?>
                                <label style="display: block; margin-bottom: 4px;">
                                    <input type="checkbox" name="lp_adminbar_uitschakelen[]"
                                        value="<?php echo esc_attr( $rol_slug ); ?>"
                                        <?php checked( in_array( $rol_slug, $adminbar_uitschakelen, true ) ); ?>>
                                    <?php echo esc_html( translate_user_role( $rol_info['name'] ) ); ?>
                                </label>
                            <?php endforeach; ?>
                        </fieldset>
                        <p class="description"><?php esc_html_e( 'De WordPress-adminbalk wordt verborgen voor de geselecteerde rollen op de frontend.', 'mijn-ledenportaal' ); ?></p>
                    </td>
                </tr>
                <tr>
                    <th scope="row">
                        <?php esc_html_e( 'Beveiliging', 'mijn-ledenportaal' ); ?>
                    </th>
                    <td>
                        <label style="font-weight: 600;">
                            <input type="checkbox" name="lp_beveilig_alles" value="1" <?php checked( $beveilig_alles, '1' ); ?>>
                            <?php esc_html_e( 'Beveilig de hele website', 'mijn-ledenportaal' ); ?>
                        </label>
                        <p class="description" style="margin-top: 4px;"><?php esc_html_e( 'Alle pagina\'s, archieven en Elementor-templates zijn alleen toegankelijk voor ingelogde leden. De login-, registratie- en wachtwoordpagina\'s blijven altijd bereikbaar.', 'mijn-ledenportaal' ); ?></p>
                    </td>
                </tr>
                <tr>
                    <th scope="row">
                        <label for="lp_uitgesloten_urls"><?php esc_html_e( 'Uitgesloten URL\'s', 'mijn-ledenportaal' ); ?></label>
                    </th>
                    <td>
                        <textarea name="lp_uitgesloten_urls" id="lp_uitgesloten_urls" rows="6" class="large-text code"><?php echo esc_textarea( $uitgesloten_urls ); ?></textarea>
                        <p class="description"><?php esc_html_e( 'Één URL-pad per regel, bijv. /over-ons of /nieuws/. Deze pagina\'s zijn altijd publiek toegankelijk, ook als "Beveilig de hele website" aan staat. Gebruik * als wildcard, bijv. /nieuws/*.', 'mijn-ledenportaal' ); ?></p>
                    </td>
                </tr>
                <tr>
                    <th scope="row"><?php esc_html_e( 'Beveiligde berichttypen', 'mijn-ledenportaal' ); ?></th>
                    <td>
                        <fieldset>
                            <?php foreach ( $alle_post_types as $pt ) :
                                if ( $pt->name === 'attachment' ) continue; ?>
                                <label style="display: block; margin-bottom: 4px;">
                                    <input type="checkbox" name="lp_beveiligde_post_types[]"
                                        value="<?php echo esc_attr( $pt->name ); ?>"
                                        <?php checked( in_array( $pt->name, (array) $bev_post_types, true ) ); ?>>
                                    <?php echo esc_html( $pt->label ); ?>
                                    <span style="color:#999; font-size:12px;">(<?php echo esc_html( $pt->name ); ?>)</span>
                                </label>
                            <?php endforeach; ?>
                        </fieldset>
                        <p class="description"><?php esc_html_e( 'Beveiligt zowel individuele berichten als het archiefoverzicht van het berichttype.', 'mijn-ledenportaal' ); ?></p>
                    </td>
                </tr>
                <tr>
                    <th scope="row"><?php esc_html_e( 'Beveiligde taxonomieën', 'mijn-ledenportaal' ); ?></th>
                    <td>
                        <fieldset>
                            <?php foreach ( $alle_taxonomieen as $tax ) : ?>
                                <label style="display: block; margin-bottom: 4px;">
                                    <input type="checkbox" name="lp_beveiligde_taxonomieen[]"
                                        value="<?php echo esc_attr( $tax->name ); ?>"
                                        <?php checked( in_array( $tax->name, (array) $bev_taxonomieen, true ) ); ?>>
                                    <?php echo esc_html( $tax->label ); ?>
                                    <span style="color:#999; font-size:12px;">(<?php echo esc_html( $tax->name ); ?>)</span>
                                </label>
                            <?php endforeach; ?>
                        </fieldset>
                        <p class="description"><?php esc_html_e( 'Beveiligt taxonomie-archiefpagina\'s (categorie, tag, custom taxonomie).', 'mijn-ledenportaal' ); ?></p>
                    </td>
                </tr>
                <tr>
                    <th scope="row">
                        <?php esc_html_e( 'Specifieke beveiligde pagina\'s', 'mijn-ledenportaal' ); ?>
                    </th>
                    <td>
                        <fieldset>
                            <?php foreach ( $alle_paginas as $pagina ) : ?>
                                <label style="display: block; margin-bottom: 4px;">
                                    <input type="checkbox"
                                        name="lp_beveiligde_paginas[]"
                                        value="<?php echo esc_attr( $pagina->ID ); ?>"
                                        <?php checked( in_array( $pagina->ID, $beveiligde, true ) ); ?>>
                                    <?php echo esc_html( $pagina->post_title ); ?>
                                </label>
                            <?php endforeach; ?>
                        </fieldset>
                        <p class="description"><?php esc_html_e( 'Gebruik dit als alternatief voor "hele website beveiligen": alleen deze pagina\'s zijn afgeschermd.', 'mijn-ledenportaal' ); ?></p>
                    </td>
                </tr>
            </table>

            <?php submit_button( __( 'Instellingen opslaan', 'mijn-ledenportaal' ) ); ?>
        </form>
    </div>
    <?php
}

/**
 * Admin pagina: Mails
 */
function lp_admin_mails_pagina() {
    if ( ! current_user_can( 'manage_options' ) ) return;

    $mails = [
        'registratie_bevestiging' => [
            'label'       => __( 'Registratie bevestiging (aan lid)', 'mijn-ledenportaal' ),
            'beschrijving' => __( 'Bevestigingsmail aan het lid nadat hij/zij zich heeft aangemeld. Informeert dat de aanmelding in behandeling is.', 'mijn-ledenportaal' ),
            'ontvanger'   => __( 'Lid', 'mijn-ledenportaal' ),
        ],
        'admin_nieuw_lid' => [
            'label'       => __( 'Nieuw lid aangemeld (aan admin)', 'mijn-ledenportaal' ),
            'beschrijving' => __( 'Notificatie aan de beheerder zodra een nieuw lid zich heeft aangemeld.', 'mijn-ledenportaal' ),
            'ontvanger'   => __( 'Admin (WordPress e-mail)', 'mijn-ledenportaal' ),
        ],
        'account_goedgekeurd' => [
            'label'       => __( 'Account goedgekeurd (aan lid)', 'mijn-ledenportaal' ),
            'beschrijving' => __( 'Mail aan het lid wanneer het account is goedgekeurd door een beheerder.', 'mijn-ledenportaal' ),
            'ontvanger'   => __( 'Lid', 'mijn-ledenportaal' ),
        ],
        'account_afgewezen' => [
            'label'       => __( 'Account afgewezen (aan lid)', 'mijn-ledenportaal' ),
            'beschrijving' => __( 'Mail aan het lid wanneer het account is afgewezen door een beheerder.', 'mijn-ledenportaal' ),
            'ontvanger'   => __( 'Lid', 'mijn-ledenportaal' ),
        ],
        'account_bijgewerkt' => [
            'label'       => __( 'Accountgegevens bijgewerkt (notificatie)', 'mijn-ledenportaal' ),
            'beschrijving' => __( 'Notificatie naar het ingestelde notificatie-e-mailadres wanneer een lid zijn/haar gegevens bijwerkt.', 'mijn-ledenportaal' ),
            'ontvanger'   => __( 'Notificatie-e-mailadres (zie Instellingen)', 'mijn-ledenportaal' ),
        ],
        'wachtwoord_reset' => [
            'label'       => __( 'Wachtwoord reset-link (aan lid)', 'mijn-ledenportaal' ),
            'beschrijving' => __( 'Mail met reset-link aan het lid na het aanvragen van een nieuw wachtwoord.', 'mijn-ledenportaal' ),
            'ontvanger'   => __( 'Lid', 'mijn-ledenportaal' ),
        ],
    ];
    ?>
    <div class="wrap">
        <h1><?php esc_html_e( 'Ledenportaal — Mails', 'mijn-ledenportaal' ); ?></h1>

        <?php settings_errors( 'lp_mails' ); ?>

        <p><?php esc_html_e( 'Hier activeer of deactiveer je de verschillende e-mails die het ledenportaal verstuurt.', 'mijn-ledenportaal' ); ?></p>

        <form method="post" action="options.php">
            <?php settings_fields( 'lp_mails' ); ?>

            <table class="wp-list-table widefat fixed striped" style="margin-top: 16px;">
                <thead>
                    <tr>
                        <th style="width: 48px;"><?php esc_html_e( 'Aan', 'mijn-ledenportaal' ); ?></th>
                        <th><?php esc_html_e( 'Mail', 'mijn-ledenportaal' ); ?></th>
                        <th><?php esc_html_e( 'Ontvanger', 'mijn-ledenportaal' ); ?></th>
                        <th><?php esc_html_e( 'Omschrijving', 'mijn-ledenportaal' ); ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ( $mails as $sleutel => $info ) :
                        $actief = get_option( 'lp_mail_actief_' . $sleutel, '1' );
                    ?>
                        <tr>
                            <td style="text-align: center;">
                                <label class="lp-toggle" style="display: inline-block; position: relative; width: 40px; height: 22px;">
                                    <input type="hidden" name="lp_mail_actief_<?php echo esc_attr( $sleutel ); ?>" value="0">
                                    <input type="checkbox"
                                        name="lp_mail_actief_<?php echo esc_attr( $sleutel ); ?>"
                                        value="1"
                                        <?php checked( $actief, '1' ); ?>
                                        style="opacity: 0; width: 0; height: 0; position: absolute;">
                                    <span class="lp-toggle-slider" style="
                                        position: absolute; cursor: pointer; top: 0; left: 0; right: 0; bottom: 0;
                                        background: <?php echo $actief === '1' ? '#0091D5' : '#ccc'; ?>;
                                        border-radius: 22px; transition: background 0.2s;
                                    ">
                                        <span style="
                                            position: absolute; content: ''; height: 16px; width: 16px;
                                            left: <?php echo $actief === '1' ? '21px' : '3px'; ?>; bottom: 3px;
                                            background: white; border-radius: 50%; transition: left 0.2s;
                                        "></span>
                                    </span>
                                </label>
                            </td>
                            <td><strong><?php echo esc_html( $info['label'] ); ?></strong></td>
                            <td style="color: #666;"><?php echo esc_html( $info['ontvanger'] ); ?></td>
                            <td style="color: #666;"><?php echo esc_html( $info['beschrijving'] ); ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>

            <h2 style="margin-top: 28px;"><?php esc_html_e( 'Notificatie-instellingen', 'mijn-ledenportaal' ); ?></h2>
            <table class="form-table" style="margin-top: 0;">
                <tr>
                    <th scope="row">
                        <label for="lp_notificatie_email"><?php esc_html_e( 'Notificatie-e-mailadres', 'mijn-ledenportaal' ); ?></label>
                    </th>
                    <td>
                        <input type="email" name="lp_notificatie_email" id="lp_notificatie_email"
                            value="<?php echo esc_attr( get_option( 'lp_notificatie_email', '' ) ); ?>"
                            class="regular-text"
                            placeholder="<?php esc_attr_e( 'bijv. secretaris@voorbeeld.nl', 'mijn-ledenportaal' ); ?>">
                        <p class="description"><?php esc_html_e( 'Ontvanger van de notificatie bij accountwijzigingen (zie mail "Accountgegevens bijgewerkt" hierboven).', 'mijn-ledenportaal' ); ?></p>
                    </td>
                </tr>
            </table>

            <h2 style="margin-top: 32px;"><?php esc_html_e( 'Inhoud bewerken', 'mijn-ledenportaal' ); ?></h2>
            <p style="margin-top: 0;"><?php esc_html_e( 'Laat een veld leeg om de standaardtekst te gebruiken. Gebruik de getoonde placeholders om dynamische waarden in te voegen.', 'mijn-ledenportaal' ); ?></p>

            <?php
            $mail_placeholders = [
                'registratie_bevestiging' => [ '{{voornaam}}', '{{volledige_naam}}', '{{email}}', '{{site_naam}}', '{{site_url}}' ],
                'admin_nieuw_lid'         => [ '{{volledige_naam}}', '{{email}}', '{{site_naam}}', '{{site_url}}', '{{admin_url}}' ],
                'account_goedgekeurd'     => [ '{{voornaam}}', '{{volledige_naam}}', '{{email}}', '{{site_naam}}', '{{site_url}}', '{{login_url}}' ],
                'account_afgewezen'       => [ '{{voornaam}}', '{{volledige_naam}}', '{{email}}', '{{site_naam}}', '{{site_url}}', '{{admin_email}}' ],
                'account_bijgewerkt'      => [ '{{volledige_naam}}', '{{email}}', '{{site_naam}}', '{{site_url}}', '{{admin_url}}' ],
                'wachtwoord_reset'        => [ '{{voornaam}}', '{{volledige_naam}}', '{{email}}', '{{site_naam}}', '{{site_url}}', '{{reset_url}}' ],
            ];
            $defaults = lp_mail_defaults();
            $first    = true;
            foreach ( $mails as $sleutel => $info ) :
                $huidig_onderwerp = get_option( 'lp_mail_onderwerp_' . $sleutel, '' ) ?: ( $defaults[ $sleutel ]['onderwerp'] ?? '' );
                $huidig_inhoud    = get_option( 'lp_mail_inhoud_' . $sleutel, '' )    ?: ( $defaults[ $sleutel ]['inhoud']    ?? '' );
                $ph               = $mail_placeholders[ $sleutel ] ?? [];
                $editor_id        = 'lp_mail_inhoud_' . str_replace( '-', '_', $sleutel );
            ?>
            <?php if ( ! $first ) : ?><hr style="margin: 32px 0;"><?php endif; $first = false; ?>
            <h3 style="margin-bottom: 4px;"><?php echo esc_html( $info['label'] ); ?></h3>

            <table class="form-table" style="margin-top: 8px;">
                <tr>
                    <th style="width: 160px; padding: 8px 0;">
                        <label for="lp_mail_onderwerp_<?php echo esc_attr( $sleutel ); ?>">
                            <?php esc_html_e( 'Onderwerp', 'mijn-ledenportaal' ); ?>
                        </label>
                    </th>
                    <td style="padding: 8px 0;">
                        <input type="text"
                            name="lp_mail_onderwerp_<?php echo esc_attr( $sleutel ); ?>"
                            id="lp_mail_onderwerp_<?php echo esc_attr( $sleutel ); ?>"
                            value="<?php echo esc_attr( $huidig_onderwerp ); ?>"
                            class="large-text">
                    </td>
                </tr>
                <tr>
                    <th style="padding: 8px 0; vertical-align: top;">
                        <?php esc_html_e( 'Inhoud', 'mijn-ledenportaal' ); ?>
                    </th>
                    <td style="padding: 8px 0;">
                        <?php
                        wp_editor(
                            $huidig_inhoud,
                            $editor_id,
                            [
                                'textarea_name' => 'lp_mail_inhoud_' . $sleutel,
                                'media_buttons' => false,
                                'teeny'         => false,
                                'textarea_rows' => 10,
                                'quicktags'     => true,
                            ]
                        );
                        ?>
                        <?php if ( ! empty( $ph ) ) : ?>
                        <p class="description" style="margin-top: 6px;">
                            <?php esc_html_e( 'Beschikbare placeholders:', 'mijn-ledenportaal' ); ?>
                            <?php foreach ( $ph as $p ) : ?>
                                <code style="margin: 0 2px; cursor: pointer;" onclick="navigator.clipboard.writeText('<?php echo esc_js( $p ); ?>')" title="Klik om te kopiëren"><?php echo esc_html( $p ); ?></code>
                            <?php endforeach; ?>
                        </p>
                        <?php endif; ?>
                    </td>
                </tr>
            </table>
            <?php endforeach; ?>

            <p style="margin-top: 16px;">
                <?php submit_button( __( 'Opslaan', 'mijn-ledenportaal' ), 'primary', 'submit', false ); ?>
            </p>
        </form>
    </div>
    <script>
    document.querySelectorAll('.lp-toggle input[type="checkbox"]').forEach(function(cb) {
        cb.addEventListener('change', function() {
            var slider = this.closest('.lp-toggle').querySelector('.lp-toggle-slider');
            var knob   = slider.querySelector('span');
            if (this.checked) {
                slider.style.background = '#0091D5';
                knob.style.left = '21px';
            } else {
                slider.style.background = '#ccc';
                knob.style.left = '3px';
            }
        });
    });
    </script>
    <?php
}

/**
 * Admin pagina: Ledenbeheer
 */
function lp_admin_ledenbeheer_pagina() {
    if ( ! current_user_can( 'manage_options' ) ) return;

    // Verwerk goedkeuren / afwijzen
    if ( isset( $_POST['lp_ledenbeheer_actie'] ) && isset( $_POST['lp_ledenbeheer_nonce'] ) ) {
        if ( wp_verify_nonce( $_POST['lp_ledenbeheer_nonce'], 'lp_ledenbeheer' ) ) {
            $actie   = sanitize_key( $_POST['lp_ledenbeheer_actie'] );
            $user_id = absint( $_POST['lp_user_id'] ?? 0 );

            if ( $user_id && in_array( $actie, [ 'goedkeuren', 'afwijzen' ], true ) ) {
                if ( $actie === 'goedkeuren' ) {
                    update_user_meta( $user_id, 'lp_account_status', 'approved' );
                    do_action( 'lp_account_goedgekeurd', $user_id );
                    echo '<div class="notice notice-success"><p>' . esc_html__( 'Account goedgekeurd.', 'mijn-ledenportaal' ) . '</p></div>';
                } else {
                    update_user_meta( $user_id, 'lp_account_status', 'rejected' );
                    do_action( 'lp_account_afgewezen', $user_id );
                    echo '<div class="notice notice-warning"><p>' . esc_html__( 'Account afgewezen.', 'mijn-ledenportaal' ) . '</p></div>';
                }
            }
        }
    }

    $filter_status = sanitize_key( $_GET['lp_filter'] ?? 'all' );

    $query_args = [
        'meta_key'     => 'lp_account_status',
        'meta_compare' => 'EXISTS',
        'number'       => 200,
        'orderby'      => 'registered',
        'order'        => 'DESC',
    ];
    if ( $filter_status !== 'all' ) {
        $query_args['meta_value'] = $filter_status;
    }

    $gebruikers = get_users( $query_args );

    $status_labels  = [
        'pending'  => __( 'In afwachting', 'mijn-ledenportaal' ),
        'approved' => __( 'Goedgekeurd', 'mijn-ledenportaal' ),
        'rejected' => __( 'Afgewezen', 'mijn-ledenportaal' ),
    ];
    $status_kleuren = [
        'pending'  => '#f0ad4e',
        'approved' => '#5cb85c',
        'rejected' => '#d9534f',
    ];

    $geslacht_opties = lp_geslacht_opties();
    $afdeling_opties = lp_afdeling_opties();
    $pensioen_opties = lp_pensioen_opties();
    $functie_opties  = lp_functie_opties();
    $land_opties     = lp_land_opties();

    $huidige_url = admin_url( 'admin.php?page=lp-ledenbeheer' );
    ?>
    <div class="wrap">
        <h1><?php esc_html_e( 'Ledenportaal — Ledenbeheer', 'mijn-ledenportaal' ); ?></h1>

        <ul class="subsubsub">
            <li><a href="<?php echo esc_url( $huidige_url ); ?>" <?php echo $filter_status === 'all' ? 'class="current"' : ''; ?>><?php esc_html_e( 'Alle leden', 'mijn-ledenportaal' ); ?></a> |</li>
            <li><a href="<?php echo esc_url( add_query_arg( 'lp_filter', 'pending', $huidige_url ) ); ?>" <?php echo $filter_status === 'pending' ? 'class="current"' : ''; ?>><?php esc_html_e( 'In afwachting', 'mijn-ledenportaal' ); ?></a> |</li>
            <li><a href="<?php echo esc_url( add_query_arg( 'lp_filter', 'approved', $huidige_url ) ); ?>" <?php echo $filter_status === 'approved' ? 'class="current"' : ''; ?>><?php esc_html_e( 'Goedgekeurd', 'mijn-ledenportaal' ); ?></a> |</li>
            <li><a href="<?php echo esc_url( add_query_arg( 'lp_filter', 'rejected', $huidige_url ) ); ?>" <?php echo $filter_status === 'rejected' ? 'class="current"' : ''; ?>><?php esc_html_e( 'Afgewezen', 'mijn-ledenportaal' ); ?></a></li>
        </ul>

        <?php if ( empty( $gebruikers ) ) : ?>
            <p><?php esc_html_e( 'Geen leden gevonden.', 'mijn-ledenportaal' ); ?></p>
        <?php else : ?>
        <table class="wp-list-table widefat" style="margin-top: 20px; border-collapse: collapse;">
            <thead>
                <tr>
                    <th style="width: 32px;"></th>
                    <th><?php esc_html_e( 'Naam', 'mijn-ledenportaal' ); ?></th>
                    <th><?php esc_html_e( 'E-mail', 'mijn-ledenportaal' ); ?></th>
                    <th><?php esc_html_e( 'Geregistreerd', 'mijn-ledenportaal' ); ?></th>
                    <th><?php esc_html_e( 'Laatst gewijzigd', 'mijn-ledenportaal' ); ?></th>
                    <th><?php esc_html_e( 'Status', 'mijn-ledenportaal' ); ?></th>
                    <th><?php esc_html_e( 'Acties', 'mijn-ledenportaal' ); ?></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ( $gebruikers as $i => $gebruiker ) :
                    $uid             = $gebruiker->ID;
                    $status          = get_user_meta( $uid, 'lp_account_status', true );
                    $kleur           = $status_kleuren[ $status ] ?? '#999';
                    $label           = $status_labels[ $status ] ?? esc_html( $status );
                    $gewijzigd_datum = get_user_meta( $uid, 'lp_account_gewijzigd', true );
                    $detail_id       = 'lp-detail-' . $uid;
                    $rij_bg          = $i % 2 === 0 ? '#fff' : '#f9f9f9';

                    // Alle meta voor detailrij
                    $m = [
                        'geslacht'              => get_user_meta( $uid, 'lp_geslacht', true ),
                        'geboortedatum'         => get_user_meta( $uid, 'lp_geboortedatum', true ),
                        'telefoonnummer'        => get_user_meta( $uid, 'lp_telefoonnummer', true ),
                        'mobiel'                => get_user_meta( $uid, 'lp_mobiel', true ),
                        'straatnaam'            => get_user_meta( $uid, 'lp_straatnaam', true ),
                        'huisnummer'            => get_user_meta( $uid, 'lp_huisnummer', true ),
                        'huisnummer_toevoeging' => get_user_meta( $uid, 'lp_huisnummer_toevoeging', true ),
                        'postcode'              => get_user_meta( $uid, 'lp_postcode', true ),
                        'plaats'                => get_user_meta( $uid, 'lp_plaats', true ),
                        'land'                  => get_user_meta( $uid, 'lp_land', true ),
                        'afdeling'              => get_user_meta( $uid, 'lp_afdeling', true ),
                        'soort_pensioen'        => get_user_meta( $uid, 'lp_soort_pensioen', true ),
                        'verenigingsfunctie'    => get_user_meta( $uid, 'lp_verenigingsfunctie' ),
                        'iban'                  => get_user_meta( $uid, 'lp_iban', true ),
                        'iban_ten_name_van'     => get_user_meta( $uid, 'lp_iban_ten_name_van', true ),
                        'incasso_toestemming'   => get_user_meta( $uid, 'lp_incasso_toestemming', true ),
                        'incasso_datum'         => get_user_meta( $uid, 'lp_incasso_toestemming_datum', true ),
                    ];

                    $adres_delen = array_filter( [
                        trim( $m['straatnaam'] . ' ' . $m['huisnummer'] . ' ' . $m['huisnummer_toevoeging'] ),
                        trim( $m['postcode'] . '  ' . $m['plaats'] ),
                        $land_opties[ $m['land'] ] ?? $m['land'],
                    ] );

                    $functies_labels = array_filter( array_map(
                        fn( $k ) => $functie_opties[ $k ] ?? null,
                        (array) $m['verenigingsfunctie']
                    ) );
                ?>
                    <tr style="background: <?php echo esc_attr( $rij_bg ); ?>; border-bottom: 1px solid #e0e0e0;">
                        <td style="text-align: center; padding: 10px 6px;">
                            <button type="button"
                                class="lp-detail-toggle"
                                data-target="<?php echo esc_attr( $detail_id ); ?>"
                                aria-expanded="false"
                                style="background: none; border: none; cursor: pointer; padding: 2px; color: #2271b1; font-size: 18px; line-height: 1;">
                                &#9654;
                            </button>
                        </td>
                        <td style="padding: 10px 8px;">
                            <strong><?php echo esc_html( $gebruiker->display_name ); ?></strong>
                        </td>
                        <td style="padding: 10px 8px;"><?php echo esc_html( $gebruiker->user_email ); ?></td>
                        <td style="padding: 10px 8px;"><?php echo esc_html( date_i18n( get_option( 'date_format' ), strtotime( $gebruiker->user_registered ) ) ); ?></td>
                        <td style="padding: 10px 8px;"><?php echo $gewijzigd_datum ? esc_html( date_i18n( 'd-m-Y H:i', strtotime( $gewijzigd_datum ) ) ) : '—'; ?></td>
                        <td style="padding: 10px 8px;">
                            <span style="background: <?php echo esc_attr( $kleur ); ?>; color: white; padding: 3px 8px; border-radius: 3px; font-size: 12px;">
                                <?php echo esc_html( $label ); ?>
                            </span>
                        </td>
                        <td style="padding: 10px 8px; white-space: nowrap;">
                            <?php if ( $status !== 'approved' ) : ?>
                                <form method="post" style="display: inline;">
                                    <?php wp_nonce_field( 'lp_ledenbeheer', 'lp_ledenbeheer_nonce' ); ?>
                                    <input type="hidden" name="lp_user_id" value="<?php echo esc_attr( $uid ); ?>">
                                    <input type="hidden" name="lp_ledenbeheer_actie" value="goedkeuren">
                                    <button type="submit" class="button button-primary button-small">
                                        <?php esc_html_e( 'Goedkeuren', 'mijn-ledenportaal' ); ?>
                                    </button>
                                </form>
                            <?php endif; ?>
                            <?php if ( $status !== 'rejected' ) : ?>
                                <form method="post" style="display: inline; margin-left: 4px;">
                                    <?php wp_nonce_field( 'lp_ledenbeheer', 'lp_ledenbeheer_nonce' ); ?>
                                    <input type="hidden" name="lp_user_id" value="<?php echo esc_attr( $uid ); ?>">
                                    <input type="hidden" name="lp_ledenbeheer_actie" value="afwijzen">
                                    <button type="submit" class="button button-small" style="color: #d9534f; border-color: #d9534f;"
                                        onclick="return confirm('<?php esc_attr_e( 'Weet je zeker dat je dit account wilt afwijzen?', 'mijn-ledenportaal' ); ?>')">
                                        <?php esc_html_e( 'Afwijzen', 'mijn-ledenportaal' ); ?>
                                    </button>
                                </form>
                            <?php endif; ?>
                            <a href="<?php echo esc_url( get_edit_user_link( $uid ) ); ?>" class="button button-small" style="margin-left: 4px;">
                                <?php esc_html_e( 'Bewerken', 'mijn-ledenportaal' ); ?>
                            </a>
                        </td>
                    </tr>

                    <!-- Detailrij -->
                    <tr id="<?php echo esc_attr( $detail_id ); ?>" style="display: none; background: #f0f6fc;">
                        <td></td>
                        <td colspan="6" style="padding: 16px 20px 20px;">
                            <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(220px, 1fr)); gap: 20px;">

                                <div>
                                    <strong style="display: block; margin-bottom: 8px; color: #1d2327; font-size: 12px; text-transform: uppercase; letter-spacing: .05em;"><?php esc_html_e( 'Persoonlijk', 'mijn-ledenportaal' ); ?></strong>
                                    <?php lp_detail_rij( 'Geslacht', $geslacht_opties[ $m['geslacht'] ] ?? '' ); ?>
                                    <?php lp_detail_rij( 'Geboortedatum', $m['geboortedatum'] ? date_i18n( 'd-m-Y', strtotime( $m['geboortedatum'] ) ) : '' ); ?>
                                    <?php lp_detail_rij( 'Telefoonnummer', $m['telefoonnummer'] ); ?>
                                    <?php lp_detail_rij( 'Mobiel', $m['mobiel'] ); ?>
                                </div>

                                <div>
                                    <strong style="display: block; margin-bottom: 8px; color: #1d2327; font-size: 12px; text-transform: uppercase; letter-spacing: .05em;"><?php esc_html_e( 'Adres', 'mijn-ledenportaal' ); ?></strong>
                                    <?php foreach ( $adres_delen as $deel ) : ?>
                                        <div style="font-size: 13px; margin-bottom: 3px; color: #3c434a;"><?php echo esc_html( $deel ); ?></div>
                                    <?php endforeach; ?>
                                    <?php if ( empty( $adres_delen ) ) : ?>
                                        <span style="color: #999; font-size: 13px;">—</span>
                                    <?php endif; ?>
                                </div>

                                <div>
                                    <strong style="display: block; margin-bottom: 8px; color: #1d2327; font-size: 12px; text-transform: uppercase; letter-spacing: .05em;"><?php esc_html_e( 'Lidmaatschap', 'mijn-ledenportaal' ); ?></strong>
                                    <?php lp_detail_rij( 'Afdeling', $afdeling_opties[ $m['afdeling'] ] ?? '' ); ?>
                                    <?php lp_detail_rij( 'Soort pensioen', $pensioen_opties[ $m['soort_pensioen'] ] ?? '' ); ?>
                                    <?php lp_detail_rij( 'Verenigingsfunctie', implode( ', ', $functies_labels ) ?: '' ); ?>
                                </div>

                                <div>
                                    <strong style="display: block; margin-bottom: 8px; color: #1d2327; font-size: 12px; text-transform: uppercase; letter-spacing: .05em;"><?php esc_html_e( 'Betaalgegevens', 'mijn-ledenportaal' ); ?></strong>
                                    <?php lp_detail_rij( 'IBAN', $m['iban'] ); ?>
                                    <?php lp_detail_rij( 'Ten name van', $m['iban_ten_name_van'] ); ?>
                                    <?php if ( $m['incasso_toestemming'] === '1' ) : ?>
                                        <?php lp_detail_rij( 'Incasso', __( 'Toestemming gegeven', 'mijn-ledenportaal' ) . ( $m['incasso_datum'] ? ' (' . date_i18n( 'd-m-Y', strtotime( $m['incasso_datum'] ) ) . ')' : '' ) ); ?>
                                    <?php else : ?>
                                        <?php lp_detail_rij( 'Incasso', __( 'Geen toestemming', 'mijn-ledenportaal' ) ); ?>
                                    <?php endif; ?>
                                </div>

                            </div>
                        </td>
                    </tr>

                <?php endforeach; ?>
            </tbody>
        </table>
        <?php endif; ?>
    </div>
    <script>
    document.querySelectorAll('.lp-detail-toggle').forEach(function(btn) {
        btn.addEventListener('click', function() {
            var rij = document.getElementById(this.dataset.target);
            var open = this.getAttribute('aria-expanded') === 'true';
            rij.style.display = open ? 'none' : 'table-row';
            this.setAttribute('aria-expanded', open ? 'false' : 'true');
            this.innerHTML = open ? '&#9654;' : '&#9660;';
        });
    });
    </script>
    <?php
}

/**
 * Helper: één label + waarde rij in detailpaneel
 */
function lp_detail_rij( $label, $waarde ) {
    $waarde = (string) $waarde;
    ?>
    <div style="display: flex; gap: 6px; font-size: 13px; margin-bottom: 4px; line-height: 1.4;">
        <span style="color: #646970; min-width: 120px; flex-shrink: 0;"><?php echo esc_html( $label ); ?></span>
        <span style="color: #3c434a;"><?php echo $waarde !== '' ? esc_html( $waarde ) : '<span style="color:#999">—</span>'; ?></span>
    </div>
    <?php
}

/**
 * Geeft alle configureerbare (optioneel verplichtbare) velden terug
 */
function lp_configureerbare_velden() {
    return [
        'geslacht'              => __( 'Geslacht', 'mijn-ledenportaal' ),
        'geboortedatum'         => __( 'Geboortedatum', 'mijn-ledenportaal' ),
        'telefoonnummer'        => __( 'Telefoonnummer', 'mijn-ledenportaal' ),
        'mobiel'                => __( 'Mobiel', 'mijn-ledenportaal' ),
        'straatnaam'            => __( 'Straatnaam', 'mijn-ledenportaal' ),
        'huisnummer'            => __( 'Huisnummer', 'mijn-ledenportaal' ),
        'huisnummer_toevoeging' => __( 'Huisnummer toevoeging', 'mijn-ledenportaal' ),
        'postcode'              => __( 'Postcode', 'mijn-ledenportaal' ),
        'plaats'                => __( 'Plaats', 'mijn-ledenportaal' ),
        'land'                  => __( 'Land', 'mijn-ledenportaal' ),
        'afdeling'              => __( 'Afdeling', 'mijn-ledenportaal' ),
        'soort_pensioen'        => __( 'Soort pensioen', 'mijn-ledenportaal' ),
        'verenigingsfunctie'    => __( 'Verenigingsfunctie', 'mijn-ledenportaal' ),
    ];
}

/**
 * Helper: is een veld verplicht?
 */
function lp_veld_verplicht( $sleutel ) {
    return in_array( $sleutel, (array) get_option( 'lp_verplicht_velden', [] ), true );
}

/**
 * Admin pagina: Formuliervelden
 */
function lp_admin_formuliervelden_pagina() {
    if ( ! current_user_can( 'manage_options' ) ) return;

    $verplicht_velden = (array) get_option( 'lp_verplicht_velden', [] );
    $velden           = lp_configureerbare_velden();

    $altijd_verplicht = [
        'voornaam'           => __( 'Voornaam', 'mijn-ledenportaal' ),
        'achternaam'         => __( 'Achternaam', 'mijn-ledenportaal' ),
        'e-mailadres'        => __( 'E-mailadres', 'mijn-ledenportaal' ),
        'wachtwoord'         => __( 'Wachtwoord', 'mijn-ledenportaal' ),
        'iban'               => __( 'IBAN', 'mijn-ledenportaal' ),
        'iban_ten_name_van'  => __( 'Ten name van', 'mijn-ledenportaal' ),
        'incasso_toestemming' => __( 'Incasso toestemming', 'mijn-ledenportaal' ),
    ];
    ?>
    <div class="wrap">
        <h1><?php esc_html_e( 'Ledenportaal — Formuliervelden', 'mijn-ledenportaal' ); ?></h1>

        <?php settings_errors( 'lp_formuliervelden' ); ?>

        <p><?php esc_html_e( 'Selecteer welke velden verplicht zijn bij registratie. Altijd verplichte velden zijn niet aanpasbaar.', 'mijn-ledenportaal' ); ?></p>

        <form method="post" action="options.php">
            <?php settings_fields( 'lp_formuliervelden' ); ?>

            <table class="wp-list-table widefat fixed striped" style="margin-top: 16px; max-width: 600px;">
                <thead>
                    <tr>
                        <th style="width: 48px;"><?php esc_html_e( 'Verplicht', 'mijn-ledenportaal' ); ?></th>
                        <th><?php esc_html_e( 'Veld', 'mijn-ledenportaal' ); ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ( $altijd_verplicht as $label ) : ?>
                    <tr style="opacity: 0.6;">
                        <td style="text-align: center;">
                            <input type="checkbox" checked disabled>
                        </td>
                        <td><?php echo esc_html( $label ); ?> <em style="color:#888;font-size:12px;"><?php esc_html_e( '(altijd verplicht)', 'mijn-ledenportaal' ); ?></em></td>
                    </tr>
                    <?php endforeach; ?>
                    <?php foreach ( $velden as $sleutel => $label ) : ?>
                    <tr>
                        <td style="text-align: center;">
                            <input type="checkbox"
                                name="lp_verplicht_velden[]"
                                value="<?php echo esc_attr( $sleutel ); ?>"
                                <?php checked( in_array( $sleutel, $verplicht_velden, true ) ); ?>>
                        </td>
                        <td><?php echo esc_html( $label ); ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>

            <p style="margin-top: 16px;">
                <?php submit_button( __( 'Opslaan', 'mijn-ledenportaal' ), 'primary', 'submit', false ); ?>
            </p>
        </form>
    </div>
    <?php
}

// ──────────────────────────────────────────────────────────────
// Export
// ──────────────────────────────────────────────────────────────

/**
 * Geeft alle exporteerbare velden terug: slug => label
 */
function lp_export_velden() {
    return [
        // WordPress kern
        'user_login'      => __( 'Gebruikersnaam', 'mijn-ledenportaal' ),
        'user_email'      => __( 'E-mailadres', 'mijn-ledenportaal' ),
        'first_name'      => __( 'Voornaam', 'mijn-ledenportaal' ),
        'last_name'       => __( 'Achternaam', 'mijn-ledenportaal' ),
        'user_registered' => __( 'Registratiedatum', 'mijn-ledenportaal' ),
        'roles'           => __( 'Rol(len)', 'mijn-ledenportaal' ),
        // Ledenportaal
        'lp_geslacht'                  => __( 'Geslacht', 'mijn-ledenportaal' ),
        'lp_geboortedatum'             => __( 'Geboortedatum', 'mijn-ledenportaal' ),
        'lp_telefoonnummer'            => __( 'Telefoonnummer', 'mijn-ledenportaal' ),
        'lp_mobiel'                    => __( 'Mobiel', 'mijn-ledenportaal' ),
        'lp_straatnaam'                => __( 'Straatnaam', 'mijn-ledenportaal' ),
        'lp_huisnummer'                => __( 'Huisnummer', 'mijn-ledenportaal' ),
        'lp_huisnummer_toevoeging'     => __( 'Toevoeging', 'mijn-ledenportaal' ),
        'lp_postcode'                  => __( 'Postcode', 'mijn-ledenportaal' ),
        'lp_plaats'                    => __( 'Plaats', 'mijn-ledenportaal' ),
        'lp_land'                      => __( 'Land', 'mijn-ledenportaal' ),
        'lp_afdeling'                  => __( 'Afdeling', 'mijn-ledenportaal' ),
        'lp_soort_pensioen'            => __( 'Soort pensioen', 'mijn-ledenportaal' ),
        'lp_verenigingsfunctie'        => __( 'Verenigingsfunctie', 'mijn-ledenportaal' ),
        'lp_iban'                      => __( 'IBAN', 'mijn-ledenportaal' ),
        'lp_iban_ten_name_van'         => __( 'IBAN ten name van', 'mijn-ledenportaal' ),
        'lp_incasso_toestemming'       => __( 'Incasso toestemming', 'mijn-ledenportaal' ),
        'lp_incasso_toestemming_datum' => __( 'Incasso toestemming datum', 'mijn-ledenportaal' ),
        'lp_account_status'            => __( 'Account status', 'mijn-ledenportaal' ),
        'lp_account_gewijzigd'         => __( 'Laatste wijziging', 'mijn-ledenportaal' ),
    ];
}

/**
 * Exporteer gebruikers naar CSV (aangeroepen via admin_init)
 */
function lp_export_uitvoeren() {
    if ( ! current_user_can( 'manage_options' ) ) wp_die( 'Geen toegang.' );
    check_admin_referer( 'lp_export' );

    $alle_velden    = lp_export_velden();
    $gekozen_velden = isset( $_POST['lp_export_velden'] ) ? (array) $_POST['lp_export_velden'] : array_keys( $alle_velden );
    $gekozen_velden = array_intersect( $gekozen_velden, array_keys( $alle_velden ) );
    $gekozen_rollen = isset( $_POST['lp_export_rollen'] ) ? array_map( 'sanitize_text_field', (array) $_POST['lp_export_rollen'] ) : [];
    $datum_type     = sanitize_text_field( $_POST['lp_export_datum_type'] ?? '' );
    $datum_van      = sanitize_text_field( $_POST['lp_export_datum_van'] ?? '' );
    $datum_tot      = sanitize_text_field( $_POST['lp_export_datum_tot'] ?? '' );

    // Gebruikers ophalen
    $args = [ 'number' => -1, 'orderby' => 'ID' ];
    if ( ! empty( $gekozen_rollen ) ) {
        $args['role__in'] = $gekozen_rollen;
    }

    // Datum filter op registratiedatum
    if ( $datum_type === 'geregistreerd' && ( $datum_van || $datum_tot ) ) {
        $args['date_query'] = [];
        if ( $datum_van ) $args['date_query']['after']     = $datum_van;
        if ( $datum_tot ) $args['date_query']['before']    = $datum_tot . ' 23:59:59';
        $args['date_query']['inclusive'] = true;
    }

    $gebruikers = get_users( $args );

    // Filter op lp_account_gewijzigd als datum_type = gewijzigd
    if ( $datum_type === 'gewijzigd' && ( $datum_van || $datum_tot ) ) {
        $van_ts  = $datum_van ? strtotime( $datum_van ) : 0;
        $tot_ts  = $datum_tot ? strtotime( $datum_tot . ' 23:59:59' ) : PHP_INT_MAX;
        $gebruikers = array_filter( $gebruikers, function( $u ) use ( $van_ts, $tot_ts ) {
            $gewijzigd = get_user_meta( $u->ID, 'lp_account_gewijzigd', true );
            if ( ! $gewijzigd ) return false;
            $ts = strtotime( $gewijzigd );
            return $ts >= $van_ts && $ts <= $tot_ts;
        } );
    }

    // CSV output
    $bestandsnaam = 'ledenportaal-export-' . date( 'Y-m-d' ) . '.csv';
    header( 'Content-Type: text/csv; charset=utf-8' );
    header( 'Content-Disposition: attachment; filename="' . $bestandsnaam . '"' );
    header( 'Pragma: no-cache' );
    header( 'Expires: 0' );

    $output = fopen( 'php://output', 'w' );
    fprintf( $output, chr( 0xEF ) . chr( 0xBB ) . chr( 0xBF ) ); // UTF-8 BOM

    // Kopteksten
    $koppen = array_map( fn( $slug ) => $alle_velden[ $slug ], $gekozen_velden );
    fputcsv( $output, $koppen, ';' );

    // Rijen
    $kern_velden = [ 'user_login', 'user_email', 'first_name', 'last_name', 'user_registered', 'roles' ];
    foreach ( $gebruikers as $gebruiker ) {
        $rij = [];
        foreach ( $gekozen_velden as $veld ) {
            if ( $veld === 'roles' ) {
                $rij[] = implode( ', ', $gebruiker->roles );
            } elseif ( in_array( $veld, $kern_velden, true ) ) {
                $rij[] = $gebruiker->$veld ?? '';
            } else {
                $waarde = get_user_meta( $gebruiker->ID, $veld, true );
                $rij[]  = is_array( $waarde ) ? implode( ', ', $waarde ) : (string) $waarde;
            }
        }
        fputcsv( $output, $rij, ';' );
    }

    fclose( $output );
    exit;
}

// Vroege handler zodat er geen HTML output is voor de headers
add_action( 'admin_init', function() {
    if (
        ! is_admin() ||
        ( $_GET['page'] ?? '' ) !== 'lp-export' ||
        ! isset( $_POST['lp_export_submit'] )
    ) return;
    lp_export_uitvoeren();
} );

/**
 * Admin pagina: Import
 */
function lp_admin_import_pagina() {
    if ( ! current_user_can( 'manage_options' ) ) return;

    $resultaten = null;

    if ( isset( $_POST['lp_import_submit'] ) ) {
        check_admin_referer( 'lp_import' );
        $resultaten = lp_import_uitvoeren();
    }

    $alle_velden = lp_export_velden();
    ?>
    <div class="wrap">
        <h1><?php esc_html_e( 'Ledenportaal — Import', 'mijn-ledenportaal' ); ?></h1>

        <?php if ( $resultaten !== null ) : ?>
            <div class="notice notice-<?php echo empty( $resultaten['fouten'] ) ? 'success' : 'warning'; ?> is-dismissible">
                <p>
                    <?php printf(
                        esc_html__( 'Import voltooid: %1$d toegevoegd, %2$d bijgewerkt, %3$d overgeslagen.', 'mijn-ledenportaal' ),
                        (int) $resultaten['toegevoegd'],
                        (int) $resultaten['bijgewerkt'],
                        (int) $resultaten['overgeslagen']
                    ); ?>
                </p>
                <?php if ( ! empty( $resultaten['fouten'] ) ) : ?>
                    <ul style="margin: 4px 0 8px 16px; list-style: disc;">
                        <?php foreach ( $resultaten['fouten'] as $fout ) : ?>
                            <li><?php echo esc_html( $fout ); ?></li>
                        <?php endforeach; ?>
                    </ul>
                <?php endif; ?>
            </div>
        <?php endif; ?>

        <p><?php esc_html_e( 'Upload een CSV-bestand in hetzelfde formaat als de export (puntkomma-gescheiden, UTF-8). Bestaande gebruikers worden herkend op e-mailadres of gebruikersnaam en bijgewerkt. Nieuwe gebruikers worden aangemaakt als die optie is ingeschakeld.', 'mijn-ledenportaal' ); ?></p>

        <form method="post" enctype="multipart/form-data">
            <?php wp_nonce_field( 'lp_import' ); ?>

            <table class="form-table">
                <tr>
                    <th scope="row"><label for="lp_import_bestand"><?php esc_html_e( 'CSV-bestand', 'mijn-ledenportaal' ); ?></label></th>
                    <td>
                        <input type="file" name="lp_import_bestand" id="lp_import_bestand" accept=".csv" required>
                        <p class="description"><?php esc_html_e( 'Gebruik de exportfunctie om een voorbeeld-CSV te downloaden als sjabloon.', 'mijn-ledenportaal' ); ?></p>
                    </td>
                </tr>
                <tr>
                    <th scope="row"><?php esc_html_e( 'Nieuwe gebruikers', 'mijn-ledenportaal' ); ?></th>
                    <td>
                        <label>
                            <input type="checkbox" name="lp_import_toevoegen" value="1" checked>
                            <?php esc_html_e( 'Nieuwe gebruikers aanmaken als ze nog niet bestaan', 'mijn-ledenportaal' ); ?>
                        </label>
                    </td>
                </tr>
                <tr>
                    <th scope="row"><label for="lp_import_mail"><?php esc_html_e( 'Mail voor nieuwe gebruikers', 'mijn-ledenportaal' ); ?></label></th>
                    <td>
                        <select name="lp_import_mail" id="lp_import_mail">
                            <option value=""><?php esc_html_e( 'Geen mail versturen', 'mijn-ledenportaal' ); ?></option>
                            <option value="registratie"><?php esc_html_e( 'Registratiebevestiging sturen', 'mijn-ledenportaal' ); ?></option>
                            <option value="wachtwoord_instellen" selected><?php esc_html_e( 'Link om wachtwoord in te stellen sturen', 'mijn-ledenportaal' ); ?></option>
                        </select>
                        <p class="description"><?php esc_html_e( 'Bij "wachtwoord instellen" wordt het wachtwoordveld genegeerd en krijgt de gebruiker een e-mail met een persoonlijke link.', 'mijn-ledenportaal' ); ?></p>
                    </td>
                </tr>
                <tr id="lp-import-ww-rij">
                    <th scope="row"><label for="lp_import_wachtwoord"><?php esc_html_e( 'Standaard wachtwoord', 'mijn-ledenportaal' ); ?></label></th>
                    <td>
                        <input type="text" name="lp_import_wachtwoord" id="lp_import_wachtwoord" value="" class="regular-text" placeholder="<?php esc_attr_e( 'Leeg = automatisch genereren', 'mijn-ledenportaal' ); ?>">
                        <p class="description"><?php esc_html_e( 'Alleen van toepassing als er geen mail wordt gestuurd of bij registratiebevestiging.', 'mijn-ledenportaal' ); ?></p>
                    </td>
                </tr>
                <tr>
                    <th scope="row"><label for="lp_import_status"><?php esc_html_e( 'Accountstatus overschrijven', 'mijn-ledenportaal' ); ?></label></th>
                    <td>
                        <select name="lp_import_status" id="lp_import_status">
                            <option value=""><?php esc_html_e( '— Gebruik waarde uit CSV —', 'mijn-ledenportaal' ); ?></option>
                            <option value="pending"><?php esc_html_e( 'In afwachting', 'mijn-ledenportaal' ); ?></option>
                            <option value="approved"><?php esc_html_e( 'Goedgekeurd', 'mijn-ledenportaal' ); ?></option>
                            <option value="rejected"><?php esc_html_e( 'Afgewezen', 'mijn-ledenportaal' ); ?></option>
                        </select>
                        <p class="description"><?php esc_html_e( 'Als je hier een waarde kiest, wordt die voor alle geïmporteerde rijen gebruikt (ook bij bestaande gebruikers).', 'mijn-ledenportaal' ); ?></p>
                    </td>
                </tr>
            </table>

            <p style="margin-top: 16px;">
                <button type="submit" name="lp_import_submit" class="button button-primary">
                    <?php esc_html_e( 'CSV importeren', 'mijn-ledenportaal' ); ?>
                </button>
            </p>
        </form>

        <hr style="margin: 32px 0;">

        <h2><?php esc_html_e( 'Beschikbare kolomnamen', 'mijn-ledenportaal' ); ?></h2>
        <p><?php esc_html_e( 'De eerste rij van het CSV-bestand moet kolomnamen bevatten. Zowel de Nederlandse labels (zoals in de export) als de interne sleutels worden herkend.', 'mijn-ledenportaal' ); ?></p>
        <table class="wp-list-table widefat fixed striped" style="max-width: 520px; margin-top: 12px;">
            <thead>
                <tr>
                    <th><?php esc_html_e( 'Kolomlabel (export)', 'mijn-ledenportaal' ); ?></th>
                    <th><?php esc_html_e( 'Interne sleutel', 'mijn-ledenportaal' ); ?></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ( $alle_velden as $slug => $label ) : ?>
                <tr>
                    <td><?php echo esc_html( $label ); ?></td>
                    <td><code><?php echo esc_html( $slug ); ?></code></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <script>
    (function() {
        var mailSelect = document.getElementById('lp_import_mail');
        var wwRij      = document.getElementById('lp-import-ww-rij');
        function toggleWw() {
            wwRij.style.display = mailSelect.value === 'wachtwoord_instellen' ? 'none' : '';
        }
        mailSelect.addEventListener('change', toggleWw);
        toggleWw();
    })();
    </script>
    <?php
}

/**
 * Verwerk CSV-import en geef resultaten terug
 */
function lp_import_uitvoeren() {
    $resultaten = [
        'toegevoegd'   => 0,
        'bijgewerkt'   => 0,
        'overgeslagen' => 0,
        'fouten'       => [],
    ];

    if ( empty( $_FILES['lp_import_bestand']['tmp_name'] ) || (int) $_FILES['lp_import_bestand']['error'] !== UPLOAD_ERR_OK ) {
        $resultaten['fouten'][] = __( 'Geen geldig bestand geüpload.', 'mijn-ledenportaal' );
        return $resultaten;
    }

    $handle = fopen( $_FILES['lp_import_bestand']['tmp_name'], 'r' );
    if ( ! $handle ) {
        $resultaten['fouten'][] = __( 'Kon het bestand niet openen.', 'mijn-ledenportaal' );
        return $resultaten;
    }

    // UTF-8 BOM overslaan
    $bom = fread( $handle, 3 );
    if ( $bom !== "\xEF\xBB\xBF" ) {
        rewind( $handle );
    }

    // Scheidingsteken automatisch detecteren op basis van eerste rij
    $eerste_rij = fgets( $handle );
    if ( $eerste_rij === false ) {
        fclose( $handle );
        $resultaten['fouten'][] = __( 'CSV-bestand is leeg of ongeldig.', 'mijn-ledenportaal' );
        return $resultaten;
    }
    $scheidingsteken = substr_count( $eerste_rij, ';' ) >= substr_count( $eerste_rij, ',' ) ? ';' : ',';
    // Terug naar begin van de rij
    fseek( $handle, -strlen( $eerste_rij ), SEEK_CUR );

    // Koptekstrij lezen
    $header = fgetcsv( $handle, 0, $scheidingsteken );
    if ( ! $header ) {
        fclose( $handle );
        $resultaten['fouten'][] = __( 'CSV-bestand is leeg of ongeldig.', 'mijn-ledenportaal' );
        return $resultaten;
    }

    // Labels én slugs worden beide herkend als kolomnaam
    $alle_velden     = lp_export_velden();
    $label_naar_slug = array_flip( $alle_velden );

    $kolom_map = []; // index => veld-slug
    foreach ( $header as $i => $kolom ) {
        $kolom = trim( $kolom );
        if ( isset( $label_naar_slug[ $kolom ] ) ) {
            $kolom_map[ $i ] = $label_naar_slug[ $kolom ];
        } elseif ( isset( $alle_velden[ $kolom ] ) ) {
            $kolom_map[ $i ] = $kolom;
        }
    }

    $toevoegen        = ! empty( $_POST['lp_import_toevoegen'] );
    $mail_optie       = sanitize_key( $_POST['lp_import_mail'] ?? '' );
    $standaard_ww     = $mail_optie === 'wachtwoord_instellen' ? '' : sanitize_text_field( $_POST['lp_import_wachtwoord'] ?? '' );
    $standaard_status = sanitize_text_field( $_POST['lp_import_status'] ?? '' );
    $geldig_statussen = [ 'pending', 'approved', 'rejected' ];

    $meta_velden = [
        'lp_geslacht', 'lp_geboortedatum', 'lp_telefoonnummer', 'lp_mobiel',
        'lp_straatnaam', 'lp_huisnummer', 'lp_huisnummer_toevoeging', 'lp_postcode',
        'lp_plaats', 'lp_land', 'lp_afdeling', 'lp_soort_pensioen', 'lp_verenigingsfunctie',
        'lp_iban', 'lp_iban_ten_name_van', 'lp_incasso_toestemming',
        'lp_incasso_toestemming_datum', 'lp_account_status', 'lp_account_gewijzigd',
    ];

    $rij_nummer = 1;
    while ( ( $rij = fgetcsv( $handle, 0, $scheidingsteken ) ) !== false ) {
        $rij_nummer++;

        // Rij omzetten naar veld => waarde
        $data = [];
        foreach ( $kolom_map as $i => $slug ) {
            $data[ $slug ] = isset( $rij[ $i ] ) ? trim( $rij[ $i ] ) : '';
        }

        // Gebruiker opzoeken op e-mail of gebruikersnaam
        $gebruiker_id = null;
        if ( ! empty( $data['user_email'] ) ) {
            $gebruiker = get_user_by( 'email', $data['user_email'] );
            if ( $gebruiker ) $gebruiker_id = $gebruiker->ID;
        }
        if ( ! $gebruiker_id && ! empty( $data['user_login'] ) ) {
            $gebruiker = get_user_by( 'login', $data['user_login'] );
            if ( $gebruiker ) $gebruiker_id = $gebruiker->ID;
        }

        if ( $gebruiker_id ) {
            // Bestaande gebruiker bijwerken
            $update_args = [ 'ID' => $gebruiker_id ];
            if ( ! empty( $data['user_email'] ) ) $update_args['user_email'] = sanitize_email( $data['user_email'] );
            if ( isset( $data['first_name'] ) )   $update_args['first_name'] = sanitize_text_field( $data['first_name'] );
            if ( isset( $data['last_name'] ) )     $update_args['last_name']  = sanitize_text_field( $data['last_name'] );

            $result = wp_update_user( $update_args );
            if ( is_wp_error( $result ) ) {
                $resultaten['fouten'][]  = sprintf( __( 'Rij %1$d: %2$s', 'mijn-ledenportaal' ), $rij_nummer, $result->get_error_message() );
                $resultaten['overgeslagen']++;
                continue;
            }

            lp_import_meta_opslaan( $gebruiker_id, $data, $meta_velden, $standaard_status, $geldig_statussen );
            $resultaten['bijgewerkt']++;

        } elseif ( $toevoegen ) {
            // Nieuwe gebruiker aanmaken
            if ( empty( $data['user_email'] ) && empty( $data['user_login'] ) ) {
                $resultaten['fouten'][]  = sprintf( __( 'Rij %d: geen e-mailadres of gebruikersnaam opgegeven.', 'mijn-ledenportaal' ), $rij_nummer );
                $resultaten['overgeslagen']++;
                continue;
            }

            $login     = ! empty( $data['user_login'] ) ? sanitize_user( $data['user_login'] ) : sanitize_user( $data['user_email'] );
            $user_args = [
                'user_login' => $login,
                'user_email' => sanitize_email( $data['user_email'] ?? '' ),
                'first_name' => sanitize_text_field( $data['first_name'] ?? '' ),
                'last_name'  => sanitize_text_field( $data['last_name'] ?? '' ),
                'user_pass'  => ! empty( $standaard_ww ) ? $standaard_ww : wp_generate_password( 16 ),
                'role'       => get_option( 'lp_registratie_rol', 'subscriber' ),
            ];

            $nieuw_id = wp_insert_user( $user_args );
            if ( is_wp_error( $nieuw_id ) ) {
                $resultaten['fouten'][]  = sprintf( __( 'Rij %1$d: %2$s', 'mijn-ledenportaal' ), $rij_nummer, $nieuw_id->get_error_message() );
                $resultaten['overgeslagen']++;
                continue;
            }

            // Als er geen status in de CSV staat, gebruik pending als fallback
            if ( empty( $data['lp_account_status'] ) && empty( $standaard_status ) ) {
                $data['lp_account_status'] = 'pending';
            }
            lp_import_meta_opslaan( $nieuw_id, $data, $meta_velden, $standaard_status, $geldig_statussen );

            // Mail versturen
            if ( $mail_optie === 'registratie' ) {
                do_action( 'lp_na_registratie', $nieuw_id );
            } elseif ( $mail_optie === 'wachtwoord_instellen' ) {
                lp_import_stuur_wachtwoord_mail( $nieuw_id );
            }

            $resultaten['toegevoegd']++;

        } else {
            $resultaten['overgeslagen']++;
        }
    }

    fclose( $handle );
    return $resultaten;
}

/**
 * Sla meta-velden op voor een gebruiker tijdens import
 */
function lp_import_meta_opslaan( $user_id, $data, $meta_velden, $standaard_status, $geldig_statussen ) {
    foreach ( $meta_velden as $slug ) {
        if ( ! isset( $data[ $slug ] ) ) continue;
        $waarde = $data[ $slug ];

        if ( $slug === 'lp_account_status' ) {
            // Wordt apart afgehandeld na de loop
            continue;
        }

        if ( $slug === 'lp_verenigingsfunctie' ) {
            // Multi-value: opgeslagen als meerdere meta-rijen
            delete_user_meta( $user_id, 'lp_verenigingsfunctie' );
            $functies = array_filter( array_map( 'trim', explode( ',', $waarde ) ) );
            foreach ( $functies as $functie ) {
                add_user_meta( $user_id, 'lp_verenigingsfunctie', sanitize_text_field( $functie ) );
            }
        } else {
            update_user_meta( $user_id, $slug, sanitize_text_field( $waarde ) );
        }
    }

    // Accountstatus: standaard_status heeft voorrang op CSV-waarde
    if ( $standaard_status && in_array( $standaard_status, $geldig_statussen, true ) ) {
        update_user_meta( $user_id, 'lp_account_status', $standaard_status );
    } elseif ( isset( $data['lp_account_status'] ) && in_array( $data['lp_account_status'], $geldig_statussen, true ) ) {
        update_user_meta( $user_id, 'lp_account_status', $data['lp_account_status'] );
    }
}

/**
 * Stuur een wachtwoord-instellen mail naar een nieuw geïmporteerde gebruiker
 */
function lp_import_stuur_wachtwoord_mail( $user_id ) {
    $user = get_userdata( $user_id );
    if ( ! $user ) return;

    $reset_key = get_password_reset_key( $user );
    if ( is_wp_error( $reset_key ) ) return;

    $reset_url = network_site_url( 'wp-login.php?action=rp&key=' . $reset_key . '&login=' . rawurlencode( $user->user_login ), 'login' );
    $site_naam = get_bloginfo( 'name' );

    $onderwerp = sprintf( __( 'Stel je wachtwoord in voor %s', 'mijn-ledenportaal' ), $site_naam );
    $bericht   = sprintf(
        /* translators: 1: voornaam, 2: sitenaam, 3: reset-url */
        __( "Hallo %1\$s,\n\nJe account op %2\$s is aangemaakt. Klik op de onderstaande link om je wachtwoord in te stellen en in te loggen:\n\n%3\$s\n\nDeze link is 24 uur geldig.\n\nMet vriendelijke groet,\n%2\$s", 'mijn-ledenportaal' ),
        $user->first_name ?: $user->user_login,
        $site_naam,
        $reset_url
    );

    wp_mail( $user->user_email, $onderwerp, $bericht );
}

/**
 * Admin pagina: Export
 */
function lp_admin_export_pagina() {
    if ( ! current_user_can( 'manage_options' ) ) return;

    $alle_velden = lp_export_velden();
    $alle_rollen = get_editable_roles();
    ?>
    <div class="wrap">
        <h1><?php esc_html_e( 'Ledenportaal — Export', 'mijn-ledenportaal' ); ?></h1>

        <form method="post">
            <?php wp_nonce_field( 'lp_export' ); ?>

            <div style="display: flex; gap: 32px; align-items: flex-start; margin-top: 16px; flex-wrap: wrap;">

                <!-- Rollen -->
                <div>
                    <h3 style="margin-top: 0;"><?php esc_html_e( 'Rollen filteren', 'mijn-ledenportaal' ); ?></h3>
                    <p style="color: #646970; font-size: 13px; margin-top: -8px;"><?php esc_html_e( 'Niets aangevinkt = alle gebruikers.', 'mijn-ledenportaal' ); ?></p>
                    <ul style="margin: 0; padding: 0; list-style: none;">
                        <?php foreach ( $alle_rollen as $rol_slug => $rol ) : ?>
                        <li>
                            <label>
                                <input type="checkbox" name="lp_export_rollen[]" value="<?php echo esc_attr( $rol_slug ); ?>">
                                <?php echo esc_html( translate_user_role( $rol['name'] ) ); ?>
                                <span style="color: #888; font-size: 12px;">(<?php echo esc_html( $rol_slug ); ?>)</span>
                            </label>
                        </li>
                        <?php endforeach; ?>
                    </ul>
                </div>

                <!-- Datumfilter -->
                <div>
                    <h3 style="margin-top: 0;"><?php esc_html_e( 'Datumfilter', 'mijn-ledenportaal' ); ?></h3>
                    <p style="color: #646970; font-size: 13px; margin-top: -8px;"><?php esc_html_e( 'Leeg laten = geen filter.', 'mijn-ledenportaal' ); ?></p>

                    <div style="margin-bottom: 10px;">
                        <label style="display: block; font-weight: 600; margin-bottom: 4px;"><?php esc_html_e( 'Filteren op', 'mijn-ledenportaal' ); ?></label>
                        <select name="lp_export_datum_type">
                            <option value=""><?php esc_html_e( '— geen datumfilter —', 'mijn-ledenportaal' ); ?></option>
                            <option value="geregistreerd"><?php esc_html_e( 'Registratiedatum', 'mijn-ledenportaal' ); ?></option>
                            <option value="gewijzigd"><?php esc_html_e( 'Laatste wijziging (lp_account_gewijzigd)', 'mijn-ledenportaal' ); ?></option>
                        </select>
                    </div>

                    <div style="display: flex; gap: 12px; align-items: center;">
                        <div>
                            <label style="display: block; font-size: 12px; color: #646970;"><?php esc_html_e( 'Van', 'mijn-ledenportaal' ); ?></label>
                            <input type="date" name="lp_export_datum_van">
                        </div>
                        <div>
                            <label style="display: block; font-size: 12px; color: #646970;"><?php esc_html_e( 'Tot en met', 'mijn-ledenportaal' ); ?></label>
                            <input type="date" name="lp_export_datum_tot">
                        </div>
                    </div>
                </div>

                <!-- Velden -->
                <div>
                    <h3 style="margin-top: 0;">
                        <?php esc_html_e( 'Velden', 'mijn-ledenportaal' ); ?>
                        <span style="font-weight: normal; font-size: 13px; margin-left: 8px;">
                            <a href="#" id="lp-alles-aan" style="text-decoration: none;"><?php esc_html_e( 'alles aan', 'mijn-ledenportaal' ); ?></a>
                            &nbsp;·&nbsp;
                            <a href="#" id="lp-alles-uit" style="text-decoration: none;"><?php esc_html_e( 'alles uit', 'mijn-ledenportaal' ); ?></a>
                        </span>
                    </h3>
                    <ul style="margin: 0; padding: 0; list-style: none; column-count: 2; column-gap: 24px;">
                        <?php foreach ( $alle_velden as $slug => $label ) : ?>
                        <li>
                            <label>
                                <input type="checkbox" class="lp-veld-check" name="lp_export_velden[]" value="<?php echo esc_attr( $slug ); ?>" checked>
                                <?php echo esc_html( $label ); ?>
                            </label>
                        </li>
                        <?php endforeach; ?>
                    </ul>
                </div>

            </div>

            <p style="margin-top: 24px;">
                <button type="submit" name="lp_export_submit" class="button button-primary">
                    <?php esc_html_e( 'CSV downloaden', 'mijn-ledenportaal' ); ?>
                </button>
            </p>
        </form>
    </div>

    <script>
    document.getElementById('lp-alles-aan').addEventListener('click', function(e) {
        e.preventDefault();
        document.querySelectorAll('.lp-veld-check').forEach(function(c) { c.checked = true; });
    });
    document.getElementById('lp-alles-uit').addEventListener('click', function(e) {
        e.preventDefault();
        document.querySelectorAll('.lp-veld-check').forEach(function(c) { c.checked = false; });
    });
    </script>
    <?php
}
