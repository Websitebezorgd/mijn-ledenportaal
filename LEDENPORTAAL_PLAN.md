# Ledenportaal WordPress Plugin — Plan van Aanpak

## Doel

Bouw een custom WordPress plugin genaamd `mijn-ledenportaal` die Ultimate Member volledig vervangt. De plugin biedt registratie, login, profielbeheer en paginabeveiliging voor een ledenportaal. Formulieren en stijlen zijn hardcoded in de plugin. Geen externe form-builder dependencies.

---

## Context

- WordPress site met Elementor en een child theme
- Ultimate Member wordt verwijderd — geen UM-code of UM-meta hergebruiken
- Gebruikersvelden worden opgeslagen als standaard WordPress user meta (geen geserialiseerde arrays)
- Shortcodes worden in Elementor pagina's gezet via HTML widget
- Bestaande WordPress gebruikersrollen en `wp_users` / `wp_usermeta` tabellen worden gebruikt — geen custom database tabellen

---

## Pluginstructuur

```
wp-content/plugins/mijn-ledenportaal/
├── mijn-ledenportaal.php          ← hoofdbestand, plugin header, autoload includes
├── includes/
│   ├── registratie.php            ← formulier + verwerking nieuwe gebruiker
│   ├── login.php                  ← login formulier + verwerking
│   ├── account.php                ← profiel weergave + wijzigen formulier
│   ├── afscherming.php            ← pagina's afschermen voor niet-ingelogde gebruikers
│   ├── mails.php                  ← alle e-mailnotificaties
│   └── admin.php                  ← WordPress admin pagina voor instellingen
├── assets/
│   ├── css/
│   │   └── ledenportaal.css       ← alle frontend stijlen
│   └── js/
│       └── ledenportaal.js        ← minimale JS (validatie, UX)
└── templates/
    ├── registratie-form.php
    ├── login-form.php
    └── account-form.php
```

---

## Hoofdbestand: `mijn-ledenportaal.php`

```php
<?php
/**
 * Plugin Name: Mijn Ledenportaal
 * Description: Registratie, login en ledenbeheer zonder Ultimate Member
 * Version: 1.0.0
 * Author: WYS Media
 * Text Domain: mijn-ledenportaal
 */

if (!defined('ABSPATH')) exit;

define('LP_PATH', plugin_dir_path(__FILE__));
define('LP_URL',  plugin_dir_url(__FILE__));
define('LP_VERSION', '1.0.0');

require_once LP_PATH . 'includes/registratie.php';
require_once LP_PATH . 'includes/login.php';
require_once LP_PATH . 'includes/account.php';
require_once LP_PATH . 'includes/afscherming.php';
require_once LP_PATH . 'includes/mails.php';
require_once LP_PATH . 'includes/admin.php';

// Assets laden
add_action('wp_enqueue_scripts', function() {
    wp_enqueue_style('ledenportaal-css', LP_URL . 'assets/css/ledenportaal.css', [], LP_VERSION);
    wp_enqueue_script('ledenportaal-js', LP_URL . 'assets/js/ledenportaal.js', ['jquery'], LP_VERSION, true);
});

// Activatie hook: stel standaard opties in
register_activation_hook(__FILE__, function() {
    add_option('lp_login_pagina_id', 0);
    add_option('lp_account_pagina_id', 0);
    add_option('lp_registratie_pagina_id', 0);
    add_option('lp_beveiligde_paginas', []);
});
```

---

## Gebruikersvelden

Sla alle extra velden op als enkelvoudige user meta waarden — nooit als geserialiseerde arrays.

### Standaard WordPress velden (via `wp_update_user`)
- `first_name`
- `last_name`
- `user_email`

### Custom meta velden (via `update_user_meta`)

| Meta key | Veldtype | Omschrijving |
|---|---|---|
| `lp_geslacht` | select | Zie opties hieronder |
| `lp_straatnaam` | text | |
| `lp_huisnummer` | text | |
| `lp_huisnummer_toevoeging` | text | |
| `lp_plaats` | text | |
| `lp_postcode` | text | |
| `lp_land` | select | Landcode bijv. NL |
| `lp_geboortedatum` | date | Format: YYYY-MM-DD |
| `lp_telefoonnummer` | text | |
| `lp_mobiel` | text | |
| `lp_afdeling` | select | Zie opties hieronder |
| `lp_soort_pensioen` | select | Zie opties hieronder |
| `lp_verenigingsfunctie` | multi (aparte meta rijen) | Zie opties hieronder |
| `lp_account_status` | text | `pending` / `approved` / `rejected` |

### Dropdown opties

#### Geslacht (`lp_geslacht`)
```php
$lp_geslacht_opties = [
    'man'     => 'Man',
    'vrouw'   => 'Vrouw',
    'non_binair' => 'Non-binair / Anders',
    'geen'    => 'Zeg ik liever niet',
];
```

#### Laatste afdeling/functie bij Delta Lloyd Groep (`lp_afdeling`)
```php
$lp_afdeling_opties = [
    'groepsstaven'   => 'Groepsstaven/Finance/HRM/Facilities',
    'dl_leven'       => 'DL Leven',
    'dl_schade'      => 'DL Schade',
    'dl_bank'        => 'DL Bank/Beleggingen/Hypotheken',
    'dl_pensioenen'  => 'DL Pensioenen',
    'dl_vastgoed'    => 'DL Vastgoed',
    'is_ict'         => 'IS/ICT Automatisering',
    'noord_brabant'  => 'Noord Braband',
    'nsf'            => 'Nationaal Spaarfonds',
    'ohra'           => 'OHRA',
    'abn_amro'       => 'ABN AMRO',
    'erasmus'        => 'Erasmus',
    'dlam'           => 'Delta Lloyd Asset Management',
    'anders'         => 'Anders',
];
```

#### Soort pensioen (`lp_soort_pensioen`)
```php
$lp_pensioen_opties = [
    'niet_gepensioneerd' => 'Nog niet gepensioneerd',
    'ouderdom'           => 'Ouderdomspensioen/overbruggingspensioen',
    'nabestaanden'       => 'Nabestaandenpensioen',
];
```

#### Inzetbaar voor verenigingsfunctie (`lp_verenigingsfunctie`) — meerkeuze
```php
$lp_functie_opties = [
    'nee'                => 'Nee',
    'bestuur'            => 'Bestuur',
    'commissie_leden'    => 'Commissie Ledenservice',
    'commissie_pensioen' => 'Commissie Pensioenen',
    'communicatie'       => 'Communicatiecommissie',
];
```

**Opslaan verenigingsfunctie (meerdere waarden):**
```php
// Verwijder eerst alle bestaande waarden
delete_user_meta($user_id, 'lp_verenigingsfunctie');

// Sla elke geselecteerde optie als aparte rij op
$geselecteerd = array_map('sanitize_key', $_POST['verenigingsfunctie'] ?? []);
$toegestaan   = array_keys($lp_functie_opties);
foreach ($geselecteerd as $keuze) {
    if (in_array($keuze, $toegestaan, true)) {
        add_user_meta($user_id, 'lp_verenigingsfunctie', $keuze);
    }
}
```

**Ophalen verenigingsfunctie:**
```php
$functies = get_user_meta($user_id, 'lp_verenigingsfunctie'); // geeft array terug
```

**Weergeven in formulier (checkboxes):**
```php
$huidige_functies = get_user_meta($user_id, 'lp_verenigingsfunctie'); // array
foreach ($lp_functie_opties as $waarde => $label) {
    $checked = in_array($waarde, $huidige_functies, true) ? 'checked' : '';
    echo '<label>';
    echo '<input type="checkbox" name="verenigingsfunctie[]" value="' . esc_attr($waarde) . '" ' . $checked . '>';
    echo esc_html($label);
    echo '</label>';
}
```

**Opslaan:**
```php
update_user_meta($user_id, 'lp_telefoonnummer', sanitize_text_field($_POST['telefoonnummer']));
```

**Ophalen:**
```php
$telefoon = get_user_meta($user_id, 'lp_telefoonnummer', true);
```

**Voor meerdere waarden (bijv. meerdere verenigingsfuncties):**
Gebruik `add_user_meta()` met aparte rijen — niet `implode`. Dan is filteren later mogelijk:
```php
delete_user_meta($user_id, 'lp_verenigingsfunctie');
foreach ($functies as $functie) {
    add_user_meta($user_id, 'lp_verenigingsfunctie', sanitize_text_field($functie));
}
```

---

## `includes/registratie.php`

### Shortcode
```php
add_shortcode('ledenportaal_registratie', 'lp_render_registratie');
```

### Logica
1. Als gebruiker al ingelogd is → redirect naar accountpagina
2. Toon formulier via `templates/registratie-form.php`
3. Bij POST: valideer nonce, valideer verplichte velden, check of email al bestaat
4. Maak gebruiker aan met `wp_insert_user()` — rol: `subscriber`
5. Sla extra velden op via `update_user_meta()`
6. Stel `lp_account_status` in op `pending`
7. Trigger `lp_na_registratie` action hook (mails.php luistert hierop)
8. Toon bevestigingsbericht

### Validatie verplichte velden
- Voornaam, achternaam, e-mail, wachtwoord
- E-mail: `is_email()`
- Wachtwoord: minimaal 8 tekens
- Alle inputs: `sanitize_text_field()` of `sanitize_email()`

### Wachtwoord bevestiging
- Twee wachtwoordvelden vergelijken vóór opslaan

---

## `includes/login.php`

### Shortcode
```php
add_shortcode('ledenportaal_login', 'lp_render_login');
```

### Logica
1. Als al ingelogd → redirect naar accountpagina
2. Toon formulier via `templates/login-form.php`
3. Bij POST: valideer nonce, gebruik `wp_signon()` voor authenticatie
4. Check `lp_account_status` — als `pending` of `rejected` → weiger login met melding
5. Bij succes: redirect naar accountpagina (instelling uit admin)
6. Bij fout: toon foutmelding zonder te onthullen of email of wachtwoord fout is

### Wachtwoord vergeten
- Gebruik standaard WordPress wachtwoord reset flow (`wp_lostpassword_url()`)
- Geen custom implementatie nodig

---

## `includes/account.php`

### Shortcode
```php
add_shortcode('ledenportaal_account', 'lp_render_account');
```

### Logica
1. Als niet ingelogd → redirect naar loginpagina
2. Laad huidige gebruikersdata via `wp_get_current_user()` en `get_user_meta()`
3. Toon formulier via `templates/account-form.php` met ingevulde waarden
4. Bij POST: valideer nonce, saniteer alle velden, sla op
5. Toon succesbericht na opslaan
6. E-mailadres wijzigen: check of nieuw email al in gebruik is

### Tabs structuur (optioneel, zie screenshot)
- Tab 1: Account (voornaam, achternaam, geslacht)
- Tab 2: Adresgegevens
- Tab 3: Extra gegevens (geboortedatum, telefoon, afdeling etc.)

---

## `includes/afscherming.php`

### Aanpak
Gebruik `template_redirect` hook. Beveiligde pagina ID's komen uit plugin instellingen (admin).

```php
add_action('template_redirect', function() {
    if (!is_user_logged_in()) {
        $beveiligde_paginas = get_option('lp_beveiligde_paginas', []);
        if (is_page($beveiligde_paginas)) {
            wp_redirect(get_permalink(get_option('lp_login_pagina_id')));
            exit;
        }
    }
});
```

### Extra: check account status bij elke pageload
Als gebruiker ingelogd is maar status `pending` of `rejected` → forceer logout en redirect naar login met melding.

---

## `includes/mails.php`

Gebruik altijd `wp_mail()`. Stel HTML content type in per mail (niet globaal — dat kan andere plugins breken).

### E-mailflows

| Trigger | Ontvanger | Onderwerp |
|---|---|---|
| Na registratie | Nieuwe gebruiker | Bevestiging aanmelding |
| Na registratie | Admin | Nieuw lid aangemeld |
| Na goedkeuring (admin) | Gebruiker | Je account is goedgekeurd |
| Na afwijzing (admin) | Gebruiker | Je aanmelding is helaas afgewezen |

### Voorbeeld functie

```php
add_action('lp_na_registratie', function($user_id) {
    $user = get_userdata($user_id);

    // Mail naar gebruiker
    $headers = ['Content-Type: text/html; charset=UTF-8'];
    wp_mail(
        $user->user_email,
        'Je aanmelding is ontvangen',
        lp_mail_template('registratie_bevestiging', ['gebruiker' => $user]),
        $headers
    );

    // Mail naar admin
    wp_mail(
        get_option('admin_email'),
        'Nieuw lid: ' . $user->display_name,
        lp_mail_template('admin_nieuw_lid', ['gebruiker' => $user]),
        $headers
    );
});
```

### Mail templates
Bouw een `lp_mail_template($naam, $data)` functie die eenvoudige HTML teruggeeft. Geen aparte bestanden nodig — gewoon heredoc strings per template in `mails.php`.

### SMTP
Gebruik een SMTP plugin zoals **FluentSMTP** (gratis). De plugin zelf doet geen SMTP-configuratie — dat is buiten scope.

---

## `includes/admin.php`

Voeg een eenvoudige admin pagina toe onder "Instellingen":

### Instellingen
- Login pagina (pagina selector dropdown)
- Accountpagina (pagina selector dropdown)
- Registratiepagina (pagina selector dropdown)
- Beveiligde pagina's (multi-select of checkboxlijst van alle pagina's)

### Ledenbeheer
- Overzicht van alle leden met status (`pending` / `approved` / `rejected`)
- Knoppen: Goedkeuren / Afwijzen per gebruiker
- Bij klikken: update `lp_account_status` + trigger mail action

```php
// Goedkeuren
update_user_meta($user_id, 'lp_account_status', 'approved');
do_action('lp_account_goedgekeurd', $user_id);

// Afwijzen
update_user_meta($user_id, 'lp_account_status', 'rejected');
do_action('lp_account_afgewezen', $user_id);
```

---

## `assets/css/ledenportaal.css`

### Aanpak
- Alle plugin stijlen onder `.lp-` prefix om conflicten te vermijden
- Stijl gebaseerd op screenshot: blauwe labels, witte velden, volle breedte inputs
- Responsive (mobile-first)
- Geen externe CSS frameworks — vanilla CSS

### Kleurenschema (gebaseerd op screenshot)
```css
:root {
    --lp-blauw:      #0091D5;
    --lp-donkerblauw:#006699;
    --lp-grijs:      #f5f5f5;
    --lp-border:     #e0e0e0;
    --lp-tekst:      #333333;
    --lp-label:      #0091D5;
}
```

### Componenten om te stijlen
- `.lp-form` — formulier wrapper
- `.lp-form-groep` — label + input combinatie
- `.lp-label` — veldlabels (blauw, uppercase, small)
- `.lp-input`, `.lp-select` — invoervelden
- `.lp-knop` — submit knop (blauw, volle breedte)
- `.lp-melding`, `.lp-melding--succes`, `.lp-melding--fout` — feedback berichten
- `.lp-sectie-titel` — scheiding tussen formulier secties

---

## `assets/js/ledenportaal.js`

Houd JavaScript minimaal:
- Client-side wachtwoord bevestiging check
- Toon/verberg wachtwoord toggle
- Optioneel: tab navigatie op accountpagina

Geen zware frameworks. Vanilla JS of minimaal jQuery (al geladen door WordPress).

---

## Templates

Elke template is een PHP bestand dat via `ob_start()` / `ob_get_clean()` wordt geladen vanuit de shortcode functie.

### Conventies
- Altijd `esc_attr()`, `esc_html()` op uitvoer
- Altijd nonce veld in elk formulier: `wp_nonce_field('lp_actie_naam')`
- Foutmeldingen bovenaan het formulier tonen
- Succesbericht vervangt formulier (of toon boven formulier)

---

## Beveiliging — Verplichte checks

Elke POST handler moet:

```php
// 1. Nonce validatie
check_admin_referer('lp_formulier_naam'); // of wp_verify_nonce voor AJAX

// 2. Inlogcheck waar van toepassing
if (!is_user_logged_in()) wp_die('Geen toegang');

// 3. Saniteer ALLE input
$waarde = sanitize_text_field($_POST['veld']);
$email  = sanitize_email($_POST['email']);
$int    = absint($_POST['getal']);

// 4. Escape ALLE output
echo esc_html($waarde);
echo esc_attr($waarde); // in HTML attributen
```

Gebruik nooit `$_POST` direct in database queries of output.

---

## WordPress best practices

- Gebruik `__()` / `_e()` voor alle strings (i18n-ready, ook al vertaal je nu niet)
- Prefix alle functies, hooks en opties met `lp_` om conflicten te voorkomen
- Gebruik `wp_insert_user()` en `wp_update_user()` — nooit direct in `wp_users` schrijven
- Gebruik `get_option()` / `update_option()` voor plugin instellingen
- Registreer shortcodes via `add_shortcode()` — nooit direct `echo` in shortcode callbacks (gebruik `return`)
- Geen PHP warnings: check altijd of `$_POST['veld']` bestaat met `isset()` voor gebruik

---

## Fasering voor Claude Code

### Fase 1 — Fundament
1. Hoofdbestand met plugin header en includes
2. CSS variabelen en basis stijlen
3. Admin instellingen pagina

### Fase 2 — Kernformulieren
4. Login formulier + verwerking
5. Registratie formulier + verwerking
6. Account formulier + verwerking

### Fase 3 — Beveiliging en mails
7. Paginabeveiliging via `template_redirect`
8. Account status check bij login
9. Alle e-mailflows

### Fase 4 — Admin ledenbeheer
10. Ledenlijst in admin
11. Goedkeuren / Afwijzen functionaliteit

---

## Verwijdering Ultimate Member

Doe dit NADAT de plugin klaar en getest is:

1. Exporteer eventueel bestaande UM user meta als backup (via phpMyAdmin of WP CLI)
2. Deactiveer Ultimate Member plugin
3. Verwijder Ultimate Member plugin
4. Verwijder Members plugin (als die ook weg mag)
5. Controleer of `wp_usermeta` tabel opgeruimd kan worden van `um_` prefixed keys (optioneel, doet geen kwaad om te laten staan)

---

## Niet in scope (bewuste keuzes)

- Geen drag & drop formulierbouwer
- Geen avatar/profielfoto upload (kan later toegevoegd worden)
- Geen sociale login (Google, LinkedIn)
- Geen betaalmuur / membership levels
- Geen eigen SMTP configuratie — gebruik FluentSMTP als losse plugin
- Geen custom database tabellen
