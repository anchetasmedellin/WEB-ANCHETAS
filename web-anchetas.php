<?php
/**
 * Plugin Name: WEB ANCHETAS
 * Description: Configuracion inicial del sitio y del catalogo para Anchetas Medellin.
 * Version: 0.1.0
 * Author: Codex
 * Requires at least: 6.0
 * Requires PHP: 7.4
 */

if (! defined('ABSPATH')) {
    exit;
}

define('WEB_ANCHETAS_VERSION', '0.1.0');
define('WEB_ANCHETAS_FILE', __FILE__);
define('WEB_ANCHETAS_PATH', plugin_dir_path(__FILE__));

require_once WEB_ANCHETAS_PATH . 'includes/class-web-anchetas-seeder.php';

register_activation_hook(__FILE__, array('Web_Anchetas_Seeder', 'activate'));

add_action('admin_menu', 'web_anchetas_register_admin_page');

function web_anchetas_register_admin_page() {
    add_menu_page(
        'WEB ANCHETAS',
        'WEB ANCHETAS',
        'manage_options',
        'web-anchetas',
        'web_anchetas_render_admin_page',
        'dashicons-storefront',
        58
    );
}

function web_anchetas_render_admin_page() {
    if (! current_user_can('manage_options')) {
        return;
    }

    $message = '';

    if (isset($_POST['web_anchetas_seed_site'])) {
        check_admin_referer('web_anchetas_seed_site');

        $result  = Web_Anchetas_Seeder::seed_site();
        $message = sprintf(
            'Configuracion aplicada. Paginas: %d. Categorias: %d. Productos: %d.',
            count($result['pages']),
            count($result['categories']),
            count($result['products'])
        );
    }

    echo '<div class="wrap">';
    echo '<h1>WEB ANCHETAS</h1>';
    echo '<p>Este plugin crea la base inicial de Anchetas Medellin en WordPress y WooCommerce.</p>';

    if ($message) {
        echo '<div class="notice notice-success"><p>' . esc_html($message) . '</p></div>';
    }

    echo '<ul style="list-style:disc; padding-left:20px;">';
    echo '<li>Actualiza la portada principal del sitio.</li>';
    echo '<li>Crea paginas comerciales en espanol.</li>';
    echo '<li>Crea categorias y productos iniciales de WooCommerce.</li>';
    echo '<li>Traduce los titulos base de WooCommerce.</li>';
    echo '</ul>';

    echo '<form method="post">';
    wp_nonce_field('web_anchetas_seed_site');
    submit_button('Crear o actualizar contenido inicial', 'primary', 'web_anchetas_seed_site');
    echo '</form>';
    echo '</div>';
}
