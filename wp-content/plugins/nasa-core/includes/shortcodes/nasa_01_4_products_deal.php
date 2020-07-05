<?php
function nasa_sc_products_deal($atts, $content = null) {
    global $woocommerce, $nasa_animated_products, $nasa_opt;
    
    if (!$woocommerce) {
        return $content;
    }
    
    $dfAttr = array(
        'id' => '',
        'auto_slide' => "false",
        'arrows' => 1,
        'type_grid' => 'best_selling',
        'deal_grid_limit' => 3,
        'el_class' => ''
    );
    extract(shortcode_atts($dfAttr, $atts));
    
    if(!(int) $id) {
        return '';
    }
    
    /**
     * Cache shortcode
     */
    $key = false;
    if (isset($nasa_opt['nasa_cache_shortcodes']) && $nasa_opt['nasa_cache_shortcodes']) {
        $key = nasa_key_shortcode('nasa_products_deal', $dfAttr, $atts);
        $content = nasa_get_cache_shortcode($key);
    }
    
    if (!$content) {
        $deal_grid_limit = (int) $deal_grid_limit < 3 ? 3 : (int) $deal_grid_limit;

        $_id = rand();
        $product = nasa_getProductDeals($id);
        $catids = array();

        ob_start();
        if ($product && $product->is_visible()) :
            $id_post = $product->get_type() == 'variation' ? wp_get_post_parent_id($id) : $id;
            $product_error = $id_post ? false : true;
            $post = get_post($id_post);

            if(!isset($nasa_animated_products)) {
                $nasa_animated_products = isset($_REQUEST['effect-product']) && in_array($_REQUEST['effect-product'], array('hover-fade', 'hover-flip', 'hover-bottom-to-top', 'no')) ? $_REQUEST['effect-product'] : (isset($nasa_opt['animated_products']) ? $nasa_opt['animated_products'] : '');

                if($nasa_animated_products == 'no') {
                    $nasa_animated_products = '';
                }
            }

            $attachment_ids = $nasa_animated_products != '' ? $product->get_gallery_image_ids() : array();
            $count_imgs = count($attachment_ids);
            $img_thumbs = $img_disp = array();
            $thumbs = '';
            $title = $product->get_title() . ($product_error ? esc_html__(' - Has been error. You need rebuilt this product.', 'nasa-core') : '');
            $link = $product_error ? '#' : get_the_permalink($id);

            $image_pri = array();
            if ($primaryImg = get_post_thumbnail_id($product->get_id())) {
                $image_pri['src'] = wp_get_attachment_image_src($primaryImg, apply_filters('single_product_normal_thumbnail_size', 'shop_catalog'));
            }

            $terms = get_the_terms($id, 'product_cat');
            if (!empty($terms)) {
                foreach ($terms as $v) {
                    $catids[] = $v->term_taxonomy_id;
                }
            }

            if ($count_imgs) {
                // primary image
                foreach ($attachment_ids as $key => $img) {
                    // $img_disp[$key]['link'] = wp_get_attachment_url($img);
                    $img_disp[$key]['src'] = wp_get_attachment_image_src(
                        $img,
                        apply_filters('catalog_product_large_thumbnail_size', 'shop_catalog'), array(
                            'title' => $title
                        )
                    );
                    break;
                }
            }
            ?>
            <div class="woocommerce nasa-products-deal<?php echo ' nasa-products-deal-' . $_id . ($el_class != '' ? ' ' . $el_class : ''); ?>">
                <?php
                if(is_file(NASA_CORE_PRODUCT_LAYOUTS . 'nasa_products_deal/product_deal.php')) :
                    include NASA_CORE_PRODUCT_LAYOUTS . 'nasa_products_deal/product_deal.php';
                endif;
                ?>
            </div>
        <?php
        endif;
        wp_reset_postdata();
        $content = ob_get_clean();
        
        if ($content) {
            nasa_set_cache_shortcode($key, $content);
        }
    }

    return $content;
}

// **********************************************************************// 
// ! Register New Element: Nasa product Deal
// **********************************************************************//
function nasa_register_product_deals(){
    vc_map(array(
        "name" => esc_html__("Product Deal Schedule", 'nasa-core'),
        "base" => "nasa_products_deal",
        'icon' => 'icon-wpb-nasatheme',
        'description' => esc_html__("Display product deal and more.", 'nasa-core'),
        "class" => "",
        "category" => 'Nasa Core',
        "params" => array(
            array(
                "type" => "dropdown",
                "heading" => esc_html__("Select a product deal", 'nasa-core'),
                "param_name" => "id",
                "value" => nasa_getListProductDeals(),
                "admin_label" => true
            ),

            array(
                "type" => "dropdown",
                "heading" => esc_html__('Slide auto', 'nasa-core'),
                "param_name" => 'auto_slide',
                "value" => array(
                    esc_html__('Yes, please', 'nasa-core') => 'true',
                    esc_html__('No, thank', 'nasa-core') => 'false'
                ),
                "std" => 'false'
            ),

            array(
                "type" => "textfield",
                "heading" => esc_html__("Product grid limit", 'nasa-core'),
                "param_name" => "deal_grid_limit",
                "value" => 8,
            ),

            array(
                "type" => "dropdown",
                "heading" => esc_html__("Type show for grid", 'nasa-core'),
                "param_name" => "type_grid",
                "value" => array(
                    esc_html__('Best Selling', 'nasa-core') => 'best_selling',
                    esc_html__('Featured Products', 'nasa-core') => 'featured_product',
                    esc_html__('Top Rate', 'nasa-core') => 'top_rate',
                    esc_html__('Recent Products', 'nasa-core') => 'recent_product',
                    esc_html__('On Sale', 'nasa-core') => 'on_sale',
                    esc_html__('Recent Review', 'nasa-core') => 'recent_review',
                    esc_html__('Product Deals', 'nasa-core') => 'deals'
                ),
                "std" => 'best_selling',
                "admin_label" => true,
                "description" => esc_html__("Select type products grid to show.", 'nasa-core')
            ),

            array(
                "type" => "dropdown",
                "heading" => esc_html__('Show arrows', 'nasa-core'),
                "param_name" => 'arrows',
                "value" => array(
                    esc_html__('Yes, please', 'nasa-core') => 1,
                    esc_html__('No, thank', 'nasa-core') => 0
                ),
                "std" => 1,
                "description" => esc_html__("Show arrows.", 'nasa-core')
            ),

            array(
                "type" => "textfield",
                "heading" => esc_html__("Extra class name", 'nasa-core'),
                "param_name" => "el_class",
                "description" => esc_html__("If you wish to style particular content element differently, then use this field to add a class name and then refer to it in your css file.", 'nasa-core')
            )
        )
    ));
}
