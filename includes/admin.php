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
        'verenigingsfunctie'    => get_user_meta( $user->ID, 'lp_verenigingsfunctie' ),
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
    register_setting( 'lp_instellingen', 'lp_goedkeuring_flow', [
        'sanitize_callback' => function( $v ) {
            return in_array( $v, [ 'manual', 'automatic' ], true ) ? $v : 'manual';
        },
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
    $beveiligde            = get_option( 'lp_beveiligde_paginas', [] );
    if ( ! is_array( $beveiligde ) ) $beveiligde = [];
    $goedkeuring_flow      = get_option( 'lp_goedkeuring_flow', 'manual' );
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
                        <?php esc_html_e( 'Beveiligde pagina\'s', 'mijn-ledenportaal' ); ?>
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
                        <p class="description"><?php esc_html_e( 'Niet-ingelogde bezoekers worden doorgestuurd naar de loginpagina.', 'mijn-ledenportaal' ); ?></p>
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

    // Haal gebruikers op met lp_account_status meta
    $filter_status = sanitize_key( $_GET['lp_filter'] ?? 'all' );

    $query_args = [
        'meta_key'     => 'lp_account_status',
        'meta_compare' => 'EXISTS',
        'number'       => 100,
        'orderby'      => 'registered',
        'order'        => 'DESC',
    ];

    if ( $filter_status !== 'all' ) {
        $query_args['meta_value'] = $filter_status;
    }

    $gebruikers = get_users( $query_args );

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
            <table class="wp-list-table widefat fixed striped" style="margin-top: 20px;">
                <thead>
                    <tr>
                        <th><?php esc_html_e( 'Naam', 'mijn-ledenportaal' ); ?></th>
                        <th><?php esc_html_e( 'E-mail', 'mijn-ledenportaal' ); ?></th>
                        <th><?php esc_html_e( 'Geregistreerd', 'mijn-ledenportaal' ); ?></th>
                        <th><?php esc_html_e( 'Status', 'mijn-ledenportaal' ); ?></th>
                        <th><?php esc_html_e( 'Acties', 'mijn-ledenportaal' ); ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ( $gebruikers as $gebruiker ) :
                        $status = get_user_meta( $gebruiker->ID, 'lp_account_status', true );
                        $kleur  = $status_kleuren[ $status ] ?? '#999';
                        $label  = $status_labels[ $status ] ?? esc_html( $status );
                    ?>
                        <tr>
                            <td>
                                <strong><?php echo esc_html( $gebruiker->display_name ); ?></strong>
                                <br>
                                <a href="<?php echo esc_url( get_edit_user_link( $gebruiker->ID ) ); ?>" class="button button-small">
                                    <?php esc_html_e( 'Bewerken', 'mijn-ledenportaal' ); ?>
                                </a>
                            </td>
                            <td><?php echo esc_html( $gebruiker->user_email ); ?></td>
                            <td><?php echo esc_html( date_i18n( get_option( 'date_format' ), strtotime( $gebruiker->user_registered ) ) ); ?></td>
                            <td>
                                <span style="background: <?php echo esc_attr( $kleur ); ?>; color: white; padding: 3px 8px; border-radius: 3px; font-size: 12px;">
                                    <?php echo esc_html( $label ); ?>
                                </span>
                            </td>
                            <td>
                                <?php if ( $status !== 'approved' ) : ?>
                                    <form method="post" style="display: inline;">
                                        <?php wp_nonce_field( 'lp_ledenbeheer', 'lp_ledenbeheer_nonce' ); ?>
                                        <input type="hidden" name="lp_user_id" value="<?php echo esc_attr( $gebruiker->ID ); ?>">
                                        <input type="hidden" name="lp_ledenbeheer_actie" value="goedkeuren">
                                        <button type="submit" class="button button-primary button-small">
                                            <?php esc_html_e( 'Goedkeuren', 'mijn-ledenportaal' ); ?>
                                        </button>
                                    </form>
                                <?php endif; ?>
                                <?php if ( $status !== 'rejected' ) : ?>
                                    <form method="post" style="display: inline; margin-left: 4px;">
                                        <?php wp_nonce_field( 'lp_ledenbeheer', 'lp_ledenbeheer_nonce' ); ?>
                                        <input type="hidden" name="lp_user_id" value="<?php echo esc_attr( $gebruiker->ID ); ?>">
                                        <input type="hidden" name="lp_ledenbeheer_actie" value="afwijzen">
                                        <button type="submit" class="button button-small" style="color: #d9534f; border-color: #d9534f;"
                                            onclick="return confirm('<?php esc_attr_e( 'Weet je zeker dat je dit account wilt afwijzen?', 'mijn-ledenportaal' ); ?>')">
                                            <?php esc_html_e( 'Afwijzen', 'mijn-ledenportaal' ); ?>
                                        </button>
                                    </form>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>
    <?php
}
