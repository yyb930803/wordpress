<?php
/**
 * Created by PhpStorm.
 * User: Villatheme-Thanh
 * Date: 08-06-19
 * Time: 2:35 PM
 */

namespace WACVP\Inc\Settings;

use WACVP\Inc\Data;
use WACVP\Inc\Facebook\Api;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class FB_Messenger_Settings extends Admin_Settings {

	protected static $instance = null;

	public function __construct() {

	}

	public static function get_instance() {

		if ( null == self::$instance ) {
			self::$instance = new self;
		}

		return self::$instance;
	}

	public function setting_page() {
		$data           = Data::get_params();
		$link_login     = $check = '';
		$show_field     = false;
		$link_call_back = add_query_arg( array( 'page' => 'wacv_settings' ), admin_url( 'admin.php' ) );
		$user_token     = $data['user_token'];
		if ( $data['app_id'] && $data['app_secret'] ) {
			$fb_api     = Api::get_instance();
			$check      = $user_token ? $fb_api->check_token_live( $user_token ) : false;
			$link_login = $fb_api->get_link_login(
				$link_call_back,
				array(
					'manage_pages',
					'email',
					'public_profile',
					'pages_messaging'
				) );

			if ( isset( $_GET['code'] ) ) {
				$token      = $fb_api->get_Token( $link_call_back );
				$user_token = $fb_api->extoken( $token );

				if ( $user_token == "error" ) {
					$link_call_back = $link_call_back . "&wacv_error=error";
					wp_safe_redirect( $link_call_back );
				} else {
					$new_data = ( wp_parse_args( array( 'user_token' => $user_token ), $data ) );
					update_option( 'wacv_params', $new_data );
					wp_safe_redirect( $link_call_back );
				}
				exit();
			}
		}
		do_action( 'wacv_before_fb_settings' );
		?>
        <div class="vi-ui bottom attached tab segment tab-admin" data-tab="facebook">
            <h4><?php esc_html_e( 'Connect', 'woo-abandoned-cart-recovery' ) ?></h4>
            <table class="wacv-table">
                <input type="hidden" name="wacv_params[user_token]" value="<?php echo $user_token ?>">
				<?php
				$this->text_option( 'app_id', __( 'App ID', 'woo-abandoned-cart-recovery' ) );
				$this->text_option( 'app_secret', __( 'App secret', 'woo-abandoned-cart-recovery' ) );
				$this->select_option( 'app_lang', Data::get_instance()->list_language(), __( 'Language', 'woo-abandoned-cart-recovery' ) );
				$this->text_option_read_only( admin_url( 'admin.php?page=wacv_settings' ), __( 'Valid OAuth redirected URls:', 'woo-abandoned-cart-recovery' ) );
				$this->text_option_read_only( admin_url( 'admin-ajax.php?action=wacv_fb_message' ), __( 'Callback Webhooks URL', 'woo-abandoned-cart-recovery' ) );
				$this->text_option_read_only( $data['app_verify_token'], __( 'Verify Token', 'woo-abandoned-cart-recovery' ), 'app_verify_token', 'wacv-change-token', 'sync alternate', __( 'Change webhooks verify token.', "woo-abandoned-cart-recovery" ) );
				?>
            </table>
			<?php
			if ( ! $check && $link_login ) {
				?>
                <table class='wacv-table'>
                    <tr>
                        <td class="col-1"></td>
                        <td class="col-2">
                            <a class="vi-ui primary button wacv-btn" href="<?php echo $link_login; ?>">
								<?php esc_html_e( 'Login Facebook', 'woo-abandoned-cart-recovery' ) ?>
                            </a>
                        </td>
                        <td class="col-3"></td>
                    </tr>
                </table>
				<?php
			} else {
				if ( $user_token && $data['app_id'] && $data['app_secret'] ) {
					$list_page   = $fb_api->Get_List_Page( $user_token );
					$page_option = array();
					if ( isset( $list_page['accounts'] ) ) {
						$show_field = true;
						?>
                        <table class='wacv-table'>
                            <tr>
                                <td class="col-1"></td>
                                <td class="col-2">
                                    <button type="button" class="wacv-log-out-fb wacv-btn vi-ui small icon red button">
										<?php esc_html_e( 'Log out Facebook', 'woo-abandoned-cart-recovery' ); ?>
                                    </button>
                                </td>
                                <td class="col-3"></td>
                            </tr>
                        </table>
                        <hr>

                        <h4><?php esc_html_e( 'Config', 'woo-abandoned-cart-recovery' ) ?></h4>
						<?php
						foreach ( $list_page['accounts'] as $page ) {
							$page_option[ $page['id'] ] = $page['name'];
						}
						$list_opt = count( $page_option ) > 0 ? $page_option : array( __( 'You haven\'t had any page. Create page before complete settings', 'woo-abandoned-cart-recovery' ) );
						?>
                        <table class='wacv-table'>
							<?php $this->select_option( 'page_id', $list_opt, __( 'Active page', 'woo-abandoned-cart-recovery' ) ); ?>
                        </table>

						<?php
					} else {
						if ( $link_login ) {
							?>
                            <table class='wacv-table'>
                                <tr>
                                    <td class="col-1"></td>
                                    <td class="col-2">
                                        <a class="vi-ui primary button wacv-btn" href="<?php echo $link_login; ?>">
											<?php esc_html_e( 'Reconnect Facebook', 'woo-abandoned-cart-recovery' ) ?>
                                        </a>
                                        <span style="color: red"><?php esc_html_e( 'No page was selected when connect Facebook', 'woo-abandoned-cart-recovery' ) ?></span>
                                    </td>
                                    <td class="col-3"></td>
                                </tr>
                            </table>
							<?php
						}
					}
				}
			}
			?>

            <table class='wacv-table' style="<?php echo $show_field ? '' : 'display:none;' ?>">
		        <?php
		        $this->checkbox_option( 'checkbox_require', __( "Send to messenger require", 'woo-abandoned-cart-recovery' ), __( '', 'woo-abandoned-cart-recovery' ) );
		        $this->send_message_rules_settings( 'messenger_rules' );
		        $this->checkbox_option( 'fb_test_mode', __( "Test mode", 'woo-abandoned-cart-recovery' ), __( 'If enable, a sample message will send to customer immediately', 'woo-abandoned-cart-recovery' ) ); ?>
            </table>

            <table class='wacv-table'>
                <tr>
                    <td class="col-1" valign="top">
                    </td>
                    <td class="col-2">
                        <div class="vi-ui styled accordion wacv-accor">
                            <div class="title">
                                <i class="dropdown icon"></i>
						        <?php esc_html_e( 'Config and Submit Facebook app', 'woo-abandoned-cart-recovery' ) ?>
                            </div>
                            <div class="content">
                                <ul>
                                    <li>
                                        <iframe width="560" height="315" src="https://www.youtube.com/embed/LVtyEFMsIzA"
                                                frameborder="0" allow="autoplay; encrypted-media"
                                                allowfullscreen></iframe>
                                    </li>
                                    <li>
										<?php esc_html_e( "1 - ", 'woo-abandoned-cart-recovery' ) ?>
                                        <a target="_blank"
                                           href="https://drive.google.com/file/d/1Ro6c6ZsSpm9-O4xNh4ps0v9pAac0fP77/view?usp=sharing">
											<?php esc_html_e( 'Download attachment file', "woo-abandoned-cart-recovery" ) ?>
                                        </a>
                                    </li>
                                    <li>
										<?php esc_html_e( "2 - Create facebook application at ", 'woo-abandoned-cart-recovery' ) ?>
                                        <a target="_blank" href="https://developers.facebook.com">https://developers.facebook.com</a>
                                    </li>
                                    <li>
										<?php esc_html_e( "3 - At the Dashboard, add apps: Facebook login, Webhooks, Messenger.", 'woo-abandoned-cart-recovery' ) ?>
                                    </li>
                                    <li>
										<?php esc_html_e( "4 -  Go to Settings > Basic. Copy App ID, App secret into App ID, app secret input field above.", 'woo-abandoned-cart-recovery' ) ?>
                                    </li>
                                    <li>
										<?php esc_html_e( "5 - Copy 'Valid OAuth redirected URls:' field to Facebook login > Settings in your app.", 'woo-abandoned-cart-recovery' ) ?>
                                    </li>
                                    <li>
										<?php esc_html_e( "6 - Copy 'Callback Webhooks URL' & 'Verify Token' field to Webhooks > Page.", 'woo-abandoned-cart-recovery' ) ?>
                                    </li>
                                    <li>
										<?php esc_html_e( "7 - Go to Messenger > Settings > Webhooks, click button 'Edit event', select 'messages', 'messaging_postbacks', 'messaging_optins', 'messaging_checkout_updates', 'message_echoes'.", 'woo-abandoned-cart-recovery' ) ?>
                                    </li>
                                    <li>
										<?php esc_html_e( "8 - Click 'Save settings' button.", 'woo-abandoned-cart-recovery' ) ?>
                                    </li>
                                    <li>
										<?php esc_html_e( "9 - Click 'Login Facebook' button.", 'woo-abandoned-cart-recovery' ) ?>
                                    </li>
                                    <li>
										<?php esc_html_e( "10 - Select active page and turn on test mode.", 'woo-abandoned-cart-recovery' ) ?>
                                    </li>
                                    <li>
										<?php esc_html_e( "11 - Add rule to send messages and save settings", 'woo-abandoned-cart-recovery' ) ?>
                                    </li>
                                    <li>
										<?php esc_html_e( "12 - Submit your app to Facebook, see video above", 'woo-abandoned-cart-recovery' ) ?>
                                    </li>
                                </ul>
                            </div>
                        </div>
                    </td>
                    <td class="col-3"></td>
                </tr>
            </table>
        </div>
		<?php

	}

	//Messenger rule

	public function send_messenger_rules_settings() {
		$data = self::get_field( 'messenger_rules' );
//		check( Data::get_instance()->params );
		?>
        <tr class="vlt-row vlt-margin-top">

            <td class="vlt-third col-1">
                <label><?php esc_html_e( 'Send to messenger rules', 'woo-abandoned-cart-recovery' ) ?></label>
            </td>

            <td class="vlt-twothird col-2">
                <table class="wacv-messenger-rules-table vi-ui celled table">
                    <thead>
                    <tr>
                        <th class="cols-1"><?php esc_html_e( 'Send after', 'woo-abandoned-cart-recovery' ); ?></th>
                        <th class="cols-2"><?php esc_html_e( 'Unit', 'woo-abandoned-cart-recovery' ); ?></th>
                        <th class="cols-3"><?php esc_html_e( 'Message', 'woo-abandoned-cart-recovery' ); ?></th>
                        <th class="cols-4"><?php esc_html_e( 'Action', 'woo-abandoned-cart-recovery' ); ?></th>
                    </tr>
                    </thead>
                    <tbody class="wacv-table-row-target-mess">
					<?php
					if ( isset( $data['time_to_send'] ) ) {
						$loop = count( $data['time_to_send'] );

						for ( $i = 0; $i < $loop; $i ++ ) { ?>
                            <tr class="wacv-table-row-target-mess" data-index="<?php echo $i ?>">
                                <td class="vlt-padding-small wacv-messenger-time">
                                    <input type="number" name="wacv_params[messenger_rules][time_to_send][]"
                                           class="vlt-input vlt-border vlt-none-shadow vlt-round"
                                           value="<?php echo $data['time_to_send'][ $i ] ?>" min="1">
                                </td>
                                <td class="vlt-padding-small wacv-messenger-unit">
                                    <select name="wacv_params[messenger_rules][unit][]"
                                            class="vlt-input vlt-border vlt-none-shadow vlt-round">
                                        <option value="minutes" <?php echo $data['unit'][ $i ] == 'minutes' ? 'selected' : ''; ?>><?php esc_html_e( 'minutes', 'woo-abandoned-cart-recovery' ); ?></option>
                                        <option value="hours" <?php echo $data['unit'][ $i ] == 'hours' ? 'selected' : ''; ?>><?php esc_html_e( 'hours', 'woo-abandoned-cart-recovery' ); ?></option>
                                    </select>
                                </td>
                                <td class="vlt-padding-small">
                                    <input type="text" value="<?php echo $data['message'][ $i ] ?>"
                                           name="wacv_params[messenger_rules][message][]"
                                           class="vlt-input vlt-border vlt-none-shadow vlt-round">
                                </td>
                                <td align="center" class="vlt-padding-small">
                                    <button class="wacv-delete-messenger-rule vi-ui small icon red button"
                                            type="button">
                                        <i class="trash icon"> </i>
                                    </button>
                                </td>
                            </tr>
						<?php }
					} ?>
                    </tbody>
                </table>
                <button type="button" class="wacv-add-messenger-rule vi-ui small icon green button">
					<?php esc_html_e( 'Add rule', 'woo-abandoned-cart-recovery' ); ?>
                </button>

            </td>
            <td class="col-3"></td>
        </tr>

		<?php
	}
}
