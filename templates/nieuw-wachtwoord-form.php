<?php
if ( ! defined( 'ABSPATH' ) ) exit;
// Beschikbare variabelen: $fouten (array), $key (string), $login (string), $key_geldig (bool)

$vergeten_id = get_option( 'lp_wachtwoord_vergeten_pagina_id', 0 );
?>

<div class="lp-formulier-wrap lp-formulier-wrap--smal">

    <?php if ( ! $key_geldig ) : ?>
        <div class="lp-melding lp-melding--fout">
            <?php esc_html_e( 'Deze reset-link is verlopen of ongeldig.', 'mijn-ledenportaal' ); ?>
        </div>
        <?php if ( $vergeten_id ) : ?>
            <p class="lp-form-voetnoot">
                <a href="<?php echo esc_url( get_permalink( $vergeten_id ) ); ?>" class="lp-link">
                    <?php esc_html_e( 'Vraag een nieuwe reset-link aan', 'mijn-ledenportaal' ); ?>
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

        <form class="lp-form" method="post" novalidate>
            <?php wp_nonce_field( 'lp_nieuw_wachtwoord', 'lp_nieuw_wachtwoord_nonce' ); ?>
            <input type="hidden" name="key" value="<?php echo esc_attr( $key ); ?>">
            <input type="hidden" name="login" value="<?php echo esc_attr( $login ); ?>">

            <div class="lp-form-groep">
                <label class="lp-label" for="lp-nieuw-wachtwoord"><?php esc_html_e( 'Nieuw wachtwoord', 'mijn-ledenportaal' ); ?> <span class="lp-verplicht">*</span></label>
                <div class="lp-wachtwoord-wrap">
                    <input class="lp-input" type="password" id="lp-nieuw-wachtwoord" name="nieuw_wachtwoord"
                        required autocomplete="new-password" minlength="8">
                    <button type="button" class="lp-wachtwoord-toggle" data-target="lp-nieuw-wachtwoord" aria-label="<?php esc_attr_e( 'Toon wachtwoord', 'mijn-ledenportaal' ); ?>">
                        <span class="lp-oog-icon">&#128065;</span>
                    </button>
                </div>
                <p class="lp-veld-hint"><?php esc_html_e( 'Minimaal 8 tekens.', 'mijn-ledenportaal' ); ?></p>
            </div>

            <div class="lp-form-groep">
                <label class="lp-label" for="lp-nieuw-wachtwoord2"><?php esc_html_e( 'Wachtwoord bevestigen', 'mijn-ledenportaal' ); ?> <span class="lp-verplicht">*</span></label>
                <div class="lp-wachtwoord-wrap">
                    <input class="lp-input" type="password" id="lp-nieuw-wachtwoord2" name="nieuw_wachtwoord2"
                        required autocomplete="new-password" minlength="8">
                    <button type="button" class="lp-wachtwoord-toggle" data-target="lp-nieuw-wachtwoord2" aria-label="<?php esc_attr_e( 'Toon wachtwoord', 'mijn-ledenportaal' ); ?>">
                        <span class="lp-oog-icon">&#128065;</span>
                    </button>
                </div>
                <p class="lp-wachtwoord-match-melding" style="display: none; color: #d9534f;">
                    <?php esc_html_e( 'Wachtwoorden komen niet overeen.', 'mijn-ledenportaal' ); ?>
                </p>
            </div>

            <div class="lp-form-groep">
                <button type="submit" name="lp_nieuw_wachtwoord_submit" class="lp-knop">
                    <?php esc_html_e( 'Wachtwoord opslaan', 'mijn-ledenportaal' ); ?>
                </button>
            </div>

        </form>

    <?php endif; ?>

</div>
