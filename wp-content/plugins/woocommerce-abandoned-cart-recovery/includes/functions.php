<?php
/**
 * Created by PhpStorm.
 * User: Villatheme-Thanh
 * Date: 12-07-19
 * Time: 10:11 AM
 */

namespace WACVP\Inc;


if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Functions {

	protected static $instance = null;

	public static function get_instance() {

		if ( null == self::$instance ) {
			self::$instance = new self;
		}

		return self::$instance;
	}

	public static function get_email_template() {
		$args = array(
			'post_type'      => 'wacv_email_template',
			'post_status'    => 'publish',
			'orderby'        => 'id',
			'order'          => 'ASC',
			'posts_per_page' => - 1
		);

		$email_templates = get_posts( $args );
		$list_template   = array();
		foreach ( $email_templates as $template ) {
			$value = ! empty( $template->post_title ) ? esc_html( $template->post_title ) : 'no title';

			$list_template[] = array(
				'id'    => $template->ID,
				'value' => $value
			);
		}

		return $list_template;
	}


	public static function get_time() {
		$start = strtotime( 'midnight', current_time( 'timestamp' ) ) + 1;
		$end   = strtotime( 'tomorrow', current_time( 'timestamp' ) ) - 1;

		if ( isset( $_GET['wacv_time_range'] ) ) {
			$start = isset( $_GET['wacv_start'] ) ? strtotime( sanitize_text_field( $_GET['wacv_start'] ) ) : $start;
			$end   = isset( $_GET['wacv_end'] ) ? strtotime( sanitize_text_field( $_GET['wacv_end'] ) ) + 86399 : $end;

			if ( $start > $end ) {
				$tmp   = $start;
				$start = $end;
				$end   = $tmp;
			}

		} else {
			$time_range = get_option( 'wacv_time_range' );
			switch ( $time_range ) {
//				case 'today':
//					break;
				case 'yesterday':
					$start = $start - 86400;
					$end   = $end - 86400;
					break;
				case '30days':
					$start = $start - 86400 * 30;
					break;
				case '90days':
					$start = $start - 86400 * 90;
					break;
				case '365days':
					$start = $start - 86400 * 365;
					break;
			}
		}

		return array( 'start' => $start, 'end' => $end );
	}

	public static function is_bot() {

		if ( ! isset( $_SERVER['HTTP_USER_AGENT'] ) ) {
			return true;
		}

		$define_bots = array(
			'AspiegelBot',
			'curl'
		);

		foreach ( $define_bots as $bot ) {
			if ( stristr( $_SERVER['HTTP_USER_AGENT'], $bot ) ) {
				return true;
			}
		}
	}
}
