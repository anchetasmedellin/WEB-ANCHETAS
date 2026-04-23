<?php
/**
 * Plugin Name: WEB ANCHETAS
 * Description: Configuracion premium del sitio y del catalogo para Anchetas Medellin Premium.
 * Version: 0.2.0
 * Author: Codex
 * Requires at least: 6.0
 * Requires PHP: 7.4
 */

if (! defined('ABSPATH')) {
    exit;
}

define('WEB_ANCHETAS_VERSION', '0.2.0');
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
            'Actualizacion premium aplicada. Paginas: %d. Categorias: %d. Productos: %d.',
            count($result['pages']),
            count($result['categories']),
            count($result['products'])
        );
    }

    echo '<div class="wrap">';
    echo '<h1>WEB ANCHETAS</h1>';
    echo '<p>Este plugin crea y actualiza la version premium de Anchetas Medellin Premium en WordPress y WooCommerce.</p>';

    if ($message) {
        echo '<div class="notice notice-success"><p>' . esc_html($message) . '</p></div>';
    }

    echo '<ul style="list-style:disc; padding-left:20px;">';
    echo '<li>Renueva la portada principal con estilo premium.</li>';
    echo '<li>Crea landing pages comerciales con los enlaces principales del sitio de referencia.</li>';
    echo '<li>Actualiza contacto, cobertura, pagos y facturacion electronica.</li>';
    echo '<li>Crea o actualiza categorias y productos iniciales de WooCommerce.</li>';
    echo '</ul>';

    echo '<form method="post">';
    wp_nonce_field('web_anchetas_seed_site');
    submit_button('Aplicar version premium del sitio', 'primary', 'web_anchetas_seed_site');
    echo '</form>';
    echo '</div>';
}
