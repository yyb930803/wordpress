<?php
defined('NASA_VERSION') or define('NASA_VERSION', '2.1.8');

/* Check if WooCommerce is active */
defined('NASA_WOO_ACTIVED') or define('NASA_WOO_ACTIVED', (bool) class_exists('WooCommerce'));
defined('NASA_WISHLIST_ENABLE') or define('NASA_WISHLIST_ENABLE', (bool) (defined('YITH_WCWL') && get_option('yith_wcwl_enabled') == 'yes'));

/* Check if nasa-core is active */
defined('NASA_CORE_ACTIVED') or define('NASA_CORE_ACTIVED', false);
defined('NASA_CORE_IN_ADMIN') or define('NASA_CORE_IN_ADMIN', is_admin());

/* user info */
defined('NASA_CORE_USER_LOGIGED') or define('NASA_CORE_USER_LOGIGED', is_user_logged_in());

/* bundle type product */
defined('NASA_COMBO_TYPE') or define('NASA_COMBO_TYPE', 'yith_bundle');

/* Nasa theme prefix use for nasa-core */
defined('NASA_THEME_PREFIX') or define('NASA_THEME_PREFIX', 'elessi');

/* Time now */
defined('NASA_TIME_NOW') or define('NASA_TIME_NOW', time());

/**
 *
 * nasa_upload_dir
 */
if (!isset($nasa_upload_dir)) {
    $nasa_upload_dir = wp_upload_dir();
}

/**
 * Cache plugin support
 */
function elessi_plugins_cache_support() {
    /**
     * Check WP Super Cache active
     */
    global $super_cache_enabled;
    $super_cache_enabled = isset($super_cache_enabled) ? $super_cache_enabled : false;
    
    $plugin_cache_support = (
        /**
         * Check W3 Total Cache active
         */
        (defined('W3TC') && W3TC) ||
            
        /**
         * Check WP Fastest Cache
         */
        class_exists('WpFastestCache') ||
            
        /**
         * Check WP Super Cache active
         */
        (defined('WP_CACHE') && WP_CACHE && $super_cache_enabled) ||
            
        /**
         * Check AutoptimizeCache active
         */
        class_exists('autoptimizeCache') ||
            
        /**
         * Check WP_ROCKET active
         */
        (defined('WP_ROCKET_SLUG') && WP_ROCKET_SLUG) ||
        
        /**
         * Check SG_CachePress
         */
        class_exists('SG_CachePress') ||
        
        /**
         * Check LiteSpeed Cache
         */
        class_exists('LiteSpeed_Cache')
    );
    
    return apply_filters('elessi_plugins_cache_support', $plugin_cache_support);
}

// Init $nasa_opt
$GLOBALS['nasa_opt'] = elessi_get_options();
function elessi_get_options() {
    $options = get_theme_mods();
    
    if(!empty($options)) {
        foreach ($options as $key => $value) {
            if (is_string($value)) {
                $options[$key] = str_replace(
                    array(
                        '[site_url]', 
                        '[site_url_secure]',
                    ),
                    array(
                        site_url('', 'http'),
                        site_url('', 'https'),
                    ),
                    $value
                );
            }
        }
    }
    
    /**
     * Check Mobile Detect
     */
    $options['nasa_in_mobile'] = false;
    if (defined('NASA_IS_PHONE') && NASA_IS_PHONE && (!isset($options['enable_nasa_mobile']) || $options['enable_nasa_mobile'])) {
        $options['nasa_in_mobile'] = true;
        
        $options['showing_info_top'] = false;
        $options['enable_change_view'] = false;
        $options['breadcrumb_row'] = 'single';
    }
    
    /**
     * Check WP Super Cache active
     */
    global $super_cache_enabled;
    $super_cache_enabled = isset($super_cache_enabled) ? $super_cache_enabled : false;
    
    if(!defined('NASA_PLG_CACHE_ACTIVE') && elessi_plugins_cache_support()) {
        define('NASA_PLG_CACHE_ACTIVE', true);
    }
    
    if(defined('NASA_PLG_CACHE_ACTIVE') && NASA_PLG_CACHE_ACTIVE) {
        /**
         * Disable optimized speed
         */
        $options['enable_optimized_speed'] = '0';
    }
    
    return apply_filters('nasa_theme_options', $options);
}

/**
 * Global Nasa Theme
 */
function elessi_init_global() {
    global $nasa_opt;
    
    $hoverProductEffect = array('hover-fade', 'hover-flip', 'hover-bottom-to-top', 'no');
    
    /**
     * Animated effect
     */
    $nasa_animated_products = 
        isset($_REQUEST['effect-product']) && in_array(
            $_REQUEST['effect-product'],
            $hoverProductEffect
        ) ? $_REQUEST['effect-product'] :
        (isset($nasa_opt['animated_products']) ? $nasa_opt['animated_products'] : '');
    
    if($nasa_animated_products == 'no') {
        $nasa_animated_products = '';
    }
    
    $GLOBALS['nasa_animated_products'] = $nasa_animated_products;
    
    /**
     * $loadmoreStyle 
     */
    $GLOBALS['loadmoreStyle'] = array('infinite', 'load-more');
}

elessi_init_global();

/**
 * Convert css content
 * 
 * @param type $css
 * @return type
 */
function elessi_convert_css($css) {
    $css = strip_tags($css);
    $css = preg_replace('!/\*[^*]*\*+([^/][^*]*\*+)*/!', '', $css);
    $css = str_replace(': ', ':', $css);
    $css = str_replace(array("\r\n", "\r", "\n", "\t", '  ', '    ', '    '), '', $css);

    return $css;
}

/* wp-admin loading $nasa-opt =============== */
if(NASA_CORE_IN_ADMIN){
    require_once ELESSI_THEME_PATH . '/admin/index.php';
}

// **********************************************************************// 
// ! Add Font Awesome, Font Pe7s
// **********************************************************************//
add_action('wp_enqueue_scripts', 'elessi_add_fonts_style');
function elessi_add_fonts_style() {
    global $nasa_opt;
    /**
     * Add Font Awesome
     */
    wp_enqueue_style('elessi-font-awesome-style', ELESSI_THEME_URI . '/assets/font-awesome-4.7.0/css/font-awesome.min.css', array(), false, 'all');

    /**
     * Add Font Awesome 5.0.13
     */
    if(isset($nasa_opt['include_font_awesome_new']) && $nasa_opt['include_font_awesome_new']) {
        wp_enqueue_style('elessi-font-awesome-5-free-style', ELESSI_THEME_URI . '/assets/font-awesome-5.0.13/css/fontawesome-all.min.css', array(), false, 'all');
    }

    /**
     * Add Font Pe7s
     */
    wp_enqueue_style('elessi-font-pe7s-style', ELESSI_THEME_URI . '/assets/font-pe-icon-7-stroke/css/pe-icon-7-stroke.css', array(), false, 'all');
}

/**
 * Dequeue scripts and styles
 */
add_action('wp_enqueue_scripts', 'elessi_dequeue_scripts', 100);
function elessi_dequeue_scripts() {
    global $nasa_opt;
    
    /**
     * Ignore css
     */
    if (!NASA_CORE_IN_ADMIN) {
        wp_deregister_style('woocommerce-layout');
        wp_deregister_style('woocommerce-smallscreen');
        wp_deregister_style('woocommerce-general');
    }
    
    /**
     * Dequeue contact-form-7 css
     */
    if(function_exists('wpcf7_style_is') && wpcf7_style_is()) {
        wp_dequeue_style('contact-form-7');
    }
    
    /**
     * Dequeue YITH WooCommerce Product Compare colorbox css
     */
    if(class_exists('YITH_Woocompare_Frontend') && (!isset($nasa_opt['nasa-product-compare']) || $nasa_opt['nasa-product-compare'])) {
        wp_dequeue_style('jquery-colorbox');
        wp_dequeue_script('jquery-colorbox');
    }
    
    /**
     * Dequeue YITH WooCommerce Product Wishlist css
     */
    if(NASA_WISHLIST_ENABLE && !defined('YITH_WCWL_PREMIUM')) {
        wp_deregister_style('jquery-selectBox');
        wp_deregister_style('yith-wcwl-font-awesome');
        wp_deregister_style('yith-wcwl-font-awesome-ie7');
        wp_deregister_style('yith-wcwl-main');
    }
    
    /**
     * Dequeue YITH WooCommerce Product Bundles css
     */
    if(defined('YITH_WCPB')) {
        wp_deregister_style('yith_wcpb_bundle_frontend_style');
    }
}

/**
 * enqueue scripts
 */
add_action('wp_enqueue_scripts', 'elessi_enqueue_scripts', 998);
function elessi_enqueue_scripts() {
    global $nasa_opt;
    
    $themeVersion = isset($nasa_opt['js_theme_version']) && $nasa_opt['js_theme_version'] ? NASA_VERSION : null;
    
    wp_enqueue_script('jquery-cookie', ELESSI_THEME_URI . '/assets/js/min/jquery.cookie.min.js', array('jquery'), null, true);
    wp_enqueue_script('modernizr', ELESSI_THEME_URI . '/assets/js/min/modernizr.min.js', array('jquery'), null, true);
    
    wp_enqueue_script('jquery-JRespond', ELESSI_THEME_URI . '/assets/js/min/jquery.jRespond.min.js', array('jquery'), null, true);
    wp_enqueue_script('jquery-waypoints', ELESSI_THEME_URI . '/assets/js/min/jquery.waypoints.min.js', array('jquery'), null, true);
    wp_enqueue_script('jquery-tipr', ELESSI_THEME_URI . '/assets/js/min/jquery.tipr.min.js', array('jquery'), null, true);
    wp_enqueue_script('jquery-variations', ELESSI_THEME_URI . '/assets/js/min/jquery.variations.min.js', array('jquery'), null, true);
    
    if(class_exists('WC_AJAX')) {
        $params_variations = array(
            'wc_ajax_url' => WC_AJAX::get_endpoint('%%endpoint%%'),
            'i18n_no_matching_variations_text' => esc_attr__('Sorry, no products matched your selection. Please choose a different combination.', 'elessi-theme'),
            'i18n_make_a_selection_text' => esc_attr__('Please select some product options before adding this product to your cart.', 'elessi-theme'),
            'i18n_unavailable_text' => esc_attr__('Sorry, this product is unavailable. Please choose a different combination.', 'elessi-theme')
        );
        wp_add_inline_script('jquery-variations', 'var nasa_params_variations=' . json_encode($params_variations) . '; var _quicked_gallery = true;', 'before');
    }
    
    /**
     * magnific popup
     */
    if(!wp_script_is('jquery-magnific-popup')) {
        wp_enqueue_script('jquery-magnific-popup', ELESSI_THEME_URI . '/assets/js/min/jquery.magnific-popup.js', array('jquery'), null, true);
    }
    
    /**
     * owl carousel slider
     */
    if(!wp_script_is('owl-carousel')) {
        wp_enqueue_script('owl-carousel', ELESSI_THEME_URI . '/assets/js/min/owl.carousel.min.js', array('jquery'), null, true);
    }
    
    /**
     * Slick slider
     */
    if(!wp_script_is('jquery-slick')) {
        wp_enqueue_script('jquery-slick', ELESSI_THEME_URI . '/assets/js/min/jquery.slick.min.js', array('jquery'), null, true);
    }
    
    /**
     * Parallax
     */
    wp_enqueue_script('jquery-stellar', ELESSI_THEME_URI . '/assets/js/min/jquery.stellar.min.js', array('jquery'), null, true);
    
    /**
     * Countdown js
     */
    if(!wp_script_is('countdown')) {
        wp_enqueue_script('countdown', ELESSI_THEME_URI . '/assets/js/min/countdown.min.js', array('jquery'), null, true);
        wp_localize_script(
            'elessi-countdown', 'nasa_countdown_l10n',
            array(
                'days'      => esc_html__('Days', 'elessi-theme'),
                'months'    => esc_html__('Months', 'elessi-theme'),
                'weeks'     => esc_html__('Weeks', 'elessi-theme'),
                'years'     => esc_html__('Years', 'elessi-theme'),
                'hours'     => esc_html__('Hours', 'elessi-theme'),
                'minutes'   => esc_html__('Mins', 'elessi-theme'),
                'seconds'   => esc_html__('Secs', 'elessi-theme'),
                'day'       => esc_html__('Day', 'elessi-theme'),
                'month'     => esc_html__('Month', 'elessi-theme'),
                'week'      => esc_html__('Week', 'elessi-theme'),
                'year'      => esc_html__('Year', 'elessi-theme'),
                'hour'      => esc_html__('Hour', 'elessi-theme'),
                'minute'    => esc_html__('Min', 'elessi-theme'),
                'second'    => esc_html__('Sec', 'elessi-theme')
            )
        );
    }
    
    /**
     * Easy zoom js
     */
    wp_enqueue_script('jquery-easyzoom', ELESSI_THEME_URI . '/assets/js/min/jquery.easyzoom.min.js', array('jquery'), null, true);
    
    /**
     * Wow js
     */
    if(!isset($nasa_opt['disable_wow']) || !$nasa_opt['disable_wow']) {
        wp_enqueue_script('wow', ELESSI_THEME_URI . '/assets/js/min/wow.min.js', array('jquery'), null, true);
    }
    
    /**
     * masonry-isotope
     */
    if(!wp_script_is('jquery-masonry-isotope')) {
        wp_enqueue_script('jquery-masonry-isotope', ELESSI_THEME_URI . '/assets/js/min/jquery.masonry-isotope.min.js', array('jquery'), null, true);
    }
    
    /**
     * Select2
     */
    if(NASA_WOO_ACTIVED && !wp_script_is('select2')) {
        wp_enqueue_script('select2', WC()->plugin_url() . '/assets/js/select2/select2.full.min.js', array('jquery'), null, true);
        wp_enqueue_style('select2');
    }
    
    /**
     * Theme js
     */
    wp_enqueue_script('elessi-functions-js', ELESSI_THEME_URI . '/assets/js/min/functions.min.js', array('jquery'), $themeVersion, true);
    wp_enqueue_script('elessi-js', ELESSI_THEME_URI . '/assets/js/min/main.min.js', array('jquery'), $themeVersion, true);
    $nasa_theme_js = 'var ajaxurl="' . esc_url(admin_url('admin-ajax.php')) . '";';
    wp_add_inline_script('elessi-functions-js', $nasa_theme_js, 'before');
    
    /**
     * Add css comment reply
     */
    if (is_singular() && comments_open() && get_option('thread_comments')) {
        wp_enqueue_script('comment-reply');
    }
}

/**
 * Main Style and RTL Style
 */
add_action('wp_enqueue_scripts', 'elessi_enqueue_style', 998);
function elessi_enqueue_style() {
    global $nasa_opt;
    
    // MAIN CSS
    wp_enqueue_style('elessi-style', get_stylesheet_uri());
    
    // RTL CSS
    if (
        (isset($nasa_opt['nasa_rtl']) && $nasa_opt['nasa_rtl']) ||
        (isset($_REQUEST['rtl']) && $_REQUEST['rtl'] == '1')
    ) {
        wp_enqueue_style('elessi-style-rtl', ELESSI_THEME_URI . '/style-rtl.css', array('elessi-style'));
    }
}

/**
 * Page Coming Soon
 */
add_action('init', 'elessi_offline_site', 1);
function elessi_offline_site() {
    global $nasa_opt;
    
    /**
     * Check online site
     */
    if (!isset($nasa_opt['site_offline']) || !$nasa_opt['site_offline']) {
        return;
    }
    
    /**
     * Check is admin or logged in
     */
    if (NASA_CORE_IN_ADMIN || NASA_CORE_USER_LOGIGED) {
        return;
    }
    
    /**
     * Check time
     */
    $time = false;
    if (isset($nasa_opt['coming_soon_time']) && $nasa_opt['coming_soon_time']) {
        $time = strtotime($nasa_opt['coming_soon_time']);
        if ($time && $time < time()) {
            return;
        }
    }
    
    /**
     * Check in Login page
     */
    if ($GLOBALS['pagenow'] === 'wp-login.php') {
        return;
    }

    $file = ELESSI_CHILD_PATH . '/coming-soon/coming-soon.php';
    include_once is_file($file) ? $file : ELESSI_THEME_PATH . '/coming-soon/coming-soon.php';
    
    die();
}

// Default sidebars
add_action('widgets_init', 'elessi_widgets_sidebars_init');
function elessi_widgets_sidebars_init() {
    register_sidebar(array(
        'name' => esc_html__('Blog Sidebar', 'elessi-theme'),
        'id' => 'blog-sidebar',
        'before_widget' => '<div id="%1$s" class="widget %2$s"><a href="javascript:void(0);" class="nasa-toggle-widget"></a><div class="nasa-open-toggle">',
        'before_title'  => '<h2 class="widgettitle">',
        'after_title'   => '</h2>',
        'after_widget'  => '</div></div>'
    ));
    
    register_sidebar(array(
        'name' => esc_html__('Shop Sidebar', 'elessi-theme'),
        'id' => 'shop-sidebar',
        'before_widget' => '<div id="%1$s" class="widget %2$s"><a href="javascript:void(0);" class="nasa-toggle-widget"></a><div class="nasa-open-toggle">',
        'before_title'  => '<h2 class="widgettitle">',
        'after_title'   => '</h2>',
        'after_widget'  => '</div></div>'
    ));
    
    register_sidebar(array(
        'name' => esc_html__('Product Sidebar', 'elessi-theme'),
        'id' => 'product-sidebar',
        'before_widget' => '<div id="%1$s" class="widget %2$s"><a href="javascript:void(0);" class="nasa-toggle-widget"></a><div class="nasa-open-toggle">',
        'before_title'  => '<h2 class="widgettitle">',
        'after_title'   => '</h2>',
        'after_widget'  => '</div></div>'
    ));
}

require_once ELESSI_THEME_PATH . '/includes/nasa-google-fonts.php';

// Includes Woocommerce widgets custom
require_once ELESSI_THEME_PATH . '/widgets/wg-nasa-recent-posts.php';
require_once ELESSI_THEME_PATH . '/widgets/wg-nasa-product-categories.php';
require_once ELESSI_THEME_PATH . '/widgets/wg-nasa-product-brands.php';
require_once ELESSI_THEME_PATH . '/widgets/wg-nasa-product-filter-price.php';
require_once ELESSI_THEME_PATH . '/widgets/wg-nasa-product-filter-price-list.php';
require_once ELESSI_THEME_PATH . '/widgets/wg-nasa-product-filter-variations.php';
require_once ELESSI_THEME_PATH . '/widgets/wg-nasa-tag-cloud.php';
require_once ELESSI_THEME_PATH . '/widgets/wg-nasa-reset-filter.php';
