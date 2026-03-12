<?php
if ( ! defined( 'ABSPATH' ) ) exit;
// Beschikbare variabelen: $fouten (array), $succes (bool)
?>

<div class="lp-formulier-wrap">

    <?php if ( $succes ) : ?>
        <div class="lp-melding lp-melding--succes">
            <?php esc_html_e( 'Je aanmelding is ontvangen! Je krijgt een e-mail zodra je account is goedgekeurd.', 'mijn-ledenportaal' ); ?>
        </div>

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
            <?php wp_nonce_field( 'lp_registratie', 'lp_registratie_nonce' ); ?>

            <h3 class="lp-sectie-titel"><?php esc_html_e( 'Persoonlijke gegevens', 'mijn-ledenportaal' ); ?></h3>

            <div class="lp-rij lp-rij--2">
                <div class="lp-form-groep">
                    <label class="lp-label" for="lp-voornaam"><?php esc_html_e( 'Voornaam', 'mijn-ledenportaal' ); ?> <span class="lp-verplicht">*</span></label>
                    <input class="lp-input" type="text" id="lp-voornaam" name="voornaam"
                        value="<?php echo isset( $_POST['voornaam'] ) ? esc_attr( sanitize_text_field( $_POST['voornaam'] ) ) : ''; ?>"
                        required autocomplete="given-name">
                </div>
                <div class="lp-form-groep">
                    <label class="lp-label" for="lp-achternaam"><?php esc_html_e( 'Achternaam', 'mijn-ledenportaal' ); ?> <span class="lp-verplicht">*</span></label>
                    <input class="lp-input" type="text" id="lp-achternaam" name="achternaam"
                        value="<?php echo isset( $_POST['achternaam'] ) ? esc_attr( sanitize_text_field( $_POST['achternaam'] ) ) : ''; ?>"
                        required autocomplete="family-name">
                </div>
            </div>

            <div class="lp-form-groep">
                <label class="lp-label" for="lp-geslacht"><?php esc_html_e( 'Geslacht', 'mijn-ledenportaal' ); ?></label>
                <select class="lp-select" id="lp-geslacht" name="geslacht">
                    <option value=""><?php esc_html_e( '— Selecteer —', 'mijn-ledenportaal' ); ?></option>
                    <?php foreach ( lp_geslacht_opties() as $waarde => $label ) : ?>
                        <option value="<?php echo esc_attr( $waarde ); ?>"
                            <?php selected( isset( $_POST['geslacht'] ) ? sanitize_key( $_POST['geslacht'] ) : '', $waarde ); ?>>
                            <?php echo esc_html( $label ); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="lp-form-groep">
                <label class="lp-label" for="lp-geboortedatum"><?php esc_html_e( 'Geboortedatum', 'mijn-ledenportaal' ); ?></label>
                <input class="lp-input" type="date" id="lp-geboortedatum" name="geboortedatum"
                    value="<?php echo isset( $_POST['geboortedatum'] ) ? esc_attr( sanitize_text_field( $_POST['geboortedatum'] ) ) : ''; ?>">
            </div>

            <h3 class="lp-sectie-titel"><?php esc_html_e( 'Contactgegevens', 'mijn-ledenportaal' ); ?></h3>

            <div class="lp-form-groep">
                <label class="lp-label" for="lp-email"><?php esc_html_e( 'E-mailadres', 'mijn-ledenportaal' ); ?> <span class="lp-verplicht">*</span></label>
                <input class="lp-input" type="email" id="lp-email" name="email"
                    value="<?php echo isset( $_POST['email'] ) ? esc_attr( sanitize_email( $_POST['email'] ) ) : ''; ?>"
                    required autocomplete="email">
            </div>

            <div class="lp-rij lp-rij--2">
                <div class="lp-form-groep">
                    <label class="lp-label" for="lp-telefoonnummer"><?php esc_html_e( 'Telefoonnummer', 'mijn-ledenportaal' ); ?></label>
                    <input class="lp-input" type="tel" id="lp-telefoonnummer" name="telefoonnummer"
                        value="<?php echo isset( $_POST['telefoonnummer'] ) ? esc_attr( sanitize_text_field( $_POST['telefoonnummer'] ) ) : ''; ?>"
                        autocomplete="tel">
                </div>
                <div class="lp-form-groep">
                    <label class="lp-label" for="lp-mobiel"><?php esc_html_e( 'Mobiel', 'mijn-ledenportaal' ); ?></label>
                    <input class="lp-input" type="tel" id="lp-mobiel" name="mobiel"
                        value="<?php echo isset( $_POST['mobiel'] ) ? esc_attr( sanitize_text_field( $_POST['mobiel'] ) ) : ''; ?>"
                        autocomplete="tel">
                </div>
            </div>

            <h3 class="lp-sectie-titel"><?php esc_html_e( 'Adresgegevens', 'mijn-ledenportaal' ); ?></h3>

            <div class="lp-rij lp-rij--3">
                <div class="lp-form-groep lp-col--2">
                    <label class="lp-label" for="lp-straatnaam"><?php esc_html_e( 'Straat', 'mijn-ledenportaal' ); ?></label>
                    <input class="lp-input" type="text" id="lp-straatnaam" name="straatnaam"
                        value="<?php echo isset( $_POST['straatnaam'] ) ? esc_attr( sanitize_text_field( $_POST['straatnaam'] ) ) : ''; ?>"
                        autocomplete="street-address">
                </div>
                <div class="lp-form-groep">
                    <label class="lp-label" for="lp-huisnummer"><?php esc_html_e( 'Huisnr.', 'mijn-ledenportaal' ); ?></label>
                    <input class="lp-input" type="text" id="lp-huisnummer" name="huisnummer"
                        value="<?php echo isset( $_POST['huisnummer'] ) ? esc_attr( sanitize_text_field( $_POST['huisnummer'] ) ) : ''; ?>">
                </div>
            </div>

            <div class="lp-form-groep">
                <label class="lp-label" for="lp-huisnummer-toevoeging"><?php esc_html_e( 'Toevoeging', 'mijn-ledenportaal' ); ?></label>
                <input class="lp-input lp-input--smal" type="text" id="lp-huisnummer-toevoeging" name="huisnummer_toevoeging"
                    value="<?php echo isset( $_POST['huisnummer_toevoeging'] ) ? esc_attr( sanitize_text_field( $_POST['huisnummer_toevoeging'] ) ) : ''; ?>">
            </div>

            <div class="lp-rij lp-rij--2">
                <div class="lp-form-groep">
                    <label class="lp-label" for="lp-postcode"><?php esc_html_e( 'Postcode', 'mijn-ledenportaal' ); ?></label>
                    <input class="lp-input" type="text" id="lp-postcode" name="postcode"
                        value="<?php echo isset( $_POST['postcode'] ) ? esc_attr( sanitize_text_field( $_POST['postcode'] ) ) : ''; ?>"
                        autocomplete="postal-code">
                </div>
                <div class="lp-form-groep">
                    <label class="lp-label" for="lp-plaats"><?php esc_html_e( 'Plaats', 'mijn-ledenportaal' ); ?></label>
                    <input class="lp-input" type="text" id="lp-plaats" name="plaats"
                        value="<?php echo isset( $_POST['plaats'] ) ? esc_attr( sanitize_text_field( $_POST['plaats'] ) ) : ''; ?>"
                        autocomplete="address-level2">
                </div>
            </div>

            <div class="lp-form-groep">
                <label class="lp-label" for="lp-land"><?php esc_html_e( 'Land', 'mijn-ledenportaal' ); ?></label>
                <select class="lp-select" id="lp-land" name="land" autocomplete="country">
                    <?php
                    $huidig_land = isset( $_POST['land'] ) ? sanitize_key( $_POST['land'] ) : 'NL';
                    foreach ( lp_land_opties() as $code => $naam ) :
                    ?>
                        <option value="<?php echo esc_attr( $code ); ?>" <?php selected( $huidig_land, $code ); ?>>
                            <?php echo esc_html( $naam ); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <h3 class="lp-sectie-titel"><?php esc_html_e( 'Lidmaatschapsgegevens', 'mijn-ledenportaal' ); ?></h3>

            <div class="lp-form-groep">
                <label class="lp-label" for="lp-afdeling"><?php esc_html_e( 'Laatste afdeling/functie bij Delta Lloyd Groep', 'mijn-ledenportaal' ); ?></label>
                <select class="lp-select" id="lp-afdeling" name="afdeling">
                    <option value=""><?php esc_html_e( '— Selecteer —', 'mijn-ledenportaal' ); ?></option>
                    <?php foreach ( lp_afdeling_opties() as $waarde => $label ) : ?>
                        <option value="<?php echo esc_attr( $waarde ); ?>"
                            <?php selected( isset( $_POST['afdeling'] ) ? sanitize_key( $_POST['afdeling'] ) : '', $waarde ); ?>>
                            <?php echo esc_html( $label ); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="lp-form-groep">
                <label class="lp-label" for="lp-soort-pensioen"><?php esc_html_e( 'Soort pensioen', 'mijn-ledenportaal' ); ?></label>
                <select class="lp-select" id="lp-soort-pensioen" name="soort_pensioen">
                    <option value=""><?php esc_html_e( '— Selecteer —', 'mijn-ledenportaal' ); ?></option>
                    <?php foreach ( lp_pensioen_opties() as $waarde => $label ) : ?>
                        <option value="<?php echo esc_attr( $waarde ); ?>"
                            <?php selected( isset( $_POST['soort_pensioen'] ) ? sanitize_key( $_POST['soort_pensioen'] ) : '', $waarde ); ?>>
                            <?php echo esc_html( $label ); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="lp-form-groep">
                <span class="lp-label"><?php esc_html_e( 'Inzetbaar voor verenigingsfunctie', 'mijn-ledenportaal' ); ?></span>
                <div class="lp-checkboxes">
                    <?php
                    $huidige_functies = isset( $_POST['verenigingsfunctie'] ) ? array_map( 'sanitize_key', (array) $_POST['verenigingsfunctie'] ) : [];
                    foreach ( lp_functie_opties() as $waarde => $label ) :
                    ?>
                        <label class="lp-checkbox-label">
                            <input type="checkbox" name="verenigingsfunctie[]"
                                value="<?php echo esc_attr( $waarde ); ?>"
                                <?php checked( in_array( $waarde, $huidige_functies, true ) ); ?>>
                            <?php echo esc_html( $label ); ?>
                        </label>
                    <?php endforeach; ?>
                </div>
            </div>

            <h3 class="lp-sectie-titel"><?php esc_html_e( 'Wachtwoord', 'mijn-ledenportaal' ); ?></h3>

            <div class="lp-form-groep">
                <label class="lp-label" for="lp-wachtwoord"><?php esc_html_e( 'Wachtwoord', 'mijn-ledenportaal' ); ?> <span class="lp-verplicht">*</span></label>
                <div class="lp-wachtwoord-wrap">
                    <input class="lp-input" type="password" id="lp-wachtwoord" name="wachtwoord"
                        required autocomplete="new-password" minlength="8">
                    <button type="button" class="lp-wachtwoord-toggle" data-target="lp-wachtwoord" aria-label="<?php esc_attr_e( 'Toon wachtwoord', 'mijn-ledenportaal' ); ?>">
                        <span class="lp-oog-icon">&#128065;</span>
                    </button>
                </div>
                <p class="lp-veld-hint"><?php esc_html_e( 'Minimaal 8 tekens.', 'mijn-ledenportaal' ); ?></p>
            </div>

            <div class="lp-form-groep">
                <label class="lp-label" for="lp-wachtwoord2"><?php esc_html_e( 'Wachtwoord bevestigen', 'mijn-ledenportaal' ); ?> <span class="lp-verplicht">*</span></label>
                <div class="lp-wachtwoord-wrap">
                    <input class="lp-input" type="password" id="lp-wachtwoord2" name="wachtwoord2"
                        required autocomplete="new-password" minlength="8">
                    <button type="button" class="lp-wachtwoord-toggle" data-target="lp-wachtwoord2" aria-label="<?php esc_attr_e( 'Toon wachtwoord', 'mijn-ledenportaal' ); ?>">
                        <span class="lp-oog-icon">&#128065;</span>
                    </button>
                </div>
                <p class="lp-wachtwoord-match-melding" style="display: none; color: #d9534f;">
                    <?php esc_html_e( 'Wachtwoorden komen niet overeen.', 'mijn-ledenportaal' ); ?>
                </p>
            </div>

            <div class="lp-form-groep">
                <button type="submit" name="lp_registratie_submit" class="lp-knop">
                    <?php esc_html_e( 'Aanmelden', 'mijn-ledenportaal' ); ?>
                </button>
            </div>

        </form>

    <?php endif; ?>

</div>
