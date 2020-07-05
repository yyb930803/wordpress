<?php
/*
 *
 * @package nasatheme - elessi-theme
 */

/* Define DIR AND URI OF THEME */
define('ELESSI_THEME_PATH', get_template_directory());
define('ELESSI_CHILD_PATH', get_stylesheet_directory());
define('ELESSI_THEME_URI', get_template_directory_uri());
defined('NASA_IS_PHONE') or define('NASA_IS_PHONE', false);

add_filter('gettext', 'blogwpTraduzioneWordPress');
add_filter('ngettext', 'blogwpTraduzioneWordPress');
function blogwpTraduzioneWordPress($stringaDaTradurre) {
    $stringaDaTradurre = str_ireplace('pezzi disponibili', 'disponibili', $stringaDaTradurre);
    return $stringaDaTradurre;
 }

add_action( 'template_redirect', 'define_default_payment_gateway' );
function define_default_payment_gateway(){
    if( is_checkout() && ! is_wc_endpoint_url() ) {
        // HERE define the default payment gateway ID
        $default_payment_id = 'xpay';

        WC()->session->set( 'chosen_payment_method', $default_payment_id );
    }
}

/**
 * Exclude products from a particular category on the shop page
 */
function custom_pre_get_posts_query( $q ) {

    $tax_query = (array) $q->get( 'tax_query' );

    $tax_query[] = array(
           'taxonomy' => 'product_cat',
           'field' => 'slug',
           'terms' => array( 'confezione-regalo-magnum' ), // Don't display products in the clothing category on the shop page.
           'operator' => 'NOT IN'
    );


    $q->set( 'tax_query', $tax_query );

}
add_action( 'woocommerce_product_query', 'custom_pre_get_posts_query' ); 

function custom_pre_get_posts_query2( $q ) {

    $tax_query = (array) $q->get( 'tax_query' );

    $tax_query[] = array(
           'taxonomy' => 'product_cat',
           'field' => 'slug',
           'terms' => array( 'confezione-regalo-base' ), // Don't display products in the clothing category on the shop page.
           'operator' => 'NOT IN'
    );


    $q->set( 'tax_query', $tax_query );

}
add_action( 'woocommerce_product_query', 'custom_pre_get_posts_query2' ); 




function wc_varb_price_range( $wcv_price, $product ) {
 
 
    $wcv_reg_min_price = $product->get_variation_regular_price( 'max', true );
    $wcv_min_sale_price    = $product->get_variation_sale_price( 'max', true );
    $wcv_max_price = $product->get_variation_price( 'max', true );
    $wcv_min_price = $product->get_variation_price( 'min', true );
 
    $wcv_price = ( $wcv_min_sale_price == $wcv_reg_min_price ) ?
        wc_price( $wcv_reg_min_price ) :
        '<del>' . wc_price( $wcv_reg_min_price ) . '</del>' . '<ins>' . wc_price( $wcv_min_sale_price ) . '</ins>';
 
    return ( $wcv_min_price == $wcv_max_price ) ?
        $wcv_price :
        sprintf('%s%s', $prefix, $wcv_price);
}
 
add_filter( 'woocommerce_variable_sale_price_html', 'wc_varb_price_range', 10, 2 );
add_filter( 'woocommerce_variable_price_html', 'wc_varb_price_range', 10, 2 );

add_filter( 'woocommerce_show_variation_price', '__return_true' );
function nt_product_attributes() {
global $product;
    if ( $product->has_attributes() ) {

        $attributes = ( object ) array (
        'tipo'              => $product->get_attribute( 'pa_tipo' ),
        );
    return $attributes;
    }
}
/* Global $content_width */
if (!isset($content_width)){
    $content_width = 1200; /* pixels */
}



/*
add_action('template_redirect', 'wpse_redirect_posts_to_new_page');
function wpse_redirect_posts_to_new_page() {
	$user = wp_get_current_user();
  	if ( ! is_single() || get_post_type() != 'post' || current_user_can('editor') || current_user_can('administrator') )
    return;
  	wp_redirect('https://winefully.com/member-only');
  exit;
}
*/



function js_hide_admin_bar( $show ) {
	if ( ! current_user_can( 'administrator' ) ) {
		return false;
	}
	return $show;
}
add_filter( 'show_admin_bar', 'js_hide_admin_bar' );

add_action('admin_init', 'wpse74389_check_username');
function wpse74389_check_username()
{
    $user = wp_get_current_user();

    if($user && isset($user->user_login) && 'marcello' == $user->user_login || 'winefully' == $user->user_login)  {
        wp_enqueue_style('admin-styles', get_template_directory_uri().'/marcello.css?v=897249');
    }
}

/**
 * Options theme
 */
require_once ELESSI_THEME_PATH . '/options/nasa-options.php';



add_action( 'woocommerce_archive_description', 'woocommerce_category_image', 2 );
function woocommerce_category_image() {
    if ( is_product_category() ){
	    global $wp_query;
	    $cat = $wp_query->get_queried_object();
	    $thumbnail_id = get_term_meta( $cat->term_id, 'thumbnail_id', true );
	    $image = wp_get_attachment_url( $thumbnail_id );
		
	    if ( $image ) {
		    echo '<img src="' . $image . '" alt="' . $cat->name . '" />';
		}
	}
}



add_action( 'woocommerce_archive_description', 'woocommerce_category_title', 4 );
function woocommerce_category_title() {
    if ( is_product_category() ){
	    global $wp_query;
	    $cat = $wp_query->get_queried_object();
	    echo '<div class="containertitolocantina"><h1 class="titolocantina" />' . $cat->name . '</h1></div>';
	}
}

add_filter( 'avatar_defaults', 'new_gravatar' );
function new_gravatar ($avatar_defaults) {
$myavatar = 'https://winefully.com/wp-content/uploads/2019/11/logo_home.png';
$avatar_defaults[$myavatar] = "Default Gravatar";
return $avatar_defaults;
}




add_action('after_setup_theme', 'elessi_setup');
if (!function_exists('elessi_setup')) :

    function elessi_setup() {
        load_theme_textdomain('elessi-theme', ELESSI_THEME_PATH . '/languages');
        add_theme_support('woocommerce');
        add_theme_support('automatic-feed-links');

        add_theme_support('post-thumbnails');
        add_theme_support('title-tag');
        add_theme_support('custom-background');
        add_theme_support('custom-header');

        register_nav_menus(
            array(
                'primary' => esc_html__('Main Menu', 'elessi-theme'),
                'vetical-menu' => esc_html__('Vertical Menu', 'elessi-theme'),
                'topbar-menu' => esc_html__('Top Menu - Only show level 1', 'elessi-theme')
            )
        );
        
        require_once ELESSI_THEME_PATH . '/cores/nasa-custom-wc-ajax.php';
        require_once ELESSI_THEME_PATH . '/cores/nasa-dynamic-style.php';
        require_once ELESSI_THEME_PATH . '/cores/nasa-widget-functions.php';
        require_once ELESSI_THEME_PATH . '/cores/nasa-theme-options.php';
        require_once ELESSI_THEME_PATH . '/cores/nasa-theme-functions.php';
        require_once ELESSI_THEME_PATH . '/cores/nasa-woo-functions.php';
        require_once ELESSI_THEME_PATH . '/cores/nasa-shop-ajax.php';
        require_once ELESSI_THEME_PATH . '/cores/nasa-theme-headers.php';
        require_once ELESSI_THEME_PATH . '/cores/nasa-theme-footers.php';
    }

endif;


	