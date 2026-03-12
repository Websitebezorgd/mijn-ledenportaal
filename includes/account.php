<?php
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * POST-verwerking in init — vóór alle output
 */
add_action( 'init', 'lp_verwerk_account' );

function lp_verwerk_account() {
    if ( ! isset( $_POST['lp_account_submit'] ) ) return;
    if ( ! is_user_logged_in() ) return;

    if ( ! isset( $_POST['lp_account_nonce'] ) || ! wp_verify_nonce( $_POST['lp_account_nonce'], 'lp_account' ) ) {
        $token = lp_sla_fouten_op( 'account', [ __( 'Beveiligingscontrole mislukt. Probeer opnieuw.', 'mijn-ledenportaal' ) ] );
        wp_safe_redirect( add_query_arg( 'lp_fout_account', $token, lp_huidige_url() ) );
        exit;
    }

    $user_id = get_current_user_id();

    $voornaam               = sanitize_text_field( $_POST['voornaam'] ?? '' );
    $achternaam             = sanitize_text_field( $_POST['achternaam'] ?? '' );
    $email                  = sanitize_email( $_POST['email'] ?? '' );
    $geslacht               = sanitize_key( $_POST['geslacht'] ?? '' );
    $geboortedatum          = sanitize_text_field( $_POST['geboortedatum'] ?? '' );
    $telefoonnummer         = sanitize_text_field( $_POST['telefoonnummer'] ?? '' );
    $mobiel                 = sanitize_text_field( $_POST['mobiel'] ?? '' );
    $straatnaam             = sanitize_text_field( $_POST['straatnaam'] ?? '' );
    $huisnummer             = sanitize_text_field( $_POST['huisnummer'] ?? '' );
    $huisnummer_toevoeging  = sanitize_text_field( $_POST['huisnummer_toevoeging'] ?? '' );
    $postcode               = sanitize_text_field( $_POST['postcode'] ?? '' );
    $plaats                 = sanitize_text_field( $_POST['plaats'] ?? '' );
    $land                   = sanitize_key( $_POST['land'] ?? 'NL' );
    $afdeling               = sanitize_key( $_POST['afdeling'] ?? '' );
    $soort_pensioen         = sanitize_key( $_POST['soort_pensioen'] ?? '' );
    $nieuw_wachtwoord       = $_POST['nieuw_wachtwoord'] ?? '';
    $nieuw_wachtwoord2      = $_POST['nieuw_wachtwoord2'] ?? '';

    $fouten = [];
    if ( empty( $voornaam ) )   $fouten[] = __( 'Voornaam is verplicht.', 'mijn-ledenportaal' );
    if ( empty( $achternaam ) ) $fouten[] = __( 'Achternaam is verplicht.', 'mijn-ledenportaal' );
    if ( ! is_email( $email ) ) $fouten[] = __( 'Voer een geldig e-mailadres in.', 'mijn-ledenportaal' );

    $bestaande = get_user_by( 'email', $email );
    if ( $bestaande && $bestaande->ID !== $user_id ) {
        $fouten[] = __( 'Dit e-mailadres is al in gebruik.', 'mijn-ledenportaal' );
    }

    if ( ! empty( $nieuw_wachtwoord ) ) {
        if ( strlen( $nieuw_wachtwoord ) < 8 )          $fouten[] = __( 'Nieuw wachtwoord moet minimaal 8 tekens bevatten.', 'mijn-ledenportaal' );
        if ( $nieuw_wachtwoord !== $nieuw_wachtwoord2 ) $fouten[] = __( 'Nieuwe wachtwoorden komen niet overeen.', 'mijn-ledenportaal' );
    }

    if ( ! in_array( $geslacht, array_keys( lp_geslacht_opties() ), true ) )       $geslacht = '';
    if ( ! in_array( $afdeling, array_keys( lp_afdeling_opties() ), true ) )       $afdeling = '';
    if ( ! in_array( $soort_pensioen, array_keys( lp_pensioen_opties() ), true ) ) $soort_pensioen = '';
    if ( ! in_array( $land, array_keys( lp_land_opties() ), true ) )               $land = 'NL';

    if ( ! empty( $fouten ) ) {
        $token = lp_sla_fouten_op( 'account', $fouten );
        wp_safe_redirect( add_query_arg( 'lp_fout_account', $token, lp_huidige_url() ) );
        exit;
    }

    $user_data = [
        'ID'           => $user_id,
        'first_name'   => $voornaam,
        'last_name'    => $achternaam,
        'user_email'   => $email,
        'display_name' => $voornaam . ' ' . $achternaam,
    ];
    if ( ! empty( $nieuw_wachtwoord ) ) {
        $user_data['user_pass'] = $nieuw_wachtwoord;
    }
    wp_update_user( $user_data );

    update_user_meta( $user_id, 'lp_geslacht',              $geslacht );
    update_user_meta( $user_id, 'lp_geboortedatum',         $geboortedatum );
    update_user_meta( $user_id, 'lp_telefoonnummer',        $telefoonnummer );
    update_user_meta( $user_id, 'lp_mobiel',                $mobiel );
    update_user_meta( $user_id, 'lp_straatnaam',            $straatnaam );
    update_user_meta( $user_id, 'lp_huisnummer',            $huisnummer );
    update_user_meta( $user_id, 'lp_huisnummer_toevoeging', $huisnummer_toevoeging );
    update_user_meta( $user_id, 'lp_postcode',              $postcode );
    update_user_meta( $user_id, 'lp_plaats',                $plaats );
    update_user_meta( $user_id, 'lp_land',                  $land );
    update_user_meta( $user_id, 'lp_afdeling',              $afdeling );
    update_user_meta( $user_id, 'lp_soort_pensioen',        $soort_pensioen );

    delete_user_meta( $user_id, 'lp_verenigingsfunctie' );
    $geselecteerde_functies = array_map( 'sanitize_key', (array) ( $_POST['verenigingsfunctie'] ?? [] ) );
    $geldige_functies = array_keys( lp_functie_opties() );
    foreach ( $geselecteerde_functies as $keuze ) {
        if ( in_array( $keuze, $geldige_functies, true ) ) {
            add_user_meta( $user_id, 'lp_verenigingsfunctie', $keuze );
        }
    }

    wp_safe_redirect( add_query_arg( 'lp_succes', 'account', lp_huidige_url() ) );
    exit;
}

/**
 * Shortcode: [ledenportaal_account]
 */
add_shortcode( 'ledenportaal_account', 'lp_render_account' );

function lp_render_account() {
    if ( ! is_user_logged_in() ) {
        return '';
    }

    $gebruiker = wp_get_current_user();
    $user_id   = $gebruiker->ID;
    $fouten    = lp_haal_fouten_op( 'account' );
    $succes    = isset( $_GET['lp_succes'] ) && sanitize_key( $_GET['lp_succes'] ) === 'account';

    $meta = [
        'geslacht'              => get_user_meta( $user_id, 'lp_geslacht', true ),
        'geboortedatum'         => get_user_meta( $user_id, 'lp_geboortedatum', true ),
        'telefoonnummer'        => get_user_meta( $user_id, 'lp_telefoonnummer', true ),
        'mobiel'                => get_user_meta( $user_id, 'lp_mobiel', true ),
        'straatnaam'            => get_user_meta( $user_id, 'lp_straatnaam', true ),
        'huisnummer'            => get_user_meta( $user_id, 'lp_huisnummer', true ),
        'huisnummer_toevoeging' => get_user_meta( $user_id, 'lp_huisnummer_toevoeging', true ),
        'postcode'              => get_user_meta( $user_id, 'lp_postcode', true ),
        'plaats'                => get_user_meta( $user_id, 'lp_plaats', true ),
        'land'                  => get_user_meta( $user_id, 'lp_land', true ) ?: 'NL',
        'afdeling'              => get_user_meta( $user_id, 'lp_afdeling', true ),
        'soort_pensioen'        => get_user_meta( $user_id, 'lp_soort_pensioen', true ),
        'verenigingsfunctie'    => get_user_meta( $user_id, 'lp_verenigingsfunctie' ),
        'account_status'        => get_user_meta( $user_id, 'lp_account_status', true ),
    ];

    ob_start();
    include LP_PATH . 'templates/account-form.php';
    return ob_get_clean();
}
