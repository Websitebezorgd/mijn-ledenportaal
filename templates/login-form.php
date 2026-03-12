<?php
if ( ! defined( 'ABSPATH' ) ) exit;
// Beschikbare variabelen: $fouten (array)

// Status melding bij redirect vanuit afscherming
$status_melding = '';
if ( isset( $_GET['lp_status'] ) ) {
    $lp_status = sanitize_key( $_GET['lp_status'] );
    if ( $lp_status === 'pending' ) {
        $status_melding = __( 'Je account wacht nog op goedkeuring.', 'mijn-ledenportaal' );
    } elseif ( $lp_status === 'rejected' ) {
        $status_melding = __( 'Je aanmelding is helaas afgewezen.', 'mijn-ledenportaal' );
    }
}

$registratie_id = get_option( 'lp_registratie_pagina_id', 0 );
?>

<div class="lp-formulier-wrap lp-formulier-wrap--smal">

    <?php if ( ! empty( $status_melding ) ) : ?>
        <div class="lp-melding lp-melding--waarschuwing">
            <?php echo esc_html( $status_melding ); ?>
        </div>
    <?php endif; ?>

    <?php if ( ! empty( $fouten ) ) : ?>
        <div class="lp-melding lp-melding--fout">
            <ul>
                <?php foreach ( $fouten as $fout ) : ?>
                    <li><?php echo esc_html( $fout ); ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>

    <form class="lp-form" method="post" novalidate>
        <?php wp_nonce_field( 'lp_login', 'lp_login_nonce' ); ?>

        <div class="lp-form-groep">
            <label class="lp-label" for="lp-login-email"><?php esc_html_e( 'E-mailadres', 'mijn-ledenportaal' ); ?> <span class="lp-verplicht">*</span></label>
            <input class="lp-input" type="email" id="lp-login-email" name="email"
                value="<?php echo isset( $_POST['email'] ) ? esc_attr( sanitize_email( $_POST['email'] ) ) : ''; ?>"
                required autocomplete="email">
        </div>

        <div class="lp-form-groep">
            <label class="lp-label" for="lp-login-wachtwoord"><?php esc_html_e( 'Wachtwoord', 'mijn-ledenportaal' ); ?> <span class="lp-verplicht">*</span></label>
            <div class="lp-wachtwoord-wrap">
                <input class="lp-input" type="password" id="lp-login-wachtwoord" name="wachtwoord"
                    required autocomplete="current-password">
                <button type="button" class="lp-wachtwoord-toggle" data-target="lp-login-wachtwoord" aria-label="<?php esc_attr_e( 'Toon wachtwoord', 'mijn-ledenportaal' ); ?>">
                    <span class="lp-oog-icon">&#128065;</span>
                </button>
            </div>
        </div>

        <div class="lp-form-groep lp-form-groep--inline">
            <label class="lp-checkbox-label">
                <input type="checkbox" name="onthouden" value="1" <?php checked( ! empty( $_POST['onthouden'] ) ); ?>>
                <?php esc_html_e( 'Onthoud mij', 'mijn-ledenportaal' ); ?>
            </label>
            <a href="<?php echo esc_url( wp_lostpassword_url() ); ?>" class="lp-link lp-link--klein">
                <?php esc_html_e( 'Wachtwoord vergeten?', 'mijn-ledenportaal' ); ?>
            </a>
        </div>

        <div class="lp-form-groep">
            <button type="submit" name="lp_login_submit" class="lp-knop">
                <?php esc_html_e( 'Inloggen', 'mijn-ledenportaal' ); ?>
            </button>
        </div>

        <?php if ( $registratie_id ) : ?>
            <p class="lp-form-voetnoot">
                <?php esc_html_e( 'Nog geen account?', 'mijn-ledenportaal' ); ?>
                <a href="<?php echo esc_url( get_permalink( $registratie_id ) ); ?>" class="lp-link">
                    <?php esc_html_e( 'Aanmelden als lid', 'mijn-ledenportaal' ); ?>
                </a>
            </p>
        <?php endif; ?>

    </form>

</div>
