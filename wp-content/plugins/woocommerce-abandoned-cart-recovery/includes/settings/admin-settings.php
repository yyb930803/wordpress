<?php
/**
 * Created by PhpStorm.
 * User: Villatheme-Thanh
 * Date: 29-03-19
 * Time: 8:44 AM
 */

namespace WACVP\Inc\Settings;

use WACVP\Inc\Check_Update;
use WACVP\Inc\Data;
use WACVP\Inc\Facebook\Api;
use WACVP\Inc\Plugin_Updater;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Admin_Settings {

	public static $params;
	public static $data;
	protected static $instance = null;

	public function __construct() {
		add_action( 'admin_head', array( $this, 'init' ) );
		add_action( 'init', array( $this, 'save_params' ), 1 );
		add_action( 'admin_menu', array( $this, 'admin_menu_page' ), 40 );
	}

	public static function get_instance() {
		if ( null == self::$instance ) {
			self::$instance = new self;
		}

		return self::$instance;
	}

	public function admin_menu_page() {
		add_submenu_page(
			'wacv_sections',
			__( 'Settings', 'woo-abandoned-cart-recovery' ),
			__( 'Settings', 'woo-abandoned-cart-recovery' ),
			apply_filters( 'wacv_change_role', 'manage_options' ),
			'wacv_settings',
			array( $this, 'display_settings' )
		);
	}

	public function display_settings() {
		Data::get_update_params();
		do_action( 'wacv_before_settings' );
		?>
        <div id="wacv-admin-settings">
            <div class="wacv-header">
                <h1 class="vi-ui header"><?php esc_html_e( 'Settings', 'woo-abandoned-cart-recovery' ) ?></h1>
            </div>

            <div id="wacv-settings-container">
                <form class="vi-ui form" method="post">
					<?php echo ent2ncr( self::set_nonce() ); ?>
                    <div class="vi-ui top attached tabular menu">
                        <a class="active item"
                           data-tab="general"><?php esc_html_e( 'General', 'woo-abandoned-cart-recovery' ) ?></a>
                        <a class="item"
                           data-tab="email"><?php esc_html_e( 'Email', 'woo-abandoned-cart-recovery' ) ?></a>
                        <a class="item"
                           data-tab="facebook"><?php esc_html_e( 'Facebook', 'woo-abandoned-cart-recovery' ) ?></a>
                        <a class="item" data-tab="sms"><?php esc_html_e( 'SMS', 'woo-abandoned-cart-recovery' ) ?></a>
                        <a class="item"
                           data-tab="popup"><?php esc_html_e( 'Email popup', 'woo-abandoned-cart-recovery' ) ?></a>
                        <a class="item"
                           data-tab="update"><?php esc_html_e( 'Update', 'woo-abandoned-cart-recovery' ) ?></a>
                    </div>
					<?php
					General_Settings::get_instance()->setting_page();
					Email_Settings::get_instance()->setting_page();
					FB_Messenger_Settings::get_instance()->setting_page();
					SMS_Settings::get_instance()->setting_page();
					Email_Popup_Settings::get_instance()->setting_page();
					Update::get_instance()->setting_page();
					?>
                    <div class="">
                        <button type="submit" class="vi-ui button labeled icon primary wacv-btn wacv-save-settings"
                                name="action"
                                value="save_params">
                            <i class="send icon"></i>
							<?php esc_html_e( 'Save settings', 'woo-abandoned-cart-recovery' ) ?>
                        </button>
                        <button type="submit" class="vi-ui button labeled icon  wacv-btn wacv-save-settings"
                                name="action"
                                value="save_n_check_key">
                            <i class="send icon"></i>
							<?php esc_html_e( 'Save & Check Key', 'woo-abandoned-cart-recovery' ) ?>
                        </button>
                    </div>
                </form>
            </div>
        </div>
		<?php
		do_action( 'villatheme_support_woocommerce-abandoned-cart-recovery', get_current_screen()->id );
	}

	protected static function set_nonce() {
		return wp_nonce_field( 'woo_abandoned_settings', '_woo_abandoned_cart_nonce' );
	}

	public function get_option_for_select( $filed ) {
		$options = array();
		$include = self::get_field( $filed );

		if ( is_array( $include ) && count( $include ) > 0 ) {
			$products = wc_get_products( array( 'include' => $include ) );
			foreach ( $products as $product ) {
				$options[ $product->get_id() ] = $product->get_name();
			}
		}

		return $options;
	}

	public static function get_field( $field ) {

		if ( ! self::$data ) {
			self::$data = Data::get_params();
		}
		return self::$data[ $field ];
	}

	public function get_categories() {
		$option = array();
		$args   = array(
			'taxonomy'   => "product_cat",
			'hide_empty' => 0,
			'orderby'    => 'name',
		);

		$categories = get_terms( $args );
		if ( count( $categories ) > 0 ) {
			foreach ( $categories as $category ) {
				$option[ $category->term_id ] = $category->name;
			}
		}

		return $option;
	}

	public function text_option( $field_name, $label = '', $placeholder = '', $multi = false ) {
		$set_name = $this->set_field( $field_name, $multi );
		$class    = 'wacv-' . str_replace( '_', '-', $field_name );
		?>
        <tr>
            <td class="col-1">
                <label class=""><?php esc_html_e( $label ) ?></label>
            </td>
            <td class="col-2">
                <div>
                    <input type="text"
                           name="<?php echo $set_name ?>"
                           value="<?php echo esc_html( stripslashes( self::get_field( $field_name ) ) ) ?>"
                           class="<?php echo $class ?> vlt-input vlt-border vlt-none-shadow vlt-round "
                           placeholder="<?php echo $placeholder ?>">
                </div>
            </td>
            <td class="col-3"></td>
        </tr>
		<?php
	}

	public function set_field( $name, $multi = false ) {
		return $multi ? "wacv_params[$name][]" : "wacv_params[$name]";
	}

	public function text_option_read_only( $value, $label = '', $name = '', $class = '', $sub_icon = '', $tooltip = '', $multi = false ) {
		?>
        <tr>
            <td class="col-1">
                <label class=""><?php esc_html_e( $label ) ?></label>
            </td>
            <td class="col-2">
                <div class="wacv-input-readonly-block">

                    <input type="text" value="<?php echo $value ?>" readonly="readonly"
                           class="wacv-readonly <?php echo $class . '-input' ?>"
						<?php if ( $name )
							echo "name='wacv_params[$name]'" ?> >
                    <span class="wacv-copy-icon">
                        <i class="copy outline icon"></i>
                    </span>


                </div>
            </td>
            <td class="col-3">
				<?php if ( $sub_icon ) { ?>
                    <span class="wacv-suffix-icon <?php echo $class ?>" data-tooltip="<?php echo $tooltip ?>">
                             <i class="<?php echo $sub_icon ?> icon"></i>
                        </span>
				<?php } ?>
            </td>
        </tr>
		<?php
	}

	public function number_option( $field_name, $label = '', $explain = '', $units = '', $multi = false, $min = 1, $max = 1000, $required = true ) {
		$set_name = $this->set_field( $field_name, $multi );
		$set_unit = $this->set_field( $field_name . '_unit', $multi );
		$class    = 'wacv-' . str_replace( '_', '-', $field_name );
		$col      = ! empty( $units ) ? 11 : 12;
		?>
        <tr>
            <td class="col-1">
                <label><?php esc_html_e( $label ) ?></label>
				<?php if ( $explain )
					echo '<span class="wacv-explain-group" data-tooltip="' . $explain . '" data-variation="wide"><i class="question circle icon "></i></span>' ?>
            </td>
            <td class="col-2">
                <div class="vlt-col s<?php echo $col ?>">
                    <input type="number" <?php echo $required ? 'required' : '' ?>
                           min="<?php echo $min ?>" max="<?php echo $max ?>"
                           name="<?php echo $set_name ?>"
                           value="<?php echo( self::get_field( $field_name ) ) ?>"
                           class="<?php echo $class ?>">
                </div>

            </td>
            <td>
                <div>
					<?php
					if ( ! empty( $units ) && is_array( $units ) ) {
						echo "<select name='$set_unit' class='wacv-unit'>";
						foreach ( $units as $unit ) {
							$selected = self::get_field( $field_name . '_unit' ) == $unit ? 'selected' : '';
							echo "<option $selected >$unit</option>";
						}
						echo "</select>";
					} elseif ( ! empty( $units ) ) {
						echo "<div type='text' class='wacv-unit'>$units</div>";
					}
					?>
                </div>
            </td>
        </tr>
		<?php
	}

	public function textarea_option( $field_name, $label = '', $unit = '', $multi = false ) {
		$set_name = $this->set_field( $field_name, $multi );
		$class    = 'wacv-' . str_replace( '_', '-', $field_name );
		?>
        <div>
            <div>
                <label><?php esc_html_e( $label ) ?></label>
            </div>
            <div>
                <div>
                    <textarea rows="3" class="<?php echo $class ?> vlt-textarea vlt-none-shadow vlt-round vlt-border"
                              name="<?php echo $set_name ?>"><?php echo( self::get_field( $field_name ) ) ?></textarea>
                </div>
                <div><?php echo $unit ?><span> </span></div>
            </div>
        </div>
		<?php
	}

	public function select_option( $field_name, $option = array(), $label = '', $explain = '', $units = '', $multi = false ) {
		$set_name = $this->set_field( $field_name, $multi );
		$class    = 'wacv-' . str_replace( '_', '-', $field_name );
		$col      = ! empty( $units ) ? 9 : 12;

		?>
        <tr>
            <td class="col-1">
                <label><?php esc_html_e( $label ) ?></label>
				<?php if ( $explain )
					echo '<span class="wacv-explain-group" data-tooltip="' . $explain . '" data-variation="wide"><i class="question circle icon "></i></span>' ?>
            </td>
            <td class="col-2">
                <div>
                    <select <?php echo $multi ? 'multiple' : '' ?>
                            class="<?php echo $class ?>"
                            name="<?php echo $set_name ?>">
						<?php
						if ( count( $option ) > 0 && is_array( $option ) ) {
							foreach ( $option as $value => $view ) {
								if ( is_array( self::get_field( $field_name ) ) ) {
									$selected = in_array( $value, self::get_field( $field_name ) ) ? 'selected' : '';
								} else {
									$selected = self::get_field( $field_name ) == $value ? 'selected' : '';
								}
								echo "<option value='$value' $selected>$view</option>";
							}
						} ?>
                    </select>
                </div>
            </td>
            <td class="col-3"></td>
        </tr>
		<?php
	}

	public function checkbox_option( $field_name, $label = '', $explain = '', $subffix = '' ) {
		$set_name = $this->set_field( $field_name );
		$class    = 'wacv-' . str_replace( '_', '-', $field_name );
		?>
        <tr class="">
            <td class="col-1">
                <label><?php esc_html_e( $label ) ?> </label>
				<?php if ( $explain )
					echo '<span class="wacv-explain-group" data-tooltip="' . $explain . '" data-variation="wide"><i class="question circle icon "></i></span>' ?>
            </td>
            <td class="col-2">
                <div class="vi-ui toggle checkbox">
                    <input type="checkbox" <?php checked( self::get_field( $field_name ), 1 ) ?>
                           value="1"
                           name="<?php echo $set_name ?>"
                           class="<?php echo $class ?>">
                    <label><?php esc_html_e( $subffix ) ?></label>
                </div>
            </td>
            <td class="col-3"></td>
        </tr>
		<?php
	}

	public function date_option( $field_name, $label = '' ) {
		$set_name = $this->set_field( $field_name );
		$class    = 'wacv-' . str_replace( '_', '-', $field_name );
		?>
        <div>
            <div>
                <label><?php esc_html_e( $label ) ?></label>
            </div>
            <div>
                <div>
                    <input type="date"
                           value=""
                           name="<?php echo $set_name ?>"
                           class="<?php echo $class ?>">
                </div>
            </div>
        </div>
		<?php
	}

	public function coupon_setting( $field_name, $index = 1, $coupon_rule = array() ) {

		$from_total         = isset( $coupon_rule['from_total'] ) ? $coupon_rule['from_total'] : '';
		$to_total           = isset( $coupon_rule['to_total'] ) ? $coupon_rule['to_total'] : '';
		$times              = isset( $coupon_rule['times'] ) ? $coupon_rule['times'] : '';
		$coupon_rule_method = isset( $coupon_rule['coupon_method'] ) ? $coupon_rule['coupon_method'] : '';
		$coupon_id          = isset( $coupon_rule['coupon_id'] ) ? $coupon_rule['coupon_id'] : '';
		$selected_cp_code   = ! empty( $coupon_id ) ? 'selected' : '';
		$set_name           = $this->set_field( $field_name );

		?>
        <div class="vlt-row vlt-margin-top wacv-append-target">
            <div class="vlt-third vlt-margin-bottom-8 ">
                <button class="wacv-delete-rule" type="button">-</button>
            </div>
            <div class="vlt-twothird vlt-row">
                <div class="vlt-col s2 vlt-padding-right">
                    <input type="number" name="<?php echo $set_name . '[' . $index . ']' ?>[from_total]"
                           class="vlt-input vlt-border vlt-none-shadow vlt-round" value="<?php echo $from_total ?>">
                </div>
                <div class="vlt-col s2 vlt-padding-right">
                    <input type="number" name="<?php echo $set_name . '[' . $index . ']' ?>[to_total]"
                           class="vlt-input vlt-border vlt-none-shadow vlt-round" value="<?php echo $to_total ?>">
                </div>
                <div class="vlt-col s2 vlt-padding-right">
                    <input type="number" name="<?php echo $set_name . '[' . $index . ']' ?>[times]"
                           class="vlt-input vlt-border vlt-none-shadow vlt-round" value="<?php echo $times ?>">
                </div>
                <div class="vlt-col s2 vlt-padding-right">
                    <select data-control="<?php echo $index ?>"
                            class="wacv-select-method-coupon vlt-input vlt-border vlt-none-shadow vlt-round wacv-unit "
                            name="<?php echo $set_name . '[' . $index . ']' ?>[coupon_method]">
                        <option value="wc_coupon" <?php echo $coupon_rule_method == 'wc_coupon' ? 'selected' : '' ?>><?php esc_html_e( 'WC Coupon', 'woo-abandoned-cart-recovery' ) ?></option>
                        <option value="generate" <?php echo $coupon_rule_method == 'generate' ? 'selected' : '' ?>><?php esc_html_e( 'Generate', 'woo-abandoned-cart-recovery' ) ?></option>
                    </select>
                </div>
                <div class="vlt-col s4 wacv-select-coupon-outer">
                    <select id="<?php echo $index ?>"
                            class="wacv-select-coupon vlt-input vlt-border vlt-none-shadow vlt-round wacv-unit"
                            name="<?php echo $set_name . '[' . $index . ']' ?>[coupon_id]" <?php echo $coupon_rule_method == 'generate' ? 'disabled' : '' ?>>
                        <option value="<?php echo esc_html( $coupon_id ) ?>"
							<?php echo $selected_cp_code ?>><?php echo esc_html( wc_get_coupon_code_by_id( $coupon_id ) ) ?></option>
                    </select>
                </div>
            </div>
        </div>
		<?php
	}


	public function color_field( $field_name, $label = '', $explain = '', $suffix = '' ) {
		$set_name = $this->set_field( $field_name );
		$class    = 'wacv-' . str_replace( '_', '-', $field_name );
		?>
        <tr class="">
            <td class="col-1">
                <label><?php esc_html_e( $label ) ?> </label>
				<?php if ( $explain )
					echo '<span class="wacv-explain-group" data-tooltip="' . $explain . '" data-variation="wide"><i class="question circle icon "></i></span>' ?>
            </td>
            <td class="col-2">
                <div class="">
                    <input type="text" value="<?php echo( self::get_field( $field_name ) ) ?>"
                           name="<?php echo $set_name ?>"
                           class="<?php echo $class ?> wacv-color-picker">
                    <label><?php esc_html_e( $suffix ) ?></label>
                </div>
            </td>
            <td class="col-3"></td>
        </tr>
		<?php
	}

	public function template_popup( $field_name, $label = '', $explain = '', $suffix = '' ) {
		$set_name = $this->set_field( $field_name );
		$class    = 'wacv-' . str_replace( '_', '-', $field_name );
		$value    = self::get_field( $field_name );
		?>
        <tr class="">
            <td class="col-1">
                <label><?php esc_html_e( $label ) ?> </label>
				<?php if ( $explain )
					echo '<span class="wacv-explain-group" data-tooltip="' . $explain . '" data-variation="wide"><i class="question circle icon "></i></span>' ?>
            </td>
            <td class="col-2">
                <table class="<?php echo $class ?>">
                    <tr>
                        <td>
                            <div class="wacv-select-popup-temp <?php echo $value == 'template-1' ? 'selected' : ''; ?>">
                                <div class="template-1">
                                    <p class="title">Title</p>
                                    <p class="desc">Description</p>
                                    <p class="email">Email</p>
                                    <p class="atc-btn">Add to Cart</p>
                                </div>
                                <input type="radio" value="template-1" name="<?php echo $set_name ?>"
                                       class="<?php echo $class . '-input' ?>" <?php checked( $value, 'template-1' ) ?>>
                            </div>
                        </td>
                        <td>
                            <div class="wacv-select-popup-temp <?php echo $value == 'template-2' ? 'selected' : ''; ?>">
                                <div class="template-2">
                                    <p class="title">Title</p>
                                    <p class="desc">Description</p>
                                    <div style="display: flex">
                                        <p class="email">Email</p>
                                        <p class="atc-btn">Add to Cart</p>
                                    </div>
                                </div>
                                <input type="radio" value="template-2" name="<?php echo $set_name ?>"
                                       class="<?php echo $class . '-input' ?>" <?php checked( $value, 'template-2' ) ?>>
                            </div>
                        </td>
                        <!--                        <td></td>-->
                        <!--                        <td></td>-->
                        <label><?php esc_html_e( $suffix ) ?></label>
                    </tr>
                </table>
            </td>
            <td class="col-3"></td>
        </tr>

		<?php
	}

	public function send_message_rules_settings( $field ) {
		$data = self::get_field( $field );
		?>
        <tr>
            <td class="col-1">
                <label><?php esc_html_e( 'Rules', 'woo-abandoned-cart-recovery' ) ?></label>
            </td>

            <td class="col-2">
                <table class="wacv-<?php echo $field ?>-table vi-ui celled table">
                    <thead>
                    <tr>
                        <th class="cols-1"><?php esc_html_e( 'Send after', 'woo-abandoned-cart-recovery' ); ?></th>
                        <th class="cols-2"><?php esc_html_e( 'Unit', 'woo-abandoned-cart-recovery' ); ?></th>
                        <th class="cols-3"><?php esc_html_e( 'Message', 'woo-abandoned-cart-recovery' ); ?></th>
                        <th class="cols-4"><?php esc_html_e( 'Action', 'woo-abandoned-cart-recovery' ); ?></th>
                    </tr>
                    </thead>
                    <tbody class="wacv-<?php echo $field ?>-row-target">
					<?php
					if ( isset( $data['time_to_send'] ) ) {
						$loop = count( $data['time_to_send'] );

						for ( $i = 0; $i < $loop; $i ++ ) { ?>
                            <tr class="wacv-<?php echo $field ?>-row-target" data-index="<?php echo $i ?>">
                                <td class=" wacv-messenger-time">
                                    <input type="number" name="wacv_params[<?php echo $field ?>][time_to_send][]"
                                           class="vlt-input"
                                           value="<?php echo $data['time_to_send'][ $i ] ?>" min="1">
                                </td>
                                <td class="wacv-messenger-unit">
                                    <select name="wacv_params[<?php echo $field ?>][unit][]"
                                            class="">
                                        <option value="minutes" <?php echo $data['unit'][ $i ] == 'minutes' ? 'selected' : ''; ?>><?php esc_html_e( 'minutes', 'woo-abandoned-cart-recovery' ); ?></option>
                                        <option value="hours" <?php echo $data['unit'][ $i ] == 'hours' ? 'selected' : ''; ?>><?php esc_html_e( 'hours', 'woo-abandoned-cart-recovery' ); ?></option>
                                    </select>
                                </td>
                                <td class="wacv-messenger-message">
                                    <input type="text" value="<?php echo stripslashes( $data['message'][ $i ] ) ?>"
                                           name="wacv_params[<?php echo $field ?>][message][]"
                                           class="wacv-message-content">
                                    <span class="wacv-message-length"
                                          data-tooltip="<?php esc_html_e( 'Characters left for a SMS', 'woo-abandoned-cart-recovery' ); ?>">

                                    </span>
                                </td>
                                <td align="center" class="">
                                    <button class="wacv-delete-<?php echo $field ?> vi-ui small icon red button"
                                            type="button">
                                        <i class="trash icon"> </i>
                                    </button>
                                </td>
                            </tr>
						<?php }
					} ?>
                    </tbody>
                </table>
                <button type="button" class="wacv-add-<?php echo $field ?> vi-ui small icon green button">
					<?php esc_html_e( 'Add rule', 'woo-abandoned-cart-recovery' ); ?>
                </button>
            </td>
            <td class="col-3"></td>
        </tr>
		<?php
	}

	public function save_params() {

		if ( isset( $_POST['wacv_params'] ) ) {
			if ( ! is_admin() || ! wp_verify_nonce( $_POST['_woo_abandoned_cart_nonce'], 'woo_abandoned_settings' ) ) {
				return;
			}
			if ( ! current_user_can( 'manage_options' ) ) { //apply_filters( 'wacv_change_role',
				return;
			}

			$input_data = wc_clean( $_POST['wacv_params'] );

			foreach ( Data::$params_default as $key => $value ) {
				$data[ $key ] = isset( $input_data[ $key ] ) ? $input_data[ $key ] : 0;
			}

			$data = $this->sort_rules( $data );

			update_option( 'wacv_params', $data );

			self::$data = wp_parse_args( $data, Data::$params_default );

			if ( ! empty( $data['page_id'] ) ) {
				$params = Data::get_params();
				if ( $data['page_id'] !== $params['page_id'] ) {
					$fb_api            = Api::get_instance();
					$domain            = home_url();
					$user_token        = $params['user_token'];
					$page_access_token = $fb_api->Get_Access_Token_Page( $user_token, $data['page_id'] );
					$fb_api->Set_Domain_APP( $data['page_id'], array( $domain ), $page_access_token['access_token'] );
					$fb_api->Page_SubScriber_Webhook_APP( $page_access_token['access_token'], $data['page_id'] ); //add page to webhook
				}
			}
		}
		if ( isset( $_POST['action'] ) && $_POST['action'] == 'save_n_check_key' ) {
			delete_transient( '_site_transient_update_plugins' );
			delete_option( 'woocommerce-abandoned-cart-recovery_messages' );
		}
	}

	public function sort_rules( $data ) {
		$type = array(
			'email_rules'     => 'template',
			'abd_orders'      => 'template',
			'messenger_rules' => 'message',
			'sms_abd_cart'    => 'message',
			'sms_abd_order'   => 'message'
		);
		$unit = Data::get_instance();
//		$new_data = array();
		foreach ( $type as $key => $value ) {
			if ( isset( $data[ $key ] ) ) {
				$rules = $data[ $key ];

				$count = isset( $rules['time_to_send'] ) && is_array( $rules['time_to_send'] ) ? count( $rules['time_to_send'] ) : 0;
				if ( ! $count ) {
					continue;
				}
				for ( $i = 0; $i < $count; $i ++ ) {
					if ( ! empty( $rules['time_to_send'] [ $i ] ) && ! empty( $rules[ $value ][ $i ] ) ) {
						$rules['sort'] [ $i ] = intval( $rules['time_to_send'] [ $i ] ) * $unit->case_unit( $rules['unit'] [ $i ] );
					}
				}

				asort( $rules['sort'] );
				$j         = 1;
				$new_rules = array();

				foreach ( $rules['sort'] as $k => $v ) {
					$new_rules['send_time'][]    = $j;
					$new_rules['time_to_send'][] = $rules['time_to_send'] [ $k ];
					$new_rules['unit'][]         = $rules['unit'] [ $k ];
					$new_rules[ $value ][]       = $rules[ $value ] [ $k ];
					$j ++;
				}

				$data[ $key ] = $new_rules;
			}
		}

		return $data;
	}

	public function init() {
		$key = self::get_field( 'update_key' );
		/*Check update*/
		if ( class_exists( 'WACVP\Inc\Check_Update' ) ) {
			$setting_url = admin_url( 'admin.php?page=wacv_settings' );
			new Check_Update (
				WACVP_VERSION,                    // current version
				'https://villatheme.com/wp-json/downloads/v3',  // update path
				'woocommerce-abandoned-cart-recovery/woocommerce-abandoned-cart-recovery.php',                  // plugin file slug
				'woocommerce-abandoned-cart-recovery', '29427', $key, $setting_url
			);
			new Plugin_Updater( 'woocommerce-abandoned-cart-recovery/woocommerce-abandoned-cart-recovery.php', 'woocommerce-abandoned-cart-recovery', $setting_url );
		}
	}

}
