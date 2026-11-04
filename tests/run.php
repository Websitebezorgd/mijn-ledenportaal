<?php
/**
 * Lichte tests voor de risicovolle pure functies van de plugin, zonder WordPress-installatie.
 *
 * WordPress-functies zijn hieronder minimaal nagebootst. Draaien: php tests/run.php
 * Exitcode 0 = alles geslaagd. Wordt in CI gedraaid (.github/workflows/tests.yml).
 */

if ( PHP_SAPI !== 'cli' ) exit;

define( 'ABSPATH', __DIR__ . '/' );
define( 'MINUTE_IN_SECONDS', 60 );
define( 'HOUR_IN_SECONDS', 3600 );

// ── WordPress-stubs ──────────────────────────────────────────────
$GLOBALS['lp_test'] = [ 'opties' => [], 'meta' => [], 'posts' => [], 'users' => [], 'transients' => [], 'hooks' => [], 'ingelogd' => false ];

class WP_User {
    public $ID, $user_email, $user_login, $user_nicename = '';
    function __construct( $id, $email = '', $login = '' ) { $this->ID = $id; $this->user_email = $email; $this->user_login = $login; }
}
class WP_Session_Tokens {
    private $id;
    static function get_instance( $id ) { $t = new self(); $t->id = $id; return $t; }
    function destroy_all() { $GLOBALS['lp_test']['uitgelogd'][] = $this->id; }
}
// Minimale $wpdb: alleen wat e-mail wijzigen gebruikt, werkend op de test-accounts
class LP_Test_DB {
    public $users = 'wp_users', $last_error = '';
    function prepare( $sql, ...$args ) { return [ $sql, $args ]; }
    function get_var( $q ) {
        [ , [ $nicename, $id ] ] = $q;
        foreach ( $GLOBALS['lp_test']['accounts'] as $u ) {
            if ( $u->user_nicename === $nicename && $u->ID !== $id ) return $u->ID;
        }
        return null;
    }
    function update( $tabel, $data, $where ) {
        $u = $GLOBALS['lp_test']['accounts'][ $where['ID'] ];
        foreach ( $data as $k => $v ) $u->$k = $v;
        return 1;
    }
}
$GLOBALS['wpdb'] = new LP_Test_DB();
class WP_Error {
    private $code, $message, $data;
    function __construct( $code = '', $message = '', $data = '' ) { $this->code = $code; $this->message = $message; $this->data = $data; }
    function get_error_code() { return $this->code; }
    function get_error_message() { return $this->message; }
    function get_error_data() { return $this->data; }
}
class LP_Test_Response {
    private $data;
    function __construct( $data ) { $this->data = $data; }
    function get_data() { return $this->data; }
    function set_data( $data ) { $this->data = $data; }
}

function add_action( $hook, $cb ) { $GLOBALS['lp_test']['hooks'][ $hook ][] = $cb; }
function add_filter( $hook, $cb ) { $GLOBALS['lp_test']['hooks'][ $hook ][] = $cb; }
function add_shortcode() {}
function register_activation_hook() {}
function plugin_dir_path( $f ) { return dirname( $f ) . '/'; }
function plugin_dir_url( $f ) { return ''; }
function __( $s ) { return $s; }
function sanitize_text_field( $s ) { return trim( (string) $s ); }
function sanitize_user( $u, $strict = false ) {
    $u = strip_tags( (string) $u );
    if ( $strict ) $u = preg_replace( '|[^a-z0-9 _.\-@]|i', '', $u );
    return trim( $u );
}
function get_option( $k, $d = false ) { return $GLOBALS['lp_test']['opties'][ $k ] ?? $d; }
function get_user_meta( $id, $k ) { return $GLOBALS['lp_test']['meta'][ $id ][ $k ] ?? ''; }
function get_post( $p = null ) { return is_object( $p ) ? $p : ( $GLOBALS['lp_test']['posts'][ $p ] ?? null ); }
function get_permalink( $p ) { return 'https://voorbeeld.nl/' . get_post( $p )->slug . '/'; }
function wp_parse_url( $url, $component ) { return parse_url( $url, $component ); }
function is_user_logged_in() { return $GLOBALS['lp_test']['ingelogd']; }
// Net als MySQL niet hoofdlettergevoelig
function username_exists( $login ) {
    foreach ( $GLOBALS['lp_test']['users'] as $l => $id ) if ( strcasecmp( $l, $login ) === 0 ) return $id;
    return false;
}
function get_transient( $k ) { return $GLOBALS['lp_test']['transients'][ $k ] ?? false; }
function set_transient( $k, $v ) { $GLOBALS['lp_test']['transients'][ $k ] = $v; }
function delete_transient( $k ) { unset( $GLOBALS['lp_test']['transients'][ $k ] ); }
function is_email( $e ) { return (bool) filter_var( $e, FILTER_VALIDATE_EMAIL ); }
function update_option( $k, $v ) { $GLOBALS['lp_test']['opties'][ $k ] = $v; }
function delete_option( $k ) { unset( $GLOBALS['lp_test']['opties'][ $k ] ); }
function update_user_meta( $id, $k, $v ) { $GLOBALS['lp_test']['meta'][ $id ][ $k ] = $v; }
function do_action( $hook, ...$args ) { $GLOBALS['lp_test']['acties'][] = $hook; }
function current_time( $f ) { return '2026-01-01 12:00:00'; }
function clean_user_cache( $u ) {}
function sanitize_title( $t ) { return trim( preg_replace( '/[^a-z0-9]+/', '-', strtolower( str_replace( '@', '', $t ) ) ), '-' ); }
function get_current_user_id() { return 100; }
function wp_get_current_user() { return new WP_User( 100, 'beheer@x.nl', 'beheer' ); }
function current_user_can( $cap, $id = null ) { return ! in_array( $id, $GLOBALS['lp_test']['geen_rechten'] ?? [], true ); }
// Gebruikers voor e-mail wijzigen: id => WP_User; opzoeken zonder hoofdlettergevoeligheid, net als MySQL
function get_userdata( $id ) { return $GLOBALS['lp_test']['accounts'][ $id ] ?? false; }
function get_user_by( $veld, $waarde ) {
    foreach ( $GLOBALS['lp_test']['accounts'] ?? [] as $u ) {
        if ( strcasecmp( $veld === 'email' ? $u->user_email : $u->user_login, $waarde ) === 0 ) return $u;
    }
    return false;
}

$_SERVER['REMOTE_ADDR'] = '192.0.2.1';
require dirname( __DIR__ ) . '/mijn-ledenportaal.php';

// ── Mini-testrunner ──────────────────────────────────────────────
$lp_fouten = 0;
function check( $naam, $geslaagd ) {
    global $lp_fouten;
    echo ( $geslaagd ? 'OK    ' : 'FOUT  ' ) . $naam . "\n";
    if ( ! $geslaagd ) $lp_fouten++;
}
function zet( $sleutel, $waarde ) { $GLOBALS['lp_test'][ $sleutel ] = $waarde; }
function hook( $naam ) { return $GLOBALS['lp_test']['hooks'][ $naam ][0]; }

// ── IBAN ─────────────────────────────────────────────────────────
check( 'IBAN geldig', lp_valideer_iban( 'NL91ABNA0417164300' ) );
check( 'IBAN geldig met spaties en kleine letters', lp_valideer_iban( 'nl91 abna 0417 1643 00' ) );
check( 'IBAN ongeldige checksum', ! lp_valideer_iban( 'NL91ABNA0417164301' ) );
check( 'IBAN onzin', ! lp_valideer_iban( 'hallo' ) );

// ── CSV-injectie ─────────────────────────────────────────────────
foreach ( [ '=HYPERLINK("x")', '+31612345678', '-2+3', '@SUM(A1)', "\tx", "\rx" ] as $v ) {
    check( 'CSV beschermd + terug: ' . json_encode( $v ), lp_csv_veilig( $v )[0] === "'" && lp_csv_terug( lp_csv_veilig( $v ) ) === $v );
}
foreach ( [ 'Jan', '', "O'Brien", "'quote", '06-123', '2024-01-01' ] as $v ) {
    check( 'CSV ongemoeid: ' . json_encode( $v ), lp_csv_veilig( $v ) === $v && lp_csv_terug( $v ) === $v );
}

// ── Unieke gebruikersnaam ────────────────────────────────────────
zet( 'users', [ 'jan@x.nl' => 1, 'jan@x.nl-2' => 2, 'janlid@x.nl' => 3 ] );
check( 'gebruikersnaam: vrij adres', lp_unieke_gebruikersnaam( 'piet@x.nl' ) === 'piet@x.nl' );
check( 'gebruikersnaam: volgnummer bij botsing', lp_unieke_gebruikersnaam( 'jan@x.nl' ) === 'jan@x.nl-3' );
check( 'gebruikersnaam: strikt opgeschoond (+)', lp_unieke_gebruikersnaam( 'jan+lid@x.nl' ) === 'janlid@x.nl-2' );
check( 'gebruikersnaam: max. 60 tekens', strlen( lp_unieke_gebruikersnaam( str_repeat( 'a', 80 ) . '@x.nl' ) ) <= 60 );
check( 'gebruikersnaam: leeg wordt "lid"', lp_unieke_gebruikersnaam( '+++' ) === 'lid' );

// ── Uitgesloten URL's ────────────────────────────────────────────
zet( 'opties', [ 'lp_uitgesloten_urls' => "/over-ons\n/nieuws/*\n/leden*" ] );
check( 'uitgesloten: exact pad', lp_pad_is_uitgesloten( '/over-ons/' ) );
check( 'uitgesloten: wildcard zelf', lp_pad_is_uitgesloten( '/nieuws' ) );
check( 'uitgesloten: wildcard onderliggend', lp_pad_is_uitgesloten( '/nieuws/bericht-1' ) );
check( 'uitgesloten: wildcard niet op deel van segment', ! lp_pad_is_uitgesloten( '/nieuwsbrief' ) );
check( 'uitgesloten: /leden* niet /ledenlijst-publiek', ! lp_pad_is_uitgesloten( '/ledenlijst-publiek' ) );
check( 'uitgesloten: ander pad', ! lp_pad_is_uitgesloten( '/contact' ) );
check( 'uitgesloten: null geeft geen fout', ! lp_pad_is_uitgesloten( null ) );
zet( 'opties', [ 'lp_uitgesloten_urls' => '/*' ] );
check( 'uitgesloten: /* matcht alles', lp_pad_is_uitgesloten( '/wat/dan/ook' ) && lp_pad_is_uitgesloten( '/' ) );

// ── Afscherming per bericht ──────────────────────────────────────
$post = fn( $id, $type, $slug ) => (object) [ 'ID' => $id, 'post_type' => $type, 'slug' => $slug ];
zet( 'posts', [
    10 => $post( 10, 'page', 'login' ),
    11 => $post( 11, 'page', 'leden' ),
    12 => $post( 12, 'page', 'over-ons' ),
    13 => $post( 13, 'post', 'nieuws-1' ),
    14 => $post( 14, 'page', 'home' ),
] );
zet( 'opties', [ 'lp_login_pagina_id' => 10, 'lp_beveiligde_paginas' => [ '11' ], 'lp_beveiligde_post_types' => [ 'post' ] ] );
check( 'afscherming: beveiligde pagina (id als string)', lp_post_is_beveiligd( 11 ) );
check( 'afscherming: open pagina', ! lp_post_is_beveiligd( 12 ) );
check( 'afscherming: beveiligd berichttype', lp_post_is_beveiligd( 13 ) );
check( 'afscherming: loginpagina altijd open', ! lp_post_is_beveiligd( 10 ) );
check( 'afscherming: onbekend bericht', ! lp_post_is_beveiligd( 999 ) );
zet( 'opties', [ 'lp_login_pagina_id' => 10, 'lp_beveilig_alles' => '1', 'page_on_front' => 14, 'lp_uitgesloten_urls' => '/over-ons' ] );
check( 'alles beveiligd: gewone pagina', lp_post_is_beveiligd( 11 ) );
check( 'alles beveiligd: homepage open', ! lp_post_is_beveiligd( 14 ) );
check( 'alles beveiligd: uitgesloten pad open', ! lp_post_is_beveiligd( 12 ) );

zet( 'opties', [ 'lp_beveiligde_paginas' => [ 11 ] ] );
$r = lp_rest_verberg_beveiligde_inhoud( new LP_Test_Response( [ 'title' => [ 'rendered' => 'Leden' ], 'content' => [ 'rendered' => 'geheim' ], 'excerpt' => [ 'rendered' => 'geh' ] ] ), get_post( 11 ) );
check( 'REST: inhoud en samenvatting leeg, titel blijft', $r->get_data()['content']['rendered'] === '' && $r->get_data()['excerpt']['rendered'] === '' && $r->get_data()['title']['rendered'] === 'Leden' );
$r = lp_rest_verberg_beveiligde_inhoud( new LP_Test_Response( [ 'content' => [ 'rendered' => 'publiek' ] ] ), get_post( 12 ) );
check( 'REST: open pagina intact', $r->get_data()['content']['rendered'] === 'publiek' );
zet( 'ingelogd', true );
$r = lp_rest_verberg_beveiligde_inhoud( new LP_Test_Response( [ 'content' => [ 'rendered' => 'geheim' ] ] ), get_post( 11 ) );
check( 'REST: ingelogd lid ziet inhoud', $r->get_data()['content']['rendered'] === 'geheim' );
zet( 'ingelogd', false );

// ── Login: statusblokkade ────────────────────────────────────────
zet( 'meta', [ 1 => [ 'lp_account_status' => 'pending' ], 2 => [ 'lp_account_status' => 'rejected' ], 3 => [ 'lp_account_status' => 'approved' ] ] );
$r = lp_blokkeer_login_op_status( new WP_User( 1 ) );
check( 'login: pending geweigerd', $r instanceof WP_Error && $r->get_error_data()['lp_account_status'] === 'pending' );
$r = lp_blokkeer_login_op_status( new WP_User( 2 ) );
check( 'login: rejected geweigerd', $r instanceof WP_Error && $r->get_error_data()['lp_account_status'] === 'rejected' );
check( 'login: approved doorgelaten', lp_blokkeer_login_op_status( new WP_User( 3 ) ) instanceof WP_User );
check( 'login: zonder status doorgelaten', lp_blokkeer_login_op_status( new WP_User( 4 ) ) instanceof WP_User );
$fout = new WP_Error( 'incorrect_password' );
check( 'login: fout wachtwoord ongewijzigd', lp_blokkeer_login_op_status( $fout ) === $fout );

// ── Login: rate limiting ─────────────────────────────────────────
$mislukt = hook( 'wp_login_failed' );
$gelukt  = hook( 'wp_login' );
$kees    = new WP_User( 9, 'kees@x.nl' );
check( 'rate limit: eerst doorgelaten', lp_blokkeer_login_na_te_veel_pogingen( $kees, 'kees@x.nl' ) === $kees );
for ( $i = 0; $i < 5; $i++ ) $mislukt( 'kees@x.nl', new WP_Error( 'incorrect_password' ) );
$r = lp_blokkeer_login_na_te_veel_pogingen( $kees, 'kees@x.nl' );
check( 'rate limit: na 5 fouten geblokkeerd, ook met goed wachtwoord', $r instanceof WP_Error && $r->get_error_code() === 'lp_te_veel_pogingen' );
check( 'rate limit: ander account vanaf zelfde IP nog open', lp_blokkeer_login_na_te_veel_pogingen( $kees, 'ander@x.nl' ) === $kees );
$mislukt( 'kees@x.nl', new WP_Error( 'lp_te_veel_pogingen' ) );
check( 'rate limit: eigen blokkade telt niet mee', get_transient( lp_limiet_transient( 'login', '192.0.2.1|kees@x.nl' ) ) === 5 );
$gelukt( 'kees', $kees );
check( 'rate limit: login via gebruikersnaam reset ook e-mailteller', lp_blokkeer_login_na_te_veel_pogingen( $kees, 'kees@x.nl' ) === $kees );
for ( $i = 0; $i < 30; $i++ ) $mislukt( 'spray' . $i . '@x.nl', new WP_Error( 'invalid_username' ) );
check( 'rate limit: IP-limiet na 30 fouten', lp_blokkeer_login_na_te_veel_pogingen( $kees, 'nieuw@x.nl' ) instanceof WP_Error );

// ── E-mail wijzigen: invoer lezen ────────────────────────────────
$r = lp_email_wijzig_lees_invoer( "-- commentaar\n# ook\n\na@x.nl => B@X.nl\nc@x.nl -> d@x.nl\ne@x.nl, f@x.nl\ng@x.nl   h@x.nl\n[i@x.nl](mailto:i@x.nl) => j@x.nl\nonzin\nk@x.nl => geen-adres\nJan@x.nl => jan@x.nl" );
check( 'e-mail wijzigen: alle scheidingstekens gelezen', count( $r['paren'] ) === 6 );
check( 'e-mail wijzigen: hoofdletters in nieuw adres blijven staan', $r['paren'][0]['nieuw'] === 'B@X.nl' );
check( 'e-mail wijzigen: mailto-link opgeschoond', $r['paren'][4]['oud'] === 'i@x.nl' );
check( 'e-mail wijzigen: alleen hoofdletters anders is toegestaan', $r['paren'][5] === [ 'oud' => 'Jan@x.nl', 'nieuw' => 'jan@x.nl', 'regel' => 11 ] );
check( 'e-mail wijzigen: foute regels gemeld met regelnummer', array_column( $r['fouten'], 'regel' ) === [ 9, 10 ] );

// ── E-mail wijzigen: controleren (zonder uitvoeren) ──────────────
zet( 'accounts', [
    1 => new WP_User( 1, 'oud@x.nl', 'oud@x.nl' ),
    2 => new WP_User( 2, 'bezet@x.nl', 'lid2' ),
    3 => new WP_User( 3, 'derde@x.nl', 'gedeeld@x.nl' ),
    4 => new WP_User( 4, 'gedeeld@x.nl', 'lid4' ),
    5 => new WP_User( 5, 'Hoofd@x.nl', 'hoofd@x.nl' ),
    6 => new WP_User( 6, 'klaar@x.nl', 'klaar@x.nl' ),
] );
zet( 'users', [ 'oud@x.nl' => 1, 'lid2' => 2, 'gedeeld@x.nl' => 3, 'lid4' => 4, 'hoofd@x.nl' => 5, 'klaar@x.nl' => 6, 'nieuw@x.nl' => 99 ] );
$status = fn( $oud, $nieuw ) => lp_email_wijzig_verwerk( [ 'oud' => $oud, 'nieuw' => $nieuw ] );
$r = $status( 'oud@x.nl', 'vrij@x.nl' );
check( 'e-mail wijzigen: klaar', $r['status'] === 'klaar' && $r['user_id'] === 1 );
check( 'e-mail wijzigen: onbekend adres', $status( 'wie@x.nl', 'vrij@x.nl' )['status'] === 'niet_gevonden' );
check( 'e-mail wijzigen: nieuw adres van ander lid', $status( 'oud@x.nl', 'bezet@x.nl' )['status'] === 'conflict' );
$r = $status( 'gedeeld@x.nl', 'vrij@x.nl' );
check( 'e-mail wijzigen: e-mailadres gaat voor op oude gebruikersnaam van ander lid', $r['status'] === 'klaar' && $r['user_id'] === 4 );
zet( 'accounts', $GLOBALS['lp_test']['accounts'] + [ 7 => new WP_User( 7, 'anders@x.nl', 'alleenlogin@x.nl' ) ] );
zet( 'users', $GLOBALS['lp_test']['users'] + [ 'alleenlogin@x.nl' => 7 ] );
check( 'e-mail wijzigen: zonder e-mailmatch gevonden op gebruikersnaam', $status( 'alleenlogin@x.nl', 'vrij@x.nl' )['user_id'] === 7 );
zet( 'accounts', $GLOBALS['lp_test']['accounts'] + [ 100 => new WP_User( 100, 'beheer@x.nl', 'beheer' ) ] );
check( 'e-mail wijzigen: eigen account geweigerd', $status( 'beheer@x.nl', 'ik@x.nl' )['status'] === 'eigen_account' );
zet( 'geen_rechten', [ 6 ] );
check( 'e-mail wijzigen: account zonder edit_user-recht geweigerd', $status( 'klaar@x.nl', 'ander@x.nl' )['status'] === 'geen_rechten' );
zet( 'geen_rechten', [] );
check( 'e-mail wijzigen: hoofdletters weghalen', $status( 'Hoofd@x.nl', 'hoofd@x.nl' )['status'] === 'klaar' );
check( 'e-mail wijzigen: hoofdletters toevoegen', $status( 'oud@x.nl', 'Oud@x.nl' )['status'] === 'klaar' );
check( 'e-mail wijzigen: al goed', $status( 'klaar@x.nl', 'klaar@x.nl' )['status'] === 'ongewijzigd' );
check( 'e-mail wijzigen: bezette gebruikersnaam krijgt volgnummer', lp_unieke_gebruikersnaam( 'nieuw@x.nl', 1 ) === 'nieuw@x.nl-2' );
check( 'e-mail wijzigen: eigen gebruikersnaam blijft', lp_unieke_gebruikersnaam( 'oud@x.nl', 1 ) === 'oud@x.nl' );
check( 'e-mail wijzigen: eigen gebruikersnaam blijft ook met andere hoofdletters', lp_unieke_gebruikersnaam( 'Oud@x.nl', 1 ) === 'Oud@x.nl' );

// ── E-mail wijzigen: afhankelijke regels in één lijst ────────────
$r = lp_email_wijzig_verwerk_alles( [
    [ 'oud' => 'bezet@x.nl', 'nieuw' => 'c@x.nl' ],
    [ 'oud' => 'oud@x.nl', 'nieuw' => 'Bezet@x.nl' ],
    [ 'oud' => 'klaar@x.nl', 'nieuw' => 'z@x.nl' ],
    [ 'oud' => 'derde@x.nl', 'nieuw' => 'z@x.nl' ],
    [ 'oud' => 'Hoofd@x.nl', 'nieuw' => 'hoofd@x.nl' ],
] );
check( 'e-mail wijzigen: keten (b => c, a => b) niet verwerkt', $r[0]['status'] === 'dubbel' && $r[1]['status'] === 'dubbel' );
check( 'e-mail wijzigen: twee keer hetzelfde nieuwe adres niet verwerkt', $r[2]['status'] === 'dubbel' && $r[3]['status'] === 'dubbel' );
check( 'e-mail wijzigen: alleen hoofdletters telt niet als dubbel', $r[4]['status'] === 'klaar' );

// ── E-mail wijzigen: uitvoeren en logboek ────────────────────────
zet( 'opties', [ 'wys_ueu_log' => [ [ 'time' => '2025-05-05', 'actor' => 'oud', 'old' => 'p@x.nl', 'new' => 'q@x.nl', 'user_id' => 8, 'status' => 'updated', 'message' => 'ok' ] ] ] );
$GLOBALS['lp_test']['accounts'][2]->user_nicename = 'nieuwxx-nl';
$r = lp_email_wijzig_verwerk_alles( [ [ 'oud' => 'oud@x.nl', 'nieuw' => 'NieuwX@x.nl' ], [ 'oud' => 'wie@x.nl', 'nieuw' => 'y@x.nl' ] ], true );
$jan = get_userdata( 1 );
$log = lp_email_wijzig_log();
check( 'uitvoeren: e-mail met hoofdletters, gebruikersnaam mee', $r[0]['status'] === 'bijgewerkt' && $jan->user_email === 'NieuwX@x.nl' && $jan->user_login === 'NieuwX@x.nl' );
check( 'uitvoeren: nicename volgt nieuwe naam en blijft uniek', $jan->user_nicename === 'nieuwxx-nl-2' );
check( 'uitvoeren: lid uitgelogd', $GLOBALS['lp_test']['uitgelogd'] === [ 1 ] );
check( 'uitvoeren: laatst gewijzigd en profile_update', get_user_meta( 1, 'lp_account_gewijzigd' ) !== '' && in_array( 'profile_update', $GLOBALS['lp_test']['acties'], true ) );
check( 'uitvoeren: elke regel in het logboek, nieuwste eerst', count( $log ) === 3 && $log[0]['status'] === 'niet_gevonden' && $log[1]['status'] === 'bijgewerkt' && $log[1]['door'] === 'beheer (#100)' );
check( 'uitvoeren: oud logboek eenmalig omgezet', $log[2]['status'] === 'bijgewerkt' && $log[2]['oud'] === 'p@x.nl' && ! isset( $GLOBALS['lp_test']['opties']['wys_ueu_log'] ) );

echo "\n" . ( $lp_fouten ? "{$lp_fouten} test(s) mislukt.\n" : "Alle tests geslaagd.\n" );
exit( $lp_fouten ? 1 : 0 );
