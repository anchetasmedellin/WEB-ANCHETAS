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
        update_option('blogname', 'Anchetas Medellin');
        update_option('blogdescription', 'Anchetas, desayunos sorpresa y regalos a domicilio en Medellin.');
    }

    private static function seed_pages() {
        $pages = array();

        $definitions = array(
            array(
                'slug' => 'home',
                'fallback_slugs' => array('inicio'),
                'title' => 'Anchetas Medellin',
                'content' => self::home_content(),
                'excerpt' => 'Anchetas, desayunos sorpresa y regalos especiales en Medellin.',
                'front_page' => true,
            ),
            array(
                'slug' => 'about-us',
                'fallback_slugs' => array('nosotros'),
                'title' => 'Nosotros',
                'content' => self::about_content(),
                'excerpt' => 'Conoce nuestro servicio de anchetas y regalos sorpresa en Medellin.',
            ),
            array(
                'slug' => 'contact-us',
                'fallback_slugs' => array('contacto'),
                'title' => 'Contacto',
                'content' => self::contact_content(),
                'excerpt' => 'Solicita tu cotizacion y programa entregas en Medellin.',
            ),
            array(
                'slug' => 'anchetas-cumpleanos',
                'fallback_slugs' => array(),
                'title' => 'Anchetas de cumpleanos',
                'content' => self::birthday_page_content(),
                'excerpt' => 'Detalles personalizados para celebrar cumpleanos en Medellin.',
            ),
            array(
                'slug' => 'desayunos-sorpresa',
                'fallback_slugs' => array(),
                'title' => 'Desayunos sorpresa',
                'content' => self::breakfast_page_content(),
                'excerpt' => 'Desayunos sorpresa con entrega a domicilio en Medellin.',
            ),
            array(
                'slug' => 'regalos-empresariales',
                'fallback_slugs' => array(),
                'title' => 'Regalos empresariales',
                'content' => self::business_page_content(),
                'excerpt' => 'Anchetas y regalos corporativos para empresas en Medellin.',
            ),
            array(
                'slug' => 'anchetas-romanticas',
                'fallback_slugs' => array(),
                'title' => 'Anchetas romanticas',
                'content' => self::romantic_page_content(),
                'excerpt' => 'Regalos romanticos para aniversarios y momentos especiales.',
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

    private static function upsert_page($definition) {
        $page = get_page_by_path($definition['slug'], OBJECT, 'page');

        if (! $page && ! empty($definition['fallback_slugs'])) {
            foreach ($definition['fallback_slugs'] as $fallback_slug) {
                $page = get_page_by_path($fallback_slug, OBJECT, 'page');
                if ($page) {
                    break;
                }
            }
        }

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
                'name' => 'Anchetas de cumpleanos',
                'slug' => 'anchetas-cumpleanos',
                'description' => 'Regalos listos para celebrar cumpleanos y fechas especiales.',
            ),
            array(
                'name' => 'Desayunos sorpresa',
                'slug' => 'desayunos-sorpresa',
                'description' => 'Desayunos sorpresa con entrega a domicilio en Medellin.',
            ),
            array(
                'name' => 'Anchetas romanticas',
                'slug' => 'anchetas-romanticas',
                'description' => 'Detalles para aniversarios, pedidas y momentos especiales.',
            ),
            array(
                'name' => 'Regalos empresariales',
                'slug' => 'regalos-empresariales',
                'description' => 'Opciones corporativas para clientes, equipos y eventos.',
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
                'name' => 'Ancheta Dulce Clasica',
                'slug' => 'ancheta-dulce-clasica',
                'price' => '89000',
                'category' => 'anchetas-cumpleanos',
                'short_description' => 'Incluye snacks, chocolates, bebida y tarjeta personalizada.',
                'description' => '<p>Una opcion ideal para regalar en cumpleanos y celebraciones familiares. Incluye snacks dulces, chocolates, bebida, decoracion y mensaje personalizado.</p><ul><li>Presentacion lista para regalar</li><li>Ideal para cumpleanos</li><li>Entrega en Medellin</li></ul>',
            ),
            array(
                'name' => 'Desayuno Sorpresa Medellin',
                'slug' => 'desayuno-sorpresa-medellin',
                'price' => '119000',
                'category' => 'desayunos-sorpresa',
                'short_description' => 'Desayuno especial con detalles dulces y entrega sorpresa.',
                'description' => '<p>Desayuno sorpresa para empezar el dia con un detalle inolvidable. Incluye bebida caliente, panaderia, fruta, dulce y decoracion especial.</p><ul><li>Entrega programada</li><li>Ideal para parejas y familia</li><li>Disponible en Medellin</li></ul>',
            ),
            array(
                'name' => 'Ancheta Romantica Especial',
                'slug' => 'ancheta-romantica-especial',
                'price' => '149000',
                'category' => 'anchetas-romanticas',
                'short_description' => 'Caja romantica con chocolates, detalle especial y mensaje.',
                'description' => '<p>Perfecta para aniversarios, reconciliaciones o sorpresas romanticas. Incluye chocolates, bebida, decoracion y espacio para una dedicatoria personalizada.</p><ul><li>Diseno elegante</li><li>Mensaje personalizado</li><li>Entrega en Medellin</li></ul>',
            ),
            array(
                'name' => 'Ancheta Empresarial Premium',
                'slug' => 'ancheta-empresarial-premium',
                'price' => '199000',
                'category' => 'regalos-empresariales',
                'short_description' => 'Regalo corporativo para clientes, aliados y equipos.',
                'description' => '<p>Una solucion premium para empresas que quieren sorprender con una presentacion impecable. Ideal para reconocimientos, cierres de negocio y fechas especiales.</p><ul><li>Presentacion corporativa</li><li>Opcion personalizable</li><li>Atencion para pedidos por volumen</li></ul>',
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
        $product = get_page_by_path($definition['slug'], OBJECT, 'product');

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
            'woocommerce_shop_page_id' => 'Tienda',
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

    private static function contact_url() {
        $page = get_page_by_path('contact-us', OBJECT, 'page');
        if ($page) {
            return esc_url(get_permalink($page));
        }

        return esc_url(home_url('/contact-us/'));
    }

    private static function category_url($slug) {
        if (taxonomy_exists('product_cat')) {
            $term = get_term_by('slug', $slug, 'product_cat');
            if ($term && ! is_wp_error($term)) {
                return esc_url(get_term_link($term));
            }
        }

        return esc_url(home_url('/product-category/' . $slug . '/'));
    }

    private static function home_content() {
        $shop_url    = self::shop_url();
        $contact_url = self::contact_url();

        return '<section><h1>Anchetas Medellin</h1><p>Regalos con entrega a domicilio para cumpleanos, aniversarios, fechas especiales y detalles empresariales en Medellin.</p><p><a href="' . $shop_url . '">Ver tienda</a> | <a href="' . $contact_url . '">Solicitar cotizacion</a></p></section><section><h2>Nuestras lineas principales</h2><ul><li>Anchetas de cumpleanos con chocolates, snacks y mensajes personalizados</li><li>Desayunos sorpresa para parejas, familia y celebraciones especiales</li><li>Anchetas romanticas para aniversarios y momentos inolvidables</li><li>Regalos empresariales para clientes, aliados y colaboradores</li></ul></section><section><h2>Por que elegirnos</h2><ul><li>Entrega en Medellin</li><li>Presentacion cuidada y lista para sorprender</li><li>Opciones personalizadas segun presupuesto</li><li>Atencion cercana y rapida</li></ul></section><section><h2>Haz tu pedido</h2><p>Te ayudamos a elegir la ancheta ideal segun la ocasion, el presupuesto y el estilo del regalo que quieres enviar.</p><p><a href="' . $shop_url . '">Comprar ahora</a></p></section>';
    }

    private static function about_content() {
        return '<section><h1>Nosotros</h1><p>En Anchetas Medellin creamos detalles pensados para emocionar. Trabajamos con presentaciones cuidadas, productos seleccionados y opciones personalizadas para que cada regalo tenga un significado especial.</p><p>Atendemos pedidos para fechas familiares, celebraciones romanticas y regalos empresariales, con entregas en Medellin y enfoque en puntualidad, presentacion y servicio.</p></section><section><h2>Nuestro enfoque</h2><ul><li>Calidad en cada detalle</li><li>Personalizacion segun la ocasion</li><li>Atencion agil para pedidos urgentes</li><li>Soluciones para personas y empresas</li></ul></section>';
    }

    private static function contact_content() {
        return '<section><h1>Contacto</h1><p>Solicita tu pedido, consulta cobertura o pide una cotizacion personalizada para una ancheta especial en Medellin.</p><ul><li>Ciudad: Medellin, Colombia</li><li>Horario: Lunes a sabado</li><li>Pedidos: por mensaje o formulario de contacto</li></ul><p>Si ya sabes que regalo quieres, cuentanos la ocasion, el presupuesto y la fecha de entrega.</p></section>';
    }

    private static function birthday_page_content() {
        $category_url = self::category_url('anchetas-cumpleanos');

        return '<section><h1>Anchetas de cumpleanos</h1><p>Tenemos opciones pensadas para sorprender en cumpleanos con chocolates, snacks, bebidas, decoracion y mensajes personalizados.</p><ul><li>Presentaciones clasicas y premium</li><li>Opciones para hombres, mujeres y ninos</li><li>Entrega a domicilio en Medellin</li></ul><p><a href="' . $category_url . '">Ver categoria</a></p></section>';
    }

    private static function breakfast_page_content() {
        $category_url = self::category_url('desayunos-sorpresa');

        return '<section><h1>Desayunos sorpresa</h1><p>Desayunos especiales para celebrar desde temprano con un detalle bonito y bien presentado.</p><ul><li>Ideal para parejas y familia</li><li>Entrega programada</li><li>Opciones con dulce, fruta y panaderia</li></ul><p><a href="' . $category_url . '">Ver categoria</a></p></section>';
    }

    private static function business_page_content() {
        $category_url = self::category_url('regalos-empresariales');

        return '<section><h1>Regalos empresariales</h1><p>Preparamos anchetas empresariales para clientes, equipos y eventos corporativos con una presentacion profesional y personalizable.</p><ul><li>Pedidos por volumen</li><li>Opciones segun presupuesto</li><li>Ideal para reconocimientos y fechas especiales</li></ul><p><a href="' . $category_url . '">Ver categoria</a></p></section>';
    }

    private static function romantic_page_content() {
        $category_url = self::category_url('anchetas-romanticas');

        return '<section><h1>Anchetas romanticas</h1><p>Detalles para aniversarios, reconciliaciones, celebraciones de pareja y momentos especiales.</p><ul><li>Mensajes personalizados</li><li>Presentacion elegante</li><li>Opciones con chocolates y bebida</li></ul><p><a href="' . $category_url . '">Ver categoria</a></p></section>';
    }
}
