<?php
/**
 * Created by PhpStorm.
 * User: Villatheme-Thanh
 * Date: 06-04-19
 * Time: 9:22 AM
 */

namespace WACVP\Inc\Reports;

use WACVP\Inc\Query_DB;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Reports {

	protected static $instance = null;

	public $query;

	public $plugin_slug;

	private function __construct() {

		$this->query = Query_DB::get_instance();

		add_action( 'admin_menu', array( $this, 'admin_menu_page' ), 10 );
		add_action( 'wp_ajax_get_reports', array( $this, 'get_reports' ) );
		add_filter( 'set-screen-option', array( $this, 'abandoned_table_screen_options' ), 10, 3 );
	}

	public static function get_instance() {

		if ( null == self::$instance ) {
			self::$instance = new self;
		}

		return self::$instance;
	}


	public function abandoned_table_screen_options( $status, $option, $value ) {
		return $value;
	}

	public function admin_menu_page() {
		$abd_page = add_menu_page(
			esc_html__( 'Abandoned Cart', 'woo-abandoned-cart-recovery' ),
			esc_html__( 'Abandoned Cart', 'woo-abandoned-cart-recovery' ),
			apply_filters( 'wacv_change_role', 'manage_options' ),
			'wacv_sections',
			array( $this, 'abandoned_cart_table' ),
			'dashicons-cart',
			2
		);
		add_action( "load-$abd_page", array( $this, 'abd_screen_options' ) );

		add_submenu_page(
			'wacv_sections',
			__( 'Abandoned Carts', 'woo-abandoned-cart-recovery' ),
			__( 'Abandoned Carts', 'woo-abandoned-cart-recovery' ),
			apply_filters( 'wacv_change_role', 'manage_options' ),
			'wacv_sections',
			array( $this, 'abandoned_cart_table' )
		);

		$report_page = add_submenu_page(
			'wacv_sections',
			__( 'Reports', 'woo-abandoned-cart-recovery' ),
			__( 'Reports', 'woo-abandoned-cart-recovery' ),
			apply_filters( 'wacv_change_role', 'manage_options' ),
			'wacv_reports',
			array( $this, 'display_reports' )
		);
		add_action( "load-$report_page", array( $this, 'report_screen_options' ) );
	}

	public function abandoned_cart_table() {
		Abandoned_Report_Table::get_instance()->abandoned_table();
	}

	public function abd_screen_options() {
		Abandoned_Report_Table::get_instance();
		$option = 'per_page';
		$args   = array(
			'label'   => __( 'Display', 'woo-abandoned-cart-recovery' ),
			'default' => 30,
			'option'  => 'wacv_acr_per_page'
		);
		add_screen_option( $option, $args );
	}

	public function display_reports() {
		if ( isset( $_GET['page'] ) && $_GET['page'] == 'wacv_reports' ) {
			if ( isset( $_GET['tab'] ) ) {
				$tab = sanitize_text_field( $_GET['tab'] );
				switch ( $tab ) {
					case 'cart_logs':
						$section = isset( $_GET['section'] ) && $_GET['section'] == 'product' ? true : false;
						echo $this->report_header( $tab );
						//important, open after finish
						?>
						<!--cut here to format-->
                        <div class='wacv-abandoned-reports vlt-container'>
                            <div>
                                <ul class="subsubsub">
                                    <li class="<?php echo !$section?'wacv-active':''?>">
                                        <a href="<?php echo admin_url( 'admin.php?page=wacv_reports&tab=cart_logs' )?>">
                                        <?php esc_html_e('Logs azioni', 'woo-abandoned-cart-recovery')?>
                                        </a>
                                    </li>
                                    <li>|</li>
                                    <li class="<?php echo $section?'wacv-active':''?>">
                                        <a href="<?php echo admin_url( 'admin.php?page=wacv_reports&tab=cart_logs&section=product' )?>">
                                            <?php esc_html_e('Prodotti', 'woo-abandoned-cart-recovery')?>
                                        </a>
                                    </li>
                                </ul>
                            </div>
<!--                      end of cut-->
						<?php

						if ( $section ) {
							Cart_Logs_Report_Product::get_instance()->report_cart_logs();
						} else {
							Cart_Logs_Report_Table::get_instance()->report_cart_logs();
						}
						echo "</div>";
						break;
					case 'product':
						echo $this->report_header( $tab );
						echo "<div class='wacv-abandoned-reports vlt-container'>";
						Product_Report_Table::get_instance()->report_product();
						echo "</div>";
						break;
					case 'coupon':
						echo $this->report_header( $tab );
						echo "<div class='wacv-abandoned-reports vlt-container'>";
						Coupon_Used_Report_Table::get_instance()->report_coupon();
						echo "</div>";
						break;
				}
			} else {
				echo $this->report_header( 'general' );
				$this->report_general();
			}
		}
	}

	public function report_header( $active ) {
		ob_start();
		?>
        <h3><?php esc_html_e( 'Reports', 'woo-abandoned-cart-recovery' ) ?></h3>
        <div class="wacv-reports-control-bar vlt-tab-group">
            <a class="vlt-tab-item <?php echo $active == 'general' ? 'vlt-active' : '' ?>"
               href="<?php echo admin_url( 'admin.php?page=wacv_reports' ) ?>"><?php esc_html_e( 'Generale', 'woo-abandoned-cart-recovery' ) ?></a>
            <a class="vlt-tab-item <?php echo $active == 'cart_logs' ? 'vlt-active' : '' ?>"
               href="<?php echo admin_url( 'admin.php?page=wacv_reports&tab=cart_logs' ) ?>"><?php esc_html_e( 'Logs carrello', 'woo-abandoned-cart-recovery' ) ?></a>
            <a class="vlt-tab-item <?php echo $active == 'product' ? 'vlt-active' : '' ?>"
               href="<?php echo admin_url( 'admin.php?page=wacv_reports&tab=product' ) ?>"><?php esc_html_e( 'Prodotti', 'woo-abandoned-cart-recovery' ) ?></a>
            <a class="vlt-tab-item <?php echo $active == 'coupon' ? 'vlt-active' : '' ?>"
               href="<?php echo admin_url( 'admin.php?page=wacv_reports&tab=coupon' ) ?>"><?php esc_html_e( 'Coupon', 'woo-abandoned-cart-recovery' ) ?></a>
        </div>
		<?php
		return ob_get_clean();
	}

	public function report_general() {
		?>
        <div class="wacv-abandoned-reports vlt-container">
            <div class="wacv-select-time-range vlt-row">
				<?php
				$start  =  $end = $selected = '';
				$button = 'button';
				include_once WACVP_INCLUDES . 'templates/html-date-picker.php';
				?>
            </div>
            <div class="wacv-general-reports-group vlt-row"></div>
            <div class="wacv-chart-container">
                <canvas id="myChart"></canvas>
            </div>
        </div>

		<?php
	}

	public function report_screen_options() {
		if ( isset( $_GET['tab'] ) ) {
			$tab = sanitize_text_field( $_GET['tab'] );
			switch ( $tab ) {
				case 'cart_logs':
					if ( isset( $_GET['section'] ) && $_GET['section'] == 'product' ) {
						Cart_Logs_Report_Product::get_instance();
					} else {
						Cart_Logs_Report_Table::get_instance();
					}
					break;
				case 'product':
					Product_Report_Table::get_instance();
					break;
				case 'coupon':
					Coupon_Used_Report_Table::get_instance();
					break;
			}
			$option = 'per_page';
			$args   = array(
				'label'   => __( 'Display', 'woo-abandoned-cart-recovery' ),
				'default' => 30,
				'option'  => 'wacv_acr_per_page'
			);
			add_screen_option( $option, $args );
		}
	}

	public function get_reports() {
		if ( isset( $_POST['action'] ) && $_POST['action'] == 'get_reports' ) {
			if ( wp_verify_nonce( $_POST['_ajax_nonce'], 'wacv_get_reports' ) ) {
				if ( isset( $_POST['data'] ) ) {
					$data = wc_clean( $_POST['data'] );

					if ( isset( $data['time_option'] ) ) {
						$end = current_time( 'timestamp' );
						switch ( $data['time_option'] ) {
							case 'today':
								$start = strtotime( 'midnight', $end );
								$end   = strtotime( 'tomorrow', $start ) - 1;
								$this->get_final_data( $start, $end, 'P1H', 'H' );
								break;
							case 'yesterday':
								$start = strtotime( 'midnight', $end - 86400 );
								$end   = strtotime( 'tomorrow', $start ) - 1;
								$this->get_final_data( $start, $end, 'P1H', 'H' );
								break;
							case '30days':
								$start = $end - 30 * 86400;
								$this->get_final_data( $start, $end, 'P1D', 'M-d' );
								break;
							case '90days':
								$start = $end - 90 * 86400;
								$this->get_final_data( $start, $end, 'P7D', 'W', 'Week' );
								break;
							case '365days':
								$start = $end - 365 * 86400;
								$this->get_final_data( $start, $end, 'P1M', "M 'y" );
								break;
						}
					}

					if ( isset( $data['from_date'] ) || isset( $data['to_date'] ) ) {
						if ( $data['to_date'] - $data['from_date'] < 86400 ) {
							$this->get_final_data( $data['from_date'], $data['to_date'], 'P1H', "H" );
						} else {
							$this->get_final_data( $data['from_date'], $data['to_date'], 'P1D', "M-d" );
						}
					}
				}
			}
		}
		wp_die();
	}

	public function get_final_data( $start, $end, $format_step, $format_view, $prefix = '' ) {
		$response = $chart_data = array();
		$total    = $tax = $order_total = $order_tax = 0;

		if ( $format_view == 'H' ) {
			$abd_data = $rcv_data = array_fill( 0, 24, 0 );
		} else {
			$abd_data = $rcv_data = $this->get_array_time_range( $start, $end, $format_step, $format_view, $prefix );
		}
		//Abandoned report

		$abd_results = $this->query->get_abd_cart_report( $start, $end );
		$prefix      = $prefix ? $prefix . ' ' : '';

		foreach ( $abd_results as $item ) {
			$cart_items = json_decode( $item->abandoned_cart_info )->cart;
			$hour       = date_i18n( 'H', $item->abandoned_cart_time );
			$day        = date_i18n( 'd', $item->abandoned_cart_time );
			$month      = date_i18n( 'M', $item->abandoned_cart_time );
			$week       = date_i18n( 'W', $item->abandoned_cart_time );
			$year       = date_i18n( 'y', $item->abandoned_cart_time );

			$time = '';

			switch ( $format_view ) {
				case 'H':
					$time = intval( $hour );
					break;
				case 'M-d':
					$time = $month . '-' . $day;
					break;
				case "M 'y":
					$time = $month . " '" . $year;
					break;
				case 'W':
					$time = $prefix . $week;
					break;
			}

			foreach ( $cart_items as $product ) {
				$total += ( $product->line_total );
				$tax   += ( $product->line_tax );
				if ( ! isset( $abd_data[ $time ] ) ) {
					$abd_data[ $time ] = 0;
				}
				$abd_data[ $time ] += $product->line_total + $product->line_tax;
			}
		}


		//Recovered report

		$recovered_results = $this->query->get_recovered_cart_report( $start, $end );

		foreach ( $recovered_results as $item ) {
			$hour  = date_i18n( 'H', $item->abandoned_cart_time );
			$day   = date_i18n( 'd', $item->abandoned_cart_time );
			$month = date_i18n( 'M', $item->abandoned_cart_time );
			$week  = date_i18n( 'W', $item->abandoned_cart_time );
			$year  = date_i18n( 'y', $item->abandoned_cart_time );

			$time = '';

			switch ( $format_view ) {
				case 'H':
					$time = intval( $hour );
					break;
				case 'M-d':
					$time = $month . '-' . $day;
					break;
				case "M 'y":
					$time = $month . " '" . $year;
					break;
				case 'W':
					$time = $prefix . $week;
					break;
			}

			$recovered_items = wc_get_order( $item->recovered_cart );
			$order_total     += $recovered_items->get_total();
			$order_tax       += $recovered_items->get_total_tax();
			if ( ! isset( $rcv_data[ $time ] ) ) {
				$rcv_data[ $time ] = 0;
			}
			$rcv_data[ $time ] += $recovered_items->get_total() + $recovered_items->get_total_tax();
		}

		//Response report

		$_rcv_data['label'] = array_keys( $rcv_data );
		$_rcv_data['value'] = array_values( $rcv_data );
		$_abd_data['label'] = array_keys( $abd_data );
		$_abd_data['value'] = array_values( $abd_data );

		$response['abd_count']      = count( $abd_results );
		$response['abd_total']      = wc_price( $total + $tax );
		$response['abd_tax']        = wc_price( $tax );
		$response['abd_chart_data'] = $_abd_data;

		$response['rcv_count']      = count( $recovered_results );
		$response['rcv_total']      = wc_price( $order_total + $order_tax );
		$response['rcv_tax']        = wc_price( $order_tax );
		$response['rcv_chart_data'] = $_rcv_data;

		$response['email_sent']    = $this->query->get_email_history_report( $start, $end, 'email' );
		$response['email_clicked'] = $this->query->get_email_history_report( $start, $end, 'email', 1 );

		$response['messenger_sent']    = $this->query->get_email_history_report( $start, $end, 'messenger' );
		$response['messenger_clicked'] = $this->query->get_email_history_report( $start, $end, 'messenger', 1 );

		return wp_send_json( $response );
	}

	public function get_array_time_range( $start, $end, $format_step, $format_view, $prefix = '' ) {
		$prefix = $prefix ? $prefix . ' ' : '';
		$range  = array();
		$period = new \DatePeriod(
			new \DateTime( date_i18n( 'Y-m-d', $start ) ),
			new \DateInterval( $format_step ),
			new \DateTime( date_i18n( 'Y-m-d', $end + 86400 ) )
		);

		foreach ( $period as $p ) {
			$range[ $prefix . $p->format( $format_view ) ] = 0;
		}

		return $range;
	}
}
