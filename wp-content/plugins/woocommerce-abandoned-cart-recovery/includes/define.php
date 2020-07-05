<?php

namespace WACVP\Inc;


use WACVP\Inc\Email\Email_Templates;
use WACVP\Inc\Email\Get_Email;
use WACVP\Inc\Email\Send_Email_Cron;
use WACVP\Inc\Execute\Abandoned_Cart;
use WACVP\Inc\Execute\Abandoned_Order_Reminder;
use WACVP\Inc\Execute\Cart_Logs;
use WACVP\Inc\Execute\Guest;
use WACVP\Inc\Execute\Recovered;
use WACVP\Inc\Facebook\Api;
use WACVP\Inc\Facebook\FB_Plugin;
use WACVP\Inc\Facebook\Send_Message;
use WACVP\Inc\Reports\Reports;
use WACVP\Inc\Settings\Admin_Settings;
use WACVP\Inc\SMS\Send_SMS_Cron;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}


$plugin_url = plugins_url( '', __FILE__ );
$plugin_url = str_replace( '/includes', '', $plugin_url );

define( 'WACVP_CSS', $plugin_url . "/assets/css/" );
define( 'WACVP_CSS_DIR', WACVP_DIR . "assets" . DIRECTORY_SEPARATOR . "css" . DIRECTORY_SEPARATOR );
define( 'WACVP_JS', $plugin_url . "/assets/js/" );
define( 'WACVP_JS_DIR', WACVP_DIR . "assets" . DIRECTORY_SEPARATOR . "js" . DIRECTORY_SEPARATOR );
define( 'WACVP_IMAGES', $plugin_url . "/assets/img/" );
define( 'WACVP_FLAG', $plugin_url . "/assets/img/flag/" );

define( 'WACVP_MINUTE', 60 );
define( 'WACVP_HOUR', 3600 );
define( 'WACVP_DAY', 86400 );


//Auto load class
spl_autoload_register( function ( $class ) {
	$prefix   = __NAMESPACE__;
	$base_dir = __DIR__;
	$len      = strlen( $prefix );

	if ( strncmp( $prefix, $class, $len ) !== 0 ) {
		return;
	}

	$relative_class = strtolower( substr( $class, $len ) );
	$relative_class = strtolower( str_replace( '_', '-', $relative_class ) );
	$file           = $base_dir . str_replace( '\\', '/', $relative_class ) . '.php';

	if ( file_exists( $file ) ) {
		require_once $file;
	} else {
		return;
	}
} );


/*
 * Initialize Plugin
 */
function init() {
	Init::get_instance();
	Admin_Settings::get_instance();
	Guest::get_instance();
	Abandoned_Cart::get_instance();
	Recovered::get_instance();
	Send_Email_Cron::get_instance();
	Send_SMS_Cron::get_instance();
	Email_Templates::get_instance();
	Reports::get_instance();
	Ajax::get_instance();
	Cron::get_instance();
	Cart_Logs::get_instance();
	Get_Email::get_instance();
	Api::get_instance();
	FB_Plugin::get_instance();
	Send_Message::get_instance();
	Abandoned_Order_Reminder::get_instance();

	if ( is_file( WACVP_INCLUDES . 'facebook-sdk/autoload.php' ) ) {
		require_once WACVP_INCLUDES . 'facebook-sdk/autoload.php';
	}

	if ( is_file( WACVP_INCLUDES . 'support.php' ) ) {
		require_once WACVP_INCLUDES . 'support.php';
		new \VillaTheme_Support_Pro(
			array(
				'support'   => 'https://villatheme.com/supports/forum/plugins/woocommerce-abandoned-cart-recovery/',
				'docs'      => 'http://docs.villatheme.com/?item=woocommerce-abandoned-cart-recovery',
				'review'    => 'https://codecanyon.net/downloads',
				'css'       => WACVP_CSS,
				'image'     => WACVP_IMAGES,
				'slug'      => WACVP_SLUG,
				'menu_slug' => WACVP_SLUG,
				'version'   => WACVP_VERSION
			)
		);
	}
}

add_action( 'plugins_loaded', 'WACVP\\Inc\\init' );

class Init {

	protected static $instance = null;

	private function __construct() {
		add_action( 'admin_enqueue_scripts', array( $this, 'wacv_admin_enqueue' ) );
		add_action( 'init', array( $this, 'plugin_textdomain' ) );
	}

	public static function get_instance() {

		if ( null == self::$instance ) {
			self::$instance = new self;
		}

		return self::$instance;
	}

	public function plugin_textdomain() {
		$locale = is_admin() && function_exists( 'get_user_locale' ) ? get_user_locale() : get_locale();
		load_textdomain( 'woo-abandoned-cart-recovery', WACVP_LANGUAGES . "woo-abandoned-cart-recovery-$locale.mo" );
		load_plugin_textdomain( 'woo-abandoned-cart-recovery', false, WACVP_LANGUAGES );
	}


	public function wacv_admin_enqueue() {

		$page_id = get_current_screen()->id;
		switch ( $page_id ) {
			case 'abandoned-cart_page_wacv_settings':
				$this->delete_script();
				$this->plugin_enqueue_script( 'admin', array( 'jquery' ) );
				$this->plugin_enqueue_script( 'select2', array( 'jquery' ) );
				$this->plugin_enqueue_script( 'jquery.address-1.6.min', array( 'jquery' ) );
				$this->plugin_enqueue_script( 'tab.min', array( 'jquery' ) );
//				$this->plugin_enqueue_script( 'popup.min', array( 'jquery' ) );
				$this->plugin_enqueue_script( 'accordion.min', array( 'jquery' ) );
				$this->plugin_enqueue_script( 'date-picker', array( 'jquery' ) );
				wp_enqueue_script( 'wp-color-picker' );
				wp_enqueue_style( 'wp-color-picker' );

				$this->plugin_enqueue_style( array(
					'admin-settings',
					'checkbox.min',
					'select2.min',
					'form.min',
					'segment.min',
					'table.min',
					'tab.min',
					'menu.min',
					'button.min',
					'icon.min',
					'popup.min',
					'accordion.min',
					'message.min',
					'flag.min',
				) );
				$obj = array( 'ajax_url' => admin_url( 'admin-ajax.php' ) );
				wp_localize_script( WACVP_SLUG . 'admin', 'wacv_ls', $obj );
				break;

			case 'toplevel_page_wacv_sections':
				$this->delete_script();
				$this->plugin_enqueue_script( 'date-picker', array( 'jquery' ) );
				$this->plugin_enqueue_script( 'abandoned-report', array( 'jquery' ) );

				$this->plugin_enqueue_style( array(
					'admin-settings',
					'flag.min',
					'icon.min',
				) );
				$obj = array( 'ajax_url' => admin_url( 'admin-ajax.php' ) );
				wp_localize_script( WACVP_SLUG . 'abandoned-report', 'wacv_ls', $obj );
				break;

			case 'abandoned-cart_page_wacv_reports':
				$this->delete_script();
				if ( ! isset( $_GET['tab'] ) ) {
					$this->plugin_enqueue_script( 'chart.min' );
					$this->plugin_enqueue_script( 'reports', array( 'jquery' ) );
					$obj = array(
						'ajax_url' => admin_url( 'admin-ajax.php' ),
						'currency' => get_woocommerce_currency_symbol(),
						'nonce'    => wp_create_nonce( 'wacv_get_reports' )
					);
					wp_localize_script( WACVP_SLUG . 'reports', 'wacv_ls', $obj );
					$this->plugin_enqueue_style( array( 'chart.min' ) );
				} elseif ( isset( $_GET['tab'] ) && $_GET['tab'] == 'cart_logs' ) {
					$this->plugin_enqueue_script( 'date-picker', array( 'jquery' ) );
				}
				$this->plugin_enqueue_style( array( 'reports', 'w3', 'flag.min' ) );
				break;

			case 'wacv_email_template':
				wp_enqueue_media();
				$this->delete_script();
				$this->plugin_enqueue_script( 'select2' );
				$this->plugin_enqueue_script(
					'email-template',
					array( 'jquery', 'jquery-ui-sortable', 'jquery-ui-draggable', 'wp-color-picker', 'wp-tinymce' ) );

				$this->plugin_enqueue_style(
					array( 'email-template', 'w3', 'checkbox.min', 'select2.min', 'icon.min' )
				);
				wp_enqueue_style( 'wp-color-picker' );

				$obj = array(
					'ajax_url' => admin_url( 'admin-ajax.php' ),
					'img_src'  => WACVP_IMAGES,
					'nonce'    => wp_create_nonce( 'wacv_send_test_mail' ),
				);
				wp_localize_script( WACVP_SLUG . 'email-template', 'wacv_ls', $obj );
				break;
		}
	}

	public function delete_script() {
		global $wp_scripts;
		$scripts = $wp_scripts->registered;
		foreach ( $scripts as $k => $script ) {
			preg_match( '/^\/wp-/i', $script->src, $result );
			if ( count( array_filter( $result ) ) < 1 ) {
				if ( $script->handle !== 'wacv-setup-demo' && $script->handle !== 'query-monitor' ) {
					wp_dequeue_script( $script->handle );
				}
			}
		}
	}

	public function plugin_enqueue_script( $script, $depend = array() ) {
		wp_enqueue_script( WACVP_SLUG . $script, WACVP_JS . $script . '.js', $depend, true, true );
	}

	public function plugin_enqueue_style( $styles ) {
		if ( is_array( $styles ) ) {
			foreach ( $styles as $style ) {
				wp_enqueue_style( WACVP_SLUG . $style, WACVP_CSS . $style . '.css' );
			}
		} else {
			wp_enqueue_style( WACVP_SLUG . $styles, WACVP_CSS . $styles . '.css' );
		}
	}


	public function update_email_template_notice() {
		$check_notice = get_option( 'wacv_hide_notice' );
		if ( $check_notice ) {
			return;
		}
		?>
        <div id="wacv-message" class="notice notice-warning is-dismissible">
            <p style="font-size: 15px;">
				<?php _e( 'Email templates have been updated. Please re-create your email templates.', 'woo-abandoned-cart-recovery' ); ?>
            </p>
        </div>
        <script type="text/javascript" id="wacv-dismiss-notice">
            'use strict';
            jQuery(document).ready(function ($) {
                $('body').on('click', '#wacv-message .notice-dismiss', function () {
                    $.ajax({
                        url: '<?php echo admin_url( 'admin-ajax.php' )?>',
                        type: 'post',
                        data: {action: 'wacv_hide_notice'},
                        success: function (res) {
                        },
                        error: function (res) {
                        }
                    });
                });
            });
        </script>
		<?php
	}

	public function wacv_hide_notice() {
		update_option( 'wacv_hide_notice', 1 );
		wp_die();
	}
}

