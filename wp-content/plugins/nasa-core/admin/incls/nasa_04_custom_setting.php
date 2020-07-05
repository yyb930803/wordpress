<?php
add_action('init', 'nasa_custom_option_themes', 11);
function nasa_custom_option_themes() {
    global $of_options;
    if (empty($of_options)) {
        $of_options = array();
    }

    $of_options[] = array(
        "name" => esc_html__("Nasa Core Options", 'nasa-core'),
        "target" => 'nasa-option',
        "type" => "heading"
    );

    $of_options[] = array(
        "name" => esc_html__("Nasa Global Options", 'nasa-core'),
        "std" => "<h4>" . esc_html__("Nasa Global Options", 'nasa-core') . "</h4>",
        "type" => "info"
    );
    
    $of_options[] = array(
        "name" => esc_html__('Enable Mobile Layout', 'nasa-core'),
        "id" => "enable_nasa_mobile",
        "std" => 0,
        "type" => "switch"
    );
    
    $of_options[] = array(
        "name" => esc_html__('CDN Images Site', 'nasa-core'),
        "id" => "enable_nasa_cdn_images",
        "std" => 0,
        "type" => "switch"
    );
    
    $of_options[] = array(
        "name" => esc_html__('CDN CNAME.', 'nasa-core'),
        "desc" => esc_html__('Input CNAME. It will be replaced for home URL of images your site. (Ex: https://elessi-cdn.nasatheme.com)', 'nasa-core'),
        "id" => "nasa_cname_images",
        "std" => "",
        "type" => "text"
    );
    
    $of_options[] = array(
        "name" => esc_html__('Cache Files', 'nasa-core'),
        "id" => "enable_nasa_cache",
        "std" => 1,
        "type" => "switch"
    );
    
    $of_options[] = array(
        "name" => esc_html__('Cache Shortcodes (Apply with Cache Files)', 'nasa-core'),
        "id" => "nasa_cache_shortcodes",
        "std" => 0,
        "type" => "switch"
    );
    
    $of_options[] = array(
        "name" => esc_html__('Cache Variable Loop Products (Apply with Cache Files)', 'nasa-core'),
        "id" => "nasa_cache_variables",
        "std" => 1,
        "type" => "switch"
    );
    
    $of_options[] = array(
        "name" => esc_html__('Expire Time (Seconds - Expire time live file.)', 'nasa-core'),
        "desc" => '<a href="javascript:void(0);" class="button-primary nasa-clear-variations-cache" data-ok="' . esc_html__('Clear Cache Success !', 'nasa-core') . '" data-miss="' . esc_html__('Cache Empty!', 'nasa-core') . '" data-fail="' . esc_html__('Error!', 'nasa-core') . '">' . esc_html__('Clear Cache', 'nasa-core') . '</a><span class="nasa-admin-loader hidden-tag"><img src="' . NASA_CORE_PLUGIN_URL . 'admin/assets/ajax-loader.gif" /></span>',
        "id" => "nasa_cache_expire",
        "std" => '3600',
        "type" => "text"
    );

    $of_options[] = array(
        "name" => esc_html__("Enable Effect Pin Space (Pin Banner)", 'nasa-core'),
        "id" => "effect_pin_product_banner",
        "std" => 0,
        "type" => "switch"
    );
    
    $of_options[] = array(
        "name" => esc_html__("Enable Size Guide Product", 'nasa-core'),
        "id" => "enable_size_guide",
        "std" => '1',
        "type" => "switch"
    );
    
    $of_options[] = array(
        "name" => esc_html__("Size Guide Product", 'nasa-core'),
        "id" => "size_guide",
        "std" => '',
        "type" => "media"
    );

    $of_options[] = array(
        "name" => esc_html__("Nasa UX variations product's variable", 'nasa-core'),
        "std" => "<h4>" . esc_html__("Nasa UX variations product's variable", 'nasa-core') . "</h4>",
        "type" => "info"
    );

    $of_options[] = array(
        "name" => esc_html__('Enable UX Variations', 'nasa-core'),
        "id" => "enable_nasa_variations_ux",
        "std" => 1,
        "type" => "switch"
    );

    $of_options[] = array(
        "name" => esc_html__('Enable UX Variations With Type Select', 'nasa-core'),
        "id" => "enable_nasa_ux_select",
        "std" => 1,
        "type" => "switch"
    );

    $of_options[] = array(
        "name" => esc_html__("Display Type with Image Attribute", 'nasa-core'),
        "id" => "nasa_attr_display_type",
        "std" => "round",
        "type" => "select",
        "options" => array(
            "round" => esc_html__("Round", 'nasa-core'),
            "square" => esc_html__("Square", 'nasa-core')
        )
    );

    // limit_show num of 1 variation
    $of_options[] = array(
        "name" => esc_html__('Limit in product grid', 'nasa-core'),
        "desc" => esc_html__('Limit show variations/1 attribute in product grid. Empty input to show all', 'nasa-core'),
        "id" => "limit_nasa_variations_ux",
        "std" => "5",
        "type" => "text"
    );

    // Loading ux variations in loop by ajax
    $of_options[] = array(
        "name" => esc_html__('UX Variations Loop by Ajax', 'nasa-core'),
        "id" => "load_variations_ux_ajax",
        "std" => 0,
        "type" => "switch"
    );

    $of_options[] = array(
        "name" => esc_html__('Gallery for Variation', 'nasa-core'),
        "id" => "gallery_images_variation",
        "std" => 1,
        "type" => "switch"
    );

    $of_options[] = array(
        "name" => esc_html__("Nasa Options Archive product page", 'nasa-core'),
        "std" => "<h4>" . esc_html__("Nasa Options Archive product page", 'nasa-core') . "</h4>",
        "type" => "info"
    );

    /*
     * Elessi-theme not use
     */
    $of_options[] = array(
        "name" => esc_html__("Enable Recommend Products", 'nasa-core'),
        "id" => "enable_recommend_product",
        "std" => "0",
        "type" => "switch"
    );

    $of_options[] = array(
        "name" => esc_html__('Title for Recommended', 'nasa-core'),
        "id" => "recommend_product_title",
        "std" => "Recommend Products",
        "type" => "text"
    );

    $of_options[] = array(
        "name" => esc_html__("Limit Number of Visible Recommended Products", 'nasa-core'),
        "id" => "recommend_product_limit",
        "std" => "9",
        "type" => "text"
    );

    $of_options[] = array(
        "name" => esc_html__("Desktop Columns", 'nasa-core'),
        "id" => "recommend_columns_desk",
        "std" => "5-cols",
        "type" => "select",
        "options" => array(
            "3-cols" => esc_html__("3 columns", 'nasa-core'),
            "4-cols" => esc_html__("4 columns", 'nasa-core'),
            "5-cols" => esc_html__("5 columns", 'nasa-core')
        )
    );

    $of_options[] = array(
        "name" => esc_html__("Mobile Columns", 'nasa-core'),
        "id" => "recommend_columns_small",
        "std" => "1-col",
        "type" => "select",
        "options" => array(
            "1-cols" => esc_html__("1 column", 'nasa-core'),
            "2-cols" => esc_html__("2 columns", 'nasa-core')
        )
    );

    $of_options[] = array(
        "name" => esc_html__("Tablet Columns", 'nasa-core'),
        "id" => "recommend_columns_tablet",
        "std" => "3-cols",
        "type" => "select",
        "options" => array(
            "1-col" => esc_html__("1 column", 'nasa-core'),
            "2-cols" => esc_html__("2 columns", 'nasa-core'),
            "3-cols" => esc_html__("3 columns", 'nasa-core')
        )
    );

    $of_options[] = array(
        "name" => esc_html__("Recommend Position", 'nasa-core'),
        "id" => "recommend_product_position",
        "std" => "bot",
        "type" => "select",
        "options" => array(
            "top" => esc_html__("Top", 'nasa-core'),
            "bot" => esc_html__("Bottom", 'nasa-core')
        )
    );

    /*
     * Share and follow
     */
    $of_options[] = array(
        "name" => esc_html__("Nasa Options Share & Follow", 'nasa-core'),
        "std" => "<h4>" . esc_html__("Nasa Options Share & Follow", 'nasa-core') . "</h4>",
        "type" => "info"
    );

    $of_options[] = array(
        "name" => esc_html__("Share Icons", 'nasa-core'),
        "desc" => esc_html__("Select icons to be shown on share icons on product page, blog page and [share] shortcode", 'nasa-core'),
        "id" => "social_icons",
        "std" => array(
            "facebook",
            "twitter",
            "email",
            "pinterest"
        ),
        "type" => "multicheck",
        "options" => array(
            "facebook" => esc_html__("Facebook", 'nasa-core'),
            "twitter" => esc_html__("Twitter", 'nasa-core'),
            "pinterest" => esc_html__("Pinterest", 'nasa-core'),
            "linkedin" => esc_html__("Linkedin", 'nasa-core'),
            "telegram" => esc_html__("Telegram", 'nasa-core'),
            "vk" => esc_html__("VK", 'nasa-core'),
            "email" => esc_html__("Email", 'nasa-core')
        )
    );

    $of_options[] = array(
        "name" => esc_html__("Facebook URL Follow", 'nasa-core'),
        "id" => "facebook_url_follow",
        "std" => "",
        "type" => "text"
    );
    
    $of_options[] = array(
        "name" => esc_html__("VK URL Follow", 'nasa-core'),
        "id" => "vk_url_follow",
        "std" => "",
        "type" => "text"
    );

    $of_options[] = array(
        "name" => esc_html__("Twitter URL Follow", 'nasa-core'),
        "id" => "twitter_url_follow",
        "std" => "",
        "type" => "text"
    );

    $of_options[] = array(
        "name" => esc_html__("Email URL", 'nasa-core'),
        "id" => "email_url_follow",
        "std" => "",
        "type" => "text"
    );

    $of_options[] = array(
        "name" => esc_html__("Pinterest URL Follow", 'nasa-core'),
        "id" => "pinterest_url_follow",
        "std" => "",
        "type" => "text"
    );

    $of_options[] = array(
        "name" => esc_html__("Instagram URL Follow", 'nasa-core'),
        "id" => "instagram_url",
        "std" => "",
        "type" => "text"
    );

    $of_options[] = array(
        "name" => esc_html__("RSS URL Follow", 'nasa-core'),
        "id" => "rss_url_follow",
        "std" => "",
        "type" => "text"
    );

    $of_options[] = array(
        "name" => esc_html__("Linkedin URL Follow", 'nasa-core'),
        "id" => "linkedin_url_follow",
        "std" => "",
        "type" => "text"
    );

    $of_options[] = array(
        "name" => esc_html__("Youtube URL Follow", 'nasa-core'),
        "id" => "youtube_url_follow",
        "std" => "",
        "type" => "text"
    );
    
    $of_options[] = array(
        "name" => esc_html__("Tumblr URL Follow", 'nasa-core'),
        "id" => "tumblr_url_follow",
        "std" => "",
        "type" => "text"
    );

    $of_options[] = array(
        "name" => esc_html__("Flickr URL Follow", 'nasa-core'),
        "id" => "flickr_url_follow",
        "std" => "",
        "type" => "text"
    );

    $of_options[] = array(
        "name" => esc_html__("Telegram URL Follow", 'nasa-core'),
        "id" => "telegram_url_follow",
        "std" => "",
        "type" => "text"
    );

    $of_options[] = array(
        "name" => esc_html__("Whatsapp URL Follow Only Show in Mobile", 'nasa-core'),
        "id" => "whatsapp_url_follow",
        "std" => "",
        "type" => "text"
    );
    
    $of_options[] = array(
        "name" => esc_html__("Weibo URL Follow", 'nasa-core'),
        "id" => "weibo_url_follow",
        "std" => "",
        "type" => "text"
    );
    
    $of_options[] = array(
        "name" => esc_html__("Amazon URL", 'nasa-core'),
        "id" => "amazon_url_follow",
        "std" => "",
        "type" => "text"
    );
    
    /**
     * Register Instagram
     */
    $of_options[] = array(
        "name" => esc_html__("Connect Instagram Accounts", 'nasa-core'),
        "std" => "<h4>" . esc_html__("Connect Instagram Accounts", 'nasa-core') . "</h4>",
        "type" => "info"
    );
    
    $of_options[] = array(
        "name" => esc_html__("Instagram Accounts", 'nasa-core'),
        "id" => "nasa_instagram",
        "std" => "",
        "type" => "instagram_acc",
        "url" => nasa_url_access_token_intagram()
    );
}
