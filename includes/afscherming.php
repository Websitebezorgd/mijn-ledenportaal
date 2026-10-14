<?php
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Portaalpagina's (login, registratie, wachtwoord) die altijd bereikbaar moeten blijven
 */
function lp_altijd_toegankelijke_paginas() {
    return array_values( array_filter( [
        (int) get_option( 'lp_login_pagina_id', 0 ),
        (int) get_option( 'lp_registratie_pagina_id', 0 ),
        (int) get_option( 'lp_wachtwoord_vergeten_pagina_id', 0 ),
        (int) get_option( 'lp_nieuw_wachtwoord_pagina_id', 0 ),
    ] ) );
}

/**
 * Valt een URL-pad onder de uitgesloten URL's uit de instellingen?
 */
function lp_pad_is_uitgesloten( $pad ) {
    $uitgesloten_urls = get_option( 'lp_uitgesloten_urls', '' );
    if ( $uitgesloten_urls === '' ) return false;

    $pad = rtrim( (string) $pad, '/' ) ?: '/';
    foreach ( array_filter( array_map( 'trim', explode( "\n", $uitgesloten_urls ) ) ) as $patroon ) {
        $patroon = rtrim( $patroon, '/' ) ?: '/';
        if ( str_ends_with( $patroon, '*' ) ) {
            $prefix = rtrim( substr( $patroon, 0, -1 ), '/' );
            if ( str_starts_with( $pad, $prefix ) ) return true;
        } elseif ( $pad === $patroon ) {
            return true;
        }
    }
    return false;
}

/**
 * Is de inhoud van dit bericht/deze pagina afgeschermd voor niet-ingelogde bezoekers?
 *
 * Per-post variant van de regels in template_redirect, voor plekken waar inhoud buiten de
 * normale paginaweergave om wordt getoond: REST API, feeds en zoekresultaten.
 * Taxonomieën schermen (net als in template_redirect) alleen archiefpagina's af.
 */
function lp_post_is_beveiligd( $post ) {
    $post = get_post( $post );
    if ( ! $post ) return false;

    if ( in_array( $post->ID, lp_altijd_toegankelijke_paginas(), true ) ) return false;
    if ( lp_pad_is_uitgesloten( wp_parse_url( (string) get_permalink( $post ), PHP_URL_PATH ) ) ) return false;

    if ( get_option( 'lp_beveilig_alles', '' ) ) {
        return $post->ID !== (int) get_option( 'page_on_front', 0 );
    }

    $beveiligde_paginas = array_map( 'intval', (array) get_option( 'lp_beveiligde_paginas', [] ) );
    if ( $post->post_type === 'page' && in_array( $post->ID, $beveiligde_paginas, true ) ) return true;

    return in_array( $post->post_type, (array) get_option( 'lp_beveiligde_post_types', [] ), true );
}

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
    $altijd_toegankelijk = lp_altijd_toegankelijke_paginas();
    $beveilig_alles      = get_option( 'lp_beveilig_alles', '' );

    // Uitgesloten URL's zijn altijd toegankelijk
    if ( lp_pad_is_uitgesloten( parse_url( $_SERVER['REQUEST_URI'] ?? '', PHP_URL_PATH ) ) ) {
        return;
    }

    $is_afgeschermd = false;

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

/**
 * REST API: geen inhoud van afgeschermde berichten aan niet-ingelogde bezoekers.
 * template_redirect draait niet voor /wp-json/, dus zonder dit is alles via wp/v2/pages en
 * wp/v2/posts op te halen. Titel en link blijven zichtbaar, net als in menu's.
 */
add_action( 'rest_api_init', function() {
    foreach ( get_post_types( [ 'show_in_rest' => true ] ) as $post_type ) {
        add_filter( "rest_prepare_{$post_type}", 'lp_rest_verberg_beveiligde_inhoud', 10, 2 );
    }
} );

function lp_rest_verberg_beveiligde_inhoud( $response, $post ) {
    if ( is_user_logged_in() || ! lp_post_is_beveiligd( $post ) ) return $response;

    $data = $response->get_data();
    foreach ( [ 'content', 'excerpt' ] as $veld ) {
        if ( isset( $data[ $veld ] ) ) {
            $data[ $veld ] = [ 'rendered' => '', 'protected' => true ];
        }
    }
    $response->set_data( $data );
    return $response;
}

/**
 * Feeds: geen inhoud van afgeschermde berichten (bijv. /feed/ als berichten beveiligd zijn)
 */
add_filter( 'the_content_feed', 'lp_feed_verberg_beveiligde_inhoud' );
add_filter( 'the_excerpt_rss',  'lp_feed_verberg_beveiligde_inhoud' );

function lp_feed_verberg_beveiligde_inhoud( $inhoud ) {
    if ( is_user_logged_in() || ! lp_post_is_beveiligd( get_post() ) ) return $inhoud;
    return '';
}

/**
 * Zoekresultaten: afgeschermde pagina's en berichttypen niet tonen aan niet-ingelogde bezoekers.
 * Met "Beveilig de hele website" wordt de zoekpagina zelf al door template_redirect afgeschermd.
 */
add_action( 'pre_get_posts', function( $query ) {
    if ( is_admin() || ! $query->is_main_query() || ! $query->is_search() || is_user_logged_in() ) return;
    if ( get_option( 'lp_beveilig_alles', '' ) ) return;

    $beveiligde_paginas = array_filter( (array) get_option( 'lp_beveiligde_paginas', [] ), 'lp_post_is_beveiligd' );
    if ( $beveiligde_paginas ) {
        $query->set( 'post__not_in', array_merge( (array) $query->get( 'post__not_in' ), array_map( 'intval', $beveiligde_paginas ) ) );
    }

    $bev_post_types = (array) get_option( 'lp_beveiligde_post_types', [] );
    if ( $bev_post_types ) {
        $huidig     = $query->get( 'post_type' );
        $types      =( $huidig && $huidig !== 'any' ) ? (array) $huidig : array_values( get_post_types( [ 'exclude_from_search' => false ] ) );
        $toegestaan = array_values( array_diff( $types, $bev_post_types ) );
        // Lege lijst zou "alle typen" betekenen; een onbestaand type geeft geen resultaten
        $query->set( 'post_type', $toegestaan ?: [ 'lp_geen_resultaten' ] );
    }
} );
