<?php
function nasa_sc_banners($atts, $content = null) {
    global $nasa_opt;
    
    $dfAtts = array(
        'align' => 'left',
        'valign' => 'top',
        'move_x' => '',
        'link' => '',
        'hover' => '',
        'content' => '',
        'font_style' => '',
        'banner_style' => '',
        'img' => '',
        'img_src' => '',
        'height' => '',
        'width' => '',
        'text_color' => 'light',
        'banner_responsive' => 'yes',
        'text-align' => '',
        'content-width' => '',
        'effect_text' => '',
        'data_delay' => '0ms',
        'seam_icon' => '',
        'border_inner' => 'no',
        'border_outner' => 'no',
        'el_class' => ''
    );
    $a = shortcode_atts($dfAtts, $atts);
    
    $class_woo = (!isset($nasa_opt['disable_wow']) || !$nasa_opt['disable_wow']) ? '' : ' animated';
    
    $seam_icon = ($a['seam_icon'] != '') ? '<div class="seam_icon ' . $a['seam_icon'] . '"><span class="pe7-icon pe-7s-play"></span></div>' : '';

    $move_x = '';
    if ($a['move_x'] != '') {
        if ($a['align'] == 'left') {
            $move_x = ' left: ' . $a['move_x'] . ';';
        } elseif ($a['align'] == 'right') {
            $move_x = ' right: ' . $a['move_x'] . ';';
        }
    }

    $a_class = '';
    $a_class .= ($a['align'] != '') ? ' align-' . $a['align'] : '';
    $a_class .= ($a['valign'] != '') ? ' valign-' . $a['valign'] : '';

    $onclick = '';
    if ($a['link'] != '') {
        $a_class .= ' cursor-pointer';
        $onclick = ' onclick="window.location=\'' . $a['link'] . '\'"';
    }

    $src = '';
    $image = '';
    if ($a['img_src'] != '') {
        $image = wp_get_attachment_image_src($a['img_src'], 'full');
        $src = isset($image[0]) ? $image[0] : '';
    }

    if($src == '') {
        return '';
    }
    
    $a['height'] = !(int) $a['height'] ? (isset($image[2]) ? $image[2] : 200) : (int) $a['height'];
    $ratio = isset($image[2]) ? $a['height'] / $image[2] : false;
    $a['width'] = !(int) $a['width'] ? (isset($image[1]) && $ratio ? ($ratio * $image[1]) : 200) : (int) $a['width'];
    
    $height = 'height: ' . (int) $a['height'] . 'px;';
    $text_color = ($a['text_color'] != '') ? ' ' . $a['text_color'] : '';
    $text_align = ($a['text-align'] != '') ? ' ' . $a['text-align'] : '';
    $hover_effect = ($a['hover'] != '') ? ' hover-' . $a['hover'] : '';
    $content_width = ($a['content-width'] != '') ? 'width: ' . $a['content-width'] . ';' : '';
    $effect_text = ($a['effect_text'] != '') ? $a['effect_text'] : 'fadeIn';
    $data_delay = ($a['data_delay'] != '') ? $a['data_delay'] : '';
    $el_class = ($a['el_class'] != '') ? ' ' . $a['el_class'] : '';
    $el_class .= $a['banner_responsive'] != 'yes' ? ' nasa-not-responsive' : '';
    
    $borderInner = $a['border_inner'] == 'yes' ? '<div class="nasa-border-inner-wrap"><div class="nasa-border-inner"></div></div>' : '';
    $borderOutner = $a['border_outner'] == 'yes' ? '<div class="nasa-border-outner-wrap"><div class="nasa-border-outner"></div></div>' : '';
    
    $content = trim($content) ?
        '<div class="banner-content-warper"><div class="nasa-banner-content banner-content' . $a_class . $text_color . $text_align . '" style="' . $content_width . $move_x . '">' .
            '<div class="banner-inner wow ' . $effect_text . $class_woo . '" data-animation="' . $effect_text . '">' . 
                nasa_fixShortcode($content) .
            '</div>' .
        '</div></div>' : '';

    $banner_bg = 'background-image: url(' . esc_url($src) . ');';
    $banner_bg .= ($a['hover'] != 'carousel') ? ' background-position: center center;' : '';

    $bg_lax = '';
    if ($a['hover'] == 'lax' || $a['hover'] == 'carousel') {
        $bg_lax = ' ' . $banner_bg;
        $banner_bg = '';
    }

    $fontstyle = $a['font_style'] ? ' banner-font-' . $a['font_style'] : '';

    return $seam_icon .
        '<div class="banner nasa_banner' . $fontstyle . $hover_effect . $el_class . '" data-wow-delay="' . $data_delay . '"' . $onclick . ' style="' . $height . $bg_lax . '">' . $borderOutner . $borderInner .
            '<div class="banner-image nasa-banner-image" style="' . $banner_bg . '" data-height="' . $a['height'] . '" data-width="' . $a['width'] . '"></div>' . $content .
        '</div>';
}

// **********************************************************************// 
// ! Register New Element: Banner 
// **********************************************************************//
function nasa_register_banner(){
    $banner_params = array(
        'name' => 'Banner',
        'base' => 'nasa_banner',
        'icon' => 'icon-wpb-nasatheme',
        'description' => esc_html__("Display banners", 'nasa-core'),
        'category' => 'Nasa Core',
        'as_parent' => array('except' => 'nasa_banner'), // Use only|except attributes to limit child shortcodes (separate multiple values with comma)
        'params' => array(
            array(
                'type' => 'attach_image',
                "heading" => esc_html__("Banner Image", 'nasa-core'),
                "param_name" => "img_src"
            ),
            array(
                'type' => 'textfield',
                "heading" => esc_html__("Banner Height", 'nasa-core'),
                "param_name" => "height",
                "edit_field_class" => "vc_col-sm-6 vc_column",
                "value" => ""
            ),
            array(
                'type' => 'textfield',
                "heading" => esc_html__("Banner Width", 'nasa-core'),
                "param_name" => "width",
                "edit_field_class" => "vc_col-sm-6 vc_column",
                "value" => ""
            ),
            array(
                "type" => "textfield",
                "heading" => esc_html__("Link", 'nasa-core'),
                "edit_field_class" => "vc_col-sm-6 vc_column",
                "param_name" => "link"
            ),
            array(
                "type" => "textfield",
                "heading" => esc_html__("Content Width (%)", 'nasa-core'),
                "edit_field_class" => "vc_col-sm-6 vc_column",
                "param_name" => "content-width",
                "value" => '',
            ),
            array(
                "type" => "dropdown",
                "heading" => esc_html__("Horizontal alignment", 'nasa-core'),
                "param_name" => "align",
                "edit_field_class" => "vc_col-sm-6 vc_column",
                "value" => array(
                    esc_html__("Left", 'nasa-core') => "left",
                    esc_html__("Center", 'nasa-core') => "center",
                    esc_html__("Right", 'nasa-core') => "right"
                )
            ),
            array(
                "type" => "textfield",
                "heading" => esc_html__("Move Horizontal a distance (%)", "nasa-core"),
                "param_name" => "move_x",
                "value" => "",
                "edit_field_class" => "vc_col-sm-6 vc_column",
                "dependency" => array(
                    "element" => "align",
                    "value" => array(
                        "left",
                        "right"
                    )
                ),
            ),
            array(
                "type" => "dropdown",
                "heading" => esc_html__("Vertical alignment", 'nasa-core'),
                "param_name" => "valign",
                "edit_field_class" => "vc_col-sm-6 vc_column",
                "value" => array(
                    esc_html__("Top", 'nasa-core') => "top",
                    esc_html__("Middle", 'nasa-core') => "middle",
                    esc_html__("Bottom", 'nasa-core') => "bottom"
                )
            ),
            array(
                "type" => "dropdown",
                "heading" => esc_html__("Text alignment", "nasa-core"),
                "param_name" => "text-align",
                "edit_field_class" => "vc_col-sm-6 vc_column",
                "value" => array(
                    esc_html__("Left", 'nasa-core') => "text-left",
                    esc_html__("Center", 'nasa-core') => "text-center",
                    esc_html__("Right", 'nasa-core') => "text-right"
                )
            ),
            array(
                "type" => "dropdown",
                "heading" => "Text Color",
                "param_name" => "text_color",
                "edit_field_class" => "vc_col-sm-6 vc_column",
                "value" => array(
                    esc_html__('Black', 'nasa-core') => 'light',
                    esc_html__('White', 'nasa-core') => 'dark',
                )
            ),
            array(
                "type" => "dropdown",
                "heading" => esc_html__("Responsive", 'nasa-core'),
                "param_name" => "banner_responsive",
                "edit_field_class" => "vc_col-sm-6 vc_column",
                "value" => array(
                    esc_html__('Yes', 'nasa-core') => 'yes',
                    esc_html__('No', 'nasa-core') => 'no'
                ),
                "std" => 'yes',
                "admin_label" => true
            ),
            array(
                "type" => "textarea_html",
                "holder" => "div",
                "heading" => "Banner Text",
                "param_name" => "content",
                "value" => "",
            ),
            array(
                "type" => "animation_style",
                "heading" => esc_html__("Effect banner content", 'nasa-core'),
                "param_name" => "effect_text",
                "value" => "fadeIn",
                "description" => esc_html__("Select initial loading animation for text content.", "nasa-core"),
            ),
            array(
                "type" => "dropdown",
                "heading" => esc_html__("Effect banner hover.", 'nasa-core'),
                "param_name" => "hover",
                "value" => array(
                    esc_html__('None', 'nasa-core') => '',
                    esc_html__('Zoom', 'nasa-core') => 'zoom',
                    esc_html__('Zoom Out', 'nasa-core') => 'reduction',
                    esc_html__('Fade', 'nasa-core') => 'fade',
                    esc_html__('Carousel', 'nasa-core') => 'carousel',
                    esc_html__('Parallax Lax', 'nasa-core') => 'lax'
                )
            ),
            array(
                "type" => "dropdown",
                "heading" => esc_html__('Animation delay', 'nasa-core'),
                "param_name" => "data_delay",
                "value" => array(
                    esc_html__('None', 'nasa-core') => '',
                    esc_html__('100ms', 'nasa-core') => '100ms',
                    esc_html__('200ms', 'nasa-core') => '200ms',
                    esc_html__('300ms', 'nasa-core') => '300ms',
                    esc_html__('400ms', 'nasa-core') => '400ms',
                    esc_html__('500ms', 'nasa-core') => '500ms',
                    esc_html__('600ms', 'nasa-core') => '600ms',
                    esc_html__('700ms', 'nasa-core') => '700ms',
                    esc_html__('800ms', 'nasa-core') => '800ms',
                ),
                "description" => esc_html__("Delay time animation display text content", "nasa-core")
            ),
            array(
                "type" => "dropdown",
                "heading" => esc_html__("Seam icon", 'nasa-core'),
                "param_name" => "seam_icon",
                "value" => array(
                    esc_html__('None', 'nasa-core') => '',
                    esc_html__('Left alignment', 'nasa-core') => 'align_left',
                    esc_html__('Right alignment', 'nasa-core') => 'align_right',
                )
            ),

            array(
                "type" => "dropdown",
                "heading" => esc_html__("Border inner", 'nasa-core'),
                "param_name" => "border_inner",
                "value" => array(
                    esc_html__('Yes', 'nasa-core') => 'yes',
                    esc_html__('No', 'nasa-core') => 'no'
                ),
                "std" => 'no',
                "admin_label" => true
            ),

            array(
                "type" => "dropdown",
                "heading" => esc_html__("Border outner", 'nasa-core'),
                "param_name" => "border_outner",
                "value" => array(
                    esc_html__('Yes', 'nasa-core') => 'yes',
                    esc_html__('No', 'nasa-core') => 'no'
                ),
                "std" => 'no',
                "admin_label" => true
            ),

            array(
                "type" => "textfield",
                "heading" => esc_html__("Extra Class", 'nasa-core'),
                "param_name" => "el_class",
                "description" => esc_html__('If you wish to style particular content element differently, then use this field to add a class name and then refer to it in your css file.', 'nasa-core')
            )
        )
    );

    vc_map($banner_params);
}