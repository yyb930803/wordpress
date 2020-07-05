<?php
/**
 * Created by PhpStorm.
 * User: Villatheme-Thanh
 * Date: 15-04-19
 * Time: 8:44 AM
 */

namespace WACVP\Inc;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Cron_Job {
	public static $params;

	protected static $instance = null;

	/**
	 * Setup instance attributes
	 *
	 * @since     1.0.0
	 */
	public function __construct() {

		if ( ! wp_next_scheduled( 'wacv_delete_coupon' ) ) {
			wp_schedule_event( time(), 'daily', 'wacv_delete_coupon' );
		}

		add_action( 'wacv_delete_coupon', array( $this, 'delete_coupon' ) );

	}

	public static function get_instance() {

		// If the single instance hasn't been set, set it now.
		if ( null == self::$instance ) {
			self::$instance = new self;
		}
		self::$params = get_option( 'wacv_params' );

		return self::$instance;
	}

	public function delete_coupon() {
		$param = Data::get_instance()->params;

		if ( isset( $param['gnr_coupon_delete'] ) && $param['gnr_coupon_delete'] == 1 ) {
			$args    = array(
				'post_type'      => 'shop_coupon',
				'posts_per_page' => - 1,
				'meta_query'     => array(
					'relation' => 'AND',
					array(
						'key'   => 'date_expires',
						'value' => current_time( 'timestamp' ),

						'compare' => '<'
					),
					array(
						'key'     => 'usage_count',
						'value'   => 0,
						'compare' => '='
					)
				)

			);
			$coupons = new \WP_Query( $args );
			if ( $coupons->have_posts() ) :
				while ( $coupons->have_posts() ) : $coupons->the_post();
//					check( get_the_title() );
					wp_delete_post( get_the_ID(), true );
				endwhile;
			endif;
			wp_reset_postdata();
		}
	}
}
