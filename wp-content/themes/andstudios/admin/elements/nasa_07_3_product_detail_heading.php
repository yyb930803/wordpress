<?php
add_action('init', 'elessi_product_detail_heading');
if (!function_exists('elessi_product_detail_heading')) {
    function elessi_product_detail_heading() {
        /* ----------------------------------------------------------------------------------- */
        /* The Options Array */
        /* ----------------------------------------------------------------------------------- */
        // Set the Options Array
        global $of_options;
        if(empty($of_options)) {
            $of_options = array();
        }
        
        $of_options[] = array(
            "name" => esc_html__("Single Product Page", 'elessi-theme'),
            "target" => 'product-detail',
            "type" => "heading",
        );
        
        $of_options[] = array(
            "name" => esc_html__("Single Product Layout", 'elessi-theme'),
            "id" => "product_detail_layout",
            "std" => "new",
            "type" => "select",
            "options" => array(
                "new" => esc_html__("New layout (sidebar - Off-Canvas)", 'elessi-theme'),
                "classic" => esc_html__("Classic layout (Sidebar - columns)", 'elessi-theme')
            ),
            
            'class' => 'nasa-theme-option-parent'
        );
        
        $of_options[] = array(
            "name" => esc_html__("Images Columns", 'elessi-theme'),
            "id" => "product_image_layout",
            "std" => "double",
            "type" => "select",
            "options" => array(
                "double" => esc_html__("Double images", 'elessi-theme'),
                "single" => esc_html__("Single images", 'elessi-theme')
            ),
            
            'class' => 'nasa-theme-option-child nasa-product_detail_layout nasa-product_detail_layout-new'
        );
        
        $of_options[] = array(
            "name" => esc_html__("Images Style", 'elessi-theme'),
            "id" => "product_image_style",
            "std" => "slide",
            "type" => "select",
            "options" => array(
                "slide" => esc_html__("Slide images", 'elessi-theme'),
                "scroll" => esc_html__("Scroll images", 'elessi-theme')
            ),
            
            'class' => 'nasa-theme-option-child nasa-product_detail_layout nasa-product_detail_layout-new'
        );
        
        $of_options[] = array(
            "name" => esc_html__("Thumbnail Layout", 'elessi-theme'),
            "id" => "product_thumbs_style",
            "std" => "ver",
            "type" => "select",
            "options" => array(
                "ver" => esc_html__("Vertical", 'elessi-theme'),
                "hoz" => esc_html__("Horizontal", 'elessi-theme')
            ),
            
            'class' => 'nasa-theme-option-child nasa-product_detail_layout nasa-product_detail_layout-classic'
        );
        
        $of_options[] = array(
            "name" => esc_html__("Hover Zoom Image", 'elessi-theme'),
            "id" => "product-zoom",
            "std" => 1,
            "type" => "switch"
        );
        
        $of_options[] = array(
            "name" => esc_html__("Lightbox Image When click", 'elessi-theme'),
            "id" => "product-image-lightbox",
            "std" => 1,
            "type" => "switch"
        );
        
        $of_options[] = array(
            "name" => esc_html__("Focus Main Image", 'elessi-theme'),
            "id" => "enable_focus_main_image",
            "desc" => esc_html__("Focus main image after active variation product", 'elessi-theme'),
            "std" => "0",
            "type" => "switch"
        );

        $of_options[] = array(
            "name" => esc_html__("Product Sidebar", 'elessi-theme'),
            "id" => "product_sidebar",
            "std" => "left",
            "type" => "select",
            "options" => array(
                "left" => esc_html__("Left Sidebar", 'elessi-theme'),
                "right" => esc_html__("Right Sidebar", 'elessi-theme'),
                "no" => esc_html__("No sidebar", 'elessi-theme')
            )
        );
        
        $of_options[] = array(
            "name" => esc_html__("Deal Time in Single or Quickview", 'elessi-theme'),
            "id" => "single-product-deal",
            "std" => 1,
            "type" => "switch"
        );
        
        $of_options[] = array(
            "name" => esc_html__("Buy Now", 'elessi-theme'),
            "id" => "enable_buy_now",
            "std" => "1",
            "type" => "switch"
        );
        
        $of_options[] = array(
            "name" => esc_html__("Buy Now Background Color", 'elessi-theme'),
            "id" => "buy_now_bg_color",
            "std" => "",
            "type" => "color"
        );
        
        $of_options[] = array(
            "name" => esc_html__("Buy Now Background Color Hover", 'elessi-theme'),
            "id" => "buy_now_bg_color_hover",
            "std" => "",
            "type" => "color"
        );
        
        $of_options[] = array(
            "name" => esc_html__("Buy Now Shadow Color", 'elessi-theme'),
            "id" => "buy_now_color_shadow",
            "std" => "",
            "type" => "color"
        );
        
        $of_options[] = array(
            "name" => esc_html__("Sticky Add To Cart", 'elessi-theme'),
            "id" => "enable_fixed_add_to_cart",
            "std" => "1",
            "type" => "switch"
        );
        
        $options = array(
            "no" => esc_html__("Not Show", 'elessi-theme'),
            "ext" => esc_html__("Extends Desktop", 'elessi-theme')
        );
        
        if (class_exists('Nasa_Mobile_Detect')) {
            $options['btn'] = esc_html__("Only Show Buttons", 'elessi-theme');
        }
        
        $of_options[] = array(
            "name" => esc_html__("Sticky Add To Cart In Mobile", 'elessi-theme'),
            "id" => "mobile_fixed_add_to_cart",
            "std" => "no",
            "type" => "select",
            "options" => $options
        );
        
        $of_options[] = array(
            "name" => esc_html__("Stock Progress Bar", 'elessi-theme'),
            "id" => "enable_progess_stock",
            "std" => "1",
            "type" => "switch"
        );
        
        $of_options[] = array(
            "name" => esc_html__("Technical Specifications", 'elessi-theme'),
            "id" => "enable_specifications",
            "std" => "1",
            "type" => "switch"
        );

        $of_options[] = array(
            "name" => esc_html__("Show the Specifications in the Desciption tab", 'elessi-theme'),
            "id" => "merge_specifi_to_desc",
            "std" => "1",
            "type" => "switch"
        );
        
        $of_options[] = array(
            "name" => esc_html__("Tabs Layout", 'elessi-theme'),
            "id" => "tab_style_info",
            "std" => "2d-no-border",
            "type" => "select",
            "options" => array(
                "2d-no-border" => esc_html__("Classic 2D - No border", 'elessi-theme'),
                "2d-radius" => esc_html__("Classic 2D - Radius", 'elessi-theme'),
                "2d" => esc_html__("Classic 2D", 'elessi-theme'),
                "3d" => esc_html__("Classic 3D", 'elessi-theme'),
                "slide" => esc_html__("Slide", 'elessi-theme'),
                "accordion" => esc_html__("Accordion", 'elessi-theme')
            ),
            'class' => 'nasa-theme-option-parent'
        );
        
        $of_options[] = array(
            "name" => esc_html__("Tabs Align", 'elessi-theme'),
            "id" => "tab_align_info",
            "std" => "center",
            "type" => "select",
            "options" => array(
                "center" => esc_html__("Center", 'elessi-theme'),
                "left" => esc_html__("Left", 'elessi-theme'),
                "right" => esc_html__("Right", 'elessi-theme')
            ),
            'class' => 'nasa-tab_style_info nasa-tab_style_info-2d-no-border nasa-tab_style_info-2d-radius nasa-tab_style_info-2d nasa-tab_style_info-3d nasa-tab_style_info-slide nasa-theme-option-child'
        );
        
        $of_options[] = array(
            "name" => esc_html__("Number for relate products", 'elessi-theme'),
            "id" => "release_product_number",
            "std" => "12",
            "type" => "text"
        );
        
        $of_options[] = array(
            "name" => esc_html__("Columns Relate | Upsell Products", 'elessi-theme'),
            "id" => "relate_columns_desk",
            "std" => "5-cols",
            "type" => "select",
            "options" => array(
                "3-cols" => esc_html__("3 columns", 'elessi-theme'),
                "4-cols" => esc_html__("4 columns", 'elessi-theme'),
                "5-cols" => esc_html__("5 columns", 'elessi-theme')
            )
        );
        
        $of_options[] = array(
            "name" => esc_html__("Columns Relate | Upsell Products for Mobile", 'elessi-theme'),
            "id" => "relate_columns_small",
            "std" => "1-col",
            "type" => "select",
            "options" => array(
                "1-cols" => esc_html__("1 column", 'elessi-theme'),
                "2-cols" => esc_html__("2 columns", 'elessi-theme')
            )
        );
        
        $of_options[] = array(
            "name" => esc_html__("Columns Relate | Upsell Products for Tablet", 'elessi-theme'),
            "id" => "relate_columns_tablet",
            "std" => "3-cols",
            "type" => "select",
            "options" => array(
                "1-col" => esc_html__("1 column", 'elessi-theme'),
                "2-cols" => esc_html__("2 columns", 'elessi-theme'),
                "3-cols" => esc_html__("3 columns", 'elessi-theme')
            )
        );
        
        // Enable AJAX add to cart buttons on Detail OR Quickview
        $of_options[] = array(
            "name" => esc_html__("AJAX add to cart button on Single And Quickview", 'elessi-theme'),
            "id" => "enable_ajax_addtocart",
            "std" => "1",
            "type" => "switch"
        );
        
        $of_options[] = array(
            "name" => esc_html__('Mobile Layout', 'elessi-theme'),
            "desc" => esc_html__('Note: Mobile layout for single product pages will hide all widgets and sidebar to increase performance.', 'elessi-theme'),
            "id" => "single_product_mobile",
            "std" => 0,
            "type" => "switch"
        );
    }
}
