<?php
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Shortcode: [ledenportaal_login]
 */
add_shortcode( 'ledenportaal_login', 'lp_render_login' );

function lp_render_login() {
    // Al ingelogd → redirect naar accountpagina
    if ( is_user_logged_in() ) {
        $account_id = get_option( 'lp_account_pagina_id', 0 );
        if ( $account_id ) {
            wp_redirect( get_permalink( $account_id ) );
            exit;
        }
    }

    $fouten = [];

    if ( isset( $_POST['lp_login_submit'] ) ) {
        // 1. Nonce validatie
        if ( ! isset( $_POST['lp_login_nonce'] ) || ! wp_verify_nonce( $_POST['lp_login_nonce'], 'lp_login' ) ) {
            $fouten[] = __( 'Beveiligingscontrole mislukt. Probeer opnieuw.', 'mijn-ledenportaal' );
        } else {
            $email      = sanitize_email( $_POST['email'] ?? '' );
            $wachtwoord = $_POST['wachtwoord'] ?? '';

            if ( empty( $email ) || empty( $wachtwoord ) ) {
                $fouten[] = __( 'Vul je e-mailadres en wachtwoord in.', 'mijn-ledenportaal' );
            } else {
                $gebruiker = wp_signon( [
                    'user_login'    => $email,
                    'user_password' => $wachtwoord,
                    'remember'      => ! empty( $_POST['onthouden'] ),
                ], is_ssl() );

                if ( is_wp_error( $gebruiker ) ) {
                    // Geen specifieke foutmelding — voorkom username/wachtwoord onthulling
                    $fouten[] = __( 'E-mailadres of wachtwoord is onjuist.', 'mijn-ledenportaal' );
                } else {
                    // 4. Check account status
                    $status = get_user_meta( $gebruiker->ID, 'lp_account_status', true );

                    if ( $status === 'pending' ) {
                        wp_logout();
                        $fouten[] = __( 'Je account wacht nog op goedkeuring. Je ontvangt een e-mail zodra je account is geactiveerd.', 'mijn-ledenportaal' );
                    } elseif ( $status === 'rejected' ) {
                        wp_logout();
                        $fouten[] = __( 'Je aanmelding is helaas afgewezen. Neem contact op voor meer informatie.', 'mijn-ledenportaal' );
                    } else {
                        // 5. Redirect naar accountpagina
                        $account_id = get_option( 'lp_account_pagina_id', 0 );
                        $redirect_url = $account_id ? get_permalink( $account_id ) : home_url();
                        wp_redirect( $redirect_url );
                        exit;
                    }
                }
            }
        }
    }

    ob_start();
    include LP_PATH . 'templates/login-form.php';
    return ob_get_clean();
}

/**
 * Uitloggen shortcode / link handler
 */
add_action( 'init', function() {
    if ( isset( $_GET['lp_uitloggen'] ) && wp_verify_nonce( $_GET['lp_uitloggen'], 'lp_uitloggen' ) ) {
        wp_logout();
        $login_id = get_option( 'lp_login_pagina_id', 0 );
        $redirect = $login_id ? get_permalink( $login_id ) : home_url();
        wp_redirect( $redirect );
        exit;
    }
} );

/**
 * Helper: genereer uitlog URL
 */
function lp_uitlog_url() {
    return add_query_arg( 'lp_uitloggen', wp_create_nonce( 'lp_uitloggen' ), home_url() );
}
