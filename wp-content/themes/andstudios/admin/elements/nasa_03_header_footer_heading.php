<?php
add_action('init', 'elessi_header_footer_heading');
if (!function_exists('elessi_header_footer_heading')) {
    function elessi_header_footer_heading() {
        /* ----------------------------------------------------------------------------------- */
        /* The Options Array */
        /* ----------------------------------------------------------------------------------- */
        // Set the Options Array
        global $of_options;
        if(empty($of_options)) {
            $of_options = array();
        }
        
        $block_type = get_posts(array(
            'posts_per_page'    => -1,
            'post_status'       => 'publish',
            'post_type'         => 'nasa_block'
        ));
        $header_blocks = array('default' => esc_html__('Select the Static Block', 'elessi-theme'));
        if (!empty($block_type)) {
            foreach ($block_type as $key => $value) {
                $header_blocks[$value->post_name] = $value->post_title;
            }
        }
        
        $of_options[] = array(
            "name" => esc_html__("Header and Footer", 'elessi-theme'),
            "target" => 'header-footer',
            "type" => "heading"
        );

        $of_options[] = array(
            "name" => esc_html__("Header Option", 'elessi-theme'),
            "std" => "<h4>" . esc_html__("Header Option", 'elessi-theme') . "</h4>",
            "type" => "info"
        );
        
        $of_options[] = array(
            "name" => esc_html__("Header Layout", 'elessi-theme'),
            "id" => "header-type",
            "std" => "1",
            "type" => "images",
            "options" => array(
                '1' => ELESSI_ADMIN_DIR_URI . 'assets/images/header-1.jpg',
                '2' => ELESSI_ADMIN_DIR_URI . 'assets/images/header-2.jpg',
                '3' => ELESSI_ADMIN_DIR_URI . 'assets/images/header-3.jpg',
                '4' => ELESSI_ADMIN_DIR_URI . 'assets/images/header-4.jpg',
                'nasa-custom' => ELESSI_ADMIN_DIR_URI . 'assets/images/header-builder.gif'
            ),
            
            'class' => 'nasa-header-type-select nasa-theme-option-parent'
        );
        
        $headers_type = get_posts(array(
            'posts_per_page'    => -1,
            'post_status'       => 'publish',
            'post_type'         => 'header'
        ));
        $headers_option = array();
        $headers_option['default'] = esc_html__('Select the Header custom', 'elessi-theme');
        $header_selected = false;
        if (!empty($headers_type)) {
            foreach ($headers_type as $key => $value) {
                $header_selected = !$header_selected ? $value->post_name : $header_selected;
                $headers_option[$value->post_name] = $value->post_title;
            }
        }
        $of_options[] = array(
            "name" => esc_html__("Header Builder", 'elessi-theme'),
            "id" => "header-custom",
            "type" => "select",
            'override_numberic' => true,
            "options" => $headers_option,
            'std' => $header_selected ? $header_selected : '',
            'class' => 'hidden-tag nasa-header-type-child nasa-header-type-select-nasa-custom nasa-header-custom'
        );
        
        $menus = wp_get_nav_menus(array('orderby' => 'name'));
        $option_menu = array('' => esc_html__('Select menu', 'elessi-theme'));
        if (!empty($menus)) {
            foreach ($menus as $menu_option) {
                $option_menu[$menu_option->term_id] = $menu_option->name;
            }
        }
        
        $of_options[] = array(
            "name" => esc_html__("Select vertical menu", 'elessi-theme'),
            "id" => "vertical_menu_selected",
            "std" => "",
            "type" => "select",
            'override_numberic' => true,
            "options" => $option_menu,
            'class' => 'hidden-tag nasa-header-type-child nasa-header-type-select-4'
        );
        
        $of_options[] = array(
            "name" => esc_html__("Fullwidth Main Menu", 'elessi-theme'),
            "id" => "fullwidth_main_menu",
            "std" => 1,
            "type" => "switch",
            'class' => 'hidden-tag nasa-header-type-child nasa-header-type-select-2 nasa-header-type-select-3 nasa-fullwidth_main_menu'
        );
        
        $of_options[] = array(
            "name" => esc_html__("Transparent Header", 'elessi-theme'),
            "id" => "header_transparent",
            "std" => 0,
            "type" => "switch",
            'class' => 'hidden-tag nasa-header-type-child nasa-header-type-select-1 nasa-header-type-select-2 nasa-header-type-select-3 nasa-header_transparent'
        );
        
        $of_options[] = array(
            "name" => esc_html__("Block Header Transparent", 'elessi-theme'),
            "desc" => esc_html__("Please Create Static Block and Selected here to use.", 'elessi-theme'),
            "id" => "header-block",
            "type" => "select",
            "options" => $header_blocks,
            'class' => 'hidden-tag nasa-header-type-child nasa-header-type-select-1 nasa-header-type-select-2 nasa-header-type-select-3 nasa-header-block'
        );

        $of_options[] = array(
            "name" => esc_html__("Sticky", 'elessi-theme'),
            "id" => "fixed_nav",
            "std" => 1,
            "type" => "switch",
            'class' => 'hidden-tag nasa-header-type-child nasa-header-type-select-1 nasa-header-type-select-2 nasa-header-type-select-3 nasa-header-type-select-4 nasa-fixed_nav'
        );
        
        $of_options[] = array(
            "name" => esc_html__("Search Bar Effect", 'elessi-theme'),
            "id" => "search_effect",
            "std" => "right-to-left",
            "type" => "select",
            "options" => array(
                "rightToLeft" => esc_html__("Right To Left", 'elessi-theme'),
                "fadeInDown" => esc_html__("Fade In Down", 'elessi-theme'),
                "fadeInUp" => esc_html__("Fade In Up", 'elessi-theme'),
                "leftToRight" => esc_html__("Left To Right", 'elessi-theme'),
                "fadeIn" => esc_html__("Fade In", 'elessi-theme'),
                "noEffect" => esc_html__("No Effect", 'elessi-theme')
            ),
            'class' => 'hidden-tag nasa-header-type-child nasa-header-type-select-1 nasa-header-type-select-2 nasa-search_effect'
        );

        $of_options[] = array(
            "name" => esc_html__("Toggle Top Bar", 'elessi-theme'),
            "id" => "topbar_toggle",
            "std" => 0,
            "type" => "switch",
            'class' => 'hidden-tag nasa-topbar_toggle nasa-header-type-child nasa-header-type-select-1 nasa-header-type-select-2 nasa-header-type-select-3 nasa-header-type-select-4 nasa-fixed_nav'
        );
        
        $of_options[] = array(
            "name" => esc_html__("Default Top Bar Show", 'elessi-theme'),
            "id" => "topbar_default_show",
            "std" => 1,
            "type" => "switch",
            'class' => 'hidden-tag nasa-topbar_df-show'
        );

        $of_options[] = array(
            "name" => esc_html__("Languages Switcher - Requires WPML", 'elessi-theme'),
            "id" => "switch_lang",
            "std" => 0,
            "type" => "switch"
        );
        
        $of_options[] = array(
            "name" => esc_html__("Currencies Switcher - Requires Package of WPML", 'elessi-theme'),
            "id" => "switch_currency",
            "std" => 0,
            "type" => "switch"
        );
        
        //(%symbol%) %code%
        $of_options[] = array(
            "name" => esc_html__("Format Currency", 'elessi-theme'),
            "desc" => esc_html__("Default (%symbol%) %code%. You can custom for this. Ex (%name% (%symbol%) - %code%)", 'elessi-theme'),
            "id" => "switch_currency_format",
            "std" => "",
            "type" => "text"
        );
        
        $of_options[] = array(
            "name" => esc_html__("Topbar Content", 'elessi-theme'),
            "desc" => esc_html__("Please Create Static Block and Selected here to use.", 'elessi-theme'),
            "id" => "topbar_content",
            "type" => "select",
            "options" => $header_blocks,
            'class' => 'hidden-tag nasa-header-type-child nasa-header-type-select-1 nasa-header-type-select-2 nasa-header-type-select-3 nasa-header-type-select-4 nasa-topbar_content'
        );

        /**
         * Deprecated
         */
        /*
        $of_options[] = array(
            "name" => esc_html__("Top bar left content", 'elessi-theme'),
            "desc" => '<a href="javascript:void(0);" class="reset_topbar_left"><b>Default value</b></a> for left top bar.<br /><a href="javascript:void(0);" class="restore_topbar_left"><b>Restore text</b></a> for top bar left.<br />',
            "id" => "topbar_left",
            "std" => '',
            "type" => "textarea"
        ); */
        
        $of_options[] = array(
            "name" => esc_html__("Toggle Header Icons - Responsive mode", 'elessi-theme'),
            "id" => "topbar_mobile_icons_toggle",
            "std" => 0,
            "type" => "switch",
            'class' => 'hidden-tag nasa-header-type-child nasa-header-type-select-1 nasa-header-type-select-2 nasa-header-type-select-3 nasa-header-type-select-4 nasa-topbar_mobile_icons_toggle'
        );

        $of_options[] = array(
            "name" => esc_html__("Header Elements", 'elessi-theme'),
            "std" => "<h4>" . esc_html__("Header Elements", 'elessi-theme') . "</h4>",
            "type" => "info"
        );
        
        $of_options[] = array(
            "name" => esc_html__("Topbar Background", 'elessi-theme'),
            "id" => "bg_color_topbar",
            "std" => "",
            "type" => "color"
        );
        
        $of_options[] = array(
            "name" => esc_html__("Topbar Text color", 'elessi-theme'),
            "id" => "text_color_topbar",
            "std" => "",
            "type" => "color"
        );
        
        $of_options[] = array(
            "name" => esc_html__("Topbar Text color hover", 'elessi-theme'),
            "id" => "text_color_hover_topbar",
            "std" => "",
            "type" => "color"
        );
        
        $of_options[] = array(
            "name" => esc_html__("Background Color Header", 'elessi-theme'),
            "id" => "bg_color_header",
            "std" => "",
            "type" => "color",
            'class' => 'hidden-tag nasa-header-type-child nasa-header-type-select-1 nasa-header-type-select-2 nasa-header-type-select-3 nasa-header-type-select-4 nasa-bg_color_header'
        );
        
        $of_options[] = array(
            "name" => esc_html__("Header Icons", 'elessi-theme'),
            "id" => "text_color_header",
            "std" => "",
            "type" => "color",
            'class' => 'hidden-tag nasa-header-type-child nasa-header-type-select-1 nasa-header-type-select-2 nasa-header-type-select-3 nasa-header-type-select-4 nasa-text_color_header'
        );
        
        $of_options[] = array(
            "name" => esc_html__("Header Icons Hover", 'elessi-theme'),
            "id" => "text_color_hover_header",
            "std" => "",
            "type" => "color",
            'class' => 'hidden-tag nasa-header-type-child nasa-header-type-select-1 nasa-header-type-select-2 nasa-header-type-select-3 nasa-header-type-select-4 nasa-text_color_hover_header'
        );
        
        $of_options[] = array(
            "name" => esc_html__("Main Menu Background Color", 'elessi-theme'),
            "id" => "bg_color_main_menu",
            "std" => "",
            "type" => "color",
            'class' => 'hidden-tag nasa-header-type-child nasa-header-type-select-2 nasa-header-type-select-3 nasa-header-type-select-4 nasa-bg_color_main_menu'
        );
        
        $of_options[] = array(
            "name" => esc_html__("Main Menu Text Color", 'elessi-theme'),
            "id" => "text_color_main_menu",
            "std" => "",
            "type" => "color",
            'class' => 'hidden-tag nasa-header-type-child nasa-header-type-select-1 nasa-header-type-select-2 nasa-header-type-select-3 nasa-header-type-select-4 nasa-text_color_main_menu'
        );
        
        $of_options[] = array(
            "name" => esc_html__("Footer Option", 'elessi-theme'),
            "std" => "<h4>" . esc_html__("Footer Option", 'elessi-theme') . "</h4>",
            "type" => "info"
        );

        $footers_type = get_posts(array(
            'posts_per_page'    => -1,
            'post_status'       => 'publish',
            'post_type'         => 'footer'
        ));
        
        /**
         * Footer Desktop
         */
        $footers_option = $footers_mobile = array();
        $footers_option['default'] = esc_html__('Select the Footer type', 'elessi-theme');
        $footers_mobile['default'] = esc_html__('Extends from Desktop', 'elessi-theme');
        $footer_selected = false;
        if (!empty($footers_type)) {
            foreach ($footers_type as $key => $value) {
                $footer_selected = !$footer_selected ? $value->post_name : $footer_selected;
                $footers_option[$value->post_name] = $value->post_title;
                $footers_mobile[$value->post_name] = $value->post_title;
            }
        }
        
        /**
         * Footer Desktop
         */
        $of_options[] = array(
            "name" => esc_html__("Footer Layout", 'elessi-theme'),
            "id" => "footer-type",
            "type" => "select",
            'override_numberic' => true,
            "options" => $footers_option,
            'std' => $footer_selected ? $footer_selected : ''
        );
        
        /**
         * Footer Mobile
         */
        $of_options[] = array(
            "name" => esc_html__("Footer Mobile Layout", 'elessi-theme'),
            "id" => "footer-mobile",
            "type" => "select",
            'override_numberic' => true,
            "options" => $footers_mobile,
            'std' => ''
        );
    }
}
