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

require_once LP_PATH . 'includes/registratie.php';
require_once LP_PATH . 'includes/wachtwoord.php';
require_once LP_PATH . 'includes/login.php';
require_once LP_PATH . 'includes/account.php';
require_once LP_PATH . 'includes/afscherming.php';
require_once LP_PATH . 'includes/mails.php';
require_once LP_PATH . 'includes/admin.php';
require_once LP_PATH . 'includes/migratie.php';

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

// Assets laden
add_action('wp_enqueue_scripts', function () {
    wp_enqueue_style('ledenportaal-css', LP_URL . 'assets/css/ledenportaal.css', [], LP_VERSION);
    wp_enqueue_script('ledenportaal-js', LP_URL . 'assets/js/ledenportaal.js', ['jquery'], LP_VERSION, true);
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
