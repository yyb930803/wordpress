<?php
add_action('init', 'nasa_init_shortcode');
function nasa_init_shortcode() {
    /**
     * Shortcode [nasa_products]
     */
    add_shortcode('nasa_products', 'nasa_sc_products');
    
    /**
     * Shortcode [nasa_products_masonry]
     */
    add_shortcode('nasa_products_masonry', 'nasa_sc_products_masonry');
    
    /**
     * Shortcode [nasa_products_viewed]
     */
    add_shortcode('nasa_products_viewed', 'nasa_sc_products_viewed');
    
    /**
     * Shortcode [nasa_products_main]
     */
    add_shortcode('nasa_products_main', 'nasa_sc_products_main');
    
    /**
     * Shortcode [nasa_products_deal]
     */
    add_shortcode('nasa_products_deal', 'nasa_sc_products_deal');
    
    /**
     * Shortcode [nasa_products_special_deal]
     */
    add_shortcode('nasa_products_special_deal', 'nasa_sc_products_special_deal');
    
    /**
     * Shortcode [nasa_tag_cloud]
     */
    add_shortcode("nasa_tag_cloud", "nasa_sc_tag_cloud");
    
    /**
     * Shortcode [nasa_product_categories]
     */
    add_shortcode("nasa_product_categories", "nasa_sc_product_categories");
    
    /**
     * Shortcode [nasa_pin_products_banner]
     */
    add_shortcode("nasa_pin_products_banner", "nasa_sc_pin_products_banner");
    
    /**
     * Shortcode [nasa_pin_material_banner]
     */
    add_shortcode("nasa_pin_material_banner", "nasa_sc_pin_material_banner");
    
    /**
     * Shortcode [nasa_products_byids]
     */
    add_shortcode('nasa_products_byids', 'nasa_sc_products_byids');
    
    /**
     * Shortcode [nasa_slider][/nasa_slider]
     */
    add_shortcode("nasa_slider", "nasa_sc_carousel");
    
    /**
     * Shortcode [nasa_banner][/nasa_banner]
     */
    add_shortcode('nasa_banner', 'nasa_sc_banners');
    
    /**
     * Shortcode [nasa_mega_menu]
     */
    add_shortcode('nasa_mega_menu', 'nasa_sc_mega_menu');
    
    /**
     * Shortcode [nasa_menu]
     */
    add_shortcode('nasa_menu', 'nasa_sc_menu');
    
    /**
     * Shortcode [nasa_menu_vertical]
     */
    add_shortcode('nasa_menu_vertical', 'nasa_sc_menu_vertical');
    
    /**
     * Shortcode [nasa_menu_vertical]
     */
    add_shortcode('nasa_compare_imgs', 'nasa_sc_compare_imgs');
    
    /**
     * Shortcode [nasa_post]
     */
    add_shortcode("nasa_post", "nasa_sc_posts");
    
    /**
     * Shortcode [nasa_search_posts]
     */
    add_shortcode("nasa_search_posts", "nasa_sc_search_post");
    
    /**
     * Shortcode [nasa_search_posts]
     */
    add_shortcode('nasa_button', 'nasa_sc_buttons');
    
    /**
     * Shortcode [nasa_brands]
     */
    add_shortcode('nasa_brands', 'nasa_sc_brands');
    
    /**
     * Deprecated
     * Shortcode [nasa_google_map]
     */
    add_shortcode('nasa_google_map', 'nasa_sc_google_maps');
    
    /**
     * Shortcode [nasa_message_box]
     */
    add_shortcode("nasa_message_box", "nasa_sc_message_box");
    
    /**
     * Shortcode [nasa_share]
     */
    add_shortcode('nasa_share', 'nasa_sc_share');
    
    /**
     * Shortcode [nasa_follow]
     */
    add_shortcode("nasa_follow", "nasa_sc_follow");
    
    /**
     * Shortcode [nasa_get_static_block]
     */
    add_shortcode('nasa_get_static_block', 'nasa_get_static_block');
    
    /**
     * Shortcode [nasa_team_member]
     */
    add_shortcode('nasa_team_member', 'nasa_sc_team_member');
    
    /**
     * Shortcode [nasa_title]
     */
    add_shortcode('nasa_title', 'nasa_title');
    
    add_shortcode("nasa_service_box", "nasa_sc_service_box");
    add_shortcode('nasa_client', 'nasa_sc_client');
    add_shortcode('nasa_contact_us', "nasa_sc_contact_us");
    add_shortcode('nasa_opening_time', 'nasa_opening_time');
    add_shortcode('nasa_countdown', 'nasa_countdown_time');
    add_shortcode('nasa_separator_link', 'nasa_sc_separator_link');
    
    /**
     * Deprecated
     * Shortcode [nasa_instagram]
     */
    add_shortcode('nasa_instagram', 'nasa_sc_instagram');
    
    /**
     * Shortcode [nasa_instagram_feed]
     */
    add_shortcode('nasa_instagram_feed', 'nasa_sc_instagram_feed');
    
    /**
     * Register Shortcode in Backend
     */
    $bakeryActive = class_exists('WPBakeryVisualComposerAbstract') ? true : false;
    $shorcodeBackend = $bakeryActive && (NASA_CORE_IN_ADMIN || (isset($_REQUEST['action']) && $_REQUEST['action'] === 'vc_load_shortcode')) ? true : false;
    
    if ($shorcodeBackend) {
        add_action('init', 'nasa_register_product', 999);
        add_action('init', 'nasa_register_products_masonry', 999);
        add_action('init', 'nasa_register_products_viewed', 999);
        add_action('init', 'nasa_register_products_main', 999);
        add_action('init', 'nasa_register_product_deals', 999);
        add_action('init', 'nasa_register_product_special_deals', 999);
        add_action('init', 'nasa_register_tagcloud', 999);
        add_action('init', 'nasa_register_productCategories', 999);
        add_action('init', 'nasa_register_products_banner', 999);
        add_action('init', 'nasa_register_material_banner', 999);
        add_action('init', 'nasa_register_products_byids', 999);
        add_action('init', 'nasa_register_slider', 999);
        add_action('init', 'nasa_register_banner', 999);
        add_action('init', 'nasa_register_mega_menu_shortcode', 999);
        add_action('init', 'nasa_register_menu_shortcode', 999);
        add_action('init', 'nasa_register_menuVertical', 999);
        add_action('init', 'nasa_register_compare_imgs', 999);
        add_action('init', 'nasa_register_latest_post', 999);
        add_action('init', 'nasa_register_search_posts', 999);
        add_action('init', 'nasa_register_brands', 999);
        add_action('init', 'nasa_register_google_maps', 999);
        add_action('init', 'nasa_register_share_follow', 999);
        add_action('init', 'nasa_register_static_block', 999);
        add_action('init', 'nasa_register_team_member', 999);
        add_action('init', 'nasa_register_title', 999);
        add_action('init', 'nasa_register_others', 999);
        add_action('init', 'nasa_register_instagram', 999);
        add_action('init', 'nasa_register_instagram_feed', 999);
    }
}

if (!function_exists('nasa_getListProductDeals')) {

    function nasa_getListProductDeals() {
        global $woocommerce;
        
        $list = array();
        if (!$woocommerce) {
            return $list;
        }
        $args = array(
            'post_type' => array('product', 'product_variation'),
            'posts_per_page' => 100,
            'post_status' => 'publish',
            'paged' => 1,
            'meta_query'     => array(
                'relation' => 'OR',
                
                // Simple products type
                array(
                    'key'           => '_sale_price_dates_to',
                    'value'         => 0,
                    'compare'       => '>',
                    'type'          => 'numeric'
                )
            )
        );
        
        $args['post__in'] = wc_get_product_ids_on_sale();
        $products = new WP_Query($args);
        if ($products->have_posts()) {
            while ($products->have_posts()) {
                $products->the_post();
                
                global $product;
                if (!$product->is_visible()){
                    continue;
                }
                $title = html_entity_decode(get_the_title());
                $list[$title] = $product->get_id();
            }
        }

        return $list;
    }

}

if (!function_exists('nasa_get_cat_product_array')) {

    function nasa_get_cat_product_array() {
        $categories = get_categories(array(
            'taxonomy' => 'product_cat',
            'orderby' => 'name',
            'hide_empty' => false
        ));
        
        $list = array(
            esc_html__('Select category', 'nasa-core') => ''
        );

        if (!empty($categories)) {
            foreach ($categories as $v) {
                $list[$v->name . ' ( ' . $v->slug . ' )'] = $v->slug;
            }
        }

        return $list;
    }

}