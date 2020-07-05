<?php
/**
 * Created by PhpStorm.
 * User: Villatheme-Thanh
 * Date: 06-06-19
 * Time: 8:31 AM
 */

namespace WACVP\Inc\Facebook;

use WACVP\Inc\Data;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class FB_Plugin {
	protected static $instance = null;
	private $settings;
	private $user_ref;

	public function __construct() {
		$this->settings = Data::get_params();
		$this->user_ref = md5( rand( 111111111, 999999999 ) );
		add_action( 'woocommerce_after_add_to_cart_button', array( $this, 'show_messenger_cb' ) );
//		add_action( 'wp_ajax_message', array( $this, 'facebook_return' ) );
//		add_action( 'wp_ajax_nopriv_message', array( $this, 'facebook_return' ) );
		add_action( 'wp_ajax_wacv_fb_message', array( $this, 'facebook_return' ) );
		add_action( 'wp_ajax_nopriv_wacv_fb_message', array( $this, 'facebook_return' ) );
		add_action( 'wp_ajax_wacv_review_app', array( $this, 'send_test_mode' ) );
		add_action( 'wp_ajax_nopriv_wacv_review_app', array( $this, 'send_test_mode' ) );
		add_action( 'wp_ajax_wacv_logout_fb', array( $this, 'logout_fb' ) );
		add_action( 'rest_api_init', array( $this, 'register_api' ) );
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_scripts' ) );
		add_action( 'wp_print_scripts', array( $this, 'js_function_global' ) );
	}

	public static function get_instance() {
		if ( null == self::$instance ) {
			self::$instance = new self;
		}

		return self::$instance;
	}

	public function js_function_global() {
		?>
        <script type="text/javascript">
            window.getCookie = function (cname) {
                var name = cname + "=";
                var decodedCookie = decodeURIComponent(document.cookie);
                var ca = decodedCookie.split(';');
                for (var i = 0; i < ca.length; i++) {
                    var c = ca[i];
                    while (c.charAt(0) == ' ') {
                        c = c.substring(1);
                    }
                    if (c.indexOf(name) == 0) {
                        return c.substring(name.length, c.length);
                    }
                }
                return "";
            };
            window.wacvSetCookie = function (cname, cvalue, exdays) {
                var d = new Date();
                d.setTime(d.getTime() + (exdays * 1000));//* 24 * 60 * 60
                var expires = "expires=" + d.toUTCString();
                document.cookie = cname + "=" + cvalue + ";" + expires + ";path=/";
            };
        </script>
		<?php
	}

	public function show_messenger_cb() {
		//when user logged in
		if ( ! is_user_logged_in() && $this->settings['single_page'] ) {
			return;
		}
		if ( $this->settings['app_id'] && $this->settings['app_secret'] ) {
			?>
            <script type="text/javascript">
                window.cbRequire = <?php echo $this->settings['checkbox_require']?>;

                jQuery(document).ready(function ($) {

                    if (window.connectFB && typeof window.connectFB === 'function') {
                        window.connectFB();
                    }

                    var ajaxSent = false;

                    $('.single_add_to_cart_button, .ajax_add_to_cart').on('click', function (e) {
                        if (ajaxSent || window.fbHidden) {
                            return;
                        }
                        let $this = $(this);
                        if (!window.cbStt && !window.getCookie('wacv_fb_checkbox') && window.cbRequire) {
                            e.preventDefault();
                            e.stopImmediatePropagation();
                            $('.fb-messenger-checkbox').css({
                                'border-radius': '3px',
                                'box-shadow': 'inset 0px 0px 2px 2px rgba(255,0,0,0.5)'
                            });
                        } else if (window.cbStt) {
                            e.preventDefault();
                            window.confirmOptin.run();
                            $.ajax({
                                url: '<?php echo admin_url( 'admin-ajax.php' )?>',
                                type: 'post',
                                data: {
                                    action: 'wacv_get_info',
                                    user_ref: window.user_ref
                                },
                                beforeSend: function () {
                                },
                                success: function (res) {
                                    ajaxSent = true;
                                    $this.click();
                                    $('#wacv-modal').fadeOut(200);
                                    window.wacvSetCookie('wacv_fb_checkbox', true, 86400);
                                },
                                error: function (res) {
                                }
                            });
                            fbSendReminder($this);
                        }
                    });

                    function fbSendReminder(button) {
                        let $form = button.closest('form.cart'), id = button.val(), data;

                        if ($form.length > 0) {
                            data = {
                                action: 'wacv_review_app',
                                product_id: $form.find('input[name=product_id]').val() || id,
                                'add-to-cart': $form.find('input[name=product_id]').val() || id,
                                product_sku: '',
                                quantity: $form.find('input[name=quantity]').val(),
                                variation_id: $form.find('input[name=variation_id]').val() || 0,
                                user_ref: $form.find('.fb-messenger-checkbox').attr('user_ref')
                            };
                        } else {
                            data = {
                                action: 'wacv_review_app',
                                product_id: el.attr('data-product_id'),
                                'add-to-cart': el.attr('data-product_id'),
                                product_sku: '',
                                quantity: el.attr('data-quantity'),
                                variation_id: 0,
                                user_ref: $form.find('.fb-messenger-checkbox').attr('user_ref')
                            };
                        }

                        $.ajax({
                            type: 'post',
                            url: '<?php echo admin_url( 'admin-ajax.php' )?>',
                            dataType: 'json',
                            data: data,
                            success: function (response) {
                                console.log(response);
                            },
                        });
                        return false;
                    }
                });
            </script>
            <div class="fb-messenger-checkbox-container"></div>
			<?php
		}
	}

	public function enqueue_scripts() {
		if ( ! is_user_logged_in() && $this->settings['single_page'] ) {
			return;
		}
		if ( ! $this->settings['app_id'] || ! $this->settings['app_secret'] || ! $this->settings['user_token'] ) {
			return;
		}
		$js_suffix = WP_DEBUG ? '.js' : '.min.js';

		wp_enqueue_script( 'wacv-fb-chekcbox', WACVP_JS . 'fb-checkbox-plugin' . $js_suffix, array( 'jquery' ), true );
		wp_localize_script( 'wacv-fb-chekcbox', 'Fbook',
			array(
				'ajaxUrl'   => admin_url( 'admin-ajax.php' ),
				'appLang'   => $this->settings['app_lang'],
				'appID'     => $this->settings['app_id'],
				'pageID'    => $this->settings['page_id'],
				'homeURL'   => home_url(),
				'userToken' => $this->settings['user_token'],
			) );
	}

	public function facebook_return() {
		$hub_verify_token = null;
		$verify_token     = $this->settings['app_verify_token'];
		if ( isset( $_REQUEST['hub_mode'] ) && $_REQUEST['hub_mode'] == 'subscribe' ) {
			$challenge        = $_REQUEST['hub_challenge'];
			$hub_verify_token = $_REQUEST['hub_verify_token'];
			if ( $hub_verify_token === $verify_token ) {
				header( 'HTTP/1.1 200 OK' );
				echo $challenge;
				die;
			}
		}
		$json   = file_get_contents( 'php://input' );
		$action = json_decode( $json, true );
		update_option( 'fb_webhook_return', $action );
		$settings = Data::get_params();
		if ( $settings['fb_test_mode'] ) {
			$user_ref = isset( $action['entry'][0]['messaging'][0]['optin']['user_ref'] ) ? $action['entry'][0]['messaging'][0]['optin']['user_ref'] : '';
//			if ( $user_ref ) {
//				$fb_api            = Api::get_instance();
//				$page_id           = $settings['page_id'];
//				$user_token        = $settings['user_token'];
//				$page_access_token = $fb_api->Get_Access_Token_Page( $user_token, $page_id );
//				$page_token        = $page_access_token['access_token'];
//				$message           = 'For facebook review team: Please don\'t chat with both and do follow guide in our submit form. This bot doesn\'t reply customer\'s message. It auto send a reminder message to customer when they have an abandoned cart on out website. Thanks';
//				$message   = 'You will receive a remind message about your cart after 3 minutes';
//				$send_text = $fb_api->send_message_text_user_ref( $page_id, $page_token, $message, $user_ref );
//				update_option( 'fb_webhook_result', $send_text );
//			}
			$user_id = isset( $action['entry'][0]['messaging'][0]['sender']['id'] ) ? $action['entry'][0]['messaging'][0]['sender']['id'] : '';
			$message = isset( $action['entry'][0]['messaging'][0]['message']['text'] ) ? strtolower( $action['entry'][0]['messaging'][0]['message']['text'] ) : '';
			if ( $user_id ) {
				$fb_api            = Api::get_instance();
				$page_id           = $settings['page_id'];
				$user_token        = $settings['user_token'];
				$page_access_token = $fb_api->Get_Access_Token_Page( $user_token, $page_id );
				$page_token        = $page_access_token['access_token'];
				switch ( $message ) {
					case 'hi':
					case 'hello':
						$send_message = __( 'Welcome to our store. Can I help you?', 'woo-abandoned-cart-recovery' );
						break;
					case 'help':
						$send_message = __( 'Can I help you?', 'woo-abandoned-cart-recovery' );
						break;
					case 'information':
					case 'info':
						$send_message = __( 'Please view our information at ', 'woo-abandoned-cart-recovery' ) . home_url();
						break;
				}
//				$message           = 'For facebook review team: Please don\'t chat with both and do follow guide in our submit form. This bot doesn\'t reply customer\'s message. It auto send a reminder message to customer when they have an abandoned cart on out website. Thanks';
//				$message   = __( 'Please go to single product page, check into checkbox and click "Add to cart" button.', 'woo-abandoned-cart-recovery' );
				$send_text = $fb_api->send_message_text_user_id( $page_id, $page_token, $send_message, $user_id );
				update_option( 'fb_webhook_result', $send_text );
			}
		}
		wp_die();
	}

	public function send_test_mode() {
		if ( $this->settings['fb_test_mode'] ) {
			$user_ref = sanitize_text_field( $_POST['user_ref'] );
			if ( $user_ref ) {
				$fb_api                  = Api::get_instance();
				$page_id                 = $this->settings['page_id'];
				$user_token              = $this->settings['user_token'];
				$page_access_token       = $fb_api->Get_Access_Token_Page( $user_token, $page_id );
				$page_token              = $page_access_token['access_token'];
				$message                 = __( 'You have just added to cart a product', 'woo-abandoned-cart-recovery' );
				$pid                     = $_POST['variation_id'] ? sanitize_text_field( $_POST['variation_id'] ) : sanitize_text_field( $_POST['product_id'] );
				$product                 = wc_get_product( $pid );
				$button_view_url_product = str_replace( 'http:', 'https:', get_the_permalink( $pid ) );
				$image_url               = wp_get_attachment_image_src( get_post_thumbnail_id( $pid ), 'single-post-thumbnail' );
				$array_product[]         = array(
					"title"          => $product->get_name(),
					"subtitle"       => $product->get_short_description(),
					"image_url"      => str_replace( 'http:', 'https:', $image_url[0] ),
					"default_action" => array(
						"type"                 => "web_url",
						"url"                  => $button_view_url_product,
						"messenger_extensions" => true,
						"webview_height_ratio" => "tall",
						"fallback_url"         => $button_view_url_product
					),
					"buttons"        => array(
						array(
							"type"  => "web_url",
							"url"   => $button_view_url_product,
							"title" => 'Checkout'
						)
					)
				);
				$send_text               = $fb_api->send_message_text_user_ref( $page_id, $page_token, $message, $user_ref );
				$send_product            = $fb_api->send_message_abd_cart_user_ref( $page_id, $page_token, $user_ref, $array_product );
				wp_send_json_success( $send_text );
			}
		}
		// wp_send_json_success('ddd');
		wp_die();
	}

	public function logout_fb() {
		$new_data = array( 'user_token' => '', 'page_id' => '' );
		$data     = wp_parse_args( $new_data, $this->settings );
		$result   = update_option( 'wacv_params', $data );
		if ( $result ) {
			wp_send_json_success();
		}
		wp_die();
	}

	public function register_api() {
		/*Auto update plugins*/
		register_rest_route(
			'webhook', '/return', array(
				'methods'  => 'get',
				'callback' => array( $this, 'fb_return' ),
			)
		);
	}

	public function fb_return( $request ) {
//		check( $request );
	}
}