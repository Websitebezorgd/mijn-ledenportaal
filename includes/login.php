<?php
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * POST-verwerking in init — vóór alle output
 */
add_action( 'init', 'lp_verwerk_login' );

function lp_verwerk_login() {
    if ( ! isset( $_POST['lp_login_submit'] ) ) return;

    if ( ! isset( $_POST['lp_login_nonce'] ) || ! wp_verify_nonce( $_POST['lp_login_nonce'], 'lp_login' ) ) {
        $token = lp_sla_fouten_op( 'login', [ __( 'Beveiligingscontrole mislukt. Probeer opnieuw.', 'mijn-ledenportaal' ) ] );
        wp_safe_redirect( add_query_arg( 'lp_fout_login', $token, lp_huidige_url() ) );
        exit;
    }

    $email      = sanitize_email( $_POST['email'] ?? '' );
    $wachtwoord = $_POST['wachtwoord'] ?? '';

    if ( empty( $email ) || empty( $wachtwoord ) ) {
        $token = lp_sla_fouten_op( 'login', [ __( 'Vul je e-mailadres en wachtwoord in.', 'mijn-ledenportaal' ) ] );
        wp_safe_redirect( add_query_arg( 'lp_fout_login', $token, lp_huidige_url() ) );
        exit;
    }

    $gebruiker = wp_signon( [
        'user_login'    => $email,
        'user_password' => $wachtwoord,
        'remember'      => ! empty( $_POST['onthouden'] ),
    ], is_ssl() );

    if ( is_wp_error( $gebruiker ) ) {
        $token = lp_sla_fouten_op( 'login', [ __( 'E-mailadres of wachtwoord is onjuist.', 'mijn-ledenportaal' ) ] );
        wp_safe_redirect( add_query_arg( 'lp_fout_login', $token, lp_huidige_url() ) );
        exit;
    }

    $status = get_user_meta( $gebruiker->ID, 'lp_account_status', true );

    if ( $status === 'pending' ) {
        wp_logout();
        $login_id = get_option( 'lp_login_pagina_id', 0 );
        $url      = $login_id ? add_query_arg( 'lp_status', 'pending', get_permalink( $login_id ) ) : home_url();
        wp_safe_redirect( $url );
        exit;
    }

    if ( $status === 'rejected' ) {
        wp_logout();
        $login_id = get_option( 'lp_login_pagina_id', 0 );
        $url      = $login_id ? add_query_arg( 'lp_status', 'rejected', get_permalink( $login_id ) ) : home_url();
        wp_safe_redirect( $url );
        exit;
    }

    // Stuur terug naar de pagina waar de bezoeker vandaan kwam (via lp_redirect param)
    $redirect_naar = isset( $_POST['lp_redirect'] ) ? esc_url_raw( urldecode( $_POST['lp_redirect'] ) ) : '';

    if ( $redirect_naar && wp_validate_redirect( $redirect_naar, false ) ) {
        wp_safe_redirect( $redirect_naar );
    } else {
        $account_id = get_option( 'lp_account_pagina_id', 0 );
        wp_safe_redirect( $account_id ? get_permalink( $account_id ) : home_url() );
    }
    exit;
}

/**
 * Al ingelogd → redirect weg van loginpagina
 */
add_action( 'template_redirect', function() {
    if ( ! is_user_logged_in() ) return;
    $login_id = get_option( 'lp_login_pagina_id', 0 );
    if ( $login_id && is_page( $login_id ) ) {
        $account_id = get_option( 'lp_account_pagina_id', 0 );
        wp_safe_redirect( $account_id ? get_permalink( $account_id ) : home_url() );
        exit;
    }
} );

/**
 * Shortcode: [ledenportaal_login]
 */
add_shortcode( 'ledenportaal_login', 'lp_render_login' );

function lp_render_login() {
    $fouten = lp_haal_fouten_op( 'login' );

    ob_start();
    include LP_PATH . 'templates/login-form.php';
    return ob_get_clean();
}

/**
 * Uitloggen via URL
 */
add_action( 'init', function() {
    if ( isset( $_GET['lp_uitloggen'] ) && wp_verify_nonce( $_GET['lp_uitloggen'], 'lp_uitloggen' ) ) {
        wp_logout();
        $login_id = get_option( 'lp_login_pagina_id', 0 );
        wp_safe_redirect( $login_id ? get_permalink( $login_id ) : home_url() );
        exit;
    }
} );

function lp_uitlog_url() {
    return add_query_arg( 'lp_uitloggen', wp_create_nonce( 'lp_uitloggen' ), home_url() );
}
