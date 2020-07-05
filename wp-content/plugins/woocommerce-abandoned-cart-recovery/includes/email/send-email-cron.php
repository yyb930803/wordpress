<?php
/**
 * Created by PhpStorm.
 * User: Villatheme-Thanh
 * Date: 25-03-19
 * Time: 5:00 PM
 */

namespace WACVP\Inc\Email;

use WACVP\Inc\Aes_Ctr;
use WACVP\Inc\Data;
use WACVP\Inc\Query_DB;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Send_Email_Cron {

	protected static $instance = null;

	public $query;

	public $data;

	public $new_email_settings;

	public $old_email_settings;
	public $last_time = false;

	protected $characters_array;
	protected $country;

	private function __construct() {

		$this->query = Query_DB::get_instance();
		add_action( 'admin_head', array( $this, 'debug' ) );
		add_action( 'wacv_cron_send_email_abd_order', array( $this, 'send_reminder_order' ) );
		add_action( 'wacv_cron_send_email_abd_cart', array( $this, 'send_reminder_mail' ) );

		add_action( 'wp_ajax_wacv_send_abd_order', array( $this, 'wacv_send_abd_order' ) );
		add_action( 'wp_ajax_send_email_abd_manual', array( $this, 'send_email_abd_manual' ) );
		add_filter( 'woocommerce_email_styles', array( $this, 'custom_css' ) );
	}

	public function debug() {
//		$a = $this->query->update_abd_meta( 3, 'email_complete', true );
//		echo '<pre>' . print_r( $a, true ) . '</pre>';
		$this->send_reminder_mail();
	}

	public static function get_instance() {

		// If the single instance hasn't been set, set it now.
		if ( null == self::$instance ) {
			self::$instance = new self;
		}

		return self::$instance;
	}

	public function send_reminder_mail() {
		$this->data = Data::get_params();
		if ( ! empty( $this->data['email_rules'] ) ) {
			$email_rules = $this->data['email_rules'];
			for ( $i = 0; $i < count( $email_rules['send_time'] ); $i ++ ) {
				$this->last_time = $i == count( $email_rules['send_time'] ) - 1 ? true : false;
				$time_to_send    = current_time( 'timestamp' ) - intval( $email_rules['time_to_send'][ $i ] ) * Data::get_instance()->case_unit( $email_rules['unit'][ $i ] );

				$lists = $this->query->get_list_email_to_send( $time_to_send, $email_rules['send_time'][ $i ] );

				if ( is_array( $lists ) && count( $lists ) > 0 ) {
					if ( isset( $email_rules['template'][ $i ] ) ) {
						foreach ( $lists as $id => $item ) {
							$this->email_content( $item, $email_rules['template'][ $i ] );
						}
					}
				}
			}
		}
	}

	public function email_content( $item, $temp_id ) {

		$result = '';
		if ( ! empty( $item->user_id ) ) {

			$email            = $item->user_email;
			$customer_name    = $item->user_login;
			$customer_surname = '';
			$country          = $item->user_id < 100000000 ? get_user_meta( $item->user_id, 'billing_country', true ) : '';

			if ( $item->user_type == 'guest' ) {
				$results_guest = $this->query->get_guest_info( $item->user_id );
				if ( count( $results_guest ) > 0 ) {
					$email            = $results_guest[0]->billing_email;
					$customer_name    = ! empty( trim( $results_guest[0]->billing_first_name ) ) ? trim( $results_guest[0]->billing_first_name ) : '';
					$customer_surname = ! empty( trim( $results_guest[0]->billing_last_name ) ) ? trim( $results_guest[0]->billing_last_name ) : '';
					$country          = ! empty( $results_guest[0]->billing_country ) ? $results_guest[0]->billing_country : '';
				}
			}

			if ( is_email( $email ) ) {
				$cart        = ( json_decode( $item->abandoned_cart_info ) );
				$cart_detail = array();
				$cart_total  = 0;
				$pd_arr      = array();

				if ( $country && $this->data['price_incl_tax'] ) {
					$this->country = $country;
					add_filter( 'woocommerce_matched_rates', array( $this, 'add_tax_rate' ) );
				}

				foreach ( $cart->cart as $cart_item_key => $cart_item ) {
					$pid     = $cart_item->variation_id ? $cart_item->variation_id : $cart_item->product_id;
					$product = wc_get_product( $pid );

					$pd_name = $product->get_name();
					$pd_img  = get_the_post_thumbnail_url( $pid, 'thumbnail' );
					$pd_img  = $pd_img ? $pd_img : get_the_post_thumbnail_url( $cart_item->product_id, 'thumbnail' );

					$description   = explode( '.', $product->get_short_description() );
					$description   = isset( $description[0] ) ? $description[0] : '';
					$qty           = $cart_item->quantity;
					$price         = wc_get_price_including_tax( $product );
					$amount        = $price * $qty;
					$cart_total    += $amount;
					$pd_arr[]      = $cart_item->product_id;
					$pd_url        = $product->get_permalink();
					$cart_detail[] = array(
						$pd_img,
						$pd_name . ' x ' . $qty,
						$description,
						__( 'Price:', 'woo-abandoned-cart-recovery' ) . ' ' . wc_price( $amount ),
						"<a href='$pd_url' style='font-weight: inherit; color:inherit;'>$pd_name</a>",
						__( 'Quantity:', 'woo-abandoned-cart-recovery' ) . ' ' . $qty
					);
				}

				$acr_id = $item->id;
				$result = $this->create_email_content( $cart_detail, $cart_total, $temp_id, $pd_arr, $item->number_of_mailing, $email, $acr_id, $customer_name, $customer_surname );
			}
		}

		return $result;
	}

	public function create_email_content( $cart_detail, $cart_total, $temp_id, $pd_arr, $number_of_mailing, $email, $acr_id, $customer_name, $customer_surname ) {
		$coupon_code   = $message = $email_subject = '';
		$sent_email_id = uniqid() . $acr_id;
		if ( $temp_id ) {

			$this->new_email_settings = get_post_meta( $temp_id, 'wacv_email_settings_new', true );
			$this->old_email_settings = get_post_meta( $temp_id, 'wacv_email_settings', true );
			$email_settings           = $this->new_email_settings ? $this->new_email_settings : $this->old_email_settings;
			$coupon_default_params    = Data::get_instance()->get_coupon_params();
			$email_settings           = wp_parse_args( $email_settings, $coupon_default_params );

			if ( $email_settings['use_coupon'] ) {
				$coupon_code = $this->get_coupon_to_send( $email_settings, $cart_total, $pd_arr, $email );
			}

			$email_subject = isset( $email_settings['subject'] ) ? $email_settings['subject'] : $this->data['email_subject'];
			$template      = wp_specialchars_decode( get_post( $temp_id )->post_content );

		} else {
			$temp_id = '';
			ob_start();
			wc_get_template( 'email-default.php', array(), '', WACVP_TEMPLATES );
			$template      = ob_get_clean();
			$email_subject = $this->data['email_subject'];
		}

		if ( $template ) {
			$coupon             = $coupon_code ? $coupon_code : '';
			$out                = '';
			$link               = $this->create_link( $coupon, $acr_id, $sent_email_id, $temp_id );
			$tracking_open_link = $this->create_tracking_open_link( $acr_id, $sent_email_id );
			$unsubscribe_link   = $this->create_unsubscribe_link( $acr_id );

			$pattern = '/{wacv_cart_detail_start}([\s\S]+){wacv_cart_detail_end}/';

			if ( preg_match( ( $pattern ), $template, $match ) ) {

				foreach ( $cart_detail as $item ) {
					$search = array(
						'{wacv_image_product}',
						'{wacv_name_&_qty_product}',
						'{wacv_short_description}',
						'{product_amount}',
						'{product_name}',
						'{product_quantity}'
					);
					$out    .= str_replace( $search, $item, $match[1] );
				}
				$template = str_replace( $match[0], $out, $template );
			}

			$search  = array(
				'{wacv_coupon}',
				'{wacv_checkout_btn}',
				'{site_title}',
				'{customer_name}',
				'{customer_surname}',
				'{site_address}',
				'{store_address}',
				'{admin_email}',
				'{site_url}',
				'{home_url}',
				'{shop_url}',
				'{wacv_coupon_start}',
				'{wacv_coupon_end}',
				'{unsubscribe_link}',
				'{coupon_code}',
			);
			$replace = array(
				$coupon,
				$link,
				get_bloginfo(),
				$customer_name,
				$customer_surname,
				WC()->countries->get_base_address(),
				WC()->countries->get_base_address(),
				get_bloginfo( 'admin_email' ),
				site_url(),
				home_url(),
				get_permalink( wc_get_page_id( 'shop' ) ),
				'',
				'',
				$unsubscribe_link,
				$coupon,
			);

			$email_subject = str_replace( $search, $replace, $email_subject );
			$message       = str_replace( $search, $replace, $template );
			$message       = $this->complete_message( $message );
			$message       = $message . $tracking_open_link;

		}

		return $this->send_mail( $email, $email_subject, $message, $acr_id, $number_of_mailing, $coupon_code, $sent_email_id, $temp_id );
	}

	public function get_coupon_to_send( $email_settings, $cart_total, $pd_arr, $email ) {
		$coupon_code = '';

		$cats = $check_pd_sale = array();

		foreach ( $pd_arr as $p_id ) {
			$cats            = array_unique( array_merge( $cats, wc_get_product_cat_ids( $p_id ) ) );
			$check_pd_sale[] = empty( get_post_meta( $p_id, '_sale_price', true ) ) ? true : false;
		}

		if ( isset( $email_settings['use_coupon_generate'] ) && $email_settings['use_coupon_generate'] ) {
			$coupon_code = $this->generate_coupon( $email_settings, $cart_total, $pd_arr, $check_pd_sale, $cats );
		} elseif ( isset( $email_settings['wc_coupon'] ) && $email_settings['wc_coupon'] ) {
			$coupon_code = $this->get_had_coupon( $email, $email_settings, $cart_total, $pd_arr, $check_pd_sale, $cats );
		}

		return $coupon_code;
	}

	public function generate_coupon( $option, $cart_total, $pd_arr, $check_pd_sale, $cats ) {
		$coupon_code = '';
//		$total_1     = $cart_total >= intval( $option['gnr_coupon_min_spend'] ) && $cart_total <= intval( $option['gnr_coupon_max_spend'] );
//		$total_2     = $cart_total >= intval( $option['gnr_coupon_min_spend'] ) && empty( $option['gnr_coupon_max_spend'] );
//		$total_3     = empty( $option['gnr_coupon_min_spend'] ) && $cart_total <= intval( $option['gnr_coupon_max_spend'] );
//		$total_4     = empty( $option['gnr_coupon_min_spend'] ) && empty( $option['gnr_coupon_max_spend'] );
//update_option('my_logs',$pd_arr);
//		$total         = $total_1 || $total_2 || $total_3 || $total_4;
//		$product       = empty( $option['gnr_coupon_products'] ) || ! empty( array_intersect( $pd_arr, $option['gnr_coupon_products'] ) ) ? true : false;
//		$product_ex    = empty( $option['gnr_coupon_exclude_products'] ) || ! empty( array_diff( $pd_arr, $option['gnr_coupon_exclude_products'] ) ) ? true : false;
//		$categories    = empty( $option['gnr_coupon_categories'] ) || ! empty( array_intersect( $cats, $option['gnr_coupon_categories'] ) ) ? true : false;
//		$categories_ex = empty( $option['gnr_coupon_exclude_categories'] ) || ! empty( array_diff( $cats, $option['gnr_coupon_exclude_categories'] ) ) ? true : false;
//		$check_sale    = ( boolval( $option['gnr_coupon_exclude_sale_items'] ) == true && in_array( 1, $check_pd_sale ) ) || boolval( $option['gnr_coupon_exclude_sale_items'] ) == false ? true : false;
//		$check_sale    =  isset( $option['gnr_coupon_exclude_sale_items'] ) == true && in_array( 1, $check_pd_sale ) ) || boolval( $option['gnr_coupon_exclude_sale_items'] ) == false ? true : false;

//		if ( $total && $product && $product_ex && $categories && $categories_ex && $check_sale ) {
		$coupon_code = wc_get_coupon_code_by_id( $this->create_coupon( $option ) );

//		}

		return $coupon_code;
	}

	public function create_coupon( $option ) {
		$code   = $this->create_code( $option );
		$coupon = new \WC_Coupon( $code );
		$desc   = isset( $option['gnr_coupon_desc'] ) ? $option['gnr_coupon_desc'] : '';
		$coupon->set_description( $desc );
		$coupon->set_discount_type( $option['gnr_coupon_type'] );
		$coupon->set_amount( $option['gnr_coupon_amount'] );
		if ( $option['gnr_coupon_date_expiry'] ) {
			$coupon->set_date_expires( $option['gnr_coupon_date_expiry'] * 24 * 60 * 60 + current_time( 'timestamp' ) );
		}
		$coupon->set_minimum_amount( $option['gnr_coupon_min_spend'] );
		$coupon->set_maximum_amount( $option['gnr_coupon_max_spend'] );
		$coupon->set_individual_use( $option['gnr_coupon_individual'] );
		$coupon->set_exclude_sale_items( $option['gnr_coupon_exclude_sale_items'] );
		$coupon->set_product_ids( $option['gnr_coupon_products'] );
		$coupon->set_excluded_product_ids( $option['gnr_coupon_exclude_products'] );
		$coupon->set_product_categories( $option['gnr_coupon_categories'] );
		$coupon->set_excluded_product_categories( $option['gnr_coupon_exclude_categories'] );
		$coupon->set_usage_limit( $option['gnr_coupon_limit_per_cp'] );
		$coupon->set_limit_usage_to_x_items( $option['gnr_coupon_limit_x_item'] );
		$coupon->set_usage_limit_per_user( $option['gnr_coupon_limit_user'] );

		return $coupon->save();
	}

	public function create_code( $option ) {
		wp_reset_postdata();

		$code = $option['gnr_coupon_prefix'];

		for ( $i = 0; $i < 8; $i ++ ) {
			$code .= $this->rand();
		}

		$args      = array(
			'post_type'      => 'shop_coupon',
			'post_status'    => 'publish',
			'posts_per_page' => - 1,
			'title'          => $code
		);
		$the_query = new \WP_Query( $args );
		if ( $the_query->have_posts() ) {
			$code = $this->create_code( $option );
		}
		wp_reset_postdata();

		return $code;
	}

	protected function rand() {
		if ( $this->characters_array === null ) {
			$this->characters_array = array_merge( range( 0, 9 ), range( 'a', 'z' ) );
		}
		$rand = rand( 0, count( $this->characters_array ) - 1 );

		return $this->characters_array[ $rand ];
	}

	public function get_had_coupon( $customer_email, $email_settings, $cart_total, $pd_arr, $check_pd_sale, $cats ) {
//		$cp_detail   = new \WC_Coupon( $email_settings['wc_coupon'] );
//		$cp_time_exp = $cp_detail->get_date_expires() ? $cp_detail->get_date_expires()->getTimestamp() : '';
//
//		$total_1 = $cart_total >= intval( $cp_detail->get_minimum_amount() ) && $cart_total <= intval( $cp_detail->get_maximum_amount() );
//		$total_2 = $cart_total >= intval( $cp_detail->get_minimum_amount() ) && empty( $cp_detail->get_maximum_amount() );
//		$total_3 = empty( $cp_detail->get_minimum_amount() ) && $cart_total <= intval( $cp_detail->get_maximum_amount() );
//		$total_4 = empty( $cp_detail->get_minimum_amount() ) && empty( $cp_detail->get_maximum_amount() );
//
//		$total          = $total_1 || $total_2 || $total_3 || $total_4;
//		$date           = current_time( 'timestamp' ) <= $cp_time_exp || empty( $cp_time_exp ) ? true : false;
//		$product        = ! empty( array_intersect( $pd_arr, $cp_detail->get_product_ids() ) ) || empty( $cp_detail->get_product_ids() ) ? true : false;
//		$product_ex     = ! empty( array_diff( $pd_arr, $cp_detail->get_excluded_product_ids() ) ) || empty( $cp_detail->get_excluded_product_ids() ) ? true : false;
//		$categories     = ! empty( array_intersect( $cats, $cp_detail->get_product_categories() ) ) || empty( $cp_detail->get_product_categories() ) ? true : false;
//		$categories_ex  = ! empty( array_diff( $cats, $cp_detail->get_excluded_product_categories() ) ) || empty( $cp_detail->get_excluded_product_categories() ) ? true : false;
//		$restrict_email = in_array( $customer_email, $cp_detail->get_email_restrictions() ) || empty( $cp_detail->get_email_restrictions() ) ? true : false;
//		$check_sale     = ( $cp_detail->get_exclude_sale_items() == true && in_array( 1, $check_pd_sale ) ) || $cp_detail->get_exclude_sale_items() == false ? true : false;
//		$limit_per_cp   = $cp_detail->get_usage_count() < $cp_detail->get_usage_limit() || $cp_detail->get_usage_limit() == 0 ? true : false;
//		$limit_per_user = count( $cp_detail->get_used_by() ) < $cp_detail->get_usage_limit_per_user() || $cp_detail->get_usage_limit_per_user() == 0 ? true : false;
//
//		$coupon_code = $total && $date && $product && $product_ex && $categories && $categories_ex && $restrict_email && $check_sale && $limit_per_cp && $limit_per_user ? wc_get_coupon_code_by_id( $email_settings['wc_coupon'] ) : '';
		$coupon_code = wc_get_coupon_code_by_id( $email_settings['wc_coupon'] );

		return $coupon_code;
	}

	public function create_link( $coupon_code, $acr_id, $sent_email_id, $temp_id ) {
		$coupon      = $coupon_code ? '&' . $coupon_code : '';
		$template_id = $temp_id ? '&' . $temp_id : '&0';
		$pass        = get_option( 'wacv_private_key' );

		$url_encode = Aes_Ctr::encrypt( $acr_id . '&' . $sent_email_id . $template_id . $coupon, $pass, 256 );

		return site_url( '?wacv_recover=cart_link&valid=' ) . $url_encode;
	}

	public function create_tracking_open_link( $acr_id, $sent_email_id ) {
		$pass = get_option( 'wacv_private_key' );

		$url_encode = Aes_Ctr::encrypt( $acr_id . '&' . $sent_email_id, $pass, 256 );

		return "<img width='0' height='0' style='width:0; height:0;' src='" . site_url( '?wacv_open_email=' ) . $url_encode . "' >";
	}

	public function create_unsubscribe_link( $acr_id ) {

		$pass = get_option( 'wacv_private_key' );

		$url_encode = Aes_Ctr::encrypt( $acr_id, $pass, 256 );

		return site_url( '?wacv_unsubscribe=' ) . $url_encode;
	}

	public function complete_message( $template ) {
		$mailer = WC()->mailer();
		$email  = new \WC_Email();

		if ( $this->new_email_settings && ! isset( $this->new_email_settings['woo_header'] ) ) {
			$message = $email->style_inline( $this->wrap_message( $template ) );
		} elseif ( $this->new_email_settings && isset( $this->new_email_settings['woo_header'] ) ) {
			$heading     = ! empty( $this->new_email_settings['heading'] ) ? $this->new_email_settings['heading'] : '';
			$message     = $email->style_inline( $mailer->wrap_message( $heading, $template ) );
			$padding     = array( 'padding: 12px;', 'padding: 48px 48px 32px' );
			$new_padding = array( 'padding:0;', 'padding:0' );
			$message     = str_replace( $padding, $new_padding, $message );
		} else {
			$heading = ! empty( $this->old_email_settings['heading'] ) ? $this->old_email_settings['heading'] : '';
			$message = $email->style_inline( $mailer->wrap_message( $heading, $template ) );
		}

		return $message;
	}

	public function wrap_message( $message ) {
		// Buffer.
		ob_start();

		wc_get_template( 'email-header.php', '', '', WACVP_TEMPLATES );

		echo wptexturize( $message ); // WPCS: XSS ok.

		wc_get_template( 'email-footer.php', '', '', WACVP_TEMPLATES );

		$message = ob_get_clean();

		return $message;
	}

	public function send_mail( $email, $email_subject, $message, $acr_id, $number_of_mailing, $coupon, $sent_email_id, $temp_id ) {
		$param = Data::get_params();

		$headers [] = "Content-Type: text/html";

		if ( $param['email_reply_address'] ) {
			$headers [] = "Reply-To: " . $param['email_reply_address'];
		}

		$mailer = new \WC_Email();

		$result_sent_mail = $mailer->send( $email, $email_subject, $message, $headers, '' );
		if ( $result_sent_mail ) {
			$complete = $this->last_time ? '1' : null;
			$this->query->update_abd_cart_record(
				array(
					'send_mail_time'    => current_time( 'timestamp' ),
					'number_of_mailing' => $number_of_mailing + 1,
					'email_complete'    => $complete
				),
				array( 'id' => $acr_id ) );

			$this->query->insert_email_history( 'email', $acr_id, $sent_email_id, $temp_id, $email, $coupon );
		} else {
//			$this->query->update_abd_cart_record( array( 'abandoned_cart_time' => current_time( 'timestamp' ) ), array( 'id' => $acr_id ) );
		}

		return $result_sent_mail;
	}

	public function add_tax_rate( $rate ) {
		if ( ! wc_prices_include_tax() && $this->country ) {
			$rate = \WC_Tax::find_rates(
				array(
					'country'   => $this->country,
					'state'     => '',
					'postcode'  => '',
					'city'      => '',
					'tax_class' => '',
				)
			);
		}

		return $rate;
	}

	public function send_reminder_order() {
		$data_ins   = Data::get_instance();
		$this->data = Data::get_params();

		if ( ! $this->data['enable_reminder_order'] ) {
			return;
		}

		if ( ! empty( $this->data['abd_orders'] ) ) {
			$email_rules = $this->data['abd_orders'];

			for ( $i = 0; $i < count( $email_rules['send_time'] ); $i ++ ) {

				$time_to_send = current_time( 'timestamp' ) - intval( $email_rules['time_to_send'][ $i ] ) * $data_ins->case_unit( $email_rules['unit'][ $i ] );

				$args = array(
					'post_type'   => 'shop_order',
					'post_status' => $this->data['order_stt'],
					'date_query'  => array(
						array(
							'before' => array(
								'year'   => date_i18n( 'Y', $time_to_send ),
								'month'  => date_i18n( 'm', $time_to_send ),
								'day'    => date_i18n( 'd', $time_to_send ),
								'hour'   => date_i18n( 'H', $time_to_send ),
								'minute' => date_i18n( 'i', $time_to_send ),
								'second' => date_i18n( 's', $time_to_send ),
							),
							'column' => 'post_modified',
						),
					),
					'meta_query'  => array(
						array(
							'key'   => 'wacv_send_reminder_email',
							'value' => $email_rules['send_time'] [ $i ] - 1,
						),
						array(
							'key'   => 'wacv_reminder_unsubscribe',
							'value' => '',
						),
					)
				);

				$the_query = new \WP_Query( $args );

				if ( $the_query->have_posts() ) :
					foreach ( $the_query->get_posts() as $order ) {
						$this->create_email_reminder_order( $order->ID, $email_rules['template'] [ $i ], $email_rules['send_time'] [ $i ] );
					}
				endif;
			}
		}
	}

	public function create_email_reminder_order( $order_id, $template_id, $time ) {
		$template = wp_specialchars_decode( get_post( $template_id )->post_content );
		$order    = wc_get_order( $order_id );
		$email    = $order->get_billing_email();

		if ( $template && is_email( $email ) ) {
			$this->new_email_settings = get_post_meta( $template_id, 'wacv_email_settings_new', true );
			$this->old_email_settings = get_post_meta( $template_id, 'wacv_email_settings', true );
			$email_settings           = $this->new_email_settings ? $this->new_email_settings : $this->old_email_settings;
			$subject                  = isset( $email_settings['subject'] ) ? $email_settings['subject'] : '';
			$out                      = '';
			$sent_email_id            = '&' . uniqid() . $order_id;
			$pass                     = get_option( 'wacv_private_key' );
			$recover_url_encode       = Aes_Ctr::encrypt( $order_id . $sent_email_id, $pass, 256 );
			$unsub_url_encode         = Aes_Ctr::encrypt( $order_id, $pass, 256 );
			$link                     = site_url( '?wacv_recover=order_link&valid=' ) . $recover_url_encode;
			$unsubscribe_link         = site_url( '?unsubscribe=' ) . $unsub_url_encode;
			$customer_name            = $order->get_billing_first_name();
			$customer_surname         = $order->get_billing_last_name();

			$order_detail = $order->get_items();

			//remove coupon block if exist
			$pattern_cp = '/{wacv_coupon_start}([\s\S]+){wacv_coupon_end}/';
			preg_match( $pattern_cp, $template, $match_cp );
			//replace order items with shortcode
			$pattern = '/{wacv_cart_detail_start}([\s\S]+){wacv_cart_detail_end}/';

			if ( preg_match( $pattern, $template, $match ) ) {

				foreach ( $order_detail as $item ) {
					$item    = $item->get_data();
					$pid     = $item['variation_id'] ? $item['variation_id'] : $item['product_id'];
					$pd_img  = get_the_post_thumbnail_url( $pid, 'thumbnail' );
					$desc    = explode( '.', wc_get_product( $pid )->get_short_description() );
					$desc    = isset( $desc[0] ) ? $desc[0] : '';
					$p_url   = get_permalink( $pid );
					$product = array(
						$pd_img,
						$item['name'] . ' x ' . $item['quantity'],
						$desc,
						__( 'Price:', 'woo-abandoned-cart-recovery' ) . wc_price( $item['subtotal'] ),
						"<a href='$p_url' style='font-weight: inherit'>${item['name']}</a>",
						__( 'Quantity:', 'woo-abandoned-cart-recovery' ) . $item['quantity'],
					);
					$search  = array(
						'{wacv_image_product}',
						'{wacv_name_&_qty_product}',
						'{wacv_short_description}',
						'{product_amount}',
						'{product_name}',
						'{product_quantity}'
					);
					$out     .= str_replace( $search, $product, $match[1] );
				}
			}

			$search = array(
				isset( $match_cp[0] ) ? $match_cp[0] : '',
				$match[0],
				'{coupon_code}',
				'{wacv_coupon}',
				'{wacv_checkout_btn}',
				'{site_title}',
				'{customer_name}',
				'{customer_surname}',
				'{site_address}',
				'{admin_email}',
				'{site_url}',
				'{home_url}',
				'{shop_url}',
				'{wacv_coupon_start}',
				'{wacv_coupon_end}',
				'{unsubscribe_link}',
			);

			$replace = array(
				'',
				$out,
				'',
				'',
				$link,
				get_bloginfo(),
				$customer_name,
				$customer_surname,
				WC()->countries->get_base_address(),
				get_bloginfo( 'admin_email' ),
				site_url(),
				home_url(),
				get_permalink( wc_get_page_id( 'shop' ) ),
				'',
				'',
				$unsubscribe_link,
			);

			$tracking_open_link = $this->create_tracking_open_link( $order_id, $sent_email_id );
			$message            = str_replace( $search, $replace, $template );
			$message            = $this->complete_message( $message );
			$subject            = str_replace( $search, $replace, $subject );
			$send_mail          = $this->send_email_reminder_order( $email, $subject, $message . $tracking_open_link );

			if ( $send_mail ) {
				update_post_meta( $order_id, 'wacv_send_reminder_email', $time );
				$this->query->insert_email_history( 'order', $order_id, $sent_email_id, $template_id );
			}

			return $send_mail;
		}
	}

	public function send_email_reminder_order( $email, $subject, $message ) {
		$param = Data::get_params();

		$headers [] = "Content-Type: text/html";

		if ( $param['email_reply_address'] ) {
			$headers [] = "Reply-To: " . $param['email_reply_address'];
		}
		$mailer = new \WC_Email();

		$result = $mailer->send( $email, $subject, $message, $headers, '' );

		return $result;
	}

	public function wacv_send_abd_order() {
		if ( isset( $_POST['id'], $_POST['temp'] ) ) {
			$result    = true;
			$settings  = Data::get_params();
			$order_id  = sanitize_text_field( $_POST['id'] );
			$order     = wc_get_order( $order_id );
			$order_stt = $order->get_status();
			if ( in_array( 'wc-' . $order_stt, $settings['order_stt'] ) ) {
				$template = sanitize_text_field( $_POST['temp'] );
				$time     = get_post_meta( $order_id, 'wacv_send_reminder_email', true ) + 1;
				$result   = $this->create_email_reminder_order( $order_id, $template, $time );
			}
			wp_send_json( $result );
		}
		wp_die();
	}

	public function send_email_abd_manual() {
		$result = '';
		if ( isset( $_POST['id'], $_POST['temp'], $_POST['time'] ) ) {
			$id     = sanitize_text_field( $_POST['id'] );
			$temp   = sanitize_text_field( $_POST['temp'] );
			$item   = $this->query->get_abd_cart_by_id( $id );
			$result = $this->email_content( $item, $temp );
		}
		wp_send_json( $result );
		wp_die();
	}

	public function custom_css( $css ) {
		$custom_css = '';
		$custom_css .= 'p{margin:0 !important;line-height:1.8;}';
		$custom_css .= 'a{text-decoration: none; color: inherit !important; font-weight:inherit;} a:hover{color:#007CFF}';
		$custom_css .= ' .ExternalClass p, .ExternalClass span, .ExternalClass font, .ExternalClass td {line-height: 100%;} .ExternalClass {width: 100%;}';
		$css        = $css . $custom_css;

		return $css;
	}
}