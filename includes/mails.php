<?php
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Standaard onderwerp + inhoud per mail
 */
function lp_mail_defaults() {
    return [
        'registratie_bevestiging' => [
            'onderwerp' => 'Je aanmelding bij {{site_naam}} is ontvangen',
            'inhoud'    => "<p>Beste {{voornaam}},</p>\n<p>Bedankt voor je aanmelding bij <strong>{{site_naam}}</strong>.</p>\n<p>Je aanmelding is in goede orde ontvangen en wordt momenteel beoordeeld. Zodra je account is goedgekeurd, ontvang je een bevestigingsmail.</p>\n<p>Met vriendelijke groet,<br>Het team van {{site_naam}}</p>",
        ],
        'admin_nieuw_lid' => [
            'onderwerp' => 'Nieuw lid aangemeld: {{volledige_naam}}',
            'inhoud'    => "<p>Er heeft zich een nieuw lid aangemeld:</p>\n<ul>\n<li><strong>Naam:</strong> {{volledige_naam}}</li>\n<li><strong>E-mail:</strong> {{email}}</li>\n</ul>\n<p><a href=\"{{admin_url}}\" style=\"background:#0091D5;color:white;padding:10px 20px;text-decoration:none;border-radius:4px;display:inline-block;\">Bekijk ledenbeheer</a></p>",
        ],
        'account_goedgekeurd' => [
            'onderwerp' => 'Je account bij {{site_naam}} is goedgekeurd',
            'inhoud'    => "<p>Beste {{voornaam}},</p>\n<p>Goed nieuws! Je account bij <strong>{{site_naam}}</strong> is goedgekeurd.</p>\n<p>Je kunt nu inloggen op het ledenportaal:</p>\n<p><a href=\"{{login_url}}\" style=\"background:#0091D5;color:white;padding:10px 20px;text-decoration:none;border-radius:4px;display:inline-block;\">Inloggen</a></p>\n<p>Met vriendelijke groet,<br>Het team van {{site_naam}}</p>",
        ],
        'account_afgewezen' => [
            'onderwerp' => 'Je aanmelding bij {{site_naam}}',
            'inhoud'    => "<p>Beste {{voornaam}},</p>\n<p>Na beoordeling van je aanmelding bij <strong>{{site_naam}}</strong> kunnen wij je lidmaatschap helaas niet goedkeuren.</p>\n<p>Heb je vragen? Neem dan contact op via <a href=\"mailto:{{admin_email}}\">{{admin_email}}</a>.</p>\n<p>Met vriendelijke groet,<br>Het team van {{site_naam}}</p>",
        ],
        'account_bijgewerkt' => [
            'onderwerp' => 'Account bijgewerkt: {{volledige_naam}}',
            'inhoud'    => "<p>Een lid heeft zijn of haar accountgegevens bijgewerkt:</p>\n<ul>\n<li><strong>Naam:</strong> {{volledige_naam}}</li>\n<li><strong>E-mail:</strong> {{email}}</li>\n</ul>\n<p><a href=\"{{admin_url}}\" style=\"background:#0091D5;color:white;padding:10px 20px;text-decoration:none;border-radius:4px;display:inline-block;\">Bekijk profiel</a></p>",
        ],
        'wachtwoord_reset' => [
            'onderwerp' => 'Wachtwoord opnieuw instellen',
            'inhoud'    => "<p>Hallo {{voornaam}},</p>\n<p>We hebben een verzoek ontvangen om het wachtwoord van je account opnieuw in te stellen.</p>\n<p><a href=\"{{reset_url}}\" style=\"background:#0091D5;color:white;padding:10px 20px;text-decoration:none;border-radius:4px;display:inline-block;\">Nieuw wachtwoord instellen</a></p>\n<p style=\"color:#666;font-size:0.9em;\">Deze link is 24 uur geldig. Als je geen wachtwoord-reset hebt aangevraagd, kun je deze e-mail negeren.</p>",
        ],
    ];
}

/**
 * Bouw de placeholder-vervangingstabel op
 */
function lp_mail_placeholders( $naam, $gebruiker, $extra = [] ) {
    $login_pagina_id = get_option( 'lp_login_pagina_id', 0 );
    $vars = [
        '{{site_naam}}'      => get_bloginfo( 'name' ),
        '{{site_url}}'       => home_url(),
        '{{login_url}}'      => $login_pagina_id ? get_permalink( $login_pagina_id ) : wp_login_url(),
        '{{admin_email}}'    => get_option( 'admin_email' ),
        '{{voornaam}}'       => $gebruiker ? $gebruiker->first_name : '',
        '{{volledige_naam}}' => $gebruiker ? $gebruiker->display_name : '',
        '{{email}}'          => $gebruiker ? $gebruiker->user_email : '',
        '{{admin_url}}'      => $naam === 'account_bijgewerkt' && $gebruiker
                                    ? get_edit_user_link( $gebruiker->ID )
                                    : admin_url( 'admin.php?page=lp-ledenbeheer' ),
    ];
    return array_merge( $vars, $extra );
}

/**
 * Genereer de volledige HTML-mailinhoud
 */
function lp_mail_template( $naam, $data = [] ) {
    $gebruiker = $data['gebruiker'] ?? null;
    $extra     = $data['extra'] ?? [];
    $defaults  = lp_mail_defaults();

    if ( ! isset( $defaults[ $naam ] ) ) return '';

    $inhoud = get_option( 'lp_mail_inhoud_' . $naam, '' );
    if ( $inhoud === '' ) {
        $inhoud = $defaults[ $naam ]['inhoud'];
    }

    $vars   = lp_mail_placeholders( $naam, $gebruiker, $extra );
    $inhoud = str_replace( array_keys( $vars ), array_values( $vars ), $inhoud );

    $site_naam = get_bloginfo( 'name' );
    $site_url  = home_url();

    return <<<HTML
<!DOCTYPE html>
<html lang="nl">
<head><meta charset="UTF-8"></head>
<body style="font-family: Arial, sans-serif; color: #333; max-width: 600px; margin: 0 auto; padding: 20px;">
    <h2 style="color: #0091D5;">{$site_naam}</h2>
    {$inhoud}
    <hr style="border: none; border-top: 1px solid #e0e0e0; margin: 30px 0;">
    <p style="font-size: 12px; color: #999;"><a href="{$site_url}">{$site_naam}</a></p>
</body>
</html>
HTML;
}

/**
 * Haal het (bewerkbare) onderwerp op voor een mail
 */
function lp_mail_onderwerp( $naam, $data = [] ) {
    $gebruiker = $data['gebruiker'] ?? null;
    $extra     = $data['extra'] ?? [];
    $defaults  = lp_mail_defaults();

    $onderwerp = get_option( 'lp_mail_onderwerp_' . $naam, '' );
    if ( $onderwerp === '' ) {
        $onderwerp = $defaults[ $naam ]['onderwerp'] ?? '';
    }

    $vars = lp_mail_placeholders( $naam, $gebruiker, $extra );
    return str_replace( array_keys( $vars ), array_values( $vars ), $onderwerp );
}

/**
 * Na registratie: mail naar gebruiker + admin
 */
add_action( 'lp_na_registratie', function( $user_id ) {
    $user    = get_userdata( $user_id );
    $headers = [ 'Content-Type: text/html; charset=UTF-8' ];
    $data    = [ 'gebruiker' => $user ];

    if ( get_option( 'lp_mail_actief_registratie_bevestiging', '1' ) === '1' ) {
        wp_mail(
            $user->user_email,
            lp_mail_onderwerp( 'registratie_bevestiging', $data ),
            lp_mail_template( 'registratie_bevestiging', $data ),
            $headers
        );
    }

    if ( get_option( 'lp_mail_actief_admin_nieuw_lid', '1' ) === '1' ) {
        wp_mail(
            get_option( 'admin_email' ),
            lp_mail_onderwerp( 'admin_nieuw_lid', $data ),
            lp_mail_template( 'admin_nieuw_lid', $data ),
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
    $data    = [ 'gebruiker' => $user ];

    wp_mail(
        $user->user_email,
        lp_mail_onderwerp( 'account_goedgekeurd', $data ),
        lp_mail_template( 'account_goedgekeurd', $data ),
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
    $data    = [ 'gebruiker' => $user ];

    wp_mail(
        $notificatie_email,
        lp_mail_onderwerp( 'account_bijgewerkt', $data ),
        lp_mail_template( 'account_bijgewerkt', $data ),
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
    $data    = [ 'gebruiker' => $user ];

    wp_mail(
        $user->user_email,
        lp_mail_onderwerp( 'account_afgewezen', $data ),
        lp_mail_template( 'account_afgewezen', $data ),
        $headers
    );
} );
