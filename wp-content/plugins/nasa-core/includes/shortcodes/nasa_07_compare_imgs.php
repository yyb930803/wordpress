<?php
function nasa_sc_compare_imgs($atts, $content = null) {
    global $nasa_opt;
    $dfAttr = array(
        'title' => '',
        'link' => '',
        'desc_text' => '',
        'align_text' => 'center',
        'before_image' => '',
        'after_image' => '',
        'el_class' => ''
    );
    extract(shortcode_atts($dfAttr, $atts));
    
    if (!$before_image || !$after_image) {
        return $content;
    }
    
    /**
     * Cache shortcode
     */
    $key = false;
    if (isset($nasa_opt['nasa_cache_shortcodes']) && $nasa_opt['nasa_cache_shortcodes']) {
        $key = nasa_key_shortcode('nasa_compare_imgs', $dfAttr, $atts);
        $content = nasa_get_cache_shortcode($key);
    }
    
    if (!$content) {
        $before_img = wp_get_attachment_image($before_image, 'full');
        $after_img = wp_get_attachment_image($after_image, 'full');
        if (!$before_img || !$after_img) {
            return $content;
        }
        
        $classW = 'nasa-compare-images-wrap';
        $classW .= $el_class ? ' ' . $el_class : '';
        
        $content = '<div class="' . esc_attr($classW) . '">';

        $content .= '<div class="nasa-compare-images">';
        $content .=    '<div class="hidden-tag">' . $before_img . '</div>';
        $content .=    '<div class="hidden-tag">' . $after_img . '</div>';
        $content .= '</div>';
        
        if ($title) {
            $align_text = $align_text ? ' text-' . $align_text : 'text-center';
            $class_title = 'nasa-compare-images-title margin-top-15' . $align_text;
            $content .= $link ? '<a href="' . esc_url($link) . '" title="' . esc_attr($title) . '">' : '';
            $content .= '<h5 class="' . esc_attr($class_title) . '">' . $title . '</h5>';
            $content .= $link ? '</a>' : '';
            
            if ($desc_text) {
                $content .= '<p class="nasa-compare-images-desc' . esc_attr($align_text) . '">' . $desc_text . '</p>';
            }
        }
        
        $content .= '</div>';
        
        if ($content) {
            nasa_set_cache_shortcode($key, $content);
        }
    }
    
    return $content;
}

// **********************************************************************// 
// ! Register New Element: nasa compare imgs
// **********************************************************************//
function nasa_register_compare_imgs(){
    vc_map(array(
        "name" => esc_html__("Compare IMGS", 'nasa-core'),
        "base" => "nasa_compare_imgs",
        'icon' => 'icon-wpb-nasatheme',
        'description' => esc_html__("Compare images.", 'nasa-core'),
        "class" => "",
        "category" => 'Nasa Core',
        "params" => array(
            array(
                "type" => "textfield",
                "heading" => esc_html__("Title", 'nasa-core'),
                "param_name" => "title",
                "value" => '',
                "admin_label" => true,
            ),
            
            array(
                "type" => "textfield",
                "heading" => esc_html__("Link", 'nasa-core'),
                "param_name" => "link",
                'admin_label' => true,
                "value" => '',
            ),
            
            array(
                'type' => 'textfield',
                'heading' => esc_html__('Description', 'nasa-core'),
                'param_name' => 'desc_text',
                'value' => '',
            ),
            
            array(
                "type" => "dropdown",
                "heading" => esc_html__("Alignment", 'nasa-core'),
                "param_name" => "align_text",
                "value" => array(
                    esc_html__('Center', 'nasa-core') => 'center',
                    esc_html__('Left', 'nasa-core') => 'left',
                    esc_html__('Right', 'nasa-core') => 'right'
                ),
                "std" => 'center'
            ),
            
            array(
                'type' => 'attach_image',
                'heading' => esc_html__('Before Images', 'nasa-core'),
                'param_name' => 'before_image',
                'value' => '',
                "admin_label" => true,
                'description' => esc_html__('Select before image from media library.', 'nasa-core')
            ),
            
            array(
                'type' => 'attach_image',
                'heading' => esc_html__('After Images', 'nasa-core'),
                'param_name' => 'after_image',
                'value' => '',
                "admin_label" => true,
                'description' => esc_html__('Select after image from media library.', 'nasa-core')
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
