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
        // Account nog niet goedgekeurd of afgewezen (zie lp_blokkeer_login_op_status)
        $fout_data = $gebruiker->get_error_data();
        $status    = is_array( $fout_data ) ? ( $fout_data['lp_account_status'] ?? '' ) : '';
        if ( $status ) {
            $login_id = get_option( 'lp_login_pagina_id', 0 );
            $url      = $login_id ? add_query_arg( 'lp_status', $status, get_permalink( $login_id ) ) : home_url();
            wp_safe_redirect( $url );
            exit;
        }

        $melding = $gebruiker->get_error_code() === 'lp_te_veel_pogingen'
            ? $gebruiker->get_error_message()
            : __( 'E-mailadres of wachtwoord is onjuist.', 'mijn-ledenportaal' );
        $token = lp_sla_fouten_op( 'login', [ $melding ] );
        wp_safe_redirect( add_query_arg( 'lp_fout_login', $token, lp_huidige_url() ) );
        exit;
    }

    // Stuur terug naar de pagina waar de bezoeker vandaan kwam (via lp_redirect param)
    $redirect_naar = isset( $_POST['lp_redirect'] ) ? esc_url_raw( urldecode( $_POST['lp_redirect'] ) ) : '';

    if ( $redirect_naar && wp_validate_redirect( $redirect_naar, false ) ) {
        wp_safe_redirect( $redirect_naar );
    } else {
        $na_login_id = (int) get_option( 'lp_na_login_pagina_id', 0 );
        $account_id  = (int) get_option( 'lp_account_pagina_id', 0 );
        $doel        = $na_login_id ? get_permalink( $na_login_id ) : ( $account_id ? get_permalink( $account_id ) : home_url() );
        wp_safe_redirect( $doel );
    }
    exit;
}

/**
 * Weiger inloggen voor accounts die in afwachting of afgewezen zijn.
 *
 * Via authenticate (na de wachtwoordcontrole van WordPress, prioriteit 20) geldt dit voor
 * álle loginroutes: het eigen formulier, wp-login.php, XML-RPC en applicatiewachtwoorden.
 * Alleen bij een correct wachtwoord komt hier een WP_User binnen, dus de status lekt niet.
 */
add_filter( 'authenticate', 'lp_blokkeer_login_op_status', 30 );

function lp_blokkeer_login_op_status( $gebruiker ) {
    if ( ! ( $gebruiker instanceof WP_User ) ) return $gebruiker;

    $status = get_user_meta( $gebruiker->ID, 'lp_account_status', true );

    if ( $status === 'pending' ) {
        return new WP_Error( 'lp_account_pending', __( 'Je account wacht nog op goedkeuring.', 'mijn-ledenportaal' ), [ 'lp_account_status' => 'pending' ] );
    }
    if ( $status === 'rejected' ) {
        return new WP_Error( 'lp_account_rejected', __( 'Je aanmelding is helaas afgewezen.', 'mijn-ledenportaal' ), [ 'lp_account_status' => 'rejected' ] );
    }

    return $gebruiker;
}

/**
 * Rate limiting op inloggen, voor alle loginroutes (eigen formulier, wp-login.php, XML-RPC).
 *
 * Maximaal 5 mislukte pogingen per IP + gebruikersnaam en 30 per IP binnen 15 minuten.
 * Blokkeert ook bij een correct wachtwoord, anders heeft de blokkade geen zin.
 */
define( 'LP_LOGIN_MAX_PER_ACCOUNT', 5 );
define( 'LP_LOGIN_MAX_PER_IP', 30 );

add_filter( 'authenticate', 'lp_blokkeer_login_na_te_veel_pogingen', 40, 2 );

function lp_blokkeer_login_na_te_veel_pogingen( $gebruiker, $gebruikersnaam ) {
    if ( empty( $gebruikersnaam ) ) return $gebruiker;

    $ip = lp_client_ip();
    if ( lp_limiet_bereikt( 'login', $ip . '|' . $gebruikersnaam, LP_LOGIN_MAX_PER_ACCOUNT )
        || lp_limiet_bereikt( 'login_ip', $ip, LP_LOGIN_MAX_PER_IP ) ) {
        return new WP_Error( 'lp_te_veel_pogingen', __( 'Te veel mislukte inlogpogingen. Probeer het over 15 minuten opnieuw.', 'mijn-ledenportaal' ) );
    }
    return $gebruiker;
}

add_action( 'wp_login_failed', function( $gebruikersnaam, $fout = null ) {
    // Eigen blokkades (status, te veel pogingen) niet meetellen: die verlengen anders zichzelf
    if ( $fout instanceof WP_Error && str_starts_with( (string) $fout->get_error_code(), 'lp_' ) ) return;

    $ip = lp_client_ip();
    lp_limiet_tel( 'login', $ip . '|' . $gebruikersnaam, 15 * MINUTE_IN_SECONDS );
    lp_limiet_tel( 'login_ip', $ip, 15 * MINUTE_IN_SECONDS );
}, 10, 2 );

add_action( 'wp_login', function( $user_login, $gebruiker ) {
    // Er kan met gebruikersnaam of e-mailadres zijn ingelogd; reset beide tellers
    $ip = lp_client_ip();
    lp_limiet_reset( 'login', $ip . '|' . $user_login );
    lp_limiet_reset( 'login', $ip . '|' . $gebruiker->user_email );
}, 10, 2 );

/**
 * Al ingelogd → redirect weg van loginpagina
 */
add_action( 'template_redirect', function() {
    if ( ! is_user_logged_in() ) return;
    $login_id = get_option( 'lp_login_pagina_id', 0 );
    if ( $login_id && is_page( $login_id ) ) {
        $na_login_id = (int) get_option( 'lp_na_login_pagina_id', 0 );
        $account_id  = (int) get_option( 'lp_account_pagina_id', 0 );
        $doel        = $na_login_id ? get_permalink( $na_login_id ) : ( $account_id ? get_permalink( $account_id ) : home_url() );
        wp_safe_redirect( $doel );
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

add_shortcode( 'ledenportaal_uitlog_url', function() {
    return esc_url( lp_uitlog_url() );
} );
