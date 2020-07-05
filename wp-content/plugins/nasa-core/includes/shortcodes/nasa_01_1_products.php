<?php
function nasa_sc_products($atts, $content = null) {
    global $woocommerce, $nasa_opt;
    
    if (!$woocommerce) {
        return $content;
    }
    
    $dfAttr = array(
        'number' => '8',
        'cat' => '',
        'type' => 'recent_product',
        'style' => 'grid',
        'style_viewmore' => '1',
        'style_row' => 'simple',
        'title_shortcode' => '',
        'pos_nav' => 'top',
        'title_align' => 'left',
        'shop_url' => 0,
        'arrows' => 1,
        'dots' => 'false',
        'auto_slide' => 'false',
        'columns_number' => '4',
        'columns_number_small' => '1',
        'columns_number_tablet' => '2',
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
        $key = nasa_key_shortcode('nasa_products', $dfAttr, $atts);
        $content = nasa_get_cache_shortcode($key);
    }
    
    if (!$content) {
        $file = NASA_CORE_PRODUCT_LAYOUTS . 'nasa_products/' . $style . '.php';
        if (is_file($file)) :
            $is_deals = $type == 'deals' ? 'true' : 'false';
            $loop = nasa_woocommerce_query($type, $number, $cat);
            if ($_total = $loop->post_count) :
                ob_start();
                ?>
                <div class="products woocommerce<?php echo ($el_class != '') ? ' ' . esc_attr($el_class) : ''; ?>">
                    <?php include $file; ?>
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
function nasa_register_product(){
    vc_map(array(
        "name" => esc_html__("Products", 'nasa-core'),
        "base" => "nasa_products",
        'icon' => 'icon-wpb-nasatheme',
        'description' => esc_html__("Display products as many format.", 'nasa-core'),
        "class" => "",
        "category" => 'Nasa Core',
        "params" => array(
            array(
                "type" => "textfield",
                "heading" => esc_html__("Title", 'nasa-core'),
                "param_name" => "title_shortcode",
                "value" => '',
                "dependency" => array(
                    "element" => "style",
                    "value" => array(
                        "carousel", 'slide_slick'
                    )
                ),
                "description" => esc_html__("Only using for Style is Carousel.", 'nasa-core')
            ),

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
                "heading" => esc_html__("Style", 'nasa-core'),
                "param_name" => "style",
                "value" => array(
                    esc_html__('Grid', 'nasa-core') => 'grid',
                    esc_html__('Carousel', 'nasa-core') => 'carousel',
                    esc_html__('Simple Slide', 'nasa-core') => 'slide_slick',
                    esc_html__('Ajax Infinite', 'nasa-core') => 'infinite',
                    esc_html__('List', 'nasa-core') => 'list',
                    esc_html__('List Carousel', 'nasa-core') => 'list_carousel'
                ),
                'std' => 'grid',
                "admin_label" => true
            ),

            array(
                "type" => "dropdown",
                "heading" => esc_html__('Type view more', 'nasa-core'),
                "param_name" => 'style_viewmore',
                "value" => array(
                    esc_html__('Type 1', 'nasa-core') => '1',
                    esc_html__('Type 2', 'nasa-core') => '2',
                    esc_html__('Type 3 - No border', 'nasa-core') => '3'
                ),
                "std" => '1',
                "dependency" => array(
                    "element" => "style",
                    "value" => array(
                        'infinite'
                    )
                ),
                "description" => esc_html__("Only using for Style is Ajax Infinite.", 'nasa-core')
            ),

            array(
                "type" => "dropdown",
                "heading" => esc_html__('Number row of Slide', 'nasa-core'),
                "param_name" => 'style_row',
                "value" => array(
                    esc_html__('1 Row', 'nasa-core') => '1',
                    esc_html__('2 Rows', 'nasa-core') => '2',
                    esc_html__('3 Rows', 'nasa-core') => '3'
                ),
                "std" => '1',
                "dependency" => array(
                    "element" => "style",
                    "value" => array(
                        "carousel",
                        'list_carousel'
                    )
                ),
                "description" => esc_html__("Only using for Style is Carousel.", 'nasa-core')
            ),

            array(
                "type" => "dropdown",
                "heading" => esc_html__("Position Title | Navigation (Only use for Style is Carousel 1 row)", 'nasa-core'),
                "param_name" => "pos_nav",
                "value" => array(
                    esc_html__('Side', 'nasa-core') => 'left',
                    esc_html__('Top', 'nasa-core') => 'top'
                ),
                "std" => 'top',
                "description" => esc_html__("Only using for Style is Carousel 1 row.", 'nasa-core')
            ),

            array(
                "type" => "dropdown",
                "heading" => esc_html__("Title align (Only use for Style is Carousel)", 'nasa-core'),
                "param_name" => "title_align",
                "value" => array(
                    esc_html__('Left', 'nasa-core') => 'left',
                    esc_html__('Right', 'nasa-core') => 'right'
                ),
                "std" => 'left',
                "dependency" => array(
                    "element" => "pos_nav",
                    "value" => array(
                        "top"
                    )
                ),
                "description" => esc_html__("Only using for Style is Carousel.", 'nasa-core')
            ),

            array(
                "type" => "dropdown",
                "heading" => esc_html__('Shop url', 'nasa-core'),
                "param_name" => 'shop_url',
                "value" => array(
                    esc_html__('Yes, please', 'nasa-core') => 1,
                    esc_html__('No, thank', 'nasa-core') => 0
                ),
                "std" => 0,
                "dependency" => array(
                    "element" => "style",
                    "value" => array(
                        "carousel", 'slide_slick'
                    )
                ),
                "description" => esc_html__("Only using for Style is Carousel.", 'nasa-core')
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
                "dependency" => array(
                    "element" => "style",
                    "value" => array(
                        "carousel", 'list_carousel', 'slide_slick'
                    )
                ),
                "description" => esc_html__("Only using for Style is Carousel or Simple Slide.", 'nasa-core')
            ),

            array(
                "type" => "dropdown",
                "heading" => esc_html__('Show dots', 'nasa-core'),
                "param_name" => 'dots',
                "value" => array(
                    esc_html__('Yes, please', 'nasa-core') => 'true',
                    esc_html__('No, thank', 'nasa-core') => 'false'
                ),
                "std" => 'false',
                "dependency" => array(
                    "element" => "style",
                    "value" => array(
                        "carousel"
                    )
                ),
                "description" => esc_html__("Only using for Style is Carousel.", 'nasa-core')
            ),

            array(
                "type" => "dropdown",
                "heading" => esc_html__('Slide auto', 'nasa-core'),
                "param_name" => 'auto_slide',
                "value" => array(
                    esc_html__('Yes, please', 'nasa-core') => 'true',
                    esc_html__('No, thank', 'nasa-core') => 'false'
                ),
                "std" => 'false',
                "dependency" => array(
                    "element" => "style",
                    "value" => array(
                        "carousel", "list_carousel", "slide_slick"
                    )
                ),
                "description" => esc_html__("Only using for Style is Carousel or Slide.", 'nasa-core')
            ),

            array(
                "type" => "textfield",
                "heading" => esc_html__("Number of products to show", 'nasa-core'),
                "param_name" => "number",
                "value" => '8',
                "std" => '8',
                "admin_label" => true,
            ),
            array(
                "type" => "dropdown",
                "heading" => esc_html__("Columns number", 'nasa-core'),
                "param_name" => "columns_number",
                "value" => array(5, 4, 3, 2, 1),
                "std" => 4,
                "admin_label" => true,
                "description" => esc_html__("Select columns count.", 'nasa-core')
            ),
            array(
                "type" => "dropdown",
                "heading" => esc_html__("Columns number small", 'nasa-core'),
                "param_name" => "columns_number_small",
                "value" => array(2, 1),
                "std" => 1,
                "admin_label" => true,

                "dependency" => array(
                    "element" => "style",
                    "value" => array('grid', 'carousel', 'infinite', 'list', 'list_carousel')
                ),

                "description" => esc_html__("Select columns count small display.", 'nasa-core')
            ),
            array(
                "type" => "dropdown",
                "heading" => esc_html__("Columns number tablet", 'nasa-core'),
                "param_name" => "columns_number_tablet",
                "value" => array(3, 2, 1),
                "std" => 2,
                "admin_label" => true,

                "dependency" => array(
                    "element" => "style",
                    "value" => array('grid', 'carousel', 'infinite', 'list', 'list_carousel')
                ),

                "description" => esc_html__("Select columns count in tablet.", 'nasa-core')
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
