<?php
if ( ! defined( 'ABSPATH' ) ) exit;
// Beschikbare variabelen: $fouten (array), $succes (bool)

$login_id = get_option( 'lp_login_pagina_id', 0 );
?>

<div class="lp-formulier-wrap lp-formulier-wrap--smal">

    <?php if ( $succes ) : ?>
        <div class="lp-melding lp-melding--succes">
            <?php esc_html_e( 'Als dit e-mailadres bij ons bekend is, ontvang je zo meteen een e-mail met een link om je wachtwoord opnieuw in te stellen.', 'mijn-ledenportaal' ); ?>
        </div>
        <?php if ( $login_id ) : ?>
            <p class="lp-form-voetnoot">
                <a href="<?php echo esc_url( get_permalink( $login_id ) ); ?>" class="lp-link">
                    <?php esc_html_e( '← Terug naar inloggen', 'mijn-ledenportaal' ); ?>
                </a>
            </p>
        <?php endif; ?>

    <?php else : ?>

        <?php if ( ! empty( $fouten ) ) : ?>
            <div class="lp-melding lp-melding--fout">
                <ul>
                    <?php foreach ( $fouten as $fout ) : ?>
                        <li><?php echo esc_html( $fout ); ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>

        <p class="lp-intro-tekst">
            <?php esc_html_e( 'Vul je e-mailadres in. Je ontvangt een link om een nieuw wachtwoord in te stellen.', 'mijn-ledenportaal' ); ?>
        </p>

        <form class="lp-form" method="post" novalidate>
            <?php wp_nonce_field( 'lp_wachtwoord_vergeten', 'lp_wachtwoord_vergeten_nonce' ); ?>

            <div class="lp-form-groep">
                <label class="lp-label" for="lp-vergeten-email"><?php esc_html_e( 'E-mailadres', 'mijn-ledenportaal' ); ?> <span class="lp-verplicht">*</span></label>
                <input class="lp-input" type="email" id="lp-vergeten-email" name="email"
                    value=""
                    required autocomplete="email">
            </div>

            <div class="lp-form-groep">
                <button type="submit" name="lp_wachtwoord_vergeten_submit" class="lp-knop">
                    <?php esc_html_e( 'Verstuur reset-link', 'mijn-ledenportaal' ); ?>
                </button>
            </div>

        </form>

        <?php if ( $login_id ) : ?>
            <p class="lp-form-voetnoot">
                <a href="<?php echo esc_url( get_permalink( $login_id ) ); ?>" class="lp-link">
                    <?php esc_html_e( '← Terug naar inloggen', 'mijn-ledenportaal' ); ?>
                </a>
            </p>
        <?php endif; ?>

    <?php endif; ?>

</div>
