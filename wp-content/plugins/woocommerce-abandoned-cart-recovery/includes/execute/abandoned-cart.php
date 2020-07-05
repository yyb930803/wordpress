<?php
/**
 * Created by PhpStorm.
 * User: Villatheme-Thanh
 * Date: 14-03-19
 * Time: 4:26 PM
 */

namespace WACVP\Inc\Execute;

use WACVP\Inc\Data;
use WACVP\Inc\Functions;
use WACVP\Inc\Query_DB;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Abandoned_Cart {

	protected static $instance = null;

	public $query;

	public $params;

	public $os_platform;

	public $browser;

	public $ip_add;

	public function __construct() {
		$this->query = Query_DB::get_instance();
		add_action( 'woocommerce_cart_updated', array( $this, 'save_abandoned_cart' ) );
		add_action( 'woocommerce_checkout_order_processed', array( $this, 'remove_abandoned_cart_after_success_order' ) );
		add_action( 'wp_login', array( $this, 'user_login' ), 10, 2 );
	}


	public static function get_instance() {
		if ( null == self::$instance ) {
			self::$instance = new self;
		}

		return self::$instance;
	}

	public function user_login( $user_name, $user_info ) {
		$u_id        = $user_info->ID;
		$g_id        = WC()->session->get( 'user_id' );
		$session_key = WC()->session->get_customer_id();

		if ( $g_id ) {
			$this->query->update_abd_cart_record(
				array( 'user_id' => $u_id, 'user_type' => 'member' ),
				array( 'user_id' => $g_id )
			);
			$this->query->update_cart_log(
				array( 'user_id' => $u_id ),
				array( 'user_id' => $g_id )
			);

			$user_ref = $this->query->get_user_ref( $g_id );
			$user_ref = current( $user_ref );
			if ( $user_ref ) {
				update_user_meta( $u_id, 'wacv_user_ref', $user_ref );
			}
		} else {
			$this->query->update_abd_cart_record(
				array( 'user_id' => $u_id, 'user_type' => 'member' ),
				array( 'session_id' => $session_key )
			);
			$this->query->update_cart_log(
				array( 'user_id' => $u_id ),
				array( 'user_id' => $session_key )
			);
		}
	}

	public function save_abandoned_cart() {
		if ( Functions::is_bot() ) {
			return;
		}

		if ( is_admin() && ! is_ajax() ) {
			return;
		}

		//Fix when after checkout, cart not empty
		if ( $this->query::get_session( 'wacv_order_processed' ) && wc()->cart->get_cart_contents_count() ) {
			return;
		} else {
			wc()->session->__unset( 'wacv_order_processed' );
		}

		$data              = Data::get_instance();
		$this->params      = $data::get_params();
		$this->os_platform = $data->get_os_platform();
		$this->browser     = $data->get_browser();
		$this->ip_add      = \WC_Geolocation::get_ip_address();

		$current_time        = current_time( 'timestamp' );
		$compare_time_member = $current_time - $this->params['member_cut_off_time'] * 60;
		$compare_time_guest  = $current_time - $this->params['guest_cut_off_time'] * 60;

		if ( is_user_logged_in() ) {
			if ( current_user_can( 'manage_options' ) ) {
				return;
			}
			if ( $this->params['tracking_member'] ) {
				//Handle with user logged in
				$user_id = get_current_user_id();
				//Get cart data from wp_user_meta
//				$cart_data = json_encode( get_user_meta( $user_id, '_woocommerce_persistent_cart_' . get_current_blog_id(), true ) );
				$cart_data = json_encode( array( 'cart' => WC()->session->cart ) );
				$results   = $this->query->get_abd_cart_records( $user_id, 'member' );

				if ( count( $results ) == 0 ) {

					if ( $cart_data != '""' && ( $cart_data != '{"cart":[]}' ) ) {
						$insert_id = $this->query->insert_abd_cart_record( array(
							'user_id'             => $user_id,
							'abandoned_cart_info' => $cart_data,
							'abandoned_cart_time' => $current_time,
							'user_type'           => 'member',
							'customer_ip'         => $this->ip_add,
							'os_platform'         => $this->os_platform,
							'browser'             => $this->browser
						) );

						$this->query::set_session( 'wacv_cart_record_id', $insert_id );
					}

				} elseif ( isset( $results[0]->abandoned_cart_time ) && $compare_time_member > $results[0]->abandoned_cart_time ) {

					if ( ! $this->query->compare_cart_info( $user_id, $results[0]->abandoned_cart_info ) ) {

						$this->query->update_abd_cart_record(
							array( 'cart_ignored' => 1 ),
							array( 'user_id' => $user_id, 'user_type' => 'member' )
						);
						if ( $cart_data != '""' && ( $cart_data != '{"cart":[]}' ) ) {

							$insert_id = $this->query->insert_abd_cart_record( array(
								'user_id'             => $user_id,
								'abandoned_cart_info' => $cart_data,
								'abandoned_cart_time' => $current_time,
								'user_type'           => 'member',
								'customer_ip'         => $this->ip_add,
								'os_platform'         => $this->os_platform,
								'browser'             => $this->browser
							) );

							$this->query::set_session( 'wacv_cart_record_id', $insert_id );
						}
					}

				} else {
					$this->query->update_abd_cart_record(
						array( 'abandoned_cart_info' => $cart_data, 'abandoned_cart_time' => $current_time, ),
						array( 'user_id' => $user_id, 'cart_ignored' => 0 ) );
					$row = $this->query->get_abd_cart_records( $user_id, 'member' );

					if ( count( $row ) ) {
						$this->query::set_session( 'wacv_cart_record_id', $row[0]->id );
					}

				}
			}
		} else {
			//Handle with visitor
			if ( $this->params['tracking_guest'] ) {
				$user_id = $this->query::get_session( 'user_id' );

				$this->handle_guest_cart( $user_id, $compare_time_guest, $current_time );
			}
		}
	}

	//Handle with visitor
	public function handle_guest_cart( $user_id, $compare_time, $current_time ) {
		$results           = $this->query->get_abd_cart_records( $user_id, 'guest' ); //select record with cart_ignore = 0
		$cart['cart']      = WC()->session->cart;
		$updated_cart_info = json_encode( $cart );

		if ( count( $results ) > 0 && '{"cart":[]}' != $updated_cart_info ) {

			if ( ! $this->query->compare_guest_cart_info( $updated_cart_info, $results[0]->abandoned_cart_info ) ) {

				if ( $compare_time > $results[0]->abandoned_cart_time ) {

					$this->query->update_abd_cart_record(
						array( 'cart_ignored' => 1 ),
						array( 'user_id' => $user_id )
					);

					$insert_id = $this->query->insert_abd_cart_record( array(
						'user_id'             => $user_id,
						'abandoned_cart_info' => $updated_cart_info,
						'abandoned_cart_time' => $current_time,
						'user_type'           => 'guest',
						'customer_ip'         => $this->ip_add,
						'os_platform'         => $this->os_platform,
						'browser'             => $this->browser
					) );

					$this->query::set_session( 'wacv_cart_record_id', $insert_id );
				}
			} else {

				$this->query->update_abd_cart_record(
					array( 'abandoned_cart_info' => $updated_cart_info, 'abandoned_cart_time' => $current_time ),
					array( 'user_id' => $user_id, 'cart_ignored' => 0 )
				);

				$row = $this->query->get_abd_cart_records( $user_id, 'guest' );
				if ( count( $row ) ) {
					$this->query::set_session( 'wacv_cart_record_id', $row[0]->id );
				}
			}

		} else {
			$get_cookie = WC()->session->get_session_cookie();

			if ( '' != $get_cookie[0] ) { //'on' == $track_guest_user_cart_from_cart && bat option cho phep ghi lai cart voi guest

				$results = $this->query->get_abd_guest_cart_record_like_session_id( $get_cookie[0] );

				if ( 0 == count( $results ) ) {
					$user_id = ! empty( $user_id ) ? $user_id : 0;
					if ( '[]' != $updated_cart_info && '{"cart":[]}' != $updated_cart_info ) {
						$insert_id = $this->query->insert_abd_cart_record( array(
							'user_id'             => $user_id,
							'abandoned_cart_info' => $updated_cart_info,
							'abandoned_cart_time' => $current_time,
							'user_type'           => 'guest',
							'session_id'          => $get_cookie[0],
							'customer_ip'         => $this->ip_add,
							'os_platform'         => $this->os_platform,
							'browser'             => $this->browser
						) );
						$this->query::set_session( 'wacv_cart_record_id', $insert_id );
					}

				} elseif ( $compare_time > $results[0]->abandoned_cart_time ) {

					if ( '[]' != $updated_cart_info && '{"cart":[]}' != $updated_cart_info ) {

						if ( ! $this->query->compare_guest_cart_info( $updated_cart_info, $results[0]->abandoned_cart_info ) ) {

							$this->query->update_abd_cart_record(
								array( 'cart_ignored' => 1 ),
								array( 'session_id' => $get_cookie[0] )
							);

							$insert_id = $this->query->insert_abd_cart_record( array(
								'abandoned_cart_info' => $updated_cart_info,
								'abandoned_cart_time' => $current_time,
								'user_type'           => 'guest',
								'session_id'          => $get_cookie[0],
								'customer_ip'         => $this->ip_add,
								'os_platform'         => $this->os_platform,
								'browser'             => $this->browser
							) );
							$this->query::set_session( 'wacv_cart_record_id', $insert_id );
						} else {
//							echo 'false';
						}
					}
				} else {

					if ( '[]' != $updated_cart_info && '{"cart":[]}' != $updated_cart_info ) {
						if ( ! $this->query->compare_guest_cart_info( $updated_cart_info, $results[0]->abandoned_cart_info ) ) {
							$this->query->update_abd_cart_record(
								array( 'abandoned_cart_info' => $updated_cart_info ),
								array( 'session_id' => $get_cookie[0] )
							);
							$row = $this->query->get_abd_cart_records( $user_id, 'guest' );
							if ( count( $row ) ) {
								$this->query::set_session( 'wacv_cart_record_id', $row[0]->id );
							}
						}
					}
				}
			}
		}
	}

	public function remove_abandoned_cart_after_success_order( $order_id ) {
		$id             = $this->query::get_session( 'wacv_cart_record_id' );
		$order_type     = ! empty( $this->query::get_session( 'wacv_order_type' ) ) ? 1 : 0;
		$recovered_time = $order_type ? current_time( 'timestamp' ) : null;
		$recovered_id   = $order_type ? $this->query::get_session( 'wacv_recover_id' ) : null;

		//Remove record if order success
		$this->query->remove_abd_record( $id );

		if ( $order_type ) {
			$this->query->update_abd_cart_record(
				array(
					'order_type'          => $order_type,
					'recovered_cart_time' => $recovered_time
				),
				array( 'id' => $recovered_id ) );
		}

		if ( $order_type == 1 && $this->params['email_to_admin_when_cart_recover'] ) {
			$headers [] = "Content-Type: text/html";

			$subject = __( 'Have a recovered order from abandoned cart', 'woo-abandoned-cart-recovery' );

			$message = 'You’ve received a recovered order from abandoned cart. Click here to view detail <a href="' . admin_url( "post.php?post=$order_id&action=edit" ) . '">Order#' . $order_id . '</a>';

			wp_mail( get_option( 'admin_email' ), $subject, $message, $headers );
		}

		$this->query::set_session( 'wacv_order_type', '' );
		$this->query::set_session( 'wacv_order_processed', true );

		if ( $temp_id = $this->query::get_session( 'wacv_temp_id' ) ) {
			$curr_used = get_post_meta( $temp_id, 'wacv_template_used', true );
			update_post_meta( $temp_id, 'wacv_template_used', intval( $curr_used ) + 1 );
		}
	}

}