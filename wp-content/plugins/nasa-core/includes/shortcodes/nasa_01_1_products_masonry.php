<?php
function nasa_sc_products_masonry($atts, $content = null) {
    global $woocommerce, $nasa_opt, $nasa_sc;
    
    if(!isset($nasa_sc) || !$nasa_sc) {
        $nasa_sc = 1;
    }
    $GLOBALS['nasa_sc'] = $nasa_sc + 1;
    
    if (!$woocommerce) {
        return $content;
    }
    
    $dfAttr = array(
        'cat' => '',
        'type' => 'recent_product',
        'layout' => '1',
        'loadmore' => 'no',
        'sc' => $nasa_sc,
        'el_class' => ''
    );
    extract(shortcode_atts($dfAttr, $atts));
    
    if ($type == '') {
        return $content;
    }
    
    /**
     * Cache shortcode
     */
    $key = false;
    if (isset($nasa_opt['nasa_cache_shortcodes']) && $nasa_opt['nasa_cache_shortcodes']) {
        $key = nasa_key_shortcode('nasa_products_masonry', $dfAttr, $atts);
        $content = nasa_get_cache_shortcode($key);
    }
    
    if (!$content) {
        $file = NASA_CORE_PRODUCT_LAYOUTS . 'nasa_products_masonry/masonry-' . $layout . '.php';
        
        if (is_file($file)) :
            $limit = 18;
            if ($layout == 2) :
                $limit = 16;
            endif;
            
            $loop = nasa_woocommerce_query($type, $limit, $cat, 1);
            $_total = $loop->post_count;
            if ($_total) :
                $attributeWrap = '';
                if ($loadmore === 'yes') :
                    $attributeWrap = 
                        'data-next_page="2" ' .
                        'data-layout="' . $layout . '" ' .
                        'data-product_type="' . $type . '" ' .
                        'data-limit="' . $limit . '" ' .
                        'data-max_pages="' . $loop->max_num_pages . '" ' .
                        'data-cat="' . esc_attr($cat) . '"';
                endif;
                
                ob_start();
                ?>
                <div class="nasa-wrap-products-masonry<?php echo ($el_class != '') ? ' ' . esc_attr($el_class) : ''; ?>">
                    <div class="nasa-products-masonry products woocommerce"<?php echo $attributeWrap; ?>>
                        <?php include $file; ?>
                    </div>

                    <?php if ($loadmore === 'yes' && $loop->max_num_pages > 1) :
                        echo '<div class="large-12 columns text-center desktop-margin-top-40 margin-bottom-20">';
                        echo '<a class="load-more-masonry" href="javascript:void(0);" title="' . esc_attr__('LOAD MORE ...', 'nasa-core') . '" data-nodata="' . esc_attr__('ALL PRODUCTS LOADED', 'nasa-core') . '">' .
                            esc_html__('LOAD MORE ...') .
                        '</a>';
                        echo '</div>';
                    endif; ?>
                </div>
                <?php
                $content = ob_get_clean();
            endif;
        endif;
        
        if ($content) {
            nasa_set_cache_shortcode($key, $content);
        }
    }
    
    return $content;
}

// **********************************************************************// 
// ! Register New Element: nasa products
// **********************************************************************//
function nasa_register_products_masonry(){
    vc_map(array(
        "name" => esc_html__("Products Masonry", 'nasa-core'),
        "base" => "nasa_products_masonry",
        'icon' => 'icon-wpb-nasatheme',
        'description' => esc_html__("Display products as masonry layout.", 'nasa-core'),
        "class" => "",
        "category" => 'Nasa Core',
        "params" => array(
            array(
                "type" => "dropdown",
                "heading" => esc_html__("Type", 'nasa-core'),
                "param_name" => "type",
                "value" => array(
                    esc_html__('Recent Products', 'nasa-core') => 'recent_product',
                    esc_html__('Best Selling', 'nasa-core') => 'best_selling',
                    esc_html__('Featured Products', 'nasa-core') => 'featured_product',
                    esc_html__('Top Rate', 'nasa-core') => 'top_rate',
                    esc_html__('On Sale', 'nasa-core') => 'on_sale',
                    esc_html__('Recent Review', 'nasa-core') => 'recent_review',
                    esc_html__('Product Deals') => 'deals'
                ),
                'std' => 'recent_product',
                "admin_label" => true,
                "description" => esc_html__("Select type product to show.", 'nasa-core')
            ),

            array(
                "type" => "dropdown",
                "heading" => esc_html__("Layout", 'nasa-core'),
                "param_name" => "layout",
                "value" => array(
                    esc_html__('Layout type 1 (Limit 18 items)', 'nasa-core') => '1',
                    esc_html__('Layout type 2 (Limit 16 items)', 'nasa-core') => '2'
                ),
                'std' => '1',
                "admin_label" => true
            ),

            array(
                "type" => "dropdown",
                "heading" => esc_html__("Load More", 'nasa-core'),
                "param_name" => "loadmore",
                "value" => array(
                    esc_html__('Yes', 'nasa-core') => 'yes',
                    esc_html__('No', 'nasa-core') => 'no'
                ),
                'std' => 'no',
                "admin_label" => true
            ),

            array(
                "type" => "dropdown",
                "heading" => esc_html__("Product Category", 'nasa-core'),
                "param_name" => "cat",
                "admin_label" => true,
                "value" => nasa_get_cat_product_array(),
                "description" => esc_html__("Input the category name here.", 'nasa-core')
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