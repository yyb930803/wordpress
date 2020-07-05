<?php
/**
 * Created by PhpStorm.
 * User: Villatheme-Thanh
 * Date: 20-03-19
 * Time: 9:35 AM
 */

namespace WACVP\Inc\Execute;

use WACVP\Inc\Query_DB;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Guest {

	protected static $instance = null;

	public $query;

	private function __construct() {

		$this->query = Query_DB::get_instance();

		add_action( 'wp_ajax_nopriv_wacv_get_info', array( $this, 'get_info' ) );

		add_action( 'wp_ajax_wacv_get_info', array( $this, 'get_info' ) );

		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_file' ) );

		add_filter( 'woocommerce_checkout_fields', array( $this, 'guest_checkout_fields' ) );

		add_action( 'init', array( $this, 'get_email' ) );
	}

	public static function get_instance() {

		// If the single instance hasn't been set, set it now.
		if ( null == self::$instance ) {
			self::$instance = new self;
		}

		return self::$instance;
	}

	public function enqueue_file() {
		if ( is_checkout() ) {
			wp_enqueue_script( WACVP_SLUG . '-get-guest-info', WACVP_JS . 'get-guest-info.js', array( 'jquery' ), '', true );
			wp_localize_script( WACVP_SLUG . '-get-guest-info', 'wacv_localize', array( 'ajax_url' => admin_url( 'admin-ajax.php?action=wacv_ajax' ) ) );
		}
	}

	public function get_email() {
		//Compatible with Lucky wheel
		if ( isset( $_GET['action'] ) && $_GET['action'] == 'wlwl_get_email' ) {
			$data                       = wc_clean( $_POST );
			$data['billing_email']      = isset( $data['user_email'] ) ? $data['user_email'] : '';
			$data['billing_first_name'] = isset( $data['user_name'] ) ? $data['user_name'] : '';
			$this->subscribe_data( $data );
		}
		//Compatible with coupon box
		if ( isset( $_GET['action'] ) && $_GET['action'] == 'wcb_email' ) {
			$data                  = wc_clean( $_POST );
			$data['billing_email'] = isset( $data['email'] ) ? $data['email'] : '';
			$this->subscribe_data( $data );
		}

		if ( is_plugin_active( 'mailchimp-for-wp/mailchimp-for-wp.php' ) && isset( $_POST['_mc4wp_form_id'] ) ) {
			$data                       = wc_clean( $_POST );
			$data['billing_email']      = isset( $data['EMAIL'] ) ? $data['EMAIL'] : '';
			$data['billing_phone']      = isset( $data['PHONE'] ) ? $data['PHONE'] : '';
			$data['billing_city']       = isset( $data['ADDRESS']['city'] ) ? $data['ADDRESS']['city'] : '';
			$data['billing_country']    = isset( $data['ADDRESS']['country'] ) ? $data['ADDRESS']['country'] : '';
			$data['billing_first_name'] = isset( $data['FNAME'] ) ? $data['FNAME'] : '';
			$data['billing_last_name']  = isset( $data['LNAME'] ) ? $data['LNAME'] : '';
			$this->subscribe_data( $data );
		}
	}

	public function subscribe_data( $data ) {
		$this->save_info( $data );
		$cookie_time = current_time( 'timestamp' ) + 86400;
		setCookie( 'wacv_get_email', true, $cookie_time, '/' );
	}

	public function save_info( $data ) {
		$update_user = '';
		if ( ! is_user_logged_in() ) {
			global $woocommerce;
			if ( session_id() == '' ) {
				session_start();
			}

			WC()->session->set_customer_session_cookie( true );

			$guest_info = array();
			$patterns   = array(
				'billing_first_name',
				'billing_last_name',
				'billing_company',
				'billing_address_1',
				'billing_address_2',
				'billing_city',
				'billing_state',
				'billing_postcode',
				'billing_country',
				'billing_phone',
				'billing_email',
				'order_notes',
				'shipping_first_name',
				'shipping_last_name',
				'shipping_company',
				'shipping_address_1',
				'shipping_address_2',
				'shipping_city',
				'shipping_state',
				'shipping_postcode',
				'shipping_country',
				'ship_to_billing',
				'user_ref'
			);
			foreach ( $data as $key => $value ) {
				if ( in_array( $key, $patterns ) && $data[ $key ] != '' ) {
					$guest_info[ $key ] = sanitize_text_field( $data[ $key ] );
				}
			}

			$this->query::set_session( 'guest_info', $guest_info );

			$email_address = $this->query::get_session( 'guest_info' )['billing_email'];
			$email_address = $email_address ? $email_address : $guest_info['billing_email'];

			$user_ref = $this->query::get_session( 'guest_info' );
			$user_ref = isset( $user_ref['user_ref'] ) ? $user_ref['user_ref'] : '';
			$user_ref = $user_ref ? $user_ref : ( isset( $guest_info['user_ref'] ) ? $guest_info['user_ref'] : '' );

			$user_phone = $this->query::get_session( 'guest_info' )['billing_phone'];
			$user_phone = $user_phone ? $user_phone : $guest_info['billing_phone'];

			if ( is_email( $email_address ) || ! empty( $user_ref ) || $user_phone ) {
				if ( $this->query::get_session( 'user_id' ) ) {
					$user_id     = $this->query::get_session( 'user_id' );
					$update_user = $this->query->update_guest_info( $user_id, $guest_info );
				} else {
					$user_id = $this->query->insert_guest_info( $guest_info );
				}

				$this->query::set_session( 'user_id', $user_id );

				if ( $email_address ) {
					$guests = $this->query->get_guest_info_rows( $email_address );
					if ( $guests ) {
						foreach ( $guests as $guest ) {
							if ( count( $this->query->get_guest_same_email( $guest->id, 0 ) ) ) {
								if ( $guest->id == $user_id ) {
									continue;
								}
								$this->query->update_abd_cart_record(
									array( 'cart_ignored' => 1 ),
									array( 'user_id' => $guest->id, 'user_type' => 'guest' )
								);
							}
						}
					}
				}

				if ( ! empty( $user_id ) ) {

					$current_time = current_time( 'timestamp' );

					$results = $this->query->get_abd_cart_records( $user_id, 'guest' ); //select record with cart_ignore = 0

					if ( function_exists( 'WC' ) ) {
						$cart['cart'] = WC()->session->cart;
					} else {
						$cart['cart'] = $woocommerce->session->cart;
					}

					$updated_cart_info = json_encode( $cart );

					$get_cookie = WC()->session->get_session_cookie();

					if ( count( $results ) == 0 ) {

						if ( '' != $get_cookie[0] ) { //'on' == $track_guest_user_cart_from_cart && bat option cho phep ghi lai cart voi guest

							$results = $this->query->get_abd_guest_cart_record_like_session_id( $get_cookie[0] );

							if ( 0 == count( $results ) ) {
								if ( '[]' != $updated_cart_info && '{"cart":[]}' != $updated_cart_info && '{"cart":null}' != $updated_cart_info ) {
									$insert_id = $this->query->insert_abd_cart_record( array(
										'user_id'             => $user_id,
										'abandoned_cart_info' => $updated_cart_info,
										'abandoned_cart_time' => $current_time,
										'user_type'           => 'guest',
										'session_id'          => $get_cookie[0]
									) );

									$this->query::set_session( 'wacv_cart_record_id', $insert_id );
								}
							} else {
								$this->query->update_abd_cart_record(
									array(
										'user_id'             => $user_id,
										'abandoned_cart_info' => $updated_cart_info,
										'abandoned_cart_time' => $current_time
									),
									array( 'session_id' => $get_cookie[0], 'cart_ignored' => 0 )
								);

								$row = $this->query->get_abd_cart_records( $user_id, 'guest' );
								if ( count( $row ) ) {
									$this->query::set_session( 'wacv_cart_record_id', $row[0]->id );
								}
							}
						}
					}

					$this->query->update_cart_log( array( 'user_id' => $user_id ), array( 'user_id' => $get_cookie[0] ) );
				}
			}
		} else {
			if ( isset( $data['user_ref'] ) ) {
				$user_ref    = sanitize_text_field( $data['user_ref'] );
				$user_id     = get_current_user_id();
				$update_user = update_user_meta( $user_id, 'wacv_user_ref', $user_ref );
				$guest_info  = array( 'user_ref' => $user_ref );
				$this->query::set_session( 'guest_info', $guest_info );
			}
		}


		return $update_user;
	}

	public function get_info() {
		$data = wc_clean( $_POST );
		if ( empty( $data['billing_country'] ) ) {
			$ip                      = \WC_Geolocation::get_ip_address();
			$user_geo                = \WC_Geolocation::geolocate_ip( $ip );
			$data['billing_country'] = $user_geo['country'];
		}
		$update_user = $this->save_info( $data );

		wp_send_json_success( $update_user );
		wp_die();
	}

	public function guest_checkout_fields( $fields ) {
		$guest_info = $this->query::get_session( 'guest_info' );
		if ( isset( $guest_info ) ) {
			if ( ! empty( $guest_info['billing_email'] ) ) {
				$_POST['billing_email'] = esc_html( $guest_info['billing_email'] );
			}
			if ( ! empty( $guest_info['billing_first_name'] ) ) {
				$_POST['billing_first_name'] = esc_html( $guest_info['billing_first_name'] );
			}
			if ( ! empty( $guest_info['billing_last_name'] ) ) {
				$_POST['billing_last_name'] = esc_html( $guest_info['billing_last_name'] );
			}
			if ( ! empty( $guest_info['billing_phone'] ) ) {
				$_POST['billing_phone'] = esc_html( $guest_info['billing_phone'] );
			}
			if ( ! empty( $guest_info['billing_address_1'] ) ) {
				$_POST['billing_address_1'] = esc_html( $guest_info['billing_address_1'] );
			}
			if ( ! empty( $guest_info['billing_city'] ) ) {
				$_POST['billing_city'] = esc_html( $guest_info['billing_city'] );
			}
		}

		return $fields;
	}
}

