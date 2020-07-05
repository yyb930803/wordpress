<?php
add_action('init', 'elessi_general_heading');
if (!function_exists('elessi_general_heading')) {
    function elessi_general_heading() {
        /* --------------------------------------------------------------------- */
        /* The Options Array */
        /* --------------------------------------------------------------------- */
        // Set the Options Array
        global $of_options;
        if(empty($of_options)) {
            $of_options = array();
        }
        
        $of_options[] = array(
            "name" => esc_html__("General", 'elessi-theme'),
            "target" => 'general',
            "type" => "heading"
        );

        if(get_option('nasatheme_imported') !== 'imported') {
            $of_options[] = array(
                "name" => esc_html__("Import Demo Content", 'elessi-theme'),
                "desc" => esc_html__("Click for import. Please ensure our plugins are activated before content is imported.", 'elessi-theme'),
                "id" => "demo_data",
                'href' => '#',
                "std" => "",
                "btntext" => esc_html__("Import Demo Content", 'elessi-theme'),
                "type" => "button"
            );
        }
        else {
            $of_options[] = array(
                "name" => esc_html__("Demo data imported", 'elessi-theme'),
                "std" => '<h3 style="background: #fff; margin: 0; padding: 5px 10px;">' . esc_html__("Demo data was imported. If you want import demo data again, You should need reset data of your site.", 'elessi-theme') . "</h3>",
                "type" => "info"
            );
        }

        $of_options[] = array(
            "name" => esc_html__("Site Layout", 'elessi-theme'),
            "id" => "site_layout",
            "std" => "wide",
            "type" => "select",
            "options" => array(
                "wide" => esc_html__("Wide", 'elessi-theme'),
                "boxed" => esc_html__("Boxed", 'elessi-theme')
            ),
            'class' => 'nasa-theme-option-parent'
        );
        
        $of_options[] = array(
            "name" => esc_html__("Add more width site (px)", 'elessi-theme'),
            "desc" => esc_html__("The max-width of your site will be INPUT + 1200 (pixel).", 'elessi-theme'),
            "id" => "plus_wide_width",
            "std" => "",
            "type" => "text"
        );

        $of_options[] = array(
            "name" => esc_html__("Site Background Color - Only use for Site Layout => Wide", 'elessi-theme'),
            "id" => "site_bg_color",
            "std" => "#eee",
            "type" => "color",
            'class' => 'nasa-site_layout nasa-site_layout-boxed nasa-theme-option-child'
        );

        $of_options[] = array(
            "name" => esc_html__("Site Background Image - Only use for Site Layout => Wide", 'elessi-theme'),
            "id" => "site_bg_image",
            "std" => ELESSI_THEME_URI . "/assets/images/bkgd1.jpg",
            "type" => "media",
            'class' => 'nasa-site_layout nasa-site_layout-boxed nasa-theme-option-child',
            "mod" => "min"
        );
        
        $of_options[] = array(
            "name" => esc_html__("Site RTL", 'elessi-theme'),
            "id" => "nasa_rtl",
            "std" => 0,
            "type" => "switch"
        );
        
        $of_options[] = array(
            "name" => esc_html__("Disable Login/Register Menu in Topbar", 'elessi-theme'),
            "desc" => esc_html__("Yes, Please!", 'elessi-theme'),
            "id" => "hide_tini_menu_acc",
            "std" => 0,
            "type" => "checkbox"
        );
        
        $of_options[] = array(
            "name" => esc_html__("Login/Register by Ajax form", 'elessi-theme'),
            "id" => "login_ajax",
            "std" => 1,
            "type" => "switch"
        );
        
        $of_options[] = array(
            "name" => esc_html__("Mobile Menu Layout", 'elessi-theme'),
            "id" => "mobile_menu_layout",
            "std" => "light-new",
            "type" => "select",
            "options" => array(
                "light-new" => esc_html__("Light - Default", 'elessi-theme'),
                "light" => esc_html__("Light - 2", 'elessi-theme'),
                "dark" => esc_html__("Dark", 'elessi-theme')
            )
        );

        $of_options[] = array(
            "name" => esc_html__("Disable Transition Loading", 'elessi-theme'),
            "desc" => esc_html__("Yes, Please!", 'elessi-theme'),
            "id" => "disable_wow",
            "std" => 0,
            "type" => "checkbox"
        );
        
        $of_options[] = array(
            "name" => esc_html__("Delay Overlay (ms)", 'elessi-theme'),
            "id" => "delay_overlay",
            "std" => "100",
            "type" => "text"
        );
        
        $of_options[] = array(
            "name" => esc_html__("Effect Before Load Site", 'elessi-theme'),
            "id" => "effect_before_load",
            "std" => 1,
            "type" => "switch"
        );
        
        $of_options[] = array(
            "name" => esc_html__("Toggle Widgets Content", 'elessi-theme'),
            "id" => "toggle_widgets",
            "std" => "1",
            "type" => "switch"
        );
        
        $of_options[] = array(
        "name" => esc_html__("Site Mode Options", 'nasa-core'),
            "std" => "<h4>" . esc_html__("Site Mode Options", 'nasa-core') . "</h4>",
            "type" => "info"
        );
        
        $of_options[] = array(
            "name" => esc_html__("Site Offline", 'elessi-theme'),
            "id" => "site_offline",
            "std" => 0,
            "type" => "switch"
        );
        
        $of_options[] = array(
            "name" => esc_html__("Coming Soon Tittle", 'elessi-theme'),
            "id" => "coming_soon_title",
            "std" => "Comming Soon",
            "type" => "text"
        );
        
        $of_options[] = array(
            "name" => esc_html__("Coming Soon Info", 'elessi-theme'),
            "id" => "coming_soon_info",
            "std" => "Condimentum ipsum a adipiscing hac dolor set consectetur urna commodo elit parturient<br />a molestie ut nisl partu cl vallier ullamcorpe",
            "type" => "textarea"
        );
        
        $of_options[] = array(
            "name" => esc_html__("Coming Soon Image", 'elessi-theme'),
            "id" => "coming_soon_img",
            "std" => ELESSI_THEME_URI . "/assets/images/commingsoon.jpg",
            "type" => "media",
            "mod" => "min"
        );
        
        $of_options[] = array(
            "name" => esc_html__("Coming Soon Time", 'elessi-theme'),
            "id" => "coming_soon_time",
            "desc" => esc_html__("Please enter a time to return the site to Online (YYYY/mm/dd | YYYY-mm-dd).", 'elessi-theme'),
            "std" => "",
            "type" => "text"
        );
        
        $of_options[] = array(
            "name" => esc_html__("Include Theme Version when call Main js", 'elessi-theme'),
            "id" => "js_theme_version",
            "std" => 0,
            "type" => "switch"
        );
    }
}
