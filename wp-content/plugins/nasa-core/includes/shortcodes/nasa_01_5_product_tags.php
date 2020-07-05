<?php
function nasa_sc_tag_cloud($atts, $content = null) {
    global $nasa_opt;
    
    $dfAttr = array(
        'number' => 'All',
        'title' => '',
        'parent' => '0',
        'disp_type' => 'product_tag',
        'el_class' => ''
    );
    extract(shortcode_atts($dfAttr, $atts));

    /**
     * Cache shortcode
     */
    $key = false;
    if (isset($nasa_opt['nasa_cache_shortcodes']) && $nasa_opt['nasa_cache_shortcodes']) {
        $key = nasa_key_shortcode('nasa_tag_cloud', $dfAttr, $atts);
        $content = nasa_get_cache_shortcode($key);
    }
    
    if (!$content) {
        $disp_type = in_array($disp_type, array('product_cat', 'product_tag')) ? $disp_type : 'product_cat';

        $args = array(
            'taxonomy' => $disp_type,
            'echo' => false
        );

        if ((int) $number) {
            $args['number'] = (int) $number;
        }

        $tag_cloud = wp_tag_cloud(apply_filters('widget_tag_cloud_args', $args));
        $el_class = trim($el_class) != '' ? ' ' . esc_attr($el_class) : '';
        $tag_class = ' nasa-tag-cloud';
        $tag_class .= $disp_type == 'product_tag' ? ' nasa-tag-products-cloud' : '';

        $content = '<div class="widget_tag_cloud' . $el_class . '">';
        if ($title) {
            $content .= '<div class="row">';
            $content .= '<div class="large-12 columns">';
            $content .= '<h3 class="section-title"><span>' . esc_attr($title) . '</span></h3>';
            $content .= '<div class="nasa-hr full"></div>';
            $content .= '</div>';
            $content .= '</div>';
        }
        $content .= '<div class="tagcloud' . $tag_class . '" data-taxonomy="' . $disp_type . '">' . $tag_cloud . '</div></div>';
        
        if ($content) {
            nasa_set_cache_shortcode($key, $content);
        }
    }

    return $content;
}

// **********************************************************************// 
// ! Register New Element: nasa Product Tag Cloud
// **********************************************************************//
function nasa_register_tagcloud(){
    $tag_cloud_params = array(
        "name" => esc_html__("Product tags", 'nasa-core'),
        "base" => "nasa_tag_cloud",
        'icon' => 'icon-wpb-nasatheme',
        'description' => esc_html__("Display tags cloud (product tag, product categories).", 'nasa-core'),
        "content_element" => true,
        "category" => 'Nasa Core',
        "params" => array(
            array(
                'type' => 'textfield',
                'heading' => esc_html__('Title text', 'nasa-core'),
                'param_name' => 'title',
                'admin_label' => true,
                'value' => '',
                'description' => esc_html__('What text use as a element title.', 'nasa-core')
            ),
            array(
                "type" => "dropdown",
                "heading" => esc_html__('Taxonomy', 'nasa-core'),
                "param_name" => 'disp_type',
                "value" => array(
                    esc_html__('Product Categories', 'nasa-core') => 'product_cat',
                    esc_html__('Product Tags', 'nasa-core') => 'product_tag'
                ),
                "description" => esc_html__('Select source for tag cloud.', 'nasa-core')
            ),
            array(
                'type' => 'textfield',
                'heading' => esc_html__('Numbers', 'nasa-core'),
                'param_name' => 'number',
                'value' => 'All',
                'description' => esc_html__('Maximum numbers tag to display (Show all insert "0" or "All")', 'nasa-core')
            ),

            array(
                "type" => "textfield",
                "heading" => esc_html__("Extra class name", 'nasa-core'),
                "param_name" => "el_class",
                "description" => esc_html__("If you wish to style particular content element differently, then use this field to add a class name and then refer to it in your css file.", 'nasa-core')
            )
        )
    );
    vc_map($tag_cloud_params);
}