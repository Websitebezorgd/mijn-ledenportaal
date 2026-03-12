/* global jQuery */
(function ($) {
    'use strict';

    // =========================================================================
    // Wachtwoord toon/verberg toggle
    // =========================================================================
    $(document).on('click', '.lp-wachtwoord-toggle', function () {
        var targetId = $(this).data('target');
        var $input = $('#' + targetId);

        if ($input.length) {
            var isVerborgen = $input.attr('type') === 'password';
            $input.attr('type', isVerborgen ? 'text' : 'password');
            $(this).find('.lp-oog-icon').text(isVerborgen ? '\uD83D\uDE48' : '\uD83D\uDC41');
            $(this).attr('aria-label', isVerborgen ? 'Verberg wachtwoord' : 'Toon wachtwoord');
        }
    });

    // =========================================================================
    // Wachtwoord bevestiging match check
    // =========================================================================
    function controleerWachtwoordMatch($veld1, $veld2, $melding) {
        if ($veld1.length && $veld2.length && $veld2.val().length > 0) {
            var match = $veld1.val() === $veld2.val();
            $melding.toggle(!match);
            $veld2.toggleClass('lp-input--fout', !match);
        }
    }

    // Registratieformulier
    var $regWw1    = $('#lp-wachtwoord');
    var $regWw2    = $('#lp-wachtwoord2');
    var $regMelding = $('.lp-formulier-wrap:not(.lp-formulier-wrap--smal) .lp-wachtwoord-match-melding').first();

    $regWw1.add($regWw2).on('input', function () {
        controleerWachtwoordMatch($regWw1, $regWw2, $regMelding);
    });

    // Accountformulier wachtwoord wijzigen
    var $accWw1     = $('#lp-nieuw-wachtwoord');
    var $accWw2     = $('#lp-nieuw-wachtwoord2');
    var $accMelding = $('#lp-tab-wachtwoord .lp-wachtwoord-match-melding');

    $accWw1.add($accWw2).on('input', function () {
        controleerWachtwoordMatch($accWw1, $accWw2, $accMelding);
    });

    // =========================================================================
    // Tab navigatie — accountpagina
    // =========================================================================
    $(document).on('click', '.lp-tab', function () {
        var tabId = $(this).data('tab');
        var $formulier = $(this).closest('.lp-formulier-wrap');

        // Wissel actieve tab knop
        $formulier.find('.lp-tab').removeClass('lp-tab--actief');
        $(this).addClass('lp-tab--actief');

        // Wissel actieve tab inhoud
        $formulier.find('.lp-tab-inhoud').removeClass('lp-tab-inhoud--actief');
        $formulier.find('#' + tabId).addClass('lp-tab-inhoud--actief');
    });

    // =========================================================================
    // Open juiste tab als er fouten zijn (na POST)
    // =========================================================================
    (function openTabMetFout() {
        var $melding = $('.lp-melding--fout');
        if ($melding.length === 0) return;

        // Zoek het eerste veld met een fout en activeer die tab
        var tabVolgorde = ['lp-tab-account', 'lp-tab-adres', 'lp-tab-extra', 'lp-tab-wachtwoord'];

        for (var i = 0; i < tabVolgorde.length; i++) {
            var $tab = $('#' + tabVolgorde[i]);
            if ($tab.length && $tab.find('.lp-input--fout, .lp-input:invalid').length) {
                $('[data-tab="' + tabVolgorde[i] + '"]').trigger('click');
                break;
            }
        }
    })();

}(jQuery));
