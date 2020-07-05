<?php
/**
 * Created by PhpStorm.
 * User: Villatheme-Thanh
 * Date: 26-03-19
 * Time: 4:14 PM
 */

namespace WACVP\Inc\Execute;

use WACVP\Inc\Aes_Ctr;
use WACVP\Inc\Query_DB;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Recovered {

	protected static $instance = null;

	public $query;

	private function __construct() {

		$this->query = Query_DB::get_instance();

//		add_action( 'init', array( $this, 'add_more_session_prop' ) );
		add_action( 'template_redirect', array( $this, 'handle_recover_cart' ) );
		add_action( 'template_redirect', array( $this, 'handle_unsubscribe' ) );
		add_action( 'template_redirect', array( $this, 'handle_tracking_open' ) );
		add_action( 'template_redirect', array( $this, 'handle_recover_order' ) );
	}

	public static function get_instance() {

		if ( null == self::$instance ) {
			self::$instance = new self;
		}

		return self::$instance;
	}

	public function handle_recover_cart( $template ) {

		if ( isset( $_GET['wacv_recover'] ) && $_GET['wacv_recover'] == 'cart_link' ) {
			if ( '' == session_id() ) {
				@session_start();
			}
			if ( isset( $_GET['valid'] ) ) {
				$pass          = get_option( 'wacv_private_key' );
				$validate_code = str_replace( ' ', '+', rawurldecode( sanitize_text_field( $_GET['valid'] ) ) );
				$validate_code = rawurldecode( Aes_Ctr::decrypt( $validate_code, $pass, 256 ) );

				$explode       = explode( '&', $validate_code );
				$acr_id        = isset( $explode[0] ) ? $explode[0] : '';
				$sent_email_id = isset( $explode[1] ) ? $explode[1] : '';
				$temp_id       = isset( $explode[2] ) ? $explode[2] : '';
				$coupon        = isset( $explode[3] ) ? $explode[3] : '';

				$this->query->update_email_tracking( $sent_email_id, 'clicked' );

				global $wpdb;

				$query = "SELECT * FROM {$this->query->cart_record_tb} WHERE id = %d LIMIT 1";
				$acr   = $wpdb->get_results( $wpdb->prepare( $query, $acr_id ) );
//				$this->query->update_abd_cart_record( array( 'abandoned_cart_time' => current_time( 'timestamp' ) ), array( 'id' => $acr_id ) );

				if ( count( $acr ) > 0 ) {
					$user_id = $acr[0]->user_id;

					if ( $user_id < 100000000 ) {
						wp_set_current_user( $user_id );
						if ( current_user_can( 'manage_options' ) ) {
							wp_safe_redirect( site_url() );
							exit;
						}
						wp_set_auth_cookie( $user_id );

						$saved_cart = get_user_meta( $user_id, '_woocommerce_persistent_cart_' . get_current_blog_id(), true );

						if ( ! $saved_cart ) {
							wp_safe_redirect( site_url() );
							exit;
						}

						$cart = WC()->session->cart;

						if ( empty( $cart ) || ! is_array( $cart ) || 0 === count( $cart ) ) {
							WC()->session->cart = $saved_cart['cart'];
						}

						if ( isset( $sign_in ) && is_wp_error( $sign_in ) ) {
							echo $sign_in->get_error_message();
							exit;
						}

						$this->query::set_session( 'wacv_order_type', 1 );
						$this->query::set_session( 'wacv_recover_id', $acr_id );
						$this->query::set_session( 'wacv_temp_id', $temp_id );

					} else {
						$rec_cart = json_decode( $acr[0]->abandoned_cart_info, true )['cart'];

						WC()->session->set_customer_session_cookie( true );
						$guest_info = $this->recover_get_info( $user_id );
						$this->query::set_session( 'cart', $rec_cart );
						$this->query::set_session( 'user_id', $user_id );
						$this->query::set_session( 'wacv_cart_record_id', $acr_id );
						$this->query::set_session( 'wacv_recover_id', $acr_id );
						$this->query::set_session( 'guest_info', $guest_info );
						$this->query::set_session( 'wacv_order_type', 1 );
						$this->query::set_session( 'wacv_temp_id', $temp_id );
					}

					if ( $coupon ) {
						wc()->cart->apply_coupon( $coupon );
					}

					wp_safe_redirect( wc_get_checkout_url() );
					exit;
				}
			}
		}

//		return $template;
	}

	public function recover_get_info( $user_id ) {
		$result = $this->query->get_guest_info( $user_id );
		$result = $result[0];

		return $customer = array(
			"id"                  => $result->id,
			"date_modified"       => '',
			"billing_postcode"    => $result->billing_zipcode,
			"billing_city"        => $result->billing_city,
			"billing_address_1"   => $result->billing_address_1,
			"billing_address"     => $result->billing_address_1,
			"billing_address_2"   => $result->billing_address_2,
			"billing_state"       => $result->billing_city,
			"billing_country"     => $result->billing_county,
			"shipping_postcode"   => $result->shipping_zipcode,
			"shipping_city"       => $result->shipping_city,
			"shipping_address_1"  => $result->shipping_address_1,
			"shipping_address"    => $result->shipping_address_1,
			"shipping_address_2"  => $result->shipping_address_2,
			"shipping_state"      => $result->shipping_city,
			"shipping_country"    => $result->shipping_county,
			"billing_first_name"  => $result->billing_first_name,
			"billing_last_name"   => $result->billing_last_name,
			"billing_company"     => $result->billing_company_name,
			"billing_phone"       => $result->billing_phone,
			"billing_email"       => $result->billing_email,
			"shipping_first_name" => $result->shipping_first_name,
			"shipping_last_name"  => $result->shipping_last_name,
			"shipping_company"    => $result->shipping_company_name,
			"user_ref"            => $result->user_ref
		);
	}

	public function handle_unsubscribe( $template ) {
		if ( isset( $_GET['wacv_unsubscribe'] ) ) {
			$pass   = get_option( 'wacv_private_key' );
			$link   = str_replace( ' ', '+', rawurldecode( sanitize_text_field( $_GET['wacv_unsubscribe'] ) ) );
			$acr_id = rawurldecode( Aes_Ctr::decrypt( $link, $pass, 256 ) );
			$this->query->update_abd_cart_record( array( 'unsubscribe_link' => 1 ), array( 'id' => $acr_id ) );
		}

		return $template;
	}

	public function handle_tracking_open( $template ) {
		if ( isset( $_GET['wacv_open_email'] ) ) {
			$pass          = get_option( 'wacv_private_key' );
			$validate_code = str_replace( ' ', '+', rawurldecode( sanitize_text_field( $_GET['wacv_open_email'] ) ) );
			$validate_code = rawurldecode( Aes_Ctr::decrypt( $validate_code, $pass, 256 ) );
			$pos_acr       = strpos( $validate_code, '&' );
			$pos_email_id  = strpos( $validate_code, '&', $pos_acr + 1 );

			$acr_id        = intval( substr( $validate_code, 0, $pos_acr ) );
			$sent_email_id = $pos_email_id ? substr( $validate_code, $pos_acr + 1, $pos_email_id - $pos_acr - 1 ) : substr( $validate_code, $pos_acr + 1 );
			$this->query->update_email_tracking( $sent_email_id, 'opened' );

		}

		return $template;
	}

	public function handle_recover_order( $template ) {
		if ( isset( $_GET['wacv_recover'] ) && $_GET['wacv_recover'] == 'order_link' ) {
			if ( isset( $_GET['valid'] ) ) {
				$pass             = get_option( 'wacv_private_key' );
				$validate_code    = str_replace( ' ', '+', rawurldecode( sanitize_text_field( $_GET['valid'] ) ) );
				$validate_code    = rawurldecode( Aes_Ctr::decrypt( $validate_code, $pass, 256 ) );
				$order_id_pos     = strpos( $validate_code, '&' );
				$sent_mail_id_pos = strpos( $validate_code, '&', $order_id_pos + 1 );
				$order_id         = intval( substr( $validate_code, 0, $order_id_pos ) );
				$sent_email_id    = $sent_mail_id_pos ? substr( $validate_code, 0, $order_id_pos ) : substr( $validate_code, $order_id_pos + 1 );
				$order            = wc_get_order( $order_id );
				if ( $order ) {
					$check_stt = $order->get_status();

					$this->query->update_email_tracking( $sent_email_id, 'clicked' );

					if ( $check_stt == 'cancelled' ) {
						$order->update_status( 'pending' );
					}

					$checkout_url = ( $order->get_checkout_payment_url() );
					wp_safe_redirect( $checkout_url );
				} else {
					wp_safe_redirect( home_url() );
				}
				exit;
			} elseif ( isset( $_GET['unsubscribe'] ) ) {
				$pass          = get_option( 'wacv_private_key' );
				$validate_code = str_replace( ' ', '+', rawurldecode( sanitize_text_field( $_GET['unsubscribe'] ) ) );
				$validate_code = rawurldecode( Aes_Ctr::decrypt( $validate_code, $pass, 256 ) );
				$order_id      = $validate_code;
				update_post_meta( $order_id, 'wacv_reminder_unsubscribe', 1 );
				wp_safe_redirect( home_url() );
				exit;
			}
		}

		return $template;
	}

	public function add_more_session_prop() {
		if ( is_admin() ) {
			return;
		}
		if ( '' === session_id() ) {
			@session_start();
		}
		if ( isset( $_SESSION['wacv_coupon'] ) ) {
			$this->query::set_session( 'applied_coupons', ( array( $_SESSION['wacv_coupon'] ) ) );
			unset( $_SESSION['wacv_coupon'] );
		}
		if ( isset( $_SESSION['wacv_order_type'] ) ) {
			$this->query::set_session( 'wacv_order_type', $_SESSION['wacv_order_type'] );
			unset( $_SESSION['wacv_order_type'] );
		}
		if ( isset( $_SESSION['wacv_recover_id'] ) ) {
			$this->query::set_session( 'wacv_recover_id', $_SESSION['wacv_recover_id'] );
			unset( $_SESSION['wacv_recover_id'] );
		}
		if ( isset( $_SESSION['wacv_temp_id'] ) ) {
			$this->query::set_session( 'wacv_temp_id', $_SESSION['wacv_temp_id'] );
			unset( $_SESSION['wacv_temp_id'] );
		}

//		session_destroy();
	}

}
