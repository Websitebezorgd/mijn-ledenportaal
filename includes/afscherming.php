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

    // Niet ingelogd: check beveiliging
    $login_id             = (int) get_option( 'lp_login_pagina_id', 0 );
    $account_id           = (int) get_option( 'lp_account_pagina_id', 0 );
    $registratie_id       = (int) get_option( 'lp_registratie_pagina_id', 0 );
    $wachtwoord_id        = (int) get_option( 'lp_wachtwoord_vergeten_pagina_id', 0 );
    $nieuw_wachtwoord_id  = (int) get_option( 'lp_nieuw_wachtwoord_pagina_id', 0 );

    $altijd_toegankelijk = array_filter( [ $login_id, $registratie_id, $wachtwoord_id, $nieuw_wachtwoord_id ] );

    $beveilig_alles = get_option( 'lp_beveilig_alles', '' );

    // Uitgesloten URL's controleren
    $huidig_pad = rtrim( parse_url( $_SERVER['REQUEST_URI'] ?? '', PHP_URL_PATH ) ?: '', '/' ) ?: '/';
    $uitgesloten_urls = get_option( 'lp_uitgesloten_urls', '' );
    $is_uitgesloten = false;
    if ( $uitgesloten_urls !== '' ) {
        foreach ( array_filter( array_map( 'trim', explode( "\n", $uitgesloten_urls ) ) ) as $patroon ) {
            $patroon = rtrim( $patroon, '/' ) ?: '/';
            if ( str_ends_with( $patroon, '*' ) ) {
                $prefix = rtrim( substr( $patroon, 0, -1 ), '/' );
                if ( str_starts_with( $huidig_pad, $prefix ) ) {
                    $is_uitgesloten = true;
                    break;
                }
            } elseif ( $huidig_pad === $patroon ) {
                $is_uitgesloten = true;
                break;
            }
        }
    }

    $is_afgeschermd = false;

    if ( $is_uitgesloten ) {
        return; // Altijd toegankelijk
    }

    if ( $beveilig_alles ) {
        // Alles beveiligen, behalve de portaal-pagina's zelf
        $is_afgeschermd = ! is_page( $altijd_toegankelijk ) && ! is_front_page();
    } else {
        $beveiligde_paginas    = get_option( 'lp_beveiligde_paginas', [] );
        $bev_post_types        = (array) get_option( 'lp_beveiligde_post_types', [] );
        $bev_taxonomieen       = (array) get_option( 'lp_beveiligde_taxonomieen', [] );

        $is_afgeschermd = ( ! empty( $beveiligde_paginas ) && is_page( $beveiligde_paginas ) )
            || ( ! empty( $bev_post_types ) && ( is_singular( $bev_post_types ) || is_post_type_archive( $bev_post_types ) ) )
            || ( ! empty( $bev_taxonomieen ) && is_tax( $bev_taxonomieen ) )
            || ( in_array( 'category', $bev_taxonomieen, true ) && is_category() )
            || ( in_array( 'post_tag', $bev_taxonomieen, true ) && is_tag() );
    }

    if ( $is_afgeschermd ) {
        $redirect = $login_pagina_id
            ? add_query_arg( [ 'lp_status' => 'toegang_geweigerd', 'lp_redirect' => urlencode( (string) get_permalink() ) ], get_permalink( $login_pagina_id ) )
            : home_url();
        wp_redirect( $redirect );
        exit;
    }
} );
