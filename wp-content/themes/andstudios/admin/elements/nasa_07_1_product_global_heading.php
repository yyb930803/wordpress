<?php
add_action('init', 'elessi_product_global_heading');
if (!function_exists('elessi_product_global_heading')) {
    function elessi_product_global_heading() {
        /* --------------------------------------------------------------------- */
        /* The Options Array */
        /* --------------------------------------------------------------------- */
        // Set the Options Array
        global $of_options;
        if(empty($of_options)) {
            $of_options = array();
        }
        
        $of_options[] = array(
            "name" => esc_html__("Product Global Options", 'elessi-theme'),
            "target" => 'product-global',
            "type" => "heading",
        );
        
        /**
         * Release in next version
         * 
        $of_options[] = array(
            "name" => esc_html__("Loop Product Layout", 'elessi-theme'),
            "id" => "loop_layout",
            "std" => "",
            "type" => "select",
            "options" => array(
                "" => esc_html__("Default", 'elessi-theme'),
                "simple" => esc_html__("Simple", 'elessi-theme')
            )
        );
        */
        
        $of_options[] = array(
            "name" => esc_html__("Hover Product Effect", 'elessi-theme'),
            "id" => "animated_products",
            "std" => "hover-fade",
            "type" => "select",
            "options" => array(
                "hover-fade" => esc_html__("Fade", 'elessi-theme'),
                "hover-flip" => esc_html__("Flip Horizontal", 'elessi-theme'),
                "hover-bottom-to-top" => esc_html__("Bottom to top", 'elessi-theme'),
                "" => esc_html__("No effect", 'elessi-theme')
            )
        );
        
        $of_options[] = array(
            "name" => esc_html__("Back Image in Mobile Layout", 'elessi-theme'),
            "id" => "mobile_back_image",
            "std" => "0",
            "type" => "switch"
        );

        $of_options[] = array(
            "name" => esc_html__("Catalog Mode - Disable Add To Cart Feature", 'elessi-theme'),
            "id" => "disable-cart",
            "std" => "0",
            "type" => "switch"
        );
        
        $of_options[] = array(
            "name" => esc_html__("Popup Your Order After Add to Cart", 'elessi-theme'),
            "id" => "after-add-to-cart",
            "std" => "0",
            "type" => "switch"
        );
        
        $of_options[] = array(
            "name" => esc_html__("Icon Mini Cart in Header", 'elessi-theme'),
            "id" => "mini-cart-icon",
            "std" => "1",
            "type" => "images",
            "options" => array(
                // icon-nasa-cart-3 - default
                '1' => ELESSI_ADMIN_DIR_URI . 'assets/images/mini-cart-1.jpg',
                // icon-nasa-cart-2
                '2' => ELESSI_ADMIN_DIR_URI . 'assets/images/mini-cart-2.jpg',
                // icon-nasa-cart-4
                '3' => ELESSI_ADMIN_DIR_URI . 'assets/images/mini-cart-3.jpg',
                // pe-7s-cart
                '4' => ELESSI_ADMIN_DIR_URI . 'assets/images/mini-cart-4.jpg',
                // fa fa-shopping-cart
                '5' => ELESSI_ADMIN_DIR_URI . 'assets/images/mini-cart-5.jpg',
                // fa fa-shopping-bag
                '6' => ELESSI_ADMIN_DIR_URI . 'assets/images/mini-cart-6.jpg',
                // fa fa-shopping-basket
                '7' => ELESSI_ADMIN_DIR_URI . 'assets/images/mini-cart-7.jpg'
            )
        );
        
        $of_options[] = array(
            "name" => esc_html__("Icon Add To Cart in Grid", 'elessi-theme'),
            "id" => "cart-icon-grid",
            "std" => "1",
            "type" => "images",
            "options" => array(
                // fa fa-plus - default
                '1' => ELESSI_ADMIN_DIR_URI . 'assets/images/cart-plus.jpg',
                // icon-nasa-cart-3
                '2' => ELESSI_ADMIN_DIR_URI . 'assets/images/mini-cart-1.jpg',
                // icon-nasa-cart-2
                '3' => ELESSI_ADMIN_DIR_URI . 'assets/images/mini-cart-2.jpg',
                // icon-nasa-cart-4
                '4' => ELESSI_ADMIN_DIR_URI . 'assets/images/mini-cart-3.jpg',
                // pe-7s-cart
                '5' => ELESSI_ADMIN_DIR_URI . 'assets/images/mini-cart-4.jpg',
                // fa fa-shopping-cart
                '6' => ELESSI_ADMIN_DIR_URI . 'assets/images/mini-cart-5.jpg',
                // fa fa-shopping-bag
                '7' => ELESSI_ADMIN_DIR_URI . 'assets/images/mini-cart-6.jpg',
                // fa fa-shopping-basket
                '8' => ELESSI_ADMIN_DIR_URI . 'assets/images/mini-cart-7.jpg'
            )
        );
        
        $of_options[] = array(
            "name" => esc_html__("Disable Quick view", 'elessi-theme'),
            "id" => "disable-quickview",
            "desc" => esc_html__("Yes, Please!", 'elessi-theme'),
            "std" => "0",
            "type" => "checkbox"
        );
        
        $of_options[] = array(
            "name" => esc_html__("Quickview Layout", 'elessi-theme'),
            "id" => "style_quickview",
            "std" => "sidebar",
            "type" => "select",
            "options" => array(
                'popup' => esc_html__('Popup Classical', 'elessi-theme'),
                'sidebar' => esc_html__('Off-Canvas', 'elessi-theme')
            ),
            
            'class' => 'nasa-theme-option-parent'
        );
        
        $of_options[] = array(
            "name" => esc_html__("Number Show Quickview Thumbnail", 'elessi-theme'),
            "id" => "quick_view_item_thumb",
            "std" => "1-item",
            "type" => "select",
            "options" => array(
                '1-item' => esc_html__('Single Thumbnail', 'elessi-theme'),
                '2-items' => esc_html__('Double Thumbnails', 'elessi-theme')
            ),
            
            'class' => 'nasa-style_quickview nasa-style_quickview-sidebar nasa-theme-option-child'
        );
        
        $of_options[] = array(
            "name" => esc_html__("Cart Sidebar Layout", 'elessi-theme'),
            "id" => "style-cart",
            "std" => "style-1",
            "type" => "select",
            "options" => array(
                'style-1' => esc_html__('Light', 'elessi-theme'),
                'style-2' => esc_html__('Dark', 'elessi-theme')
            )
        );
        
        $of_options[] = array(
            "name" => esc_html__("Wishlist Sidebar Layout", 'elessi-theme'),
            "id" => "style-wishlist",
            "std" => "style-1",
            "type" => "select",
            "options" => array(
                'style-1' => esc_html__('Light', 'elessi-theme'),
                'style-2' => esc_html__('Dark', 'elessi-theme')
            )
        );
        
        if(defined('YITH_WCPB')) {
            // Enable Gift in grid
            $of_options[] = array(
                "name" => esc_html__("Enable Promotion Gifts featured icon", 'elessi-theme'),
                "id" => "enable_gift_featured",
                "std" => 1,
                "type" => "switch"
            );

            // Enable effect Gift featured
            $of_options[] = array(
                "name" => esc_html__("Enable Promotion Gifts effect featured icon", 'elessi-theme'),
                "id" => "enable_gift_effect",
                "std" => 0,
                "type" => "switch"
            );
        }

        // Options live search products
        $of_options[] = array(
            "name" => esc_html__("Live Search Ajax Products", 'elessi-theme'),
            "id" => "enable_live_search",
            "std" => 1,
            "type" => "switch"
        );
        
        // limit_results_search
        $of_options[] = array(
            "name" => esc_html__("Results Ajax Search (Limit Products)", 'elessi-theme'),
            "id" => "limit_results_search",
            "std" => "5",
            "type" => "text"
        );
        
        $of_options[] = array(
            "name" => esc_html__("Suggested Keywords", 'elessi-theme'),
            "desc" => 'Please input the Suggested keywords (ex: Sweater, Jacket, T-shirt ...).',
            "id" => "hotkeys_search",
            "std" => '',
            "type" => "textarea"
        );
        // End Options live search products
        
        $of_options[] = array(
            "name" => esc_html__("Display top icon filter categories", 'elessi-theme'),
            "id" => "show_icon_cat_top",
            "std" => "show-in-shop",
            "type" => "select",
            "options" => array(
                'show-in-shop' => esc_html__('Only show in shop', 'elessi-theme'),
                'show-all-site' => esc_html__('Always show all pages', 'elessi-theme'),
                'not-show' => esc_html__('Disabled', 'elessi-theme'),
            )
        );
        
        $of_options[] = array(
            "name" => esc_html__("Disable top level of categories follow current category archive (Use for Multi stores)", 'elessi-theme'),
            "desc" => esc_html__("Yes, Please!", 'elessi-theme'),
            "id" => "disable_top_level_cat",
            "std" => 0,
            "type" => "checkbox"
        );
        
        $of_options[] = array(
            "name" => esc_html__("Show Uncategorized", 'elessi-theme'),
            "id" => "show_uncategorized",
            "std" => 0,
            "type" => "switch"
        );
        
        $of_options[] = array(
            "name" => esc_html__("Disable Viewed products", 'elessi-theme'),
            "id" => "disable-viewed",
            "desc" => esc_html__("Yes, Please!", 'elessi-theme'),
            "std" => 0,
            "type" => "checkbox"
        );
        
        // limit_product_viewed
        $of_options[] = array(
            "name" => esc_html__("Viewed Products Limit", 'elessi-theme'),
            "id" => "limit_product_viewed",
            "std" => "12",
            "type" => "text"
        );
        
        $of_options[] = array(
            "name" => esc_html__("Icon Viewed Style", 'elessi-theme'),
            "id" => "style-viewed-icon",
            "std" => "style-1",
            "type" => "select",
            "options" => array(
                'style-1' => esc_html__('Light', 'elessi-theme'),
                'style-2' => esc_html__('Dark', 'elessi-theme')
            )
        );
        
        $of_options[] = array(
            "name" => esc_html__("Viewed Sidebar Layout", 'elessi-theme'),
            "id" => "style-viewed",
            "std" => "style-1",
            "type" => "select",
            "options" => array(
                'style-1' => esc_html__('Light', 'elessi-theme'),
                'style-2' => esc_html__('Dark', 'elessi-theme')
            )
        );
    }
}
