<?php

/**
 * Include and setup custom metaboxes and fields.
 *
 * @category nasa-core
 * @package  Metaboxes
 * @license  http://www.opensource.org/licenses/gpl-license.php GPL v2.0 (or later)
 * @link     https://github.com/webdevstudios/Custom-Metaboxes-and-Fields-for-WordPress
 */
add_filter('cmb_meta_boxes', 'nasa_metaboxes');

/**
 * Define the metabox and field configurations.
 *
 * @param  array $meta_boxes
 * @return array
 */
function nasa_metaboxes(array $meta_boxes) {
    global $nasa_opt;
    
    // Start with an underscore to hide fields from custom fields list
    $prefix = '_nasa_';
    
    /**
     * Product Categories level 0
     */
    $args = array(
        'taxonomy' => 'product_cat',
        'parent' => 0,
        'hierarchical' => true,
        'hide_empty' => false
    );
    
    if(!isset($nasa_opt['show_uncategorized']) || !$nasa_opt['show_uncategorized']) {
        $args['exclude'] = get_option('default_product_cat');
    }
    $categories = get_terms(apply_filters('woocommerce_product_attribute_terms', $args));
    $categories_options = array('' => esc_html__('Default', 'nasa-core'));
    if (!empty($categories)) {
        foreach ($categories as $value) {
            $categories_options[$value->slug] = $value->name;
        }
    }
    
    $attr_disp_type = array(
        "" => esc_html__("Default", 'nasa-core'),
        "round" => esc_html__("Round", 'nasa-core'),
        "square" => esc_html__("Square", 'nasa-core')
    );
    
    $custom_fonts = nasa_get_custom_fonts();
    $google_fonts = nasa_get_google_fonts();
    
    $meta_boxes['nasa_metabox_general'] = array(
        'id' => 'nasa_metabox_general',
        'title' => esc_html__('General Page Options', 'nasa-core'),
        'pages' => array('page'), // Post type
        'context' => 'normal',
        'priority' => 'high',
        'show_names' => true, // Show field names on the left
        'fields' => array(
            array(
                'name' => esc_html__('Custom width this page', 'nasa-core'),
                'desc' => esc_html__('Custom width this page', 'nasa-core'),
                'id' => $prefix . 'plus_wide_option',
                'type' => 'select',
                'options' => array(
                    '' => esc_html__('Default', 'nasa-core'),
                    '1' => esc_html__('Yes', 'nasa-core'),
                    '-1' => esc_html__('No', 'nasa-core')
                ),
                'default' => '',
                'class' => 'nasa-core-option-parent'
            ),
            
            array(
                "name" => esc_html__("Add more width site (px)", 'nasa-core'),
                "desc" => esc_html__("The max-width your site will be INPUT + 1200 (pixel). Empty will use default theme option", 'nasa-core'),
                "id" => $prefix . "plus_wide_width",
                "default" => "",
                "type" => "text",
                'class' => 'hidden-tag nasa-core-option-child core' . $prefix . 'plus_wide_option core' . $prefix . 'plus_wide_option-1'
            ),
            
            array(
                'name' => esc_html__('Override Logo', 'nasa-core'),
                'desc' => esc_html__('Upload an image for override default logo.', 'nasa-core'),
                'id' => $prefix . 'custom_logo',
                'allow' => false,
                'type' => 'file',
            ),
            
            array(
                'name' => esc_html__('Override Retina Logo', 'nasa-core'),
                'desc' => esc_html__('Upload an image for override default retina logo.', 'nasa-core'),
                'id' => $prefix . 'custom_logo_retina',
                'allow' => false,
                'type' => 'file',
            ),
            
            array(
                'name' => esc_html__('Override Primary Color', 'nasa-core'),
                'desc' => esc_html__('Yes, please', 'nasa-core'),
                'id' => $prefix . 'pri_color_flag',
                'default' => '0',
                'type' => 'checkbox',
                'class' => 'nasa-override-pri-color-flag'
            ),
            
            array(
                'name' => esc_html__('Primary color', 'nasa-core'),
                'desc' => esc_html__('Primary color', 'nasa-core'),
                'id' => $prefix . 'pri_color',
                'type' => 'colorpicker',
                'default' => '',
                'class' => 'hidden-tag nasa-option-color nasa-override-pri-color'
            ),
            
            array(
                'name' => esc_html__('Root Product Category', 'nasa-core'),
                'desc' => esc_html__('Root Product Category. (Use for Multi stores)', 'nasa-core'),
                'id' => $prefix . 'root_category',
                'type' => 'select',
                'options' => $categories_options,
                'default' => '',
                'class' => 'nasa-core-option-parent'
            ),
            
            array(
                'name' => esc_html__('Attribule Image', 'nasa-core'),
                'desc' => esc_html__('Display Type of Attribule Image', 'nasa-core'),
                'id' => $prefix . 'attr_display_type',
                'type' => 'select',
                'options' => $attr_disp_type,
                'default' => '',
                'class' => 'nasa-core-option-parent'
            )
        )
    );
    
    $meta_boxes['nasa_metabox_font'] = array(
        'id' => 'nasa_metabox_font',
        'title' => esc_html__('Font Style', 'nasa-core'),
        'pages' => array('page'), // Post type
        'context' => 'normal',
        'priority' => 'high',
        'show_names' => true, // Show field names on the left
        'fields' => array(
            array(
                'name' => esc_html__('Type Font', 'nasa-core'),
                'id' => $prefix . 'type_font_select',
                'type' => 'select',
                'options' => array(
                    "" => esc_html__("Default font", 'nasa-core'),
                    "custom" => esc_html__("Custom font", 'nasa-core'),
                    "google" => esc_html__("Google font", 'nasa-core')
                ),
                'default' => '',
                'class' => 'nasa-core-option-parent'
            ),
            
            array(
                'name' => esc_html__('Headings font (H1, H2, H3, H4, H5, H6)', 'nasa-core'),
                'id' => $prefix . 'type_headings',
                'type' => 'select',
                'options' => $google_fonts,
                'default' => isset($nasa_opt['type_headings']) ? $nasa_opt['type_headings'] : '',
                'class' => 'hidden-tag nasa-core-option-child core' . $prefix . 'type_font_select core' . $prefix . 'type_font_select-google'
            ),
            
            array(
                'name' => esc_html__('Texts font (paragraphs, etc..)', 'nasa-core'),
                'id' => $prefix . 'type_texts',
                'type' => 'select',
                'options' => $google_fonts,
                'default' => isset($nasa_opt['type_texts']) ? $nasa_opt['type_texts'] : '',
                'class' => 'hidden-tag nasa-core-option-child core' . $prefix . 'type_font_select core' . $prefix . 'type_font_select-google'
            ),
            
            array(
                'name' => esc_html__('Main navigation font', 'nasa-core'),
                'id' => $prefix . 'type_nav',
                'type' => 'select',
                'options' => $google_fonts,
                'default' => isset($nasa_opt['type_nav']) ? $nasa_opt['type_nav'] : '',
                'class' => 'hidden-tag nasa-core-option-child core' . $prefix . 'type_font_select core' . $prefix . 'type_font_select-google'
            ),
            
            array(
                'name' => esc_html__('Banner font', 'nasa-core'),
                'id' => $prefix . 'type_banner',
                'type' => 'select',
                'options' => $google_fonts,
                'default' => isset($nasa_opt['type_banner']) ? $nasa_opt['type_banner'] : '',
                'class' => 'hidden-tag nasa-core-option-child core' . $prefix . 'type_font_select core' . $prefix . 'type_font_select-google'
            ),
            
            array(
                'name' => esc_html__('Price font', 'nasa-core'),
                'id' => $prefix . 'type_price',
                'type' => 'select',
                'options' => $google_fonts,
                'default' => isset($nasa_opt['type_price']) ? $nasa_opt['type_price'] : '',
                'class' => 'hidden-tag nasa-core-option-child core' . $prefix . 'type_font_select core' . $prefix . 'type_font_select-google'
            ),
            
            array(
                'name' => esc_html__('Custom font', 'nasa-core'),
                'id' => $prefix . 'custom_font',
                'type' => 'select',
                'options' => $custom_fonts,
                'default' => isset($nasa_opt['custom_font']) ? $nasa_opt['custom_font'] : '',
                'class' => 'hidden-tag nasa-core-option-child core' . $prefix . 'type_font_select core' . $prefix . 'type_font_select-custom'
            ),
        )
    );
    
    $meta_boxes['nasa_metabox_header'] = array(
        'id' => 'nasa_metabox_header',
        'title' => esc_html__('Header Page Options', 'nasa-core'),
        'pages' => array('page'), // Post type
        'context' => 'normal',
        'priority' => 'high',
        'show_names' => true, // Show field names on the left
        'fields' => array(
            array(
                'name' => esc_html__('Header Type', 'nasa-core'),
                'desc' => esc_html__('Description (optional)', 'nasa-core'),
                'id' => $prefix . 'custom_header',
                'type' => 'select',
                'options' => array(
                    '' => esc_html__('Default', 'nasa-core'),
                    '1' => esc_html__('Header Type 1', 'nasa-core'),
                    '2' => esc_html__('Header Type 2', 'nasa-core'),
                    '3' => esc_html__('Header Type 3', 'nasa-core'),
                    '4' => esc_html__('Header Type 4', 'nasa-core'),
                    'nasa-custom' => esc_html__('Header Builder', 'nasa-core')
                ),
                'default' => '',
                'class' => 'nasa-core-option-parent'
            ),
            
            array(
                'name' => esc_html__("Sticky", 'nasa-core'),
                'desc' => esc_html__('Header sticky (Not use for Header Builder).', 'nasa-core'),
                'id' => $prefix . 'fixed_nav',
                'type' => 'select',
                'options' => array(
                    '' => esc_html__('Default', 'nasa-core'),
                    '1' => esc_html__('Yes', 'nasa-core'),
                    '-1' => esc_html__('No', 'nasa-core')
                ),
                'default' => ''
            ),
            
            array(
                'name' => esc_html__('Header Builder', 'nasa-core'),
                'desc' => esc_html__('Description (optional)', 'nasa-core'),
                'id' => $prefix . 'header_builder',
                'type' => 'select',
                'options' => nasa_get_headers_options(),
                'default' => '',
                'class' => 'hidden-tag nasa-core-option-child core' . $prefix . 'custom_header core' . $prefix . 'custom_header-nasa-custom'
            ),
            
            array(
                'name' => esc_html__('Main Menu Fullwidth', 'nasa-core'),
                'desc' => esc_html__('Main menu fullwidth', 'nasa-core'),
                'id' => $prefix . 'fullwidth_main_menu',
                'type' => 'select',
                'options' => array(
                    '' => esc_html__('Default', 'nasa-core'),
                    '1' => esc_html__('Yes', 'nasa-core'),
                    '-1' => esc_html__('No', 'nasa-core')
                ),
                'default' => '',
                'class' => 'hidden-tag nasa-core-option-child core' . $prefix . 'custom_header core' . $prefix . 'custom_header-2 core' . $prefix . 'custom_header-3'
            ),
            
            array(
                "name" => esc_html__("Extra Class Name Header", 'nasa-core'),
                'desc' => esc_html__('Custom add more class name for header page', 'nasa-core'),
                "id" => $prefix . "el_class_header",
                "default" => '',
                "type" => "text",
                'class' => 'hidden-tag nasa-core-option-child core' . $prefix . 'custom_header core' . $prefix . 'custom_header-1 core' . $prefix . 'custom_header-2 core' . $prefix . 'custom_header-3'
            ),
            
            array(
                'name' => esc_html__('Header Transparent', 'nasa-core'),
                'desc' => esc_html__('Header transparent', 'nasa-core'),
                'id' => $prefix . 'header_transparent',
                'type' => 'select',
                'options' => array(
                    '' => esc_html__('Default', 'nasa-core'),
                    '1' => esc_html__('Yes', 'nasa-core'),
                    '-1' => esc_html__('No', 'nasa-core')
                ),
                'default' => '',
                'class' => 'hidden-tag nasa-core-option-child core' . $prefix . 'custom_header core' . $prefix . 'custom_header-1 core' . $prefix . 'custom_header-2 core' . $prefix . 'custom_header-3'
            ),
            
            array(
                'name' => esc_html__('Block Header', 'nasa-core'),
                'desc' => esc_html__('Add static block to Header', 'nasa-core'),
                'id' => $prefix . 'header_block',
                'type' => 'select',
                'options' => nasa_get_blocks_options(),
                'default' => '',
                'class' => 'hidden-tag nasa-core-option-child core' . $prefix . 'custom_header core' . $prefix . 'custom_header-1 core' . $prefix . 'custom_header-2 core' . $prefix . 'custom_header-3 core' . $prefix . 'custom_header-4'
            ),
            
            array(
                'name' => esc_html__('Toggle Top Bar', 'nasa-core'),
                'desc' => esc_html__('Toggle bar page', 'nasa-core'),
                'id' => $prefix . 'topbar_toggle',
                'type' => 'select',
                'options' => array(
                    '' => esc_html__('Default', 'nasa-core'),
                    '1' => esc_html__('Yes', 'nasa-core'),
                    '2' => esc_html__('No', 'nasa-core')
                ),
                'default' => '',
                'class' => 'nasa-core-option-parent'
            ),
            
            array(
                'name' => esc_html__('Init Show Top Bar', 'nasa-core'),
                'desc' => esc_html__('Default init show Top Bar in page', 'nasa-core'),
                'id' => $prefix . 'topbar_default_show',
                'type' => 'select',
                'options' => array(
                    '' => esc_html__('Default', 'nasa-core'),
                    '1' => esc_html__('Yes', 'nasa-core'),
                    '2' => esc_html__('No', 'nasa-core')
                ),
                'default' => '',
                'class' => 'hidden-tag nasa-core-option-child core' . $prefix . 'topbar_toggle core' . $prefix . 'topbar_toggle-1'
            ),
            
            array(
                'name' => esc_html__('Header Background', 'nasa-core'),
                'desc' => esc_html__('Header Background', 'nasa-core'),
                'id' => $prefix . 'bg_color_header',
                'type' => 'colorpicker',
                'default' => '',
                'class' => 'nasa-option-color hidden-tag nasa-core-option-child core' . $prefix . 'custom_header core' . $prefix . 'custom_header-1 core' . $prefix . 'custom_header-2 core' . $prefix . 'custom_header-3'
            ),
            
            array(
                'name' => esc_html__('Header Text color', 'nasa-core'),
                'desc' => esc_html__('Override Text color items in header', 'nasa-core'),
                'id' => $prefix . 'text_color_header',
                'type' => 'colorpicker',
                'default' => '',
                'class' => 'nasa-option-color hidden-tag nasa-core-option-child core' . $prefix . 'custom_header core' . $prefix . 'custom_header-1 core' . $prefix . 'custom_header-2 core' . $prefix . 'custom_header-3'
            ),
            
            array(
                'name' => esc_html__('Header Text color hover', 'nasa-core'),
                'desc' => esc_html__('Override Text color hover items in header', 'nasa-core'),
                'id' => $prefix . 'text_color_hover_header',
                'type' => 'colorpicker',
                'default' => '',
                'class' => 'nasa-option-color hidden-tag nasa-core-option-child core' . $prefix . 'custom_header core' . $prefix . 'custom_header-1 core' . $prefix . 'custom_header-2 core' . $prefix . 'custom_header-3'
            ),
            
            array(
                'name' => esc_html__('Top Bar Background', 'nasa-core'),
                'desc' => esc_html__('Top Bar Background', 'nasa-core'),
                'id' => $prefix . 'bg_color_topbar',
                'type' => 'colorpicker',
                'default' => '',
                'class' => 'nasa-option-color'
            ),
            
            array(
                'name' => esc_html__('Top Bar Text Color', 'nasa-core'),
                'desc' => esc_html__('Override text color items in top bar', 'nasa-core'),
                'id' => $prefix . 'text_color_topbar',
                'type' => 'colorpicker',
                'default' => '',
                'class' => 'nasa-option-color'
            ),
            
            array(
                'name' => esc_html__('Top Bar Text Color Hover', 'nasa-core'),
                'desc' => esc_html__('Override Text color hover items in Top bar', 'nasa-core'),
                'id' => $prefix . 'text_color_hover_topbar',
                'type' => 'colorpicker',
                'default' => '',
                'class' => 'nasa-option-color'
            ),
            
            array(
                "name" => esc_html__("Vertical Menu", 'nasa-core'),
                "id" => $prefix . "vertical_menu_selected",
                "default" => "",
                "type" => "select",
                "options" => nasa_meta_getListMenus()
            ),
            
            array(
                "name" => esc_html__("Level 2 Allways Show", 'nasa-core'),
                'desc' => esc_html__('Yes, please', 'nasa-core'),
                "id" => $prefix . "vertical_menu_allways_show",
                "default" => '0',
                "type" => "checkbox"
            ),
            
            array(
                'name' => esc_html__('Main Menu Background', 'nasa-core'),
                'desc' => esc_html__('Override background color for Main menu (Only use header type 2)', 'nasa-core'),
                'id' => $prefix . 'bg_color_main_menu',
                'type' => 'colorpicker',
                'default' => '',
                'class' => 'nasa-option-color hidden-tag nasa-core-option-child core' . $prefix . 'custom_header core' . $prefix . 'custom_header-2 core' . $prefix . 'custom_header-3 core' . $prefix . 'custom_header-4'
            ),
            
            array(
                'name' => esc_html__('Main Menu Text color', 'nasa-core'),
                'desc' => esc_html__('Override text color for Main menu', 'nasa-core'),
                'id' => $prefix . 'text_color_main_menu',
                'type' => 'colorpicker',
                'default' => '',
                'class' => 'nasa-option-color hidden-tag nasa-core-option-child core' . $prefix . 'custom_header core' . $prefix . 'custom_header-1 core' . $prefix . 'custom_header-2 core' . $prefix . 'custom_header-3 core' . $prefix . 'custom_header-4'
            )
        )
    );
    
    $meta_boxes['nasa_metabox_breadcrumb'] = array(
        'id' => 'nasa_metabox_breadcrumb',
        'title' => esc_html__('Breadcrumb Page Options', 'nasa-core'),
        'pages' => array('page'), // Post type
        'context' => 'normal',
        'priority' => 'high',
        'show_names' => true, // Show field names on the left
        'fields' => array(
            array(
                'name' => esc_html__('Show Breadcrumb', 'nasa-core'),
                'desc' => esc_html__('Yes, please', 'nasa-core'),
                'id' => $prefix . 'show_breadcrumb',
                'default' => '0',
                'type' => 'checkbox',
                'class' => 'nasa-breadcrumb-flag'
            ),
            
            array(
                'name' => esc_html__('Breadcrumb Type', 'nasa-core'),
                'desc' => esc_html__('Type override breadcrumb', 'nasa-core'),
                'id' => $prefix . 'type_breadcrumb',
                'type' => 'select',
                'options' => array(
                    '' => esc_html__('Default', 'nasa-core'),
                    '1' => esc_html__('Has breadcrumb background', 'nasa-core')
                ),
                'default' => '',
                'class' => 'hidden-tag nasa-breadcrumb-type'
            ),
            
            array(
                'name' => esc_html__('Override Background For Breadcrumb', 'nasa-core'),
                'desc' => esc_html__('Background for breadcrumb', 'nasa-core'),
                'id' => $prefix . 'bg_breadcrumb',
                'allow' => false,
                'type' => 'file',
                'class' => 'hidden-tag nasa-breadcrumb-bg'
            ),
            
            array(
                'name' => esc_html__('Breadcrumb Background Color', 'nasa-core'),
                'desc' => esc_html__('Breadcrumb background color', 'nasa-core'),
                'id' => $prefix . 'bg_color_breadcrumb',
                'type' => 'colorpicker',
                'default' => '',
                'class' => 'hidden-tag nasa-breadcrumb-bg-color'
            ),
            
            array(
                'name' => esc_html__('Height Breadcrumb', 'nasa-core'),
                'desc' => esc_html__('Height (Pixel)', 'nasa-core'),
                'id' => $prefix . 'height_breadcrumb',
                'type' => 'text',
                'default' => '',
                'class' => 'hidden-tag nasa-breadcrumb-height'
            ),
            
            array(
                'name' => esc_html__('Breadcrumb Text Color', 'nasa-core'),
                'desc' => esc_html__('Text color', 'nasa-core'),
                'id' => $prefix . 'color_breadcrumb',
                'type' => 'colorpicker',
                'default' => '',
                'class' => 'hidden-tag nasa-breadcrumb-color'
            )
        )
    );
    
    /* Get Footers style */
    $footers_option = nasa_get_footers_options();
    $meta_boxes['nasa_metabox_footer'] = array(
        'id' => 'nasa_metabox_footer',
        'title' => esc_html__('Footer Page Options', 'nasa-core'),
        'pages' => array('page'), // Post type
        'context' => 'normal',
        'priority' => 'high',
        'show_names' => true, // Show field names on the left
        'fields' => array(
            array(
                'name' => esc_html__('Footer Type', 'nasa-core'),
                'desc' => esc_html__('Description (optional)', 'nasa-core'),
                'id' => $prefix . 'custom_footer',
                'type' => 'select',
                'options' => $footers_option,
                'default' => ''
            ),
            
            array(
                'name' => esc_html__('Footer Mobile', 'nasa-core'),
                'desc' => esc_html__('Description (optional)', 'nasa-core'),
                'id' => $prefix . 'custom_footer_mobile',
                'type' => 'select',
                'options' => $footers_option,
                'default' => ''
            )
        )
    );

    return $meta_boxes;
}

/**
 * Initialize the metabox class.
 */
add_action('init', 'nasa_init_cmb_meta_boxes');
function nasa_init_cmb_meta_boxes() {
    if (!class_exists('cmb_Meta_Box')){
        require_once NASA_CORE_PLUGIN_PATH . 'admin/metabox/init.php';
    }
}
