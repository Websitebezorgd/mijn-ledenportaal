<?php
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Paginabeveiliging via template_redirect
 */
add_action( 'template_redirect', function() {
    $login_pagina_id = get_option( 'lp_login_pagina_id', 0 );

    // Check account status voor ingelogde gebruikers
    if ( is_user_logged_in() ) {
        $user_id = get_current_user_id();
        $status  = get_user_meta( $user_id, 'lp_account_status', true );

        // Sla de login pagina zelf over
        if ( $login_pagina_id && is_page( $login_pagina_id ) ) {
            return;
        }

        if ( $status === 'pending' || $status === 'rejected' ) {
            wp_logout();
            $redirect = $login_pagina_id
                ? add_query_arg( 'lp_status', $status, get_permalink( $login_pagina_id ) )
                : home_url();
            wp_redirect( $redirect );
            exit;
        }
        return;
    }

    // Niet ingelogd: check beveiligde pagina's
    $beveiligde_paginas = get_option( 'lp_beveiligde_paginas', [] );

    if ( ! empty( $beveiligde_paginas ) && is_page( $beveiligde_paginas ) ) {
        $redirect = $login_pagina_id
            ? add_query_arg( [ 'lp_status' => 'toegang_geweigerd', 'lp_redirect' => urlencode( get_permalink() ) ], get_permalink( $login_pagina_id ) )
            : home_url();
        wp_redirect( $redirect );
        exit;
    }
} );
