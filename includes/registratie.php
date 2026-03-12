<?php
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Dropdown opties — gedeeld met andere includes
 */
function lp_geslacht_opties() {
    return [
        'man'        => __( 'Man', 'mijn-ledenportaal' ),
        'vrouw'      => __( 'Vrouw', 'mijn-ledenportaal' ),
        'non_binair' => __( 'Non-binair / Anders', 'mijn-ledenportaal' ),
        'geen'       => __( 'Zeg ik liever niet', 'mijn-ledenportaal' ),
    ];
}

function lp_afdeling_opties() {
    return [
        'groepsstaven'  => __( 'Groepsstaven/Finance/HRM/Facilities', 'mijn-ledenportaal' ),
        'dl_leven'      => __( 'DL Leven', 'mijn-ledenportaal' ),
        'dl_schade'     => __( 'DL Schade', 'mijn-ledenportaal' ),
        'dl_bank'       => __( 'DL Bank/Beleggingen/Hypotheken', 'mijn-ledenportaal' ),
        'dl_pensioenen' => __( 'DL Pensioenen', 'mijn-ledenportaal' ),
        'dl_vastgoed'   => __( 'DL Vastgoed', 'mijn-ledenportaal' ),
        'is_ict'        => __( 'IS/ICT Automatisering', 'mijn-ledenportaal' ),
        'noord_brabant' => __( 'Noord Brabant', 'mijn-ledenportaal' ),
        'nsf'           => __( 'Nationaal Spaarfonds', 'mijn-ledenportaal' ),
        'ohra'          => __( 'OHRA', 'mijn-ledenportaal' ),
        'abn_amro'      => __( 'ABN AMRO', 'mijn-ledenportaal' ),
        'erasmus'       => __( 'Erasmus', 'mijn-ledenportaal' ),
        'dlam'          => __( 'Delta Lloyd Asset Management', 'mijn-ledenportaal' ),
        'anders'        => __( 'Anders', 'mijn-ledenportaal' ),
    ];
}

function lp_pensioen_opties() {
    return [
        'niet_gepensioneerd' => __( 'Nog niet gepensioneerd', 'mijn-ledenportaal' ),
        'ouderdom'           => __( 'Ouderdomspensioen/overbruggingspensioen', 'mijn-ledenportaal' ),
        'nabestaanden'       => __( 'Nabestaandenpensioen', 'mijn-ledenportaal' ),
    ];
}

function lp_functie_opties() {
    return [
        'nee'                => __( 'Nee', 'mijn-ledenportaal' ),
        'bestuur'            => __( 'Bestuur', 'mijn-ledenportaal' ),
        'commissie_leden'    => __( 'Commissie Ledenservice', 'mijn-ledenportaal' ),
        'commissie_pensioen' => __( 'Commissie Pensioenen', 'mijn-ledenportaal' ),
        'communicatie'       => __( 'Communicatiecommissie', 'mijn-ledenportaal' ),
    ];
}

function lp_land_opties() {
    return [
        'NL' => __( 'Nederland', 'mijn-ledenportaal' ),
        'BE' => __( 'België', 'mijn-ledenportaal' ),
        'DE' => __( 'Duitsland', 'mijn-ledenportaal' ),
        'FR' => __( 'Frankrijk', 'mijn-ledenportaal' ),
        'GB' => __( 'Verenigd Koninkrijk', 'mijn-ledenportaal' ),
        'ES' => __( 'Spanje', 'mijn-ledenportaal' ),
        'PT' => __( 'Portugal', 'mijn-ledenportaal' ),
        'IT' => __( 'Italië', 'mijn-ledenportaal' ),
        'anders' => __( 'Anders', 'mijn-ledenportaal' ),
    ];
}

/**
 * Al ingelogd → redirect weg van registratiepagina
 */
add_action( 'template_redirect', function() {
    if ( ! is_user_logged_in() ) return;
    $reg_id = get_option( 'lp_registratie_pagina_id', 0 );
    if ( $reg_id && is_page( $reg_id ) ) {
        $account_id = get_option( 'lp_account_pagina_id', 0 );
        wp_safe_redirect( $account_id ? get_permalink( $account_id ) : home_url() );
        exit;
    }
} );

/**
 * POST-verwerking in init — vóór alle output
 */
add_action( 'init', 'lp_verwerk_registratie' );

function lp_verwerk_registratie() {
    if ( ! isset( $_POST['lp_registratie_submit'] ) ) return;

    if ( ! isset( $_POST['lp_registratie_nonce'] ) || ! wp_verify_nonce( $_POST['lp_registratie_nonce'], 'lp_registratie' ) ) {
        $token = lp_sla_fouten_op( 'registratie', [ __( 'Beveiligingscontrole mislukt. Probeer opnieuw.', 'mijn-ledenportaal' ) ] );
        wp_safe_redirect( add_query_arg( 'lp_fout_registratie', $token, lp_huidige_url() ) );
        exit;
    }

    // Saniteer input
    $data = [
        'voornaam'              => sanitize_text_field( $_POST['voornaam'] ?? '' ),
        'achternaam'            => sanitize_text_field( $_POST['achternaam'] ?? '' ),
        'email'                 => sanitize_email( $_POST['email'] ?? '' ),
        'wachtwoord'            => $_POST['wachtwoord'] ?? '',
        'wachtwoord2'           => $_POST['wachtwoord2'] ?? '',
        'geslacht'              => sanitize_key( $_POST['geslacht'] ?? '' ),
        'geboortedatum'         => sanitize_text_field( $_POST['geboortedatum'] ?? '' ),
        'telefoonnummer'        => sanitize_text_field( $_POST['telefoonnummer'] ?? '' ),
        'mobiel'                => sanitize_text_field( $_POST['mobiel'] ?? '' ),
        'straatnaam'            => sanitize_text_field( $_POST['straatnaam'] ?? '' ),
        'huisnummer'            => sanitize_text_field( $_POST['huisnummer'] ?? '' ),
        'huisnummer_toevoeging' => sanitize_text_field( $_POST['huisnummer_toevoeging'] ?? '' ),
        'postcode'              => sanitize_text_field( $_POST['postcode'] ?? '' ),
        'plaats'                => sanitize_text_field( $_POST['plaats'] ?? '' ),
        'land'                  => sanitize_text_field( $_POST['land'] ?? 'NL' ),
        'afdeling'              => sanitize_key( $_POST['afdeling'] ?? '' ),
        'soort_pensioen'        => sanitize_key( $_POST['soort_pensioen'] ?? '' ),
        'verenigingsfunctie'    => array_map( 'sanitize_key', (array) ( $_POST['verenigingsfunctie'] ?? [] ) ),
    ];

    // Validatie
    $fouten = [];
    if ( empty( $data['voornaam'] ) )   $fouten[] = __( 'Voornaam is verplicht.', 'mijn-ledenportaal' );
    if ( empty( $data['achternaam'] ) ) $fouten[] = __( 'Achternaam is verplicht.', 'mijn-ledenportaal' );
    if ( ! is_email( $data['email'] ) ) $fouten[] = __( 'Voer een geldig e-mailadres in.', 'mijn-ledenportaal' );
    if ( email_exists( $data['email'] ) ) $fouten[] = __( 'Dit e-mailadres is al geregistreerd.', 'mijn-ledenportaal' );
    if ( empty( $data['wachtwoord'] ) )             $fouten[] = __( 'Wachtwoord is verplicht.', 'mijn-ledenportaal' );
    if ( strlen( $data['wachtwoord'] ) < 8 )        $fouten[] = __( 'Wachtwoord moet minimaal 8 tekens bevatten.', 'mijn-ledenportaal' );
    if ( $data['wachtwoord'] !== $data['wachtwoord2'] ) $fouten[] = __( 'Wachtwoorden komen niet overeen.', 'mijn-ledenportaal' );

    // Saniteer optiekeuzevelden
    if ( ! in_array( $data['geslacht'], array_keys( lp_geslacht_opties() ), true ) )       $data['geslacht'] = '';
    if ( ! in_array( $data['afdeling'], array_keys( lp_afdeling_opties() ), true ) )       $data['afdeling'] = '';
    if ( ! in_array( $data['soort_pensioen'], array_keys( lp_pensioen_opties() ), true ) ) $data['soort_pensioen'] = '';
    if ( ! in_array( $data['land'], array_keys( lp_land_opties() ), true ) )               $data['land'] = 'NL';

    if ( ! empty( $fouten ) ) {
        $token = lp_sla_fouten_op( 'registratie', $fouten );
        // Sla formulierdata tijdelijk op (60s) zodat velden bewaard blijven
        set_transient( 'lp_reg_data_' . $token, $data, 60 );
        wp_safe_redirect( add_query_arg( 'lp_fout_registratie', $token, lp_huidige_url() ) );
        exit;
    }

    // Aanmaken gebruiker
    $user_id = wp_insert_user( [
        'user_login'   => $data['email'],
        'user_email'   => $data['email'],
        'user_pass'    => $data['wachtwoord'],
        'first_name'   => $data['voornaam'],
        'last_name'    => $data['achternaam'],
        'display_name' => $data['voornaam'] . ' ' . $data['achternaam'],
        'role'         => 'subscriber',
    ] );

    if ( is_wp_error( $user_id ) ) {
        $token = lp_sla_fouten_op( 'registratie', [ $user_id->get_error_message() ] );
        wp_safe_redirect( add_query_arg( 'lp_fout_registratie', $token, lp_huidige_url() ) );
        exit;
    }

    // Sla meta op
    update_user_meta( $user_id, 'lp_geslacht',              $data['geslacht'] );
    update_user_meta( $user_id, 'lp_geboortedatum',         $data['geboortedatum'] );
    update_user_meta( $user_id, 'lp_telefoonnummer',        $data['telefoonnummer'] );
    update_user_meta( $user_id, 'lp_mobiel',                $data['mobiel'] );
    update_user_meta( $user_id, 'lp_straatnaam',            $data['straatnaam'] );
    update_user_meta( $user_id, 'lp_huisnummer',            $data['huisnummer'] );
    update_user_meta( $user_id, 'lp_huisnummer_toevoeging', $data['huisnummer_toevoeging'] );
    update_user_meta( $user_id, 'lp_postcode',              $data['postcode'] );
    update_user_meta( $user_id, 'lp_plaats',                $data['plaats'] );
    update_user_meta( $user_id, 'lp_land',                  $data['land'] );
    update_user_meta( $user_id, 'lp_afdeling',              $data['afdeling'] );
    update_user_meta( $user_id, 'lp_soort_pensioen',        $data['soort_pensioen'] );
    $flow = get_option( 'lp_goedkeuring_flow', 'manual' );
    $status = ( $flow === 'automatic' ) ? 'approved' : 'pending';
    update_user_meta( $user_id, 'lp_account_status', $status );

    $geldige_functies = array_keys( lp_functie_opties() );
    foreach ( $data['verenigingsfunctie'] as $keuze ) {
        if ( in_array( $keuze, $geldige_functies, true ) ) {
            add_user_meta( $user_id, 'lp_verenigingsfunctie', $keuze );
        }
    }

    do_action( 'lp_na_registratie', $user_id );

    if ( $flow === 'automatic' ) {
        do_action( 'lp_account_goedgekeurd', $user_id );
    }

    $succes_param = ( $flow === 'automatic' ) ? 'registratie_goedgekeurd' : 'registratie';
    wp_safe_redirect( add_query_arg( 'lp_succes', $succes_param, lp_huidige_url() ) );
    exit;
}

/**
 * Shortcode: [ledenportaal_registratie]
 */
add_shortcode( 'ledenportaal_registratie', 'lp_render_registratie' );

function lp_render_registratie() {
    $fouten = lp_haal_fouten_op( 'registratie' );
    $succes_waarde = isset( $_GET['lp_succes'] ) ? sanitize_key( $_GET['lp_succes'] ) : '';
    $succes = in_array( $succes_waarde, [ 'registratie', 'registratie_goedgekeurd' ], true );
    $auto_goedgekeurd = ( $succes_waarde === 'registratie_goedgekeurd' );

    // Herstel formulierdata bij fout
    $token   = sanitize_key( $_GET['lp_fout_registratie'] ?? '' );
    $ingevoerd = $token ? ( get_transient( 'lp_reg_data_' . $token ) ?: [] ) : [];

    ob_start();
    include LP_PATH . 'templates/registratie-form.php';
    return ob_get_clean();
}
