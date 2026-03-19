<?php
if ( ! defined( 'ABSPATH' ) ) exit;
// Beschikbare variabelen: $gebruiker (WP_User), $meta (array), $fouten (array), $succes (bool)
?>

<div class="lp-formulier-wrap">

    <?php if ( ! empty( $fouten ) ) : ?>
        <div class="lp-melding lp-melding--fout">
            <ul>
                <?php foreach ( $fouten as $fout ) : ?>
                    <li><?php echo esc_html( $fout ); ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>

    <?php if ( $succes ) : ?>
        <div class="lp-melding lp-melding--succes">
            <?php esc_html_e( 'Je gegevens zijn opgeslagen.', 'mijn-ledenportaal' ); ?>
        </div>
    <?php endif; ?>

    <!-- Tab navigatie -->
    <div class="lp-tabs">
        <button type="button" class="lp-tab lp-tab--actief" data-tab="lp-tab-account">
            <?php esc_html_e( 'Account', 'mijn-ledenportaal' ); ?>
        </button>
        <button type="button" class="lp-tab" data-tab="lp-tab-adres">
            <?php esc_html_e( 'Adresgegevens', 'mijn-ledenportaal' ); ?>
        </button>
        <button type="button" class="lp-tab" data-tab="lp-tab-extra">
            <?php esc_html_e( 'Extra gegevens', 'mijn-ledenportaal' ); ?>
        </button>
        <button type="button" class="lp-tab" data-tab="lp-tab-betaal">
            <?php esc_html_e( 'Betaalgegevens', 'mijn-ledenportaal' ); ?>
        </button>
        <button type="button" class="lp-tab" data-tab="lp-tab-wachtwoord">
            <?php esc_html_e( 'Wachtwoord', 'mijn-ledenportaal' ); ?>
        </button>
    </div>

    <form class="lp-form" id="lp-account-form" method="post" novalidate>
        <?php wp_nonce_field( 'lp_account', 'lp_account_nonce' ); ?>

        <!-- Tab 1: Account -->
        <div class="lp-tab-inhoud lp-tab-inhoud--actief" id="lp-tab-account">
            <h5 class="lp-sectie-titel"><?php esc_html_e( 'Persoonlijke gegevens', 'mijn-ledenportaal' ); ?></h5>

            <div class="lp-rij lp-rij--2">
                <div class="lp-form-groep">
                    <label class="lp-label" for="lp-voornaam"><?php esc_html_e( 'Voornaam', 'mijn-ledenportaal' ); ?> <span class="lp-verplicht">*</span></label>
                    <input class="lp-input" type="text" id="lp-voornaam" name="voornaam"
                        value="<?php echo esc_attr( $gebruiker->first_name ); ?>"
                        required autocomplete="given-name">
                </div>
                <div class="lp-form-groep">
                    <label class="lp-label" for="lp-achternaam"><?php esc_html_e( 'Achternaam', 'mijn-ledenportaal' ); ?> <span class="lp-verplicht">*</span></label>
                    <input class="lp-input" type="text" id="lp-achternaam" name="achternaam"
                        value="<?php echo esc_attr( $gebruiker->last_name ); ?>"
                        required autocomplete="family-name">
                </div>
            </div>

            <div class="lp-form-groep">
                <label class="lp-label" for="lp-geslacht"><?php esc_html_e( 'Geslacht', 'mijn-ledenportaal' ); ?></label>
                <select class="lp-select" id="lp-geslacht" name="geslacht">
                    <option value=""><?php esc_html_e( '— Selecteer —', 'mijn-ledenportaal' ); ?></option>
                    <?php foreach ( lp_geslacht_opties() as $waarde => $label ) : ?>
                        <option value="<?php echo esc_attr( $waarde ); ?>" <?php selected( $meta['geslacht'], $waarde ); ?>>
                            <?php echo esc_html( $label ); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="lp-form-groep">
                <label class="lp-label"><?php esc_html_e( 'E-mailadres', 'mijn-ledenportaal' ); ?></label>
                <input class="lp-input" type="email" value="<?php echo esc_attr( $gebruiker->user_email ); ?>" disabled>
                <p class="lp-veld-info"><?php esc_html_e( 'Je e-mailadres kan niet worden gewijzigd. Neem contact op met de beheerder.', 'mijn-ledenportaal' ); ?></p>
            </div>
        </div>

        <!-- Tab 2: Adresgegevens -->
        <div class="lp-tab-inhoud" id="lp-tab-adres">
            <h5 class="lp-sectie-titel"><?php esc_html_e( 'Adresgegevens', 'mijn-ledenportaal' ); ?></h5>

            <div class="lp-rij lp-rij--3">
                <div class="lp-form-groep lp-col--2">
                    <label class="lp-label" for="lp-straatnaam"><?php esc_html_e( 'Straat', 'mijn-ledenportaal' ); ?></label>
                    <input class="lp-input" type="text" id="lp-straatnaam" name="straatnaam"
                        value="<?php echo esc_attr( $meta['straatnaam'] ); ?>"
                        autocomplete="street-address">
                </div>
                <div class="lp-form-groep">
                    <label class="lp-label" for="lp-huisnummer"><?php esc_html_e( 'Huisnr.', 'mijn-ledenportaal' ); ?></label>
                    <input class="lp-input" type="text" id="lp-huisnummer" name="huisnummer"
                        value="<?php echo esc_attr( $meta['huisnummer'] ); ?>">
                </div>
            </div>

            <div class="lp-form-groep">
                <label class="lp-label" for="lp-huisnummer-toevoeging"><?php esc_html_e( 'Toevoeging', 'mijn-ledenportaal' ); ?></label>
                <input class="lp-input lp-input--smal" type="text" id="lp-huisnummer-toevoeging" name="huisnummer_toevoeging"
                    value="<?php echo esc_attr( $meta['huisnummer_toevoeging'] ); ?>">
            </div>

            <div class="lp-rij lp-rij--2">
                <div class="lp-form-groep">
                    <label class="lp-label" for="lp-postcode"><?php esc_html_e( 'Postcode', 'mijn-ledenportaal' ); ?></label>
                    <input class="lp-input" type="text" id="lp-postcode" name="postcode"
                        value="<?php echo esc_attr( $meta['postcode'] ); ?>"
                        autocomplete="postal-code">
                </div>
                <div class="lp-form-groep">
                    <label class="lp-label" for="lp-plaats"><?php esc_html_e( 'Plaats', 'mijn-ledenportaal' ); ?></label>
                    <input class="lp-input" type="text" id="lp-plaats" name="plaats"
                        value="<?php echo esc_attr( $meta['plaats'] ); ?>"
                        autocomplete="address-level2">
                </div>
            </div>

            <div class="lp-form-groep">
                <label class="lp-label" for="lp-land"><?php esc_html_e( 'Land', 'mijn-ledenportaal' ); ?></label>
                <select class="lp-select" id="lp-land" name="land" autocomplete="country">
                    <?php foreach ( lp_land_opties() as $code => $naam ) : ?>
                        <option value="<?php echo esc_attr( $code ); ?>" <?php selected( $meta['land'], $code ); ?>>
                            <?php echo esc_html( $naam ); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>

        <!-- Tab 3: Extra gegevens -->
        <div class="lp-tab-inhoud" id="lp-tab-extra">
            <h5 class="lp-sectie-titel"><?php esc_html_e( 'Contactgegevens', 'mijn-ledenportaal' ); ?></h5>

            <div class="lp-rij lp-rij--2">
                <div class="lp-form-groep">
                    <label class="lp-label" for="lp-telefoonnummer"><?php esc_html_e( 'Telefoonnummer', 'mijn-ledenportaal' ); ?></label>
                    <input class="lp-input" type="tel" id="lp-telefoonnummer" name="telefoonnummer"
                        value="<?php echo esc_attr( $meta['telefoonnummer'] ); ?>"
                        autocomplete="tel">
                </div>
                <div class="lp-form-groep">
                    <label class="lp-label" for="lp-mobiel"><?php esc_html_e( 'Mobiel', 'mijn-ledenportaal' ); ?></label>
                    <input class="lp-input" type="tel" id="lp-mobiel" name="mobiel"
                        value="<?php echo esc_attr( $meta['mobiel'] ); ?>"
                        autocomplete="tel">
                </div>
            </div>

            <div class="lp-form-groep">
                <label class="lp-label" for="lp-geboortedatum"><?php esc_html_e( 'Geboortedatum', 'mijn-ledenportaal' ); ?></label>
                <input class="lp-input" type="date" id="lp-geboortedatum" name="geboortedatum"
                    value="<?php echo esc_attr( $meta['geboortedatum'] ); ?>">
            </div>

            <h5 class="lp-sectie-titel"><?php esc_html_e( 'Lidmaatschapsgegevens', 'mijn-ledenportaal' ); ?></h5>

            <div class="lp-form-groep">
                <label class="lp-label" for="lp-afdeling"><?php esc_html_e( 'Laatste afdeling/functie bij Delta Lloyd Groep', 'mijn-ledenportaal' ); ?></label>
                <select class="lp-select" id="lp-afdeling" name="afdeling">
                    <option value=""><?php esc_html_e( '— Selecteer —', 'mijn-ledenportaal' ); ?></option>
                    <?php foreach ( lp_afdeling_opties() as $waarde => $label ) : ?>
                        <option value="<?php echo esc_attr( $waarde ); ?>" <?php selected( $meta['afdeling'], $waarde ); ?>>
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
                        <option value="<?php echo esc_attr( $waarde ); ?>" <?php selected( $meta['soort_pensioen'], $waarde ); ?>>
                            <?php echo esc_html( $label ); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="lp-form-groep">
                <span class="lp-label"><?php esc_html_e( 'Inzetbaar voor verenigingsfunctie', 'mijn-ledenportaal' ); ?></span>
                <div class="lp-checkboxes">
                    <?php foreach ( lp_functie_opties() as $waarde => $label ) : ?>
                        <label class="lp-checkbox-label">
                            <input type="checkbox" name="verenigingsfunctie[]"
                                value="<?php echo esc_attr( $waarde ); ?>"
                                <?php checked( in_array( $waarde, (array) $meta['verenigingsfunctie'], true ) ); ?>>
                            <?php echo esc_html( $label ); ?>
                        </label>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>

        <!-- Tab 4: Betaalgegevens -->
        <div class="lp-tab-inhoud" id="lp-tab-betaal">
            <h5 class="lp-sectie-titel"><?php esc_html_e( 'Betaalgegevens', 'mijn-ledenportaal' ); ?></h5>
            <p class="lp-veld-hint"><?php esc_html_e( 'Vul je bankgegevens in om automatisch incasso te machtigen voor de contributie.', 'mijn-ledenportaal' ); ?></p>

            <?php if ( $meta['incasso_toestemming'] === '1' && $meta['incasso_toestemming_datum'] ) : ?>
                <p class="lp-veld-hint" style="color: #5cb85c;">
                    <?php printf(
                        esc_html__( 'Incassomachtiging actief sinds %s.', 'mijn-ledenportaal' ),
                        esc_html( date_i18n( 'd-m-Y', strtotime( $meta['incasso_toestemming_datum'] ) ) )
                    ); ?>
                </p>
            <?php endif; ?>

            <div class="lp-form-groep">
                <label class="lp-label" for="lp-iban"><?php esc_html_e( 'IBAN', 'mijn-ledenportaal' ); ?></label>
                <input class="lp-input" type="text" id="lp-iban" name="iban"
                    value="<?php echo esc_attr( $meta['iban'] ); ?>"
                    placeholder="NL00 BANK 0000 0000 00"
                    autocomplete="off"
                    style="text-transform: uppercase;">
            </div>

            <div class="lp-form-groep">
                <label class="lp-label" for="lp-iban2">
                    <?php esc_html_e( 'IBAN bevestigen', 'mijn-ledenportaal' ); ?>
                    <span class="lp-verplicht">*</span>
                </label>
                <input class="lp-input" type="text" id="lp-iban2" name="iban2"
                    value=""
                    placeholder="<?php esc_attr_e( 'Herhaal IBAN om te wijzigen', 'mijn-ledenportaal' ); ?>"
                    autocomplete="off"
                    style="text-transform: uppercase;">
                <p class="lp-veld-hint"><?php esc_html_e( 'Vereist als je het IBAN wijzigt.', 'mijn-ledenportaal' ); ?></p>
                <p class="lp-iban-match-melding" style="display: none; color: #d9534f;">
                    <?php esc_html_e( 'IBAN-nummers komen niet overeen.', 'mijn-ledenportaal' ); ?>
                </p>
            </div>

            <div class="lp-form-groep">
                <label class="lp-label" for="lp-iban-ten-name-van">
                    <?php esc_html_e( 'Ten name van', 'mijn-ledenportaal' ); ?>
                    <span class="lp-verplicht">*</span>
                </label>
                <input class="lp-input" type="text" id="lp-iban-ten-name-van" name="iban_ten_name_van"
                    value="<?php echo esc_attr( $meta['iban_ten_name_van'] ); ?>"
                    autocomplete="off">
                <p class="lp-veld-hint"><?php esc_html_e( 'Vereist bij opgave van een IBAN.', 'mijn-ledenportaal' ); ?></p>
            </div>

            <div class="lp-form-groep">
                <label class="lp-checkbox-label">
                    <input type="checkbox" name="incasso_toestemming" value="1"
                        <?php checked( $meta['incasso_toestemming'], '1' ); ?>>
                    <?php esc_html_e( 'Ik geef toestemming voor automatisch incasso van de contributie van bovenstaande rekening.', 'mijn-ledenportaal' ); ?>
                    <span class="lp-verplicht">*</span>
                </label>
                <p class="lp-veld-hint"><?php esc_html_e( 'Vereist bij opgave van een IBAN.', 'mijn-ledenportaal' ); ?></p>
            </div>
        </div>

        <!-- Tab 5: Wachtwoord -->
        <div class="lp-tab-inhoud" id="lp-tab-wachtwoord">
            <h5 class="lp-sectie-titel"><?php esc_html_e( 'Wachtwoord wijzigen', 'mijn-ledenportaal' ); ?></h5>
            <p class="lp-veld-hint"><?php esc_html_e( 'Laat leeg als je je wachtwoord niet wilt wijzigen.', 'mijn-ledenportaal' ); ?></p>

            <div class="lp-form-groep">
                <label class="lp-label" for="lp-nieuw-wachtwoord"><?php esc_html_e( 'Nieuw wachtwoord', 'mijn-ledenportaal' ); ?></label>
                <div class="lp-wachtwoord-wrap">
                    <input class="lp-input" type="password" id="lp-nieuw-wachtwoord" name="nieuw_wachtwoord"
                        autocomplete="new-password" minlength="8">
                    <button type="button" class="lp-wachtwoord-toggle" data-target="lp-nieuw-wachtwoord" aria-label="<?php esc_attr_e( 'Toon wachtwoord', 'mijn-ledenportaal' ); ?>">
                        <span class="lp-oog-icon">&#128065;</span>
                    </button>
                </div>
                <p class="lp-veld-hint"><?php esc_html_e( 'Minimaal 8 tekens.', 'mijn-ledenportaal' ); ?></p>
            </div>

            <div class="lp-form-groep">
                <label class="lp-label" for="lp-nieuw-wachtwoord2"><?php esc_html_e( 'Nieuw wachtwoord bevestigen', 'mijn-ledenportaal' ); ?></label>
                <div class="lp-wachtwoord-wrap">
                    <input class="lp-input" type="password" id="lp-nieuw-wachtwoord2" name="nieuw_wachtwoord2"
                        autocomplete="new-password" minlength="8">
                    <button type="button" class="lp-wachtwoord-toggle" data-target="lp-nieuw-wachtwoord2" aria-label="<?php esc_attr_e( 'Toon wachtwoord', 'mijn-ledenportaal' ); ?>">
                        <span class="lp-oog-icon">&#128065;</span>
                    </button>
                </div>
                <p class="lp-wachtwoord-match-melding" style="display: none; color: #d9534f;">
                    <?php esc_html_e( 'Wachtwoorden komen niet overeen.', 'mijn-ledenportaal' ); ?>
                </p>
            </div>
        </div>

        <!-- Opslaan knop (altijd zichtbaar) -->
        <?php if ( ! empty( $meta['account_gewijzigd'] ) ) : ?>
            <p class="lp-veld-hint" style="color: #999; margin-bottom: 8px;">
                <?php printf(
                    esc_html__( 'Laatst gewijzigd: %s', 'mijn-ledenportaal' ),
                    esc_html( date_i18n( 'd-m-Y H:i', strtotime( $meta['account_gewijzigd'] ) ) )
                ); ?>
            </p>
        <?php endif; ?>
        <div class="lp-form-groep lp-form-groep--acties">
            <button type="submit" name="lp_account_submit" class="lp-knop" id="lp-opslaan-knop" disabled>
                <?php esc_html_e( 'Wijzigingen opslaan', 'mijn-ledenportaal' ); ?>
            </button>
            <a href="<?php echo esc_url( lp_uitlog_url() ); ?>" class="lp-link lp-link--uitloggen">
                <?php esc_html_e( 'Uitloggen', 'mijn-ledenportaal' ); ?>
            </a>
        </div>

    </form>
    <script>
    (function() {
        // IBAN match check
        var iban1 = document.getElementById('lp-iban');
        var iban2 = document.getElementById('lp-iban2');
        var melding = iban2 ? iban2.closest('.lp-form-groep').querySelector('.lp-iban-match-melding') : null;
        function checkIban() {
            if (!melding) return;
            melding.style.display = (iban1.value && iban2.value && iban1.value.replace(/\s/g,'').toUpperCase() !== iban2.value.replace(/\s/g,'').toUpperCase()) ? '' : 'none';
        }
        if (iban1) iban1.addEventListener('input', checkIban);
        if (iban2) iban2.addEventListener('input', checkIban);

        // Knop inschakelen bij wijziging
        var form  = document.getElementById('lp-account-form');
        var knop  = document.getElementById('lp-opslaan-knop');
        if (!form || !knop) return;

        var velden = Array.from(form.querySelectorAll('input:not([disabled]), select, textarea'));

        function waarde(el) {
            if (el.type === 'checkbox' || el.type === 'radio') return el.checked;
            return el.value.trim();
        }

        var snapshot = velden.map(waarde);

        function isGewijzigd() {
            return velden.some(function(el, i) { return waarde(el) !== snapshot[i]; });
        }

        form.addEventListener('input',  function() { knop.disabled = !isGewijzigd(); });
        form.addEventListener('change', function() { knop.disabled = !isGewijzigd(); });
    })();
    </script>

</div>
