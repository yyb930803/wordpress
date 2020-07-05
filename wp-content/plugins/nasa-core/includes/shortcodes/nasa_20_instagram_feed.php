<?php
/**
 * INSTAGRAM
 */
function nasa_sc_instagram_feed($atts, $content = null) {
    extract(shortcode_atts(array(
        'access_token' => '',
        'limit_items' => '6',
        'img_size' => 'standard_resolution',
        'disp_type' => 'default',
        'photos' => '6',
        'photos_tablet' => '3',
        'photos_mobile' => '3',
        'username_show' => '',
        'instagram_link' => '',
        'el_class' => ''
    ), $atts));
    
    if (!is_file(NASA_CORE_FRONTEND_LAYOUTS . 'instagram/instagram_' . $disp_type . '.php')) {
        return null;
    }

    if (class_exists('Nasa_Instagram_Feed')) {

        if (!$access_token) {
            global $nasa_opt;
            
            if (isset($nasa_opt['nasa_instagram']) && $nasa_opt['nasa_instagram']) {
                $access_token = $nasa_opt['nasa_instagram'];
            }
        }
        
        if (!$access_token) {
            return null;
        }
        
        if ((int) $limit_items <= 0) {
            $limit_items = 6;
        }
        
        $instagramObj = new Nasa_Instagram_Feed(
            array(
                'token' => trim($access_token),
                'count' => (int) $limit_items
            )
        );
        
        $jsonData = $instagramObj->get_instagram();
        
        if (!$jsonData) {
            return null;
        }
        
        if (!isset($img_size) || !in_array($img_size, array('standard_resolution', 'low_resolution', 'thumbnail'))) {
            $img_size = 'standard_resolution';
        }
        
        ob_start();
        include NASA_CORE_FRONTEND_LAYOUTS . 'instagram/instagram_' . $disp_type . '.php';
        $content = ob_get_clean();
    }
    
    return $content;
}

function nasa_register_instagram_feed(){
    // **********************************************************************// 
    // ! Register New Element: Instagram Feed
    // **********************************************************************//
    $instagram_params = array(
        "name" => esc_html__("Instagram Feed", 'nasa-core'),
        'base' => 'nasa_instagram_feed',
        'icon' => 'icon-wpb-nasatheme',
        'description' => esc_html__("Create info Instagram.", 'nasa-core'),
        'category' => 'Nasa Core',
        'params' => array(
            array(
                "type" => "textfield",
                "heading" => esc_html__("Access Token (Default in NasaTheme Options > Nasa Core Options)", 'nasa-core') . '<a href="' . nasa_url_access_token_intagram() . '" class="button-primary" title="' . esc_attr__('Get Access Token', 'nasa-core') . '" target="_blank" style="margin-left: 10px;">' . esc_html__('Get Access Token', 'nasa-core') . '</a>',
                "param_name" => "access_token",
                "value" => "",
                "admin_label" => false,
            ),
            array(
                "type" => "textfield",
                "heading" => esc_html__("User name for display show", 'nasa-core'),
                "param_name" => "username_show",
                "value" => "",
                "admin_label" => true,
            ),
            array(
                "type" => "textfield",
                "heading" => esc_html__("Link Follow", 'nasa-core'),
                "param_name" => "instagram_link",
                "value" => "",
                "admin_label" => true,
            ),
            array(
                "type" => "dropdown",
                "heading" => esc_html__('Image Size', 'nasa-core'),
                "param_name" => 'img_size',
                "value" => array(
                    esc_html__('Large', 'nasa-core') => 'standard_resolution',
                    esc_html__('Medium', 'nasa-core') => 'low_resolution',
                    esc_html__('Thumb', 'nasa-core') => 'thumbnail'
                ),
                "std" => 'standard_resolution',
                "admin_label" => true
            ),

            array(
                "type" => "dropdown",
                "heading" => esc_html__('Display type', 'nasa-core'),
                "param_name" => 'disp_type',
                "value" => array(
                    esc_html__('Default', 'nasa-core') => 'defalut',
                    esc_html__('Carousel', 'nasa-core') => 'slide',
                    esc_html__('Zic Zac', 'nasa-core') => 'zz'
                ),
                "std" => 'defalut',
                "admin_label" => true
            ),

            array(
                "type" => "textfield",
                "heading" => esc_html__("Photos Limit", 'nasa-core'),
                "param_name" => "limit_items",
                'std' => '6',
                "admin_label" => true
            ),

            array(
                "type" => "dropdown",
                "heading" => esc_html__("Show on DeskTop", 'nasa-core'),
                "param_name" => "photos",
                "value" => array(
                    esc_html__('4', 'nasa-core') => '4',
                    esc_html__('5', 'nasa-core') => '5',
                    esc_html__('6', 'nasa-core') => '6',
                    esc_html__('7', 'nasa-core') => '7',
                    esc_html__('8', 'nasa-core') => '8',
                    esc_html__('9', 'nasa-core') => '9',
                    esc_html__('10', 'nasa-core') => '10'
                ),
                "admin_label" => true,
                'std' => '6'
            ),

            array(
                "type" => "dropdown",
                "heading" => esc_html__("Show on Tablet", 'nasa-core'),
                "param_name" => "photos_tablet",
                "value" => array(
                    esc_html__('1', 'nasa-core') => '1',
                    esc_html__('2', 'nasa-core') => '2',
                    esc_html__('3', 'nasa-core') => '3',
                    esc_html__('4', 'nasa-core') => '4',
                    esc_html__('5', 'nasa-core') => '5',
                    esc_html__('6', 'nasa-core') => '6'
                ),
                "admin_label" => true,
                'std' => '3'
            ),

            array(
                "type" => "dropdown",
                "heading" => esc_html__("Show on Mobile", 'nasa-core'),
                "param_name" => "photos_mobile",
                "value" => array(
                    esc_html__('1', 'nasa-core') => '1',
                    esc_html__('2', 'nasa-core') => '2',
                    esc_html__('3', 'nasa-core') => '3',
                    esc_html__('4', 'nasa-core') => '4',
                    esc_html__('5', 'nasa-core') => '5',
                    esc_html__('6', 'nasa-core') => '6'
                ),
                "admin_label" => true,
                'std' => '3'
            ),

            array(
                "type" => "textfield",
                "heading" => esc_html__("Extra Class", 'nasa-core'),
                "param_name" => "el_class"
            )
        )
    );

    vc_map($instagram_params);
}
