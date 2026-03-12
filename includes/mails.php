<?php
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Mail template helper
 */
function lp_mail_template( $naam, $data = [] ) {
    $gebruiker    = $data['gebruiker'] ?? null;
    $voornaam     = $gebruiker ? $gebruiker->first_name : '';
    $volledige_naam = $gebruiker ? $gebruiker->display_name : '';
    $email        = $gebruiker ? $gebruiker->user_email : '';
    $site_naam    = get_bloginfo( 'name' );
    $site_url     = home_url();
    $login_pagina_id = get_option( 'lp_login_pagina_id', 0 );
    $login_url    = $login_pagina_id ? get_permalink( $login_pagina_id ) : wp_login_url();

    switch ( $naam ) {
        case 'registratie_bevestiging':
            return <<<HTML
<!DOCTYPE html>
<html lang="nl">
<head><meta charset="UTF-8"></head>
<body style="font-family: Arial, sans-serif; color: #333; max-width: 600px; margin: 0 auto; padding: 20px;">
    <h2 style="color: #0091D5;">{$site_naam}</h2>
    <p>Beste {$voornaam},</p>
    <p>Bedankt voor je aanmelding bij <strong>{$site_naam}</strong>.</p>
    <p>Je aanmelding is in goede orde ontvangen en wordt momenteel beoordeeld. Zodra je account is goedgekeurd, ontvang je een bevestigingsmail.</p>
    <p>Met vriendelijke groet,<br>Het team van {$site_naam}</p>
    <hr style="border: none; border-top: 1px solid #e0e0e0; margin: 30px 0;">
    <p style="font-size: 12px; color: #999;"><a href="{$site_url}">{$site_naam}</a></p>
</body>
</html>
HTML;

        case 'admin_nieuw_lid':
            $admin_url = admin_url( 'admin.php?page=lp-ledenbeheer' );
            return <<<HTML
<!DOCTYPE html>
<html lang="nl">
<head><meta charset="UTF-8"></head>
<body style="font-family: Arial, sans-serif; color: #333; max-width: 600px; margin: 0 auto; padding: 20px;">
    <h2 style="color: #0091D5;">{$site_naam} — Nieuw lid</h2>
    <p>Er heeft zich een nieuw lid aangemeld:</p>
    <ul>
        <li><strong>Naam:</strong> {$volledige_naam}</li>
        <li><strong>E-mail:</strong> {$email}</li>
    </ul>
    <p>
        <a href="{$admin_url}" style="background: #0091D5; color: white; padding: 10px 20px; text-decoration: none; border-radius: 4px; display: inline-block;">
            Bekijk ledenbeheer
        </a>
    </p>
    <hr style="border: none; border-top: 1px solid #e0e0e0; margin: 30px 0;">
    <p style="font-size: 12px; color: #999;"><a href="{$site_url}">{$site_naam}</a></p>
</body>
</html>
HTML;

        case 'account_goedgekeurd':
            return <<<HTML
<!DOCTYPE html>
<html lang="nl">
<head><meta charset="UTF-8"></head>
<body style="font-family: Arial, sans-serif; color: #333; max-width: 600px; margin: 0 auto; padding: 20px;">
    <h2 style="color: #0091D5;">{$site_naam}</h2>
    <p>Beste {$voornaam},</p>
    <p>Goed nieuws! Je account bij <strong>{$site_naam}</strong> is goedgekeurd.</p>
    <p>Je kunt nu inloggen op het ledenportaal:</p>
    <p>
        <a href="{$login_url}" style="background: #0091D5; color: white; padding: 10px 20px; text-decoration: none; border-radius: 4px; display: inline-block;">
            Inloggen
        </a>
    </p>
    <p>Met vriendelijke groet,<br>Het team van {$site_naam}</p>
    <hr style="border: none; border-top: 1px solid #e0e0e0; margin: 30px 0;">
    <p style="font-size: 12px; color: #999;"><a href="{$site_url}">{$site_naam}</a></p>
</body>
</html>
HTML;

        case 'account_afgewezen':
            $admin_email = get_option( 'admin_email' );
            return <<<HTML
<!DOCTYPE html>
<html lang="nl">
<head><meta charset="UTF-8"></head>
<body style="font-family: Arial, sans-serif; color: #333; max-width: 600px; margin: 0 auto; padding: 20px;">
    <h2 style="color: #0091D5;">{$site_naam}</h2>
    <p>Beste {$voornaam},</p>
    <p>Na beoordeling van je aanmelding bij <strong>{$site_naam}</strong> kunnen wij je lidmaatschap helaas niet goedkeuren.</p>
    <p>Heb je vragen? Neem dan contact op via <a href="mailto:{$admin_email}">{$admin_email}</a>.</p>
    <p>Met vriendelijke groet,<br>Het team van {$site_naam}</p>
    <hr style="border: none; border-top: 1px solid #e0e0e0; margin: 30px 0;">
    <p style="font-size: 12px; color: #999;"><a href="{$site_url}">{$site_naam}</a></p>
</body>
</html>
HTML;

        case 'account_bijgewerkt':
            $admin_url = get_edit_user_link( $gebruiker ? $gebruiker->ID : 0 );
            return <<<HTML
<!DOCTYPE html>
<html lang="nl">
<head><meta charset="UTF-8"></head>
<body style="font-family: Arial, sans-serif; color: #333; max-width: 600px; margin: 0 auto; padding: 20px;">
    <h2 style="color: #0091D5;">{$site_naam} — Account bijgewerkt</h2>
    <p>Een lid heeft zijn of haar accountgegevens bijgewerkt:</p>
    <ul>
        <li><strong>Naam:</strong> {$volledige_naam}</li>
        <li><strong>E-mail:</strong> {$email}</li>
    </ul>
    <p>
        <a href="{$admin_url}" style="background: #0091D5; color: white; padding: 10px 20px; text-decoration: none; border-radius: 4px; display: inline-block;">
            Bekijk profiel
        </a>
    </p>
    <hr style="border: none; border-top: 1px solid #e0e0e0; margin: 30px 0;">
    <p style="font-size: 12px; color: #999;"><a href="{$site_url}">{$site_naam}</a></p>
</body>
</html>
HTML;

        default:
            return '';
    }
}

/**
 * Na registratie: mail naar gebruiker + admin
 */
add_action( 'lp_na_registratie', function( $user_id ) {
    $user    = get_userdata( $user_id );
    $headers = [ 'Content-Type: text/html; charset=UTF-8' ];

    if ( get_option( 'lp_mail_actief_registratie_bevestiging', '1' ) === '1' ) {
        wp_mail(
            $user->user_email,
            sprintf( __( 'Je aanmelding bij %s is ontvangen', 'mijn-ledenportaal' ), get_bloginfo( 'name' ) ),
            lp_mail_template( 'registratie_bevestiging', [ 'gebruiker' => $user ] ),
            $headers
        );
    }

    if ( get_option( 'lp_mail_actief_admin_nieuw_lid', '1' ) === '1' ) {
        wp_mail(
            get_option( 'admin_email' ),
            sprintf( __( 'Nieuw lid aangemeld: %s', 'mijn-ledenportaal' ), $user->display_name ),
            lp_mail_template( 'admin_nieuw_lid', [ 'gebruiker' => $user ] ),
            $headers
        );
    }
} );

/**
 * Na goedkeuring: mail naar gebruiker
 */
add_action( 'lp_account_goedgekeurd', function( $user_id ) {
    if ( get_option( 'lp_mail_actief_account_goedgekeurd', '1' ) !== '1' ) return;

    $user    = get_userdata( $user_id );
    $headers = [ 'Content-Type: text/html; charset=UTF-8' ];

    wp_mail(
        $user->user_email,
        sprintf( __( 'Je account bij %s is goedgekeurd', 'mijn-ledenportaal' ), get_bloginfo( 'name' ) ),
        lp_mail_template( 'account_goedgekeurd', [ 'gebruiker' => $user ] ),
        $headers
    );
} );

/**
 * Na account-update: notificatie naar ingesteld e-mailadres
 */
add_action( 'lp_account_bijgewerkt', function( $user_id ) {
    if ( get_option( 'lp_mail_actief_account_bijgewerkt', '1' ) !== '1' ) return;

    $notificatie_email = get_option( 'lp_notificatie_email', '' );
    if ( empty( $notificatie_email ) || ! is_email( $notificatie_email ) ) return;

    $user    = get_userdata( $user_id );
    $headers = [ 'Content-Type: text/html; charset=UTF-8' ];

    wp_mail(
        $notificatie_email,
        sprintf( __( 'Account bijgewerkt: %s', 'mijn-ledenportaal' ), $user->display_name ),
        lp_mail_template( 'account_bijgewerkt', [ 'gebruiker' => $user ] ),
        $headers
    );
} );

/**
 * Na afwijzing: mail naar gebruiker
 */
add_action( 'lp_account_afgewezen', function( $user_id ) {
    if ( get_option( 'lp_mail_actief_account_afgewezen', '1' ) !== '1' ) return;

    $user    = get_userdata( $user_id );
    $headers = [ 'Content-Type: text/html; charset=UTF-8' ];

    wp_mail(
        $user->user_email,
        sprintf( __( 'Je aanmelding bij %s', 'mijn-ledenportaal' ), get_bloginfo( 'name' ) ),
        lp_mail_template( 'account_afgewezen', [ 'gebruiker' => $user ] ),
        $headers
    );
} );
