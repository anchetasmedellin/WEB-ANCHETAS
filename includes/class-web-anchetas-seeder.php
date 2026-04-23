<?php

if (! defined('ABSPATH')) {
    exit;
}

class Web_Anchetas_Seeder {
    public static function activate() {
        self::seed_site();
        flush_rewrite_rules();
    }

    public static function seed_site() {
        self::update_site_identity();

        $pages      = self::seed_pages();
        $categories = self::seed_product_categories();
        $products   = self::seed_products();

        self::translate_woocommerce_pages();
        self::disable_hello_world_post();

        return array(
            'pages' => $pages,
            'categories' => $categories,
            'products' => $products,
        );
    }

    private static function update_site_identity() {
        update_option('blogname', 'ANCHETAS MEDELLIN PREMIUM');
        update_option('blogdescription', 'Anchetas premium, desayunos de lujo y regalos exclusivos con entrega en Medellin y toda el area metropolitana.');
    }

    private static function phone_number() {
        return '+57 323 408 3575';
    }

    private static function whatsapp_url() {
        return 'https://wa.me/573234083575';
    }

    private static function email() {
        return 'anchetasmedellin2017@gmail.com';
    }

    private static function address() {
        return 'Calle 34 #55-28, Medellin, Antioquia';
    }

    private static function map_url() {
        return 'https://maps.google.com/?q=calle%2034%2055-28%20medellin';
    }

    private static function coverage_text() {
        return 'Medellin y toda el area metropolitana';
    }

    private static function social_links() {
        return array(
            'Facebook' => 'https://www.facebook.com/anchetamedellin',
            'Instagram' => 'https://www.instagram.com/anchetasmedellin.con/',
            'LinkedIn' => 'https://www.linkedin.com/in/anchetas-medellin-840858174/',
            'X' => 'https://x.com/anchetasmede',
            'YouTube' => 'https://www.youtube.com/@anchetasmedellin',
        );
    }

    private static function social_links_html() {
        $html = '';

        foreach (self::social_links() as $label => $url) {
            $html .= '<a style="display:inline-block;margin:0 10px 10px 0;padding:10px 16px;border:1px solid #d4af37;border-radius:999px;color:#d4af37;text-decoration:none;font-weight:600;" href="' . esc_url($url) . '">' . esc_html($label) . '</a>';
        }

        return $html;
    }

    private static function seed_pages() {
        $pages = array();

        $definitions = array(
            array(
                'slug' => 'home',
                'fallback_slugs' => array('inicio'),
                'title' => 'ANCHETAS MEDELLIN PREMIUM',
                'content' => self::home_content(),
                'excerpt' => 'Anchetas premium, desayunos de lujo y regalos exclusivos en Medellin y el area metropolitana.',
                'front_page' => true,
            ),
            array(
                'slug' => 'catalogo-anchetas-sorpresa',
                'fallback_slugs' => array('anchetas-cumpleanos'),
                'title' => 'Catalogo Premium',
                'content' => self::catalog_page_content(),
                'excerpt' => 'Catalogo premium de anchetas, desayunos sorpresa y regalos de lujo.',
            ),
            array(
                'slug' => 'anchetas-navidenas',
                'fallback_slugs' => array(),
                'title' => 'Anchetas Navidenas Premium',
                'content' => self::christmas_page_content(),
                'excerpt' => 'Anchetas navidenas premium con seleccion gourmet y presentacion elegante.',
            ),
            array(
                'slug' => 'desayunos-sorpresa-medellin',
                'fallback_slugs' => array('desayunos-sorpresa'),
                'title' => 'Desayunos Sorpresa Medellin',
                'content' => self::breakfast_page_content(),
                'excerpt' => 'Desayunos sorpresa premium con entrega en Medellin y area metropolitana.',
            ),
            array(
                'slug' => 'anchetas-de-flores',
                'fallback_slugs' => array('anchetas-romanticas'),
                'title' => 'Anchetas y Arreglos Florales',
                'content' => self::flowers_page_content(),
                'excerpt' => 'Cajas florales y regalos elegantes para momentos especiales.',
            ),
            array(
                'slug' => 'anchetas-y-regalos-para-hombre',
                'fallback_slugs' => array(),
                'title' => 'Anchetas y Regalos para Hombre',
                'content' => self::mens_page_content(),
                'excerpt' => 'Regalos premium para hombre con licores, charcuteria y seleccion gourmet.',
            ),
            array(
                'slug' => 'regalos-empresariales',
                'fallback_slugs' => array('regalos-empresariales'),
                'title' => 'Regalos Empresariales Premium',
                'content' => self::business_page_content(),
                'excerpt' => 'Regalos empresariales premium con facturacion electronica y atencion corporativa.',
            ),
            array(
                'slug' => 'blog-anchetas-medellin',
                'fallback_slugs' => array(),
                'title' => 'Blog Anchetas Medellin',
                'content' => self::blog_page_content(),
                'excerpt' => 'Ideas, guias y tendencias para regalos premium en Medellin.',
            ),
            array(
                'slug' => 'mensajes',
                'fallback_slugs' => array(),
                'title' => 'Mensajes para Regalar',
                'content' => self::messages_page_content(),
                'excerpt' => 'Mensajes elegantes y emocionales para acompanar tus anchetas premium.',
            ),
            array(
                'slug' => 'contacto-entrega-a-domicilio',
                'fallback_slugs' => array('contact-us', 'contacto'),
                'title' => 'Contacto y Entrega a Domicilio',
                'content' => self::contact_content(),
                'excerpt' => 'Contacto, entregas, pagos y facturacion electronica para Medellin y el area metropolitana.',
            ),
            array(
                'slug' => 'about',
                'fallback_slugs' => array('about-us', 'nosotros'),
                'title' => 'Acerca de Anchetas Medellin Premium',
                'content' => self::about_content(),
                'excerpt' => 'Conoce la propuesta premium y luxury de Anchetas Medellin Premium.',
            ),
        );

        foreach ($definitions as $definition) {
            $page_id = self::upsert_page($definition);

            if (! $page_id) {
                continue;
            }

            $pages[] = $page_id;

            if (! empty($definition['front_page'])) {
                update_option('show_on_front', 'page');
                update_option('page_on_front', $page_id);
            }
        }

        return $pages;
    }

    private static function find_post_by_slugs($slug, $fallback_slugs, $post_type) {
        $post = get_page_by_path($slug, OBJECT, $post_type);

        if ($post) {
            return $post;
        }

        foreach ((array) $fallback_slugs as $fallback_slug) {
            $post = get_page_by_path($fallback_slug, OBJECT, $post_type);
            if ($post) {
                return $post;
            }
        }

        return null;
    }

    private static function upsert_page($definition) {
        $page = self::find_post_by_slugs($definition['slug'], $definition['fallback_slugs'], 'page');

        $page_data = array(
            'post_type' => 'page',
            'post_title' => $definition['title'],
            'post_name' => $definition['slug'],
            'post_content' => $definition['content'],
            'post_excerpt' => $definition['excerpt'],
            'post_status' => 'publish',
        );

        if ($page) {
            $page_data['ID'] = $page->ID;
            $result = wp_update_post($page_data, true);
        } else {
            $result = wp_insert_post($page_data, true);
        }

        if (is_wp_error($result)) {
            return 0;
        }

        return (int) $result;
    }

    private static function seed_product_categories() {
        if (! taxonomy_exists('product_cat')) {
            return array();
        }

        $definitions = array(
            array(
                'name' => 'Anchetas Premium',
                'slug' => 'anchetas-premium',
                'description' => 'Seleccion premium de anchetas luxury para regalos exclusivos.',
            ),
            array(
                'name' => 'Desayunos Sorpresa',
                'slug' => 'desayunos-sorpresa',
                'description' => 'Desayunos premium para sorprender en Medellin y area metropolitana.',
            ),
            array(
                'name' => 'Anchetas Navidenas',
                'slug' => 'anchetas-navidenas',
                'description' => 'Coleccion navidena premium con detalles gourmet y elegantes.',
            ),
            array(
                'name' => 'Arreglos Florales',
                'slug' => 'arreglos-florales',
                'description' => 'Arreglos florales y cajas luxury para ocasiones memorables.',
            ),
            array(
                'name' => 'Regalos para Hombre',
                'slug' => 'regalos-para-hombre',
                'description' => 'Regalos premium para hombre con caracter y sofisticacion.',
            ),
            array(
                'name' => 'Regalos Empresariales',
                'slug' => 'regalos-empresariales',
                'description' => 'Soluciones corporativas premium con facturacion electronica.',
            ),
        );

        $created = array();

        foreach ($definitions as $definition) {
            $term = term_exists($definition['slug'], 'product_cat');

            if ($term && ! is_wp_error($term)) {
                $term_id = is_array($term) ? (int) $term['term_id'] : (int) $term;
                wp_update_term(
                    $term_id,
                    'product_cat',
                    array(
                        'name' => $definition['name'],
                        'description' => $definition['description'],
                        'slug' => $definition['slug'],
                    )
                );
                $created[] = $term_id;
                continue;
            }

            $result = wp_insert_term(
                $definition['name'],
                'product_cat',
                array(
                    'slug' => $definition['slug'],
                    'description' => $definition['description'],
                )
            );

            if (is_wp_error($result)) {
                continue;
            }

            $created[] = (int) $result['term_id'];
        }

        return $created;
    }

    private static function seed_products() {
        if (! post_type_exists('product')) {
            return array();
        }

        $definitions = array(
            array(
                'name' => 'Ancheta Signature Premium',
                'slug' => 'ancheta-signature-premium',
                'fallback_slugs' => array('ancheta-dulce-clasica'),
                'price' => '189000',
                'category' => 'anchetas-premium',
                'short_description' => 'Seleccion luxury con chocolates finos, snacks premium y presentacion black gold.',
                'description' => '<p>Una ancheta premium pensada para impresionar con elegancia. Incluye seleccion gourmet, chocolates finos, snack premium, botella especial y tarjeta personalizada.</p><ul><li>Presentacion luxury black gold</li><li>Ideal para regalos memorables</li><li>Entrega en Medellin y area metropolitana</li></ul>',
            ),
            array(
                'name' => 'Desayuno Dorado Metropolitano',
                'slug' => 'desayuno-dorado-metropolitano',
                'fallback_slugs' => array('desayuno-sorpresa-medellin'),
                'price' => '169000',
                'category' => 'desayunos-sorpresa',
                'short_description' => 'Desayuno premium con presentacion elegante y entrega sorpresa.',
                'description' => '<p>Desayuno premium para celebraciones especiales con pasteleria, frutas, bebida caliente, detalle dulce y presentacion impecable.</p><ul><li>Entrega programada</li><li>Cobertura en Medellin y area metropolitana</li><li>Aceptamos todas las tarjetas de credito</li></ul>',
            ),
            array(
                'name' => 'Caja Floral Luxe',
                'slug' => 'caja-floral-luxe',
                'fallback_slugs' => array('ancheta-romantica-especial'),
                'price' => '229000',
                'category' => 'arreglos-florales',
                'short_description' => 'Arreglo floral premium con detalles gourmet y mensaje personalizado.',
                'description' => '<p>Una propuesta elegante para aniversarios, celebraciones y fechas especiales. Combina flores, detalles premium y una presentacion sofisticada.</p><ul><li>Estetica luxury</li><li>Mensaje personalizado</li><li>Ideal para sorprender con estilo</li></ul>',
            ),
            array(
                'name' => 'Caballero Reserve',
                'slug' => 'caballero-reserve',
                'fallback_slugs' => array('ancheta-empresarial-premium'),
                'price' => '239000',
                'category' => 'regalos-para-hombre',
                'short_description' => 'Regalo premium para hombre con seleccion gourmet y look sobrio.',
                'description' => '<p>Diseñado para hombres que valoran el buen gusto. Incluye seleccion gourmet, bebidas, pasabocas premium y una presentacion de alto nivel.</p><ul><li>Perfecto para cumpleanos y celebraciones</li><li>Estilo elegante y masculino</li><li>Entrega premium en Medellin</li></ul>',
            ),
            array(
                'name' => 'Corporativa Black Gold',
                'slug' => 'corporativa-black-gold',
                'fallback_slugs' => array(),
                'price' => '289000',
                'category' => 'regalos-empresariales',
                'short_description' => 'Regalo corporativo premium con facturacion electronica y presentacion ejecutiva.',
                'description' => '<p>Solucion premium para clientes, aliados y colaboradores. Diseñada para empresas que quieren regalar con elegancia y excelente impresion.</p><ul><li>Facturacion electronica</li><li>Aceptamos todas las tarjetas de credito</li><li>Atencion para pedidos corporativos</li></ul>',
            ),
        );

        $products = array();

        foreach ($definitions as $definition) {
            $product_id = self::upsert_product($definition);
            if ($product_id) {
                $products[] = $product_id;
            }
        }

        return $products;
    }

    private static function upsert_product($definition) {
        $product = self::find_post_by_slugs($definition['slug'], $definition['fallback_slugs'], 'product');

        $product_data = array(
            'post_type' => 'product',
            'post_title' => $definition['name'],
            'post_name' => $definition['slug'],
            'post_excerpt' => $definition['short_description'],
            'post_content' => $definition['description'],
            'post_status' => 'publish',
        );

        if ($product) {
            $product_data['ID'] = $product->ID;
            $result = wp_update_post($product_data, true);
        } else {
            $result = wp_insert_post($product_data, true);
        }

        if (is_wp_error($result)) {
            return 0;
        }

        $product_id = (int) $result;

        update_post_meta($product_id, '_regular_price', $definition['price']);
        update_post_meta($product_id, '_price', $definition['price']);
        update_post_meta($product_id, '_manage_stock', 'no');
        update_post_meta($product_id, '_stock_status', 'instock');
        update_post_meta($product_id, '_virtual', 'no');

        if (taxonomy_exists('product_type')) {
            wp_set_object_terms($product_id, 'simple', 'product_type');
        }

        if (taxonomy_exists('product_cat') && ! empty($definition['category'])) {
            wp_set_object_terms($product_id, $definition['category'], 'product_cat', false);
        }

        return $product_id;
    }

    private static function translate_woocommerce_pages() {
        $definitions = array(
            'woocommerce_shop_page_id' => 'Tienda Premium',
            'woocommerce_cart_page_id' => 'Carrito',
            'woocommerce_checkout_page_id' => 'Finalizar compra',
            'woocommerce_myaccount_page_id' => 'Mi cuenta',
        );

        foreach ($definitions as $option_name => $title) {
            $page_id = (int) get_option($option_name);
            if (! $page_id) {
                continue;
            }

            wp_update_post(
                array(
                    'ID' => $page_id,
                    'post_title' => $title,
                    'post_status' => 'publish',
                )
            );
        }
    }

    private static function disable_hello_world_post() {
        $post = get_page_by_path('hello-world', OBJECT, 'post');
        if (! $post) {
            return;
        }

        wp_update_post(
            array(
                'ID' => $post->ID,
                'post_status' => 'draft',
            )
        );
    }

    private static function shop_url() {
        if (function_exists('wc_get_page_permalink')) {
            return esc_url(wc_get_page_permalink('shop'));
        }

        $page_id = (int) get_option('woocommerce_shop_page_id');
        if ($page_id) {
            return esc_url(get_permalink($page_id));
        }

        return esc_url(home_url('/shop/'));
    }

    private static function page_url($slug, $fallback_slugs = array()) {
        $page = self::find_post_by_slugs($slug, $fallback_slugs, 'page');
        if ($page) {
            return esc_url(get_permalink($page));
        }

        return esc_url(home_url('/' . trim($slug, '/') . '/'));
    }

    private static function home_content() {
        $catalog_url   = self::page_url('catalogo-anchetas-sorpresa', array('anchetas-cumpleanos'));
        $christmas_url = self::page_url('anchetas-navidenas');
        $breakfast_url = self::page_url('desayunos-sorpresa-medellin', array('desayunos-sorpresa'));
        $flowers_url   = self::page_url('anchetas-de-flores');
        $mens_url      = self::page_url('anchetas-y-regalos-para-hombre');
        $business_url  = self::page_url('regalos-empresariales');
        $messages_url  = self::page_url('mensajes');
        $blog_url      = self::page_url('blog-anchetas-medellin');
        $contact_url   = self::page_url('contacto-entrega-a-domicilio', array('contact-us', 'contacto'));
        $about_url     = self::page_url('about', array('about-us', 'nosotros'));
        $shop_url      = self::shop_url();
        $whatsapp_url  = esc_url(self::whatsapp_url());
        $phone         = esc_html(self::phone_number());
        $email         = esc_html(self::email());
        $address       = esc_html(self::address());
        $coverage      = esc_html(self::coverage_text());
        $social_links  = self::social_links_html();

        return <<<HTML
<section style="background:radial-gradient(circle at top,#6b5521 0%,#1b1408 32%,#0a0a0a 100%);color:#f7f1dc;padding:56px 32px;border-radius:28px;border:1px solid #caa64b;margin-bottom:32px;box-shadow:0 24px 60px rgba(0,0,0,.28);">
    <p style="margin:0 0 10px 0;font-size:13px;letter-spacing:3px;text-transform:uppercase;color:#d4af37;">Luxury gifting in Medellin</p>
    <h1 style="margin:0 0 18px 0;font-size:52px;line-height:1.05;color:#fff3c4;">ANCHETAS MEDELLIN PREMIUM</h1>
    <p style="max-width:760px;font-size:20px;line-height:1.7;margin:0 0 24px 0;">Diseñamos anchetas premium, desayunos de lujo y regalos exclusivos con una propuesta visual black gold, atencion cercana y detalles que elevan cualquier celebracion.</p>
    <p style="max-width:760px;font-size:17px;line-height:1.7;margin:0 0 28px 0;">Atendemos {$coverage}. Generamos facturacion electronica y aceptamos todas las tarjetas de credito para que tu experiencia sea elegante, practica y confiable.</p>
    <div style="display:flex;flex-wrap:wrap;gap:14px;margin-bottom:26px;">
        <a href="{$catalog_url}" style="background:#d4af37;color:#111;padding:14px 24px;border-radius:999px;text-decoration:none;font-weight:700;">Explorar catalogo premium</a>
        <a href="{$whatsapp_url}" style="border:1px solid #d4af37;color:#f7f1dc;padding:14px 24px;border-radius:999px;text-decoration:none;font-weight:700;">Pedir por WhatsApp</a>
        <a href="{$shop_url}" style="border:1px solid rgba(255,255,255,.3);color:#f7f1dc;padding:14px 24px;border-radius:999px;text-decoration:none;font-weight:700;">Ver tienda</a>
    </div>
    <div style="display:flex;flex-wrap:wrap;gap:18px;font-size:15px;color:#ead9a1;">
        <span><strong>WhatsApp:</strong> {$phone}</span>
        <span><strong>Email:</strong> {$email}</span>
        <span><strong>Direccion:</strong> {$address}</span>
    </div>
</section>

<section style="margin-bottom:32px;">
    <h2 style="font-size:34px;margin-bottom:12px;color:#1b1b1b;">Colecciones premium inspiradas en tu marca</h2>
    <p style="font-size:17px;line-height:1.7;max-width:840px;">Tomamos como base la estructura comercial de tu otra web y la llevamos a una experiencia mas premium, mas luxury y mas elegante, manteniendo los enlaces principales, el numero de contacto y la misma direccion operativa.</p>
    <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(220px,1fr));gap:18px;margin-top:24px;">
        <div style="padding:24px;border:1px solid #ead7a2;border-radius:22px;background:#fffaf0;"><h3 style="margin-top:0;">Catalogo Premium</h3><p>Una seleccion curada para regalos con alto impacto visual y excelente presentacion.</p><a href="{$catalog_url}">Ver coleccion</a></div>
        <div style="padding:24px;border:1px solid #ead7a2;border-radius:22px;background:#fffaf0;"><h3 style="margin-top:0;">Anchetas Navidenas</h3><p>Detalles premium para fin de año, regalos empresariales y celebraciones familiares.</p><a href="{$christmas_url}">Ver navidad</a></div>
        <div style="padding:24px;border:1px solid #ead7a2;border-radius:22px;background:#fffaf0;"><h3 style="margin-top:0;">Desayunos Sorpresa</h3><p>Montajes elegantes para celebrar desde la mañana con una experiencia memorable.</p><a href="{$breakfast_url}">Ver desayunos</a></div>
        <div style="padding:24px;border:1px solid #ead7a2;border-radius:22px;background:#fffaf0;"><h3 style="margin-top:0;">Flores y Cajas Luxe</h3><p>Arreglos florales y detalles sofisticados para aniversarios y momentos especiales.</p><a href="{$flowers_url}">Ver flores</a></div>
        <div style="padding:24px;border:1px solid #ead7a2;border-radius:22px;background:#fffaf0;"><h3 style="margin-top:0;">Regalos para Hombre</h3><p>Selecciones sobrias, premium y con caracter para un regalo diferente.</p><a href="{$mens_url}">Ver regalos</a></div>
        <div style="padding:24px;border:1px solid #ead7a2;border-radius:22px;background:#fffaf0;"><h3 style="margin-top:0;">Linea Empresarial</h3><p>Soluciones para clientes, aliados y equipos con facturacion electronica y pago con tarjeta.</p><a href="{$business_url}">Ver empresarial</a></div>
    </div>
</section>

<section style="display:grid;grid-template-columns:repeat(auto-fit,minmax(240px,1fr));gap:18px;margin-bottom:32px;">
    <div style="padding:24px;border-radius:22px;background:#111;color:#f1e8c9;"><h3 style="margin-top:0;color:#d4af37;">Cobertura premium</h3><p>Entregamos en Medellin y toda el area metropolitana con atencion amable, puntualidad y presentacion impecable.</p></div>
    <div style="padding:24px;border-radius:22px;background:#111;color:#f1e8c9;"><h3 style="margin-top:0;color:#d4af37;">Pagos faciles</h3><p>Aceptamos todas las tarjetas de credito para facilitar compras personales, corporativas y pedidos de ultimo momento.</p></div>
    <div style="padding:24px;border-radius:22px;background:#111;color:#f1e8c9;"><h3 style="margin-top:0;color:#d4af37;">Facturacion electronica</h3><p>Atendemos clientes particulares y empresas con facturacion electronica y procesos mas ordenados.</p></div>
</section>

<section style="padding:30px;border:1px solid #ead7a2;border-radius:24px;background:#fffdf7;margin-bottom:32px;">
    <h2 style="font-size:30px;margin-top:0;">Explora mas secciones</h2>
    <p style="font-size:17px;line-height:1.7;">Ademas de la tienda, dejamos listas las secciones clave para replicar la navegacion comercial de tu otra web con una propuesta mas premium.</p>
    <div style="display:flex;flex-wrap:wrap;gap:12px;margin-top:18px;">
        <a href="{$messages_url}" style="padding:12px 16px;border:1px solid #d4af37;border-radius:999px;text-decoration:none;">Mensajes</a>
        <a href="{$blog_url}" style="padding:12px 16px;border:1px solid #d4af37;border-radius:999px;text-decoration:none;">Blog</a>
        <a href="{$contact_url}" style="padding:12px 16px;border:1px solid #d4af37;border-radius:999px;text-decoration:none;">Contacto</a>
        <a href="{$about_url}" style="padding:12px 16px;border:1px solid #d4af37;border-radius:999px;text-decoration:none;">Acerca de</a>
    </div>
</section>

<section style="background:#0d0d0d;color:#f7f1dc;padding:36px;border-radius:24px;">
    <h2 style="margin-top:0;color:#fff3c4;">Contacto directo y enlaces oficiales</h2>
    <p style="font-size:17px;line-height:1.7;max-width:820px;">Conservamos el mismo numero, la misma direccion operativa y los enlaces sociales de tu otra web para que la marca siga conectada en todos los canales.</p>
    <p><strong>WhatsApp:</strong> <a style="color:#d4af37;" href="{$whatsapp_url}">{$phone}</a><br><strong>Email:</strong> <a style="color:#d4af37;" href="mailto:{$email}">{$email}</a><br><strong>Direccion:</strong> {$address}</p>
    <div style="margin-top:14px;">{$social_links}</div>
</section>
HTML;
    }

    private static function catalog_page_content() {
        $shop_url      = self::shop_url();
        $whatsapp_url  = esc_url(self::whatsapp_url());
        $christmas_url = self::page_url('anchetas-navidenas');
        $breakfast_url = self::page_url('desayunos-sorpresa-medellin', array('desayunos-sorpresa'));
        $flowers_url   = self::page_url('anchetas-de-flores');
        $mens_url      = self::page_url('anchetas-y-regalos-para-hombre');
        $business_url  = self::page_url('regalos-empresariales');

        return <<<HTML
<section>
    <h1>Catalogo Premium</h1>
    <p>Nuestro catalogo premium esta pensado para clientes que quieren regalar con impacto visual, sofisticacion y excelente presentacion. Aqui reunimos selecciones luxury para celebraciones personales, regalos corporativos y detalles especiales en Medellin y toda el area metropolitana.</p>
    <p><a href="{$shop_url}">Entrar a la tienda</a> | <a href="{$whatsapp_url}">Cotizar por WhatsApp</a></p>
    <ul>
        <li><a href="{$christmas_url}">Anchetas Navidenas Premium</a></li>
        <li><a href="{$breakfast_url}">Desayunos Sorpresa Medellin</a></li>
        <li><a href="{$flowers_url}">Anchetas y Arreglos Florales</a></li>
        <li><a href="{$mens_url}">Anchetas y Regalos para Hombre</a></li>
        <li><a href="{$business_url}">Regalos Empresariales Premium</a></li>
    </ul>
</section>
HTML;
    }

    private static function christmas_page_content() {
        $whatsapp_url = esc_url(self::whatsapp_url());
        $shop_url     = self::shop_url();

        return <<<HTML
<section>
    <h1>Anchetas Navidenas Premium</h1>
    <p>Diseñamos anchetas navidenas premium con una seleccion gourmet, presentacion elegante y opciones de personalizacion para familia, pareja, clientes y equipos empresariales.</p>
    <ul>
        <li>Opciones clasicas y luxury black gold</li>
        <li>Pedidos personales y corporativos</li>
        <li>Facturacion electronica disponible</li>
        <li>Cobertura en Medellin y toda el area metropolitana</li>
    </ul>
    <p><a href="{$shop_url}">Ver tienda</a> | <a href="{$whatsapp_url}">Separar por WhatsApp</a></p>
</section>
HTML;
    }

    private static function breakfast_page_content() {
        $whatsapp_url = esc_url(self::whatsapp_url());
        $shop_url     = self::shop_url();

        return <<<HTML
<section>
    <h1>Desayunos Sorpresa Medellin</h1>
    <p>Desayunos premium creados para sorprender con elegancia desde primera hora. Ideales para cumpleanos, aniversarios, agradecimientos y celebraciones especiales.</p>
    <ul>
        <li>Presentacion impecable y lista para regalar</li>
        <li>Entrega programada en Medellin y area metropolitana</li>
        <li>Aceptamos todas las tarjetas de credito</li>
    </ul>
    <p><a href="{$shop_url}">Ver opciones</a> | <a href="{$whatsapp_url}">Pedir por WhatsApp</a></p>
</section>
HTML;
    }

    private static function flowers_page_content() {
        $whatsapp_url = esc_url(self::whatsapp_url());

        return <<<HTML
<section>
    <h1>Anchetas y Arreglos Florales</h1>
    <p>Una linea de regalos premium que combina flores, detalles gourmet y una estetica sofisticada. Perfecta para aniversarios, nacimientos, felicitaciones y momentos que merecen un gesto inolvidable.</p>
    <ul>
        <li>Cajas florales con estilo luxury</li>
        <li>Mensajes personalizados</li>
        <li>Ideal para sorprender con delicadeza y presencia</li>
    </ul>
    <p><a href="{$whatsapp_url}">Solicitar una propuesta floral</a></p>
</section>
HTML;
    }

    private static function mens_page_content() {
        $whatsapp_url = esc_url(self::whatsapp_url());

        return <<<HTML
<section>
    <h1>Anchetas y Regalos para Hombre</h1>
    <p>Regalos premium para hombre con una seleccion sobria, gourmet y elegante. Una excelente opcion para cumpleanos, aniversarios, celebraciones profesionales y sorpresas especiales.</p>
    <ul>
        <li>Presentaciones con caracter y buen gusto</li>
        <li>Opciones con bebidas, pasabocas premium y detalles exclusivos</li>
        <li>Atencion personalizada segun presupuesto y ocasion</li>
    </ul>
    <p><a href="{$whatsapp_url}">Cotizar regalo para hombre</a></p>
</section>
HTML;
    }

    private static function business_page_content() {
        $whatsapp_url = esc_url(self::whatsapp_url());

        return <<<HTML
<section>
    <h1>Regalos Empresariales Premium</h1>
    <p>Ayudamos a empresas que quieren regalar con elegancia, orden y excelente impresion. Diseñamos anchetas corporativas para clientes, aliados y colaboradores.</p>
    <ul>
        <li>Facturacion electronica</li>
        <li>Aceptamos todas las tarjetas de credito</li>
        <li>Pedidos individuales y por volumen</li>
        <li>Entrega en Medellin y toda el area metropolitana</li>
    </ul>
    <p><a href="{$whatsapp_url}">Hablar con un asesor corporativo</a></p>
</section>
HTML;
    }

    private static function blog_page_content() {
        $contact_url = self::page_url('contacto-entrega-a-domicilio', array('contact-us', 'contacto'));

        return <<<HTML
<section>
    <h1>Blog Anchetas Medellin</h1>
    <p>Este blog sera el espacio para posicionar la marca con contenido SEO y comercial sobre regalos premium en Medellin.</p>
    <ul>
        <li>Ideas de regalos premium para cumpleanos en Medellin</li>
        <li>Como elegir una ancheta de lujo para empresas</li>
        <li>Tendencias en desayunos sorpresa y cajas premium</li>
        <li>Mensajes elegantes para acompanar un regalo especial</li>
    </ul>
    <p>Si quieres una recomendacion personalizada para una fecha especial, visita nuestra pagina de <a href="{$contact_url}">contacto</a>.</p>
</section>
HTML;
    }

    private static function messages_page_content() {
        return <<<HTML
<section>
    <h1>Mensajes para Regalar</h1>
    <p>Aqui reunimos mensajes con un tono mas elegante y emocional para acompanar una ancheta premium.</p>
    <ul>
        <li>Para celebrar a alguien especial: "Hoy quise sorprenderte con un detalle que refleje lo valioso que eres para mi."</li>
        <li>Para aniversario: "Gracias por compartir conmigo momentos que merecen ser celebrados con belleza y amor."</li>
        <li>Para empresa: "Agradecemos profundamente su confianza. Este detalle representa nuestro aprecio y admiracion."</li>
        <li>Para cumpleanos: "Que este nuevo año llegue con abundancia, elegancia y momentos memorables."</li>
        <li>Para amistad: "Los mejores detalles no siempre se dicen; a veces se entregan con el corazon."</li>
    </ul>
</section>
HTML;
    }

    private static function contact_content() {
        $phone        = esc_html(self::phone_number());
        $email        = esc_html(self::email());
        $address      = esc_html(self::address());
        $coverage     = esc_html(self::coverage_text());
        $map_url      = esc_url(self::map_url());
        $whatsapp_url = esc_url(self::whatsapp_url());
        $social_links = self::social_links_html();

        return <<<HTML
<section>
    <h1>Contacto y Entrega a Domicilio</h1>
    <p>Estamos disponibles para ayudarte a elegir la ancheta premium ideal, coordinar entregas y resolver pedidos especiales en {$coverage}.</p>
    <ul>
        <li><strong>WhatsApp:</strong> <a href="{$whatsapp_url}">{$phone}</a></li>
        <li><strong>Email:</strong> <a href="mailto:{$email}">{$email}</a></li>
        <li><strong>Direccion:</strong> {$address}</li>
        <li><strong>Cobertura:</strong> {$coverage}</li>
        <li><strong>Facturacion electronica:</strong> disponible</li>
        <li><strong>Medios de pago:</strong> aceptamos todas las tarjetas de credito</li>
    </ul>
    <p><a href="{$map_url}">Ver ubicacion en Google Maps</a></p>
    <div style="margin-top:18px;">{$social_links}</div>
</section>
HTML;
    }

    private static function about_content() {
        $coverage = esc_html(self::coverage_text());

        return <<<HTML
<section>
    <h1>Acerca de Anchetas Medellin Premium</h1>
    <p>Anchetas Medellin Premium nace para ofrecer una experiencia mas sofisticada dentro del mundo de los regalos, con una identidad visual black gold, una narrativa luxury y un servicio cercano para clientes que valoran la elegancia en cada detalle.</p>
    <p>Nuestra propuesta combina presentacion impecable, seleccion cuidada de productos, cobertura en {$coverage} y soluciones formales para clientes personales y empresariales.</p>
    <ul>
        <li>Imagen premium y memorable</li>
        <li>Atencion personalizada</li>
        <li>Facturacion electronica</li>
        <li>Pagos con tarjeta de credito</li>
    </ul>
</section>
HTML;
    }
}
