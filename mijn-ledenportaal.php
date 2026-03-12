<?php
/**
 * Plugin Name: Mijn Ledenportaal
 * Description: Registratie, login en ledenbeheer zonder Ultimate Member
 * Version: 1.0.0
 * Author: WYS Media
 * Text Domain: mijn-ledenportaal
 */

if ( ! defined( 'ABSPATH' ) ) exit;

define( 'LP_PATH', plugin_dir_path( __FILE__ ) );
define( 'LP_URL',  plugin_dir_url( __FILE__ ) );
define( 'LP_VERSION', '1.0.0' );

require_once LP_PATH . 'includes/registratie.php';
require_once LP_PATH . 'includes/login.php';
require_once LP_PATH . 'includes/account.php';
require_once LP_PATH . 'includes/afscherming.php';
require_once LP_PATH . 'includes/mails.php';
require_once LP_PATH . 'includes/admin.php';

// Assets laden
add_action( 'wp_enqueue_scripts', function() {
    wp_enqueue_style( 'ledenportaal-css', LP_URL . 'assets/css/ledenportaal.css', [], LP_VERSION );
    wp_enqueue_script( 'ledenportaal-js', LP_URL . 'assets/js/ledenportaal.js', [ 'jquery' ], LP_VERSION, true );
} );

// Activatie hook: stel standaard opties in
register_activation_hook( __FILE__, function() {
    add_option( 'lp_login_pagina_id', 0 );
    add_option( 'lp_account_pagina_id', 0 );
    add_option( 'lp_registratie_pagina_id', 0 );
    add_option( 'lp_beveiligde_paginas', [] );
} );
