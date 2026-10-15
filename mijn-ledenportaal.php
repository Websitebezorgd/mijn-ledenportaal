<?php

/**
 * Plugin Name: Mijn Ledenportaal
 * Description: Registratie, login en ledenbeheer zonder Ultimate Member
 * Version: 1.0.0
 * Author: WYS Media
 * Text Domain: mijn-ledenportaal
 */

if (! defined('ABSPATH')) exit;

define('LP_PATH', plugin_dir_path(__FILE__));
define('LP_URL',  plugin_dir_url(__FILE__));
define('LP_VERSION', '1.0.0');

/**
 * Helper: sla formulierfouten op in een transient (60 seconden)
 */
function lp_sla_fouten_op($sleutel, array $fouten)
{
    $token = wp_generate_password(12, false);
    set_transient('lp_fouten_' . $sleutel . '_' . $token, $fouten, 60);
    // Geef token terug zodat het als query-param meegestuurd kan worden
    return $token;
}

/**
 * Helper: haal formulierfouten op en verwijder transient direct
 */
function lp_haal_fouten_op($sleutel)
{
    $token = sanitize_key($_GET['lp_fout_' . $sleutel] ?? '');
    if (! $token) return [];
    $fouten = get_transient('lp_fouten_' . $sleutel . '_' . $token) ?: [];
    delete_transient('lp_fouten_' . $sleutel . '_' . $token);
    return $fouten;
}

/**
 * Helper: huidige URL zonder lp_fout_* query params
 */
function lp_huidige_url()
{
    $url = (is_ssl() ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
    return remove_query_arg(['lp_fout_login', 'lp_fout_registratie', 'lp_fout_account', 'lp_fout_wachtwoord_vergeten', 'lp_fout_nieuw_wachtwoord', 'lp_status'], $url);
}

/**
 * Helper: IP-adres van de bezoeker (voor rate limiting)
 *
 * Bewust REMOTE_ADDR en geen X-Forwarded-For: die header is door de bezoeker te vervalsen.
 */
function lp_client_ip()
{
    return sanitize_text_field($_SERVER['REMOTE_ADDR'] ?? '');
}

/**
 * Helper: rate limiting via transients — is het maximum voor deze actie + sleutel bereikt?
 */
function lp_limiet_bereikt($actie, $sleutel, $max)
{
    return (int) get_transient(lp_limiet_transient($actie, $sleutel)) >= $max;
}

/**
 * Helper: tel een poging mee; het venster (seconden) begint bij elke poging opnieuw
 */
function lp_limiet_tel($actie, $sleutel, $venster)
{
    $transient = lp_limiet_transient($actie, $sleutel);
    set_transient($transient, (int) get_transient($transient) + 1, $venster);
}

function lp_limiet_reset($actie, $sleutel)
{
    delete_transient(lp_limiet_transient($actie, $sleutel));
}

function lp_limiet_transient($actie, $sleutel)
{
    return 'lp_limiet_' . $actie . '_' . md5(strtolower((string) $sleutel));
}

require_once LP_PATH . 'includes/registratie.php';
require_once LP_PATH . 'includes/wachtwoord.php';
require_once LP_PATH . 'includes/login.php';
require_once LP_PATH . 'includes/account.php';
require_once LP_PATH . 'includes/afscherming.php';
require_once LP_PATH . 'includes/mails.php';
require_once LP_PATH . 'includes/admin.php';

// Admin bar uitschakelen per rol
add_filter('show_admin_bar', function ($toon) {
    if (! is_user_logged_in()) return $toon;
    $uitgeschakeld_voor = (array) get_option('lp_adminbar_uitschakelen', []);
    if (empty($uitgeschakeld_voor)) return $toon;
    $gebruiker = wp_get_current_user();
    foreach ($gebruiker->roles as $rol) {
        if (in_array($rol, $uitgeschakeld_voor, true)) return false;
    }
    return $toon;
});

// Assets laden — versie = wijzigingstijd van het bestand, zodat browsers na een deploy de nieuwe versie ophalen
add_action('wp_enqueue_scripts', function () {
    $css = 'assets/css/ledenportaal.css';
    $js  = 'assets/js/ledenportaal.js';
    wp_enqueue_style('ledenportaal-css', LP_URL . $css, [], (string) (@filemtime(LP_PATH . $css) ?: LP_VERSION));
    wp_enqueue_script('ledenportaal-js', LP_URL . $js, ['jquery'], (string) (@filemtime(LP_PATH . $js) ?: LP_VERSION), true);
});

// Activatie hook: stel standaard opties in
register_activation_hook(__FILE__, function () {
    add_option('lp_login_pagina_id', 0);
    add_option('lp_account_pagina_id', 0);
    add_option('lp_registratie_pagina_id', 0);
    add_option('lp_wachtwoord_vergeten_pagina_id', 0);
    add_option('lp_nieuw_wachtwoord_pagina_id', 0);
    add_option('lp_beveilig_alles', '');
    add_option('lp_beveiligde_paginas', []);
});
