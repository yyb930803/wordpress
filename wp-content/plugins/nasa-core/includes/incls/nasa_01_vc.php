<?php
/**
 * Force Visual Composer to initialize as "built into the theme".
 * This will hide certain tabs under the Settings->Visual Composer page
 */
add_action('vc_before_init', 'nasa_your_prefix_vc_set_as_theme');
function nasa_your_prefix_vc_set_as_theme() {
    /* Hide update notice */
    vc_set_as_theme(false); // $disable_updater = false
}

// **********************************************************************// 
// ! Customize the VC rows and columns to use theme's Foundation framework
// **********************************************************************//
if (!function_exists('nasa_customize_custom_css_classes')) {

    function nasa_customize_vc_rows_columns($class_string, $tag) {
        // vc_row 
        if ($tag == 'vc_row' || $tag == 'vc_row_inner') {

            $replace = array(
                'vc_row-fluid' => 'row',
                'wpb_row' => '',
                'vc_row' => '',
                'vc_inner' => '',
            );

            $class_string = nasa_replace_string_with_assoc_array($replace, $class_string);
        }

        
        // vc_column
        if ($tag == 'vc_column' || $tag == 'vc_column_inner') {
            $replace = array(
                'wpb_column' => '',
                'vc_column_container' => '',
                'column_container' => '',
            );

            $class_string = nasa_replace_string_with_assoc_array(
                $replace, preg_replace('/vc_span(\d{1,2})/', 'large-$1', $class_string)
            );

            // Custom columns	
            $class_string = nasa_replace_string_with_assoc_array(
                $replace, preg_replace('/vc_(\d{1,2})\\/12/', 'large-$1', $class_string)
            );
            
            $class_string = nasa_replace_string_with_assoc_array(
                $replace, preg_replace('/vc_hidden-xs/', 'hide-for-mobile', $class_string)
            );
            
            $class_string = nasa_replace_string_with_assoc_array(
                $replace, preg_replace('/vc_hidden-lg/', 'hide-for-desktop', $class_string)
            );
            
            $class_string = nasa_replace_string_with_assoc_array(
                $replace, preg_replace('/vc_hidden-sm/', 'hide-for-small-inherit', $class_string)
            );
            
            $class_string = nasa_replace_string_with_assoc_array(
                $replace, preg_replace('/vc_hidden-md/', 'hide-for-taplet', $class_string)
            );
            
            $class_string = nasa_replace_string_with_assoc_array(
                $replace, preg_replace('/vc_col-(sm|lg)-(\d{1,2})/', 'large-$2', $class_string)
            );
            
            $class_string = nasa_replace_string_with_assoc_array(
                $replace, preg_replace('/vc_col-lg-offset-(\d{1,2})/', 'large-offset-$1', $class_string)
            );
            
            $class_string = nasa_replace_string_with_assoc_array(
                $replace, preg_replace('/vc_col-sm-offset-(\d{1,2})/', 'nasa-small-offset-$1', $class_string)
            );
            
            $class_string = nasa_replace_string_with_assoc_array(
                $replace, preg_replace('/vc_col-md-(\d{1,2})/', 'medium-$1', $class_string)
            );
            
            $class_string = nasa_replace_string_with_assoc_array(
                $replace, preg_replace('/vc_col-md-offset-(\d{1,2})/', 'medium-offset-$1', $class_string)
            );
            
            $class_string = nasa_replace_string_with_assoc_array(
                $replace, preg_replace('/vc_col-xs-(\d{1,2})/', 'small-$1', $class_string)
            );
            
            $class_string = nasa_replace_string_with_assoc_array(
                $replace, preg_replace('/vc_col-xs-offset-(\d{1,2})/', 'small-offset-$1', $class_string)
            );
            
            /**
             * Support 5 columns
             */
            $class_string = nasa_replace_string_with_assoc_array(
                $replace, preg_replace('/(large|medium|small)-(\d{1,4})\/5/', 'nasa-$1-5-col-$2', $class_string)
            );
            
            $class_string = nasa_replace_string_with_assoc_array(
                $replace, preg_replace('/(large|medium|small)-offset-(\d{1,4})\/5/', 'nasa-$1-offset-5-col-$2', $class_string)
            );
            
            $class_string .= ' nasa-col columns';
        }

        return trim($class_string);
    }

}

// Used in "nasa_customize_vc_rows_columns()" [plugin-custom-functions.php]
if (!function_exists('nasa_replace_string_with_assoc_array')) {

    function nasa_replace_string_with_assoc_array(array $replace, $subject) {
        return str_replace(array_keys($replace), array_values($replace), $subject);
    }

}

// **********************************************************************// 
// ! Visual Composer Setup
// **********************************************************************//
if (!function_exists('getCSSAnimation')) {

    function getCSSAnimation($css_animation) {
        $output = '';
        if ($css_animation != '') {
            wp_enqueue_script('waypoints');
            $output = ' wpb_animate_when_almost_visible wpb_' . $css_animation;
        }
        return $output;
    }

}

if (!function_exists('buildStyle')) {

    function buildStyle($bg_image = '', $bg_color = '', $bg_image_repeat = '', $font_color = '', $padding = '', $margin_bottom = '') {
        $has_image = false;
        $style = '';
        
        if ((int) $bg_image > 0 && ($image_url = wp_get_attachment_url($bg_image, 'large')) !== false) {
            $has_image = true;
            $style .= "background-image: url(" . $image_url . ");";
        }
        
        if (!empty($bg_color)) {
            $style .= vc_get_css_color('background-color', $bg_color);
        }
        
        if (!empty($bg_image_repeat) && $has_image) {
            if ($bg_image_repeat === 'cover') {
                $style .= "background-repeat:no-repeat;background-size: cover;";
            } elseif ($bg_image_repeat === 'contain') {
                $style .= "background-repeat:no-repeat;background-size: contain;";
            } elseif ($bg_image_repeat === 'no-repeat') {
                $style .= 'background-repeat: no-repeat;';
            }
        }
        
        if (!empty($font_color)) {
            $style .= vc_get_css_color('color', $font_color); // 'color: '.$font_color.';';
        }
        
        if ($padding != '') {
            $style .= 'padding: ' . (preg_match('/(px|em|\%|pt|cm)$/', $padding) ? $padding : $padding . 'px') . ';';
        }
        
        if ($margin_bottom != '') {
            $style .= 'margin-bottom: ' . (preg_match('/(px|em|\%|pt|cm)$/', $margin_bottom) ? $margin_bottom : $margin_bottom . 'px') . ';';
        }
        
        return empty($style) ? $style : ' style="' . $style . '"';
    }
}

add_action('init', 'nasa_vc_setup');
if (!function_exists('nasa_vc_setup')) {

    function nasa_vc_setup() {
        if (!class_exists('WPBakeryVisualComposerAbstract')){
            return;
        }
        
        //Your "container" content element should extend WPBakeryShortCodesContainer class to inherit all required functionality
        if (class_exists('WPBakeryShortCodesContainer')) {
            class WPBakeryShortCode_nasa_slider extends WPBakeryShortCodesContainer {}
            class WPBakeryShortCode_nasa_banner_grid extends WPBakeryShortCodesContainer {}
            class WPBakeryShortCode_nasa_col extends WPBakeryShortCodesContainer {}
            class WPBakeryShortCode_nasa_row extends WPBakeryShortCodesContainer {}
        }
        
        if (class_exists('WPBakeryShortCode')) {
            class WPBakeryShortCode_nasa_banner extends WPBakeryShortCode {}
        }
    }

}

// Visual Composer plugin
if (class_exists('Vc_Manager')) {
    add_filter('vc_shortcodes_css_class', 'nasa_customize_vc_rows_columns', 10, 2);
}
