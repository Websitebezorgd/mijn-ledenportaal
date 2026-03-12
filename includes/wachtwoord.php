<?php
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Stap 1: verwerk "wachtwoord vergeten" formulier (e-mailadres invoeren)
 */
add_action( 'init', 'lp_verwerk_wachtwoord_vergeten' );

function lp_verwerk_wachtwoord_vergeten() {
    if ( ! isset( $_POST['lp_wachtwoord_vergeten_submit'] ) ) return;

    if ( ! isset( $_POST['lp_wachtwoord_vergeten_nonce'] ) || ! wp_verify_nonce( $_POST['lp_wachtwoord_vergeten_nonce'], 'lp_wachtwoord_vergeten' ) ) {
        $token = lp_sla_fouten_op( 'wachtwoord_vergeten', [ __( 'Beveiligingscontrole mislukt. Probeer opnieuw.', 'mijn-ledenportaal' ) ] );
        wp_safe_redirect( add_query_arg( 'lp_fout_wachtwoord_vergeten', $token, lp_huidige_url() ) );
        exit;
    }

    $email = sanitize_email( $_POST['email'] ?? '' );

    if ( empty( $email ) || ! is_email( $email ) ) {
        $token = lp_sla_fouten_op( 'wachtwoord_vergeten', [ __( 'Voer een geldig e-mailadres in.', 'mijn-ledenportaal' ) ] );
        wp_safe_redirect( add_query_arg( 'lp_fout_wachtwoord_vergeten', $token, lp_huidige_url() ) );
        exit;
    }

    // Altijd succesbericht tonen, ook als e-mail niet bestaat (security: geen user enumeration)
    $gebruiker = get_user_by( 'email', $email );

    if ( $gebruiker ) {
        $reset_key = get_password_reset_key( $gebruiker );

        if ( ! is_wp_error( $reset_key ) ) {
            $nieuw_wachtwoord_id = get_option( 'lp_nieuw_wachtwoord_pagina_id', 0 );
            $reset_url = $nieuw_wachtwoord_id
                ? add_query_arg(
                    [ 'key' => $reset_key, 'login' => rawurlencode( $gebruiker->user_login ) ],
                    get_permalink( $nieuw_wachtwoord_id )
                )
                : '';

            if ( $reset_url ) {
                lp_mail_wachtwoord_reset( $gebruiker, $reset_url );
            }
        }
    }

    wp_safe_redirect( add_query_arg( 'lp_succes', 'wachtwoord_vergeten', lp_huidige_url() ) );
    exit;
}

/**
 * Stap 2: verwerk "nieuw wachtwoord instellen" formulier
 */
add_action( 'init', 'lp_verwerk_nieuw_wachtwoord' );

function lp_verwerk_nieuw_wachtwoord() {
    if ( ! isset( $_POST['lp_nieuw_wachtwoord_submit'] ) ) return;

    if ( ! isset( $_POST['lp_nieuw_wachtwoord_nonce'] ) || ! wp_verify_nonce( $_POST['lp_nieuw_wachtwoord_nonce'], 'lp_nieuw_wachtwoord' ) ) {
        $token = lp_sla_fouten_op( 'nieuw_wachtwoord', [ __( 'Beveiligingscontrole mislukt. Probeer opnieuw.', 'mijn-ledenportaal' ) ] );
        wp_safe_redirect( add_query_arg( 'lp_fout_nieuw_wachtwoord', $token, lp_huidige_url() ) );
        exit;
    }

    $key        = sanitize_text_field( $_POST['key'] ?? '' );
    $login      = sanitize_text_field( $_POST['login'] ?? '' );
    $wachtwoord = $_POST['nieuw_wachtwoord'] ?? '';
    $wachtwoord2 = $_POST['nieuw_wachtwoord2'] ?? '';

    $fouten = [];

    if ( empty( $key ) || empty( $login ) ) {
        $fouten[] = __( 'Ongeldige reset-link. Vraag een nieuwe aan.', 'mijn-ledenportaal' );
    }

    if ( strlen( $wachtwoord ) < 8 ) {
        $fouten[] = __( 'Wachtwoord moet minimaal 8 tekens bevatten.', 'mijn-ledenportaal' );
    }

    if ( $wachtwoord !== $wachtwoord2 ) {
        $fouten[] = __( 'Wachtwoorden komen niet overeen.', 'mijn-ledenportaal' );
    }

    if ( ! empty( $fouten ) ) {
        $token = lp_sla_fouten_op( 'nieuw_wachtwoord', $fouten );
        wp_safe_redirect( add_query_arg( 'lp_fout_nieuw_wachtwoord', $token, lp_huidige_url() ) );
        exit;
    }

    $gebruiker = check_password_reset_key( $key, $login );

    if ( is_wp_error( $gebruiker ) ) {
        $token = lp_sla_fouten_op( 'nieuw_wachtwoord', [ __( 'Deze reset-link is verlopen of ongeldig. Vraag een nieuwe aan.', 'mijn-ledenportaal' ) ] );
        wp_safe_redirect( add_query_arg( 'lp_fout_nieuw_wachtwoord', $token, lp_huidige_url() ) );
        exit;
    }

    reset_password( $gebruiker, $wachtwoord );

    $login_id = get_option( 'lp_login_pagina_id', 0 );
    $url = $login_id
        ? add_query_arg( 'lp_succes', 'nieuw_wachtwoord', get_permalink( $login_id ) )
        : add_query_arg( 'lp_succes', 'nieuw_wachtwoord', home_url() );

    wp_safe_redirect( $url );
    exit;
}

/**
 * Al ingelogd → redirect weg van wachtwoord-pagina's
 */
add_action( 'template_redirect', function() {
    if ( ! is_user_logged_in() ) return;

    $vergeten_id       = get_option( 'lp_wachtwoord_vergeten_pagina_id', 0 );
    $nieuw_wachtwoord_id = get_option( 'lp_nieuw_wachtwoord_pagina_id', 0 );

    if ( ( $vergeten_id && is_page( $vergeten_id ) ) || ( $nieuw_wachtwoord_id && is_page( $nieuw_wachtwoord_id ) ) ) {
        $account_id = get_option( 'lp_account_pagina_id', 0 );
        wp_safe_redirect( $account_id ? get_permalink( $account_id ) : home_url() );
        exit;
    }
} );

/**
 * Shortcode: [ledenportaal_wachtwoord_vergeten]
 */
add_shortcode( 'ledenportaal_wachtwoord_vergeten', 'lp_render_wachtwoord_vergeten' );

function lp_render_wachtwoord_vergeten() {
    if ( is_user_logged_in() ) return '';

    $fouten = lp_haal_fouten_op( 'wachtwoord_vergeten' );
    $succes = isset( $_GET['lp_succes'] ) && sanitize_key( $_GET['lp_succes'] ) === 'wachtwoord_vergeten';

    ob_start();
    include LP_PATH . 'templates/wachtwoord-vergeten-form.php';
    return ob_get_clean();
}

/**
 * Shortcode: [ledenportaal_nieuw_wachtwoord]
 */
add_shortcode( 'ledenportaal_nieuw_wachtwoord', 'lp_render_nieuw_wachtwoord' );

function lp_render_nieuw_wachtwoord() {
    if ( is_user_logged_in() ) return '';

    $fouten = lp_haal_fouten_op( 'nieuw_wachtwoord' );
    $key    = sanitize_text_field( $_GET['key'] ?? '' );
    $login  = sanitize_text_field( $_GET['login'] ?? '' );

    // Controleer of de key geldig is bij GET-request (voor tonen van foutmelding als link verlopen is)
    $key_geldig = true;
    if ( $key && $login ) {
        $check = check_password_reset_key( $key, $login );
        if ( is_wp_error( $check ) ) {
            $key_geldig = false;
        }
    } elseif ( empty( $fouten ) ) {
        $key_geldig = false;
    }

    ob_start();
    include LP_PATH . 'templates/nieuw-wachtwoord-form.php';
    return ob_get_clean();
}

/**
 * E-mail: wachtwoord reset
 */
function lp_mail_wachtwoord_reset( WP_User $gebruiker, string $reset_url ) {
    $naam      = $gebruiker->display_name ?: $gebruiker->first_name;
    $onderwerp = __( 'Wachtwoord opnieuw instellen', 'mijn-ledenportaal' );

    $bericht = '
<!DOCTYPE html>
<html lang="nl">
<head><meta charset="UTF-8"></head>
<body style="font-family: Arial, sans-serif; color: #333; max-width: 600px; margin: 0 auto; padding: 20px;">
    <h2 style="color: #0091D5;">' . esc_html__( 'Wachtwoord opnieuw instellen', 'mijn-ledenportaal' ) . '</h2>
    <p>' . sprintf( esc_html__( 'Hallo %s,', 'mijn-ledenportaal' ), esc_html( $naam ) ) . '</p>
    <p>' . esc_html__( 'We hebben een verzoek ontvangen om het wachtwoord van je account opnieuw in te stellen.', 'mijn-ledenportaal' ) . '</p>
    <p>
        <a href="' . esc_url( $reset_url ) . '" style="display: inline-block; background: #0091D5; color: #fff; padding: 12px 24px; text-decoration: none; border-radius: 4px;">
            ' . esc_html__( 'Nieuw wachtwoord instellen', 'mijn-ledenportaal' ) . '
        </a>
    </p>
    <p style="color: #666; font-size: 0.9em;">' . esc_html__( 'Deze link is 24 uur geldig. Als je geen wachtwoord-reset hebt aangevraagd, kun je deze e-mail negeren.', 'mijn-ledenportaal' ) . '</p>
</body>
</html>';

    add_filter( 'wp_mail_content_type', fn() => 'text/html' );
    wp_mail( $gebruiker->user_email, $onderwerp, $bericht );
    remove_filter( 'wp_mail_content_type', fn() => 'text/html' );
}
