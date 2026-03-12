<?php
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Admin menu registratie
 */
add_action( 'admin_menu', function() {
    add_menu_page(
        __( 'Ledenportaal', 'mijn-ledenportaal' ),
        __( 'Ledenportaal', 'mijn-ledenportaal' ),
        'manage_options',
        'ledenportaal',
        'lp_admin_instellingen_pagina',
        'dashicons-groups',
        30
    );

    add_submenu_page(
        'ledenportaal',
        __( 'Instellingen', 'mijn-ledenportaal' ),
        __( 'Instellingen', 'mijn-ledenportaal' ),
        'manage_options',
        'ledenportaal',
        'lp_admin_instellingen_pagina'
    );

    add_submenu_page(
        'ledenportaal',
        __( 'Ledenbeheer', 'mijn-ledenportaal' ),
        __( 'Ledenbeheer', 'mijn-ledenportaal' ),
        'manage_options',
        'lp-ledenbeheer',
        'lp_admin_ledenbeheer_pagina'
    );
} );

/**
 * Instellingen registreren
 */
add_action( 'admin_init', function() {
    register_setting( 'lp_instellingen', 'lp_login_pagina_id', [ 'sanitize_callback' => 'absint' ] );
    register_setting( 'lp_instellingen', 'lp_account_pagina_id', [ 'sanitize_callback' => 'absint' ] );
    register_setting( 'lp_instellingen', 'lp_registratie_pagina_id', [ 'sanitize_callback' => 'absint' ] );
    register_setting( 'lp_instellingen', 'lp_beveiligde_paginas', [
        'sanitize_callback' => function( $waarde ) {
            if ( ! is_array( $waarde ) ) return [];
            return array_map( 'absint', $waarde );
        }
    ] );
} );

/**
 * Admin pagina: Instellingen
 */
function lp_admin_instellingen_pagina() {
    if ( ! current_user_can( 'manage_options' ) ) return;

    $alle_paginas = get_pages( [ 'post_status' => 'publish', 'sort_column' => 'post_title' ] );
    $login_id     = get_option( 'lp_login_pagina_id', 0 );
    $account_id   = get_option( 'lp_account_pagina_id', 0 );
    $registratie_id = get_option( 'lp_registratie_pagina_id', 0 );
    $beveiligde   = get_option( 'lp_beveiligde_paginas', [] );
    if ( ! is_array( $beveiligde ) ) $beveiligde = [];
    ?>
    <div class="wrap">
        <h1><?php esc_html_e( 'Ledenportaal — Instellingen', 'mijn-ledenportaal' ); ?></h1>

        <?php settings_errors( 'lp_instellingen' ); ?>

        <form method="post" action="options.php">
            <?php settings_fields( 'lp_instellingen' ); ?>

            <table class="form-table">
                <tr>
                    <th scope="row">
                        <label for="lp_login_pagina_id"><?php esc_html_e( 'Loginpagina', 'mijn-ledenportaal' ); ?></label>
                    </th>
                    <td>
                        <select name="lp_login_pagina_id" id="lp_login_pagina_id">
                            <option value="0"><?php esc_html_e( '— Selecteer pagina —', 'mijn-ledenportaal' ); ?></option>
                            <?php foreach ( $alle_paginas as $pagina ) : ?>
                                <option value="<?php echo esc_attr( $pagina->ID ); ?>" <?php selected( $login_id, $pagina->ID ); ?>>
                                    <?php echo esc_html( $pagina->post_title ); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <?php if ( $login_id ) : ?>
                            <a href="<?php echo esc_url( get_permalink( $login_id ) ); ?>" target="_blank" class="button button-small" style="margin-left: 8px;">
                                <?php esc_html_e( 'Bekijk', 'mijn-ledenportaal' ); ?>
                            </a>
                        <?php endif; ?>
                        <p class="description"><?php esc_html_e( 'Shortcode: [ledenportaal_login]', 'mijn-ledenportaal' ); ?></p>
                    </td>
                </tr>
                <tr>
                    <th scope="row">
                        <label for="lp_account_pagina_id"><?php esc_html_e( 'Accountpagina', 'mijn-ledenportaal' ); ?></label>
                    </th>
                    <td>
                        <select name="lp_account_pagina_id" id="lp_account_pagina_id">
                            <option value="0"><?php esc_html_e( '— Selecteer pagina —', 'mijn-ledenportaal' ); ?></option>
                            <?php foreach ( $alle_paginas as $pagina ) : ?>
                                <option value="<?php echo esc_attr( $pagina->ID ); ?>" <?php selected( $account_id, $pagina->ID ); ?>>
                                    <?php echo esc_html( $pagina->post_title ); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <?php if ( $account_id ) : ?>
                            <a href="<?php echo esc_url( get_permalink( $account_id ) ); ?>" target="_blank" class="button button-small" style="margin-left: 8px;">
                                <?php esc_html_e( 'Bekijk', 'mijn-ledenportaal' ); ?>
                            </a>
                        <?php endif; ?>
                        <p class="description"><?php esc_html_e( 'Shortcode: [ledenportaal_account]', 'mijn-ledenportaal' ); ?></p>
                    </td>
                </tr>
                <tr>
                    <th scope="row">
                        <label for="lp_registratie_pagina_id"><?php esc_html_e( 'Registratiepagina', 'mijn-ledenportaal' ); ?></label>
                    </th>
                    <td>
                        <select name="lp_registratie_pagina_id" id="lp_registratie_pagina_id">
                            <option value="0"><?php esc_html_e( '— Selecteer pagina —', 'mijn-ledenportaal' ); ?></option>
                            <?php foreach ( $alle_paginas as $pagina ) : ?>
                                <option value="<?php echo esc_attr( $pagina->ID ); ?>" <?php selected( $registratie_id, $pagina->ID ); ?>>
                                    <?php echo esc_html( $pagina->post_title ); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <?php if ( $registratie_id ) : ?>
                            <a href="<?php echo esc_url( get_permalink( $registratie_id ) ); ?>" target="_blank" class="button button-small" style="margin-left: 8px;">
                                <?php esc_html_e( 'Bekijk', 'mijn-ledenportaal' ); ?>
                            </a>
                        <?php endif; ?>
                        <p class="description"><?php esc_html_e( 'Shortcode: [ledenportaal_registratie]', 'mijn-ledenportaal' ); ?></p>
                    </td>
                </tr>
                <tr>
                    <th scope="row">
                        <?php esc_html_e( 'Beveiligde pagina\'s', 'mijn-ledenportaal' ); ?>
                    </th>
                    <td>
                        <fieldset>
                            <?php foreach ( $alle_paginas as $pagina ) : ?>
                                <label style="display: block; margin-bottom: 4px;">
                                    <input type="checkbox"
                                        name="lp_beveiligde_paginas[]"
                                        value="<?php echo esc_attr( $pagina->ID ); ?>"
                                        <?php checked( in_array( $pagina->ID, $beveiligde, true ) ); ?>>
                                    <?php echo esc_html( $pagina->post_title ); ?>
                                </label>
                            <?php endforeach; ?>
                        </fieldset>
                        <p class="description"><?php esc_html_e( 'Niet-ingelogde bezoekers worden doorgestuurd naar de loginpagina.', 'mijn-ledenportaal' ); ?></p>
                    </td>
                </tr>
            </table>

            <?php submit_button( __( 'Instellingen opslaan', 'mijn-ledenportaal' ) ); ?>
        </form>
    </div>
    <?php
}

/**
 * Admin pagina: Ledenbeheer
 */
function lp_admin_ledenbeheer_pagina() {
    if ( ! current_user_can( 'manage_options' ) ) return;

    // Verwerk goedkeuren / afwijzen
    if ( isset( $_POST['lp_ledenbeheer_actie'] ) && isset( $_POST['lp_ledenbeheer_nonce'] ) ) {
        if ( wp_verify_nonce( $_POST['lp_ledenbeheer_nonce'], 'lp_ledenbeheer' ) ) {
            $actie   = sanitize_key( $_POST['lp_ledenbeheer_actie'] );
            $user_id = absint( $_POST['lp_user_id'] ?? 0 );

            if ( $user_id && in_array( $actie, [ 'goedkeuren', 'afwijzen' ], true ) ) {
                if ( $actie === 'goedkeuren' ) {
                    update_user_meta( $user_id, 'lp_account_status', 'approved' );
                    do_action( 'lp_account_goedgekeurd', $user_id );
                    echo '<div class="notice notice-success"><p>' . esc_html__( 'Account goedgekeurd.', 'mijn-ledenportaal' ) . '</p></div>';
                } else {
                    update_user_meta( $user_id, 'lp_account_status', 'rejected' );
                    do_action( 'lp_account_afgewezen', $user_id );
                    echo '<div class="notice notice-warning"><p>' . esc_html__( 'Account afgewezen.', 'mijn-ledenportaal' ) . '</p></div>';
                }
            }
        }
    }

    // Haal gebruikers op met lp_account_status meta
    $filter_status = sanitize_key( $_GET['lp_filter'] ?? 'all' );

    $query_args = [
        'meta_key'     => 'lp_account_status',
        'meta_compare' => 'EXISTS',
        'number'       => 100,
        'orderby'      => 'registered',
        'order'        => 'DESC',
    ];

    if ( $filter_status !== 'all' ) {
        $query_args['meta_value'] = $filter_status;
    }

    $gebruikers = get_users( $query_args );

    $status_labels = [
        'pending'  => __( 'In afwachting', 'mijn-ledenportaal' ),
        'approved' => __( 'Goedgekeurd', 'mijn-ledenportaal' ),
        'rejected' => __( 'Afgewezen', 'mijn-ledenportaal' ),
    ];

    $status_kleuren = [
        'pending'  => '#f0ad4e',
        'approved' => '#5cb85c',
        'rejected' => '#d9534f',
    ];

    $huidige_url = admin_url( 'admin.php?page=lp-ledenbeheer' );
    ?>
    <div class="wrap">
        <h1><?php esc_html_e( 'Ledenportaal — Ledenbeheer', 'mijn-ledenportaal' ); ?></h1>

        <ul class="subsubsub">
            <li><a href="<?php echo esc_url( $huidige_url ); ?>" <?php echo $filter_status === 'all' ? 'class="current"' : ''; ?>><?php esc_html_e( 'Alle leden', 'mijn-ledenportaal' ); ?></a> |</li>
            <li><a href="<?php echo esc_url( add_query_arg( 'lp_filter', 'pending', $huidige_url ) ); ?>" <?php echo $filter_status === 'pending' ? 'class="current"' : ''; ?>><?php esc_html_e( 'In afwachting', 'mijn-ledenportaal' ); ?></a> |</li>
            <li><a href="<?php echo esc_url( add_query_arg( 'lp_filter', 'approved', $huidige_url ) ); ?>" <?php echo $filter_status === 'approved' ? 'class="current"' : ''; ?>><?php esc_html_e( 'Goedgekeurd', 'mijn-ledenportaal' ); ?></a> |</li>
            <li><a href="<?php echo esc_url( add_query_arg( 'lp_filter', 'rejected', $huidige_url ) ); ?>" <?php echo $filter_status === 'rejected' ? 'class="current"' : ''; ?>><?php esc_html_e( 'Afgewezen', 'mijn-ledenportaal' ); ?></a></li>
        </ul>

        <?php if ( empty( $gebruikers ) ) : ?>
            <p><?php esc_html_e( 'Geen leden gevonden.', 'mijn-ledenportaal' ); ?></p>
        <?php else : ?>
            <table class="wp-list-table widefat fixed striped" style="margin-top: 20px;">
                <thead>
                    <tr>
                        <th><?php esc_html_e( 'Naam', 'mijn-ledenportaal' ); ?></th>
                        <th><?php esc_html_e( 'E-mail', 'mijn-ledenportaal' ); ?></th>
                        <th><?php esc_html_e( 'Geregistreerd', 'mijn-ledenportaal' ); ?></th>
                        <th><?php esc_html_e( 'Status', 'mijn-ledenportaal' ); ?></th>
                        <th><?php esc_html_e( 'Acties', 'mijn-ledenportaal' ); ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ( $gebruikers as $gebruiker ) :
                        $status = get_user_meta( $gebruiker->ID, 'lp_account_status', true );
                        $kleur  = $status_kleuren[ $status ] ?? '#999';
                        $label  = $status_labels[ $status ] ?? esc_html( $status );
                    ?>
                        <tr>
                            <td>
                                <strong><?php echo esc_html( $gebruiker->display_name ); ?></strong>
                                <br>
                                <a href="<?php echo esc_url( get_edit_user_link( $gebruiker->ID ) ); ?>" class="button button-small">
                                    <?php esc_html_e( 'Bewerken', 'mijn-ledenportaal' ); ?>
                                </a>
                            </td>
                            <td><?php echo esc_html( $gebruiker->user_email ); ?></td>
                            <td><?php echo esc_html( date_i18n( get_option( 'date_format' ), strtotime( $gebruiker->user_registered ) ) ); ?></td>
                            <td>
                                <span style="background: <?php echo esc_attr( $kleur ); ?>; color: white; padding: 3px 8px; border-radius: 3px; font-size: 12px;">
                                    <?php echo esc_html( $label ); ?>
                                </span>
                            </td>
                            <td>
                                <?php if ( $status !== 'approved' ) : ?>
                                    <form method="post" style="display: inline;">
                                        <?php wp_nonce_field( 'lp_ledenbeheer', 'lp_ledenbeheer_nonce' ); ?>
                                        <input type="hidden" name="lp_user_id" value="<?php echo esc_attr( $gebruiker->ID ); ?>">
                                        <input type="hidden" name="lp_ledenbeheer_actie" value="goedkeuren">
                                        <button type="submit" class="button button-primary button-small">
                                            <?php esc_html_e( 'Goedkeuren', 'mijn-ledenportaal' ); ?>
                                        </button>
                                    </form>
                                <?php endif; ?>
                                <?php if ( $status !== 'rejected' ) : ?>
                                    <form method="post" style="display: inline; margin-left: 4px;">
                                        <?php wp_nonce_field( 'lp_ledenbeheer', 'lp_ledenbeheer_nonce' ); ?>
                                        <input type="hidden" name="lp_user_id" value="<?php echo esc_attr( $gebruiker->ID ); ?>">
                                        <input type="hidden" name="lp_ledenbeheer_actie" value="afwijzen">
                                        <button type="submit" class="button button-small" style="color: #d9534f; border-color: #d9534f;"
                                            onclick="return confirm('<?php esc_attr_e( 'Weet je zeker dat je dit account wilt afwijzen?', 'mijn-ledenportaal' ); ?>')">
                                            <?php esc_html_e( 'Afwijzen', 'mijn-ledenportaal' ); ?>
                                        </button>
                                    </form>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>
    <?php
}
