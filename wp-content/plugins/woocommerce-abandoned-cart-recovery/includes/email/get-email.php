<?php
/**
 * Created by PhpStorm.
 * User: Villatheme-Thanh
 * Date: 25-05-19
 * Time: 3:37 PM
 */

namespace WACVP\Inc\Email;

use WACVP\Inc\Data;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Get_Email {

	protected static $instance = null;
	public $param;

	public function __construct() {
		add_action( 'wp_ajax_wacv_ajax_add_to_cart', array( $this, 'ajax_add_to_cart' ) );
		add_action( 'wp_ajax_nopriv_wacv_ajax_add_to_cart', array( $this, 'ajax_add_to_cart' ) );
		add_action( 'wp_head', array( $this, 'popup_get_email' ) );
		add_filter( 'woocommerce_add_to_cart_fragments', array( $this, 'print_add_to_cart_notice' ), 30 );
		add_action( 'template_redirect', array( $this, 'redirect' ) );
	}

	public function redirect() {
		if ( isset( $_POST['wacv_redirect'] ) ) {
			wp_safe_redirect( sanitize_text_field( $_POST['wacv_redirect'] ) );
			exit;
		}
	}

	public static function get_instance() {

		// If the single instance hasn't been set, set it now.
		if ( null == self::$instance ) {
			self::$instance = new self;
		}

		return self::$instance;
	}

	public function ajax_add_to_cart() {
		\WC_AJAX::get_refreshed_fragments();
		wp_die();
	}

	public function print_add_to_cart_notice( $fragment ) {
		if ( isset( $_POST['action'] ) && $_POST['action'] == 'wacv_ajax_add_to_cart' ) {
			ob_start();
			wc_print_notices();
			$notices            = ob_get_clean();
			$fragment['notice'] = $notices;
		}

		return $fragment;
	}

	public function popup_get_email() {
		if ( ! is_user_logged_in() ) {
			$this->param = Data::get_params();
			$cond[]      = is_shop() && $this->param['shop_page'];
			$cond[]      = is_product() && $this->param['single_page'];
			$cond[]      = is_cart() && $this->param['cart_page'];
			$cond[]      = is_front_page() && $this->param['front_page'];
			$cond[]      = is_home() && $this->param['front_page'];
			$cond[]      = is_product_category() && $this->param['category_page'];
			$cond[]      = is_page( explode( ',', str_replace( ' ', '', $this->param['popup_page_id'] ) ) );
			if ( array_sum( $cond ) ) {
				$this->front_end_enqueue();
				wc_get_template( 'popup-' . $this->param['template_popup'] . '.php', array( 'param' => $this->param ), '', WACVP_TEMPLATES );
			}
		}
	}

	public function front_end_enqueue() {
		$js_suffix = WP_DEBUG ? '.js' : '.min.js';
		wp_enqueue_script( 'wacv-fb-chekcbox', WACVP_JS . 'fb-checkbox-plugin' . $js_suffix, array( 'jquery' ), true );
		wp_enqueue_script( 'wacv-get-email', WACVP_JS . 'get-email' . $js_suffix, array( 'jquery' ), true, true );
		wp_enqueue_style( 'wacv-get-email', WACVP_CSS . 'get-email.css' );

		$data = Data::get_params();

		wp_localize_script( 'wacv-get-email', 'wacv_php_js',
			array(
				'ajax_url'       => admin_url( 'admin-ajax.php' ),
				'cartPage'       => wc_get_cart_url(),
				'checkoutPage'   => wc_get_checkout_url(),
				'dismissDelay'   => $data['dismiss_delay'] * 60,
				'redirect'       => $data['redirect_after_atc'] == 'to_cart_page' ? wc_get_cart_url() : ( $data['redirect_after_atc'] == 'to_checkout_page' ? wc_get_checkout_url() : '' ),
				'i18n_view_cart' => esc_attr__( 'View cart', 'woo-abandoned-cart-recovery' ),
				'emailField'     => $data['email_field'],
				'phoneField'     => $data['phone_field'],
				'style'          => $data['template_popup']
			) );

		wp_localize_script( 'wacv-fb-chekcbox', 'Fbook',
			array(
				'ajaxUrl'   => admin_url( 'admin-ajax.php' ),
				'appLang'   => $data['app_lang'],
				'appID'     => $data['app_id'],
				'pageID'    => $data['page_id'],
				'userToken' => $data['user_token'],
				'homeURL'   => home_url(),
			) );

		wp_localize_script( 'woocommerce-boost-sales-ajax-button', 'wbs_wacv', array( 'compatible' => true ) );
		//custom css

		$title_color        = $data['popup_title_color'];
		$sub_title_color    = $data['popup_sub_title_color'];
		$notice_color       = $data['popup_notice_color'];
		$bg_color           = $data['popup_bg_color'];
		$btn_color          = $data['popup_btn_color'];
		$btn_bg_color       = $data['popup_btn_bg_color'];
		$input_bg_color     = $data['popup_input_bg_color'];
		$input_border_color = $data['popup_input_border_color'];

		$css = ".wacv-get-email-title{color:$title_color}";
		$css .= ".wacv-get-email-sub-title{color:$sub_title_color}";
		$css .= ".wacv-email-invalid-notice{color:$notice_color}";
		$css .= ".wacv-modal-content{background-color:$bg_color}";
		$css .= ".wacv-get-email-btn{color:$btn_color; background-color:$btn_bg_color}";
		$css .= ".wacv-popup-input-email, .wacv-popup-input-phone-number{background-color:$input_bg_color !important; border: 1px solid $input_border_color !important}";

		wp_add_inline_style( 'wacv-get-email', $css );
	}

}