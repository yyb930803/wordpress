<?php

defined('ABSPATH') or die('You cannot access this script directly');
defined('NASA_IMPORT_TOTAL') or define('NASA_IMPORT_TOTAL', '25');

// Don't resize images
function nasa_filter_image_sizes($sizes) {
    return array();
}

// Hook importer into admin init
add_action('wp_ajax_nasa_import_contents', 'nasa_import_contents');
function nasa_import_contents() {
    set_time_limit(0);
    header('X-XSS-Protection:0');
    $partial = $_POST['file'];
    $partial = $partial ? str_replace('data', '', $partial) : '';
    $res = array('nofile' => 'false');
    if (current_user_can('manage_options')) {
        if (!defined('WP_LOAD_IMPORTERS')) {
            define('WP_LOAD_IMPORTERS', true); // we are loading importers
        }
        
        if (!class_exists('WP_Importer')) { // if main importer class doesn't exist
            $wp_importer = ABSPATH . 'wp-admin/includes/class-wp-importer.php';
            include $wp_importer;
        }

        if (!class_exists('WP_Import')) { // if WP importer doesn't exist
            $wp_import = NASA_CORE_PLUGIN_PATH . 'admin/importer/wordpress-importer.php';
            require_once $wp_import;
        }

        if (class_exists('WP_Importer') && class_exists('WP_Import')) {
            if (!isset($_SESSION['nasa_import']) || $partial == 1) {
                $_SESSION['nasa_import'] = array();
            }
            
            /* Import Woocommerce if WooCommerce Exists */
            if (class_exists('WooCommerce')) {
                $partial = $partial < 10 ? '0' . $partial : $partial;
                
                $theme_xml = NASA_CORE_PLUGIN_PATH . 'admin/importer/data_import/datas/data_Part_0' . $partial . '_of_' . NASA_IMPORT_TOTAL . '.xml';
                if (is_file($theme_xml)) {
                    $importer = new WP_Import();

                    $importer->fetch_attachments = true;
                    ob_start();
                    $importer->import($theme_xml);
                    $res['mess'] = ob_get_clean();
                } else {
                    $res['mess'] = '<p class="nasa-error">';
                    $res['mess'] .= 'file: ' . NASA_CORE_PLUGIN_PATH . 'admin/importer/data_import/datas/data_Part_0' . $partial . '_of_' . NASA_IMPORT_TOTAL . '.xml is not exists';
                    $res['mess'] .= '</p>';
                    $res['nofile'] = 'true';
                }

                $res['end'] = 1;
                die(json_encode($res));
            }
        }
    }

    $res['mess'] = '';
    $res['end'] = 0;

    die(json_encode($res));
}

add_action('wp_ajax_nasa_import_end_importer', 'nasa_import_end_importer');
function nasa_import_end_importer() {
    header('X-XSS-Protection:0');
    if (current_user_can('manage_options')) {
        /* Import Woocommerce if WooCommerce Exists */
        if (class_exists('WooCommerce')) {
            if (!defined('WP_LOAD_IMPORTERS')) {
                define('WP_LOAD_IMPORTERS', true); // we are loading importers
            }
            
            if (!class_exists('WP_Importer')) { // if main importer class doesn't exist
                $wp_importer = ABSPATH . 'wp-admin/includes/class-wp-importer.php';
                include $wp_importer;
            }

            if (!class_exists('WP_Import')) { // if WP importer doesn't exist
                $wp_import = NASA_CORE_PLUGIN_PATH . 'admin/importer/wordpress-importer.php';
                require_once $wp_import;
            }
            
            if (class_exists('WP_Importer') && class_exists('WP_Import')) {
                $importer = new WP_Import();
                $importer->process_missing_menu_items();
            }

            /* Set imported menus to registered theme locations */
            $locations = get_theme_mod('nav_menu_locations'); // registered menu locations in theme
            $menus = wp_get_nav_menus(); // registered menus

            if ($menus) {
                foreach ($menus as $menu) {
                    switch ($menu->name) {
                        case 'Main Menu':
                            $locations['primary'] = $menu->term_id;
                            break;
                        
                        default: break;
                    }
                }
            }

            set_theme_mod('nav_menu_locations', $locations); // set menus to locations
            
            // Set pages
            $woopages = array(
                'woocommerce_shop_page_id' => 'Shop',
                'woocommerce_cart_page_id' => 'Shopping cart',
                'woocommerce_checkout_page_id' => 'Checkout',
                'woocommerce_pay_page_id' => 'Checkout &#8594; Pay',
                'woocommerce_thanks_page_id' => 'Order Received',
                'woocommerce_myaccount_page_id' => 'My Account',
                'woocommerce_edit_address_page_id' => 'Edit My Address',
                'woocommerce_view_order_page_id' => 'View Order',
                'woocommerce_change_password_page_id' => 'Change Password',
                'woocommerce_logout_page_id' => 'Logout',
                'woocommerce_lost_password_page_id' => 'Lost Password'
            );

            foreach ($woopages as $woo_page_name => $woo_page_title) {
                $woopage = get_page_by_title($woo_page_title);
                if (isset($woopage) && $woopage->ID) {
                    update_option($woo_page_name, $woopage->ID); // Front Page
                }
            }

            // Woo Image sizes
            $catalog = array(
                'width' => '450', // px
                'height' => '', // px
                'crop' => 0   // false
            );

            $single = array(
                'width' => '595', // px
                'height' => '', // px
                'crop' => 0   // false
            );

            $thumbnail = array(
                'width' => '120', // px
                'height' => '150', // px
                'crop' => 1   // false
            );

            update_option('shop_catalog_image_size', $catalog);   // Product category thumbs
            update_option('shop_single_image_size', $single);   // Single product image
            update_option('shop_thumbnail_image_size', $thumbnail);  // Image gallery thumbs
            
            // Wordpress Media Setting
            update_option('medium_size_w', 450);
            update_option('large_size_w', 595);
            
            // For Woo 3.3.x
            update_option('woocommerce_single_image_width', 595);       // Single product image
            update_option('woocommerce_thumbnail_image_width', 450);    // Product category thumbs
            update_option('woocommerce_thumbnail_cropping', 'uncropped');    // Option crop
            
            // default sorting
            update_option('woocommerce_default_catalog_orderby', 'menu_order');
            
            // Number decimals
            update_option('woocommerce_price_num_decimals', '0');

            // We no longer need to install pages
            delete_option('_wc_needs_pages');
            delete_transient('_wc_activation_redirect');

            // Flush rules after install
            flush_rewrite_rules();
        }

        /* Update hompage reading */
        $home_id = get_page_by_title('Homepage');
        $blog_id = get_page_by_title('Blog');
        update_option('show_on_front', 'page');
        update_option('page_on_front', $home_id->ID);
        update_option('page_for_posts', $blog_id->ID);

        /* Add data to widgets */
        $widgets_file = NASA_CORE_PLUGIN_URL . 'admin/importer/data_import/widget_data.json';
        $widgets_json = wp_remote_get($widgets_file);
        $widget_data = isset($widgets_json['body']) ? $widgets_json['body'] : false;
        if($widget_data) {
            nasa_import_widget_data($widget_data);
        }
        
        // Create and switch child-theme
        nasa_create_child_theme();
        update_option('nasatheme_imported', 'imported');
        $_SESSION['nasa_import'] = array();
        exit('imported');
    }
}

// Parsing Widgets Function
// Thanks to http://wordpress.org/plugins/widget-settings-importexport/
function nasa_import_widget_data($widget_data) {
    $json_data = $widget_data;
    $json_data = json_decode($json_data, true);

    $sidebar_data = $json_data[0];
    $widget_data = $json_data[1];

    foreach ($widget_data as $widget_data_title => $widget_data_value) {
        $widgets[$widget_data_title] = '';
        foreach ($widget_data_value as $widget_data_key => $widget_data_array) {
            if (is_int($widget_data_key)) {
                $widgets[$widget_data_title][$widget_data_key] = 'on';
            }
        }
    }
    unset($widgets[""]);

    foreach ($sidebar_data as $title => $sidebar) {
        $count = count($sidebar);
        for ($i = 0; $i < $count; $i++) {
            $widget = array();
            $widget['type'] = trim(substr($sidebar[$i], 0, strrpos($sidebar[$i], '-')));
            $widget['type-index'] = trim(substr($sidebar[$i], strrpos($sidebar[$i], '-') + 1));
            if (!isset($widgets[$widget['type']][$widget['type-index']])) {
                unset($sidebar_data[$title][$i]);
            }
        }
        $sidebar_data[$title] = array_values($sidebar_data[$title]);
    }

    foreach ($widgets as $widget_title => $widget_value) {
        if (is_array($widget_value) && $widget_value) {
            foreach ($widget_value as $widget_key => $widget_value) {
                $widgets[$widget_title][$widget_key] = $widget_data[$widget_title][$widget_key];
            }
        }
    }

    $sidebar_data = array(array_filter($sidebar_data), $widgets);

    nasa_parse_import_data($sidebar_data);
}

function nasa_parse_import_data($import_array) {
    global $wp_registered_sidebars;
    $sidebars_data = $import_array[0];
    $widget_data = $import_array[1];
    $current_sidebars = get_option('sidebars_widgets');
    $new_widgets = array();

    foreach ($sidebars_data as $import_sidebar => $import_widgets) :

        foreach ($import_widgets as $import_widget) :
            //if the sidebar exists
            if (isset($wp_registered_sidebars[$import_sidebar])) :
                $title = trim(substr($import_widget, 0, strrpos($import_widget, '-')));
                $index = trim(substr($import_widget, strrpos($import_widget, '-') + 1));
                $current_widget_data = get_option('widget_' . $title);
                $new_widget_name = nasa_get_new_widget_name($title, $index);
                $new_index = trim(substr($new_widget_name, strrpos($new_widget_name, '-') + 1));

                if (!empty($new_widgets[$title]) && is_array($new_widgets[$title])) {
                    while (array_key_exists($new_index, $new_widgets[$title])) {
                        $new_index++;
                    }
                }
                $current_sidebars[$import_sidebar][] = $title . '-' . $new_index;
                if (array_key_exists($title, $new_widgets)) {
                    $new_widgets[$title][$new_index] = $widget_data[$title][$index];
                    $multiwidget = $new_widgets[$title]['_multiwidget'];
                    unset($new_widgets[$title]['_multiwidget']);
                    $new_widgets[$title]['_multiwidget'] = $multiwidget;
                } else {
                    $current_widget_data[$new_index] = $widget_data[$title][$index];
                    $current_multiwidget = isset($current_widget_data['_multiwidget']) ? $current_widget_data['_multiwidget'] : false;
                    $new_multiwidget = isset($widget_data[$title]['_multiwidget']) ? $widget_data[$title]['_multiwidget'] : false;
                    $multiwidget = ($current_multiwidget != $new_multiwidget) ? $current_multiwidget : 1;
                    unset($current_widget_data['_multiwidget']);
                    $current_widget_data['_multiwidget'] = $multiwidget;
                    $new_widgets[$title] = $current_widget_data;
                }

            endif;
        endforeach;
    endforeach;

    if (isset($new_widgets) && isset($current_sidebars)) {
        update_option('sidebars_widgets', $current_sidebars);

        foreach ($new_widgets as $title => $content) {
            update_option('widget_' . $title, $content);
        }
        
        return true;
    }

    return false;
}

function nasa_get_new_widget_name($widget_name, $widget_index) {
    $current_sidebars = get_option('sidebars_widgets');
    $all_widget_array = array();
    
    foreach ($current_sidebars as $sidebar => $widgets) {
        if (!empty($widgets) && is_array($widgets) && $sidebar != 'wp_inactive_widgets') {
            foreach ($widgets as $widget) {
                $all_widget_array[] = $widget;
            }
        }
    }
    
    while (in_array($widget_name . '-' . $widget_index, $all_widget_array)) {
        $widget_index++;
    }
    
    $new_widget_name = $widget_name . '-' . $widget_index;
    
    return $new_widget_name;
}

// Rename sidebar
function nasa_name_to_class($name) {
    $class = str_replace(array(' ', ',', '.', '"', "'", '/', "\\", '+', '=', ')', '(', '*', '&', '^', '%', '$', '#', '@', '!', '~', '`', '<', '>', '?', '[', ']', '{', '}', '|', ':',), '', $name);
    return $class;
}

function nasa_get_import_files($directory, $filetype) {
    $phpversion = phpversion();
    $files = array();

    // Check if the php version allows for recursive iterators
    if (version_compare($phpversion, '5.2.11', '>')) {
        if ($filetype != '*') {
            $filetype = '/^.*\.' . $filetype . '$/';
        } else {
            $filetype = '/.+\.[^.]+$/';
        }
        
        $directory_iterator = new RecursiveDirectoryIterator($directory);
        $recusive_iterator = new RecursiveIteratorIterator($directory_iterator);
        $regex_iterator = new RegexIterator($recusive_iterator, $filetype);

        foreach ($regex_iterator as $file) {
            $files[] = $file->getPathname();
        }
        // Fallback to glob() for older php versions
    } else {
        if ($filetype != '*') {
            $filetype = '*.' . $filetype;
        }

        foreach (glob($directory . $filetype) as $filename) {
            $filename = basename($filename);
            $files[] = $directory . $filename;
        }
    }

    return $files;
}

/**
 * Create child theme
 */
function nasa_create_child_theme() {
    global $wp_filesystem;
    
    // Initialize the WP filesystem
    if (empty($wp_filesystem)) {
        require_once ABSPATH . '/wp-admin/includes/file.php';
        WP_Filesystem();
    }
    
    $zip = NASA_CORE_PLUGIN_PATH . 'admin/importer/theme-child/theme-child.zip';
    if(!$wp_filesystem->is_file($zip)) {
        return;
    }
    
    // unzip child-theme
    $theme_root = NASA_THEME_PATH . '/../';
    $pathArrayString = str_replace(array('/', '\\'), '|', NASA_THEME_PATH);
    $themeNameArray = explode('|', $pathArrayString);
    $theme_name = end($themeNameArray);
    $theme_child = $theme_name . '-child';
    
    if (!$wp_filesystem->is_dir($theme_root . $theme_child)) {
        wp_mkdir_p($theme_root . $theme_child);
        unzip_file($zip, $theme_root . $theme_child);
    }
    
    // Active Child Theme
    if(is_dir($theme_root . $theme_child)) {
        switch_theme($theme_child);
    }
    
    nasa_theme_set_option_default();
    if(function_exists('nasa_theme_rebuilt_css_dynamic')) {
        nasa_theme_rebuilt_css_dynamic();
    }
}

function nasa_theme_set_option_default() {
    set_theme_mod('type_font_select', 'google');
    set_theme_mod('type_headings', 'Nunito Sans');
    set_theme_mod('type_texts', 'Nunito Sans');
    set_theme_mod('type_nav', 'Nunito Sans');
    set_theme_mod('type_alt', 'Nunito Sans');
    set_theme_mod('type_banner', 'Nunito Sans');
    
    set_theme_mod('header-type', '1');
    set_theme_mod('topbar_content', 'topbar');
    set_theme_mod('footer-type', 'footer-light-2');
    set_theme_mod('footer-mobile', 'footer-mobile');
    
    set_theme_mod('show_icon_cat_top', 'show-all-site');
    
    set_theme_mod('button_radius', '0');
    set_theme_mod('button_border', '1');
    set_theme_mod('input_radius', '0');
    
    set_theme_mod('facebook_url_follow', '#');
    set_theme_mod('twitter_url_follow', '#');
    set_theme_mod('pinterest_url_follow', '#');
    set_theme_mod('instagram_url', '#');
    
    set_theme_mod('enable_portfolio', '1');
    set_theme_mod('portfolio_columns', '5-cols');
    
    set_theme_mod('enable_nasa_mobile', '1');
    set_theme_mod('single_product_mobile', '1');
    
    update_option('yith_woocompare_compare_button_in_products_list', 'yes');
    update_option('woocommerce_enable_myaccount_registration', 'yes');
}
