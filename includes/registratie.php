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
 * Shortcode: [ledenportaal_registratie]
 */
add_shortcode( 'ledenportaal_registratie', 'lp_render_registratie' );

function lp_render_registratie() {
    // Al ingelogd → redirect naar accountpagina
    if ( is_user_logged_in() ) {
        $account_id = get_option( 'lp_account_pagina_id', 0 );
        if ( $account_id ) {
            wp_redirect( get_permalink( $account_id ) );
            exit;
        }
    }

    $fouten  = [];
    $succes  = false;

    if ( isset( $_POST['lp_registratie_submit'] ) ) {
        // 1. Nonce validatie
        if ( ! isset( $_POST['lp_registratie_nonce'] ) || ! wp_verify_nonce( $_POST['lp_registratie_nonce'], 'lp_registratie' ) ) {
            $fouten[] = __( 'Beveiligingscontrole mislukt. Probeer opnieuw.', 'mijn-ledenportaal' );
        } else {
            // 2. Saniteer input
            $voornaam     = sanitize_text_field( $_POST['voornaam'] ?? '' );
            $achternaam   = sanitize_text_field( $_POST['achternaam'] ?? '' );
            $email        = sanitize_email( $_POST['email'] ?? '' );
            $wachtwoord   = $_POST['wachtwoord'] ?? '';
            $wachtwoord2  = $_POST['wachtwoord2'] ?? '';
            $geslacht     = sanitize_key( $_POST['geslacht'] ?? '' );
            $geboortedatum = sanitize_text_field( $_POST['geboortedatum'] ?? '' );
            $telefoonnummer = sanitize_text_field( $_POST['telefoonnummer'] ?? '' );
            $mobiel       = sanitize_text_field( $_POST['mobiel'] ?? '' );
            $straatnaam   = sanitize_text_field( $_POST['straatnaam'] ?? '' );
            $huisnummer   = sanitize_text_field( $_POST['huisnummer'] ?? '' );
            $huisnummer_toevoeging = sanitize_text_field( $_POST['huisnummer_toevoeging'] ?? '' );
            $postcode     = sanitize_text_field( $_POST['postcode'] ?? '' );
            $plaats       = sanitize_text_field( $_POST['plaats'] ?? '' );
            $land         = sanitize_key( $_POST['land'] ?? 'NL' );
            $afdeling     = sanitize_key( $_POST['afdeling'] ?? '' );
            $soort_pensioen = sanitize_key( $_POST['soort_pensioen'] ?? '' );

            // 3. Validatie
            if ( empty( $voornaam ) ) $fouten[] = __( 'Voornaam is verplicht.', 'mijn-ledenportaal' );
            if ( empty( $achternaam ) ) $fouten[] = __( 'Achternaam is verplicht.', 'mijn-ledenportaal' );
            if ( empty( $email ) || ! is_email( $email ) ) $fouten[] = __( 'Voer een geldig e-mailadres in.', 'mijn-ledenportaal' );
            if ( empty( $wachtwoord ) ) $fouten[] = __( 'Wachtwoord is verplicht.', 'mijn-ledenportaal' );
            if ( strlen( $wachtwoord ) < 8 ) $fouten[] = __( 'Wachtwoord moet minimaal 8 tekens bevatten.', 'mijn-ledenportaal' );
            if ( $wachtwoord !== $wachtwoord2 ) $fouten[] = __( 'Wachtwoorden komen niet overeen.', 'mijn-ledenportaal' );
            if ( email_exists( $email ) ) $fouten[] = __( 'Dit e-mailadres is al geregistreerd.', 'mijn-ledenportaal' );

            // Geldige opties controleren
            $geldige_geslachten = array_keys( lp_geslacht_opties() );
            if ( ! empty( $geslacht ) && ! in_array( $geslacht, $geldige_geslachten, true ) ) {
                $geslacht = '';
            }
            $geldige_afdelingen = array_keys( lp_afdeling_opties() );
            if ( ! empty( $afdeling ) && ! in_array( $afdeling, $geldige_afdelingen, true ) ) {
                $afdeling = '';
            }
            $geldige_pensioenen = array_keys( lp_pensioen_opties() );
            if ( ! empty( $soort_pensioen ) && ! in_array( $soort_pensioen, $geldige_pensioenen, true ) ) {
                $soort_pensioen = '';
            }
            $geldige_landen = array_keys( lp_land_opties() );
            if ( ! in_array( $land, $geldige_landen, true ) ) {
                $land = 'NL';
            }

            // 4. Aanmaken gebruiker
            if ( empty( $fouten ) ) {
                $user_id = wp_insert_user( [
                    'user_login'  => $email,
                    'user_email'  => $email,
                    'user_pass'   => $wachtwoord,
                    'first_name'  => $voornaam,
                    'last_name'   => $achternaam,
                    'display_name' => $voornaam . ' ' . $achternaam,
                    'role'        => 'subscriber',
                ] );

                if ( is_wp_error( $user_id ) ) {
                    $fouten[] = $user_id->get_error_message();
                } else {
                    // 5. Sla meta op
                    update_user_meta( $user_id, 'lp_geslacht', $geslacht );
                    update_user_meta( $user_id, 'lp_geboortedatum', $geboortedatum );
                    update_user_meta( $user_id, 'lp_telefoonnummer', $telefoonnummer );
                    update_user_meta( $user_id, 'lp_mobiel', $mobiel );
                    update_user_meta( $user_id, 'lp_straatnaam', $straatnaam );
                    update_user_meta( $user_id, 'lp_huisnummer', $huisnummer );
                    update_user_meta( $user_id, 'lp_huisnummer_toevoeging', $huisnummer_toevoeging );
                    update_user_meta( $user_id, 'lp_postcode', $postcode );
                    update_user_meta( $user_id, 'lp_plaats', $plaats );
                    update_user_meta( $user_id, 'lp_land', $land );
                    update_user_meta( $user_id, 'lp_afdeling', $afdeling );
                    update_user_meta( $user_id, 'lp_soort_pensioen', $soort_pensioen );

                    // Verenigingsfunctie (meerkeuze)
                    delete_user_meta( $user_id, 'lp_verenigingsfunctie' );
                    $geselecteerde_functies = array_map( 'sanitize_key', $_POST['verenigingsfunctie'] ?? [] );
                    $geldige_functies = array_keys( lp_functie_opties() );
                    foreach ( $geselecteerde_functies as $keuze ) {
                        if ( in_array( $keuze, $geldige_functies, true ) ) {
                            add_user_meta( $user_id, 'lp_verenigingsfunctie', $keuze );
                        }
                    }

                    // 6. Account status pending
                    update_user_meta( $user_id, 'lp_account_status', 'pending' );

                    // 7. Trigger mail action
                    do_action( 'lp_na_registratie', $user_id );

                    $succes = true;
                }
            }
        }
    }

    ob_start();
    include LP_PATH . 'templates/registratie-form.php';
    return ob_get_clean();
}
