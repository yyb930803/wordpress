<?php
/**
 * Created by PhpStorm.
 * User: Villatheme-Thanh
 * Date: 28-03-19
 * Time: 5:02 PM
 */

namespace WACVP\Inc\Email;

use WACVP\Inc\Query_DB;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Email_Templates {

	protected static $instance = null;

	/**
	 * Setup instance attributes
	 *
	 * @since     1.0.0
	 */
	public function __construct() {
		if ( is_admin() ) {
			add_action( 'admin_init', array( $this, 'register_custom_post_type' ) );
			add_action( 'add_meta_boxes', array( $this, 'add_meta_boxes' ) );
			add_action( 'save_post_wacv_email_template', array( $this, 'save_post' ) );
			add_filter( 'manage_wacv_email_template_posts_columns', array( $this, 'add_columns' ) );
			add_action( 'manage_wacv_email_template_posts_custom_column', array( $this, 'column_content' ), 10, 2 );
			add_action( 'wp_ajax_send_test_email', array( $this, 'send_test_email' ) );
			add_action( 'admin_menu', array( $this, 'admin_menu_page' ), 20 );
//			add_action( 'wp_insert_post', array( $this, 'test' ), 20,2 );
			add_filter( 'post_row_actions', array( $this, 'duplicate_email_template_row_action' ), 10, 2 );
			add_action( 'admin_action_duplicate_email', array( $this, 'duplicate_email_template' ) );
		}
	}

	public static function get_instance() {

		// If the single instance hasn't been set, set it now.
		if ( null == self::$instance ) {
			self::$instance = new self;
		}

		return self::$instance;
	}

	public function admin_menu_page() {
		add_submenu_page(
			'wacv_sections',
			__( 'Email Templates', 'woo-abandoned-cart-recovery' ),
			__( 'Email Templates', 'woo-abandoned-cart-recovery' ),
			apply_filters( 'wacv_change_role', 'manage_options' ),
			'edit.php?post_type=wacv_email_template'
		);
	}

	public function register_custom_post_type() {
		$labels = array(
			'name'               => _x( 'Email Templates', 'Post Type General Name', 'woo-abandoned-cart-recovery' ),
			'singular_name'      => _x( 'Email Templates', 'Post Type Singular Name', 'woo-abandoned-cart-recovery' ),
			'menu_name'          => __( 'Email Templates', 'woo-abandoned-cart-recovery' ),
			'parent_item_colon'  => __( 'Parent Email', 'woo-abandoned-cart-recovery' ),
			'all_items'          => __( 'All Emails', 'woo-abandoned-cart-recovery' ),
			'view_item'          => __( 'View Template', 'woo-abandoned-cart-recovery' ),
			'add_new_item'       => __( 'Add New Email Template', 'woo-abandoned-cart-recovery' ),
			'add_new'            => __( 'Add New', 'woo-abandoned-cart-recovery' ),
			'edit_item'          => __( 'Edit Email Templates', 'woo-abandoned-cart-recovery' ),
			'update_item'        => __( 'Update Email Templates', 'woo-abandoned-cart-recovery' ),
			'search_items'       => __( 'Search Email Templates', 'woo-abandoned-cart-recovery' ),
			'not_found'          => __( 'Not Found', 'woo-abandoned-cart-recovery' ),
			'not_found_in_trash' => __( 'Not found in Trash', 'woo-abandoned-cart-recovery' ),
		);

		$email_temp_args = array(
			'label'               => __( 'Email Templates', 'woo-abandoned-cart-recovery' ),
			'labels'              => $labels,
			'supports'            => array( 'title' ),
			'hierarchical'        => false,
			'public'              => false,
			'show_ui'             => true,
			'show_in_menu'        => 'edit.php?post_type=wacv_email_template',
			'show_in_nav_menus'   => true,
			'show_in_admin_bar'   => false,
			'can_export'          => true,
			'has_archive'         => true,
			'exclude_from_search' => true,
			'publicly_queryable'  => true,
			'capability_type'     => 'post',
			'query_var'           => true,
			'capabilities'        => apply_filters( 'wacv_capabilities_email_template', array() ),
			/*'create_posts' => 'do_not_allow', //Disable add new */
			'menu_position'       => null,
			'map_meta_cap'        => true,
		);

		// Registering your Custom Post Type
		register_post_type( 'wacv_email_template', $email_temp_args );
	}


	public function add_meta_boxes() {

//		add_meta_box(
//			'send_test_email',
//			__( 'Email settings', 'woo-abandoned-cart-recovery' ),
//			array( $this, 'send_test_email_box' ),
//			'wacv_email_template',
//			'side'
//		);
		add_meta_box(
			'email_settings',
			__( 'Email settings', 'woo-abandoned-cart-recovery' ),
			array( $this, 'email_settings' ),
			'wacv_email_template',
			'side'
		);
		add_meta_box(
			'preview_box',
			__( 'Email template builder', 'woo-abandoned-cart-recovery' ),
			array( $this, 'preview' ),
			'wacv_email_template'
		);
		add_meta_box(
			'coupon_setting',
			__( 'Coupon settings', 'woo-abandoned-cart-recovery' ),
			array( $this, 'coupon_setting_box' ),
			'wacv_email_template'
		);

		remove_meta_box( 'eg-meta-box', 'wacv_email_template', 'normal' );
	}


	public function email_settings( $post ) {
		$meta_value  = ( get_post_meta( $post->ID, 'wacv_email_settings_new', true ) );
		$subject     = isset( $meta_value['subject'] ) ? $meta_value['subject'] : __( 'Hey !! You left something in your cart', 'woo-abandoned-cart-recovery' );
		$heading     = isset( $meta_value['heading'] ) ? $meta_value['heading'] : __( 'Hey !! You left something in your cart', 'woo-abandoned-cart-recovery' );
		$woo_header  = isset( $meta_value['woo_header'] ) ? $meta_value['woo_header'] : '';
		$heading_stt = $woo_header ? 'block' : 'none';
		?>
        <div class="wacv-email-subject wacv-padding">
            <p class="wacv-label"><?php esc_html_e( 'Subject', 'woo-abandoned-cart-recovery' ) ?></p>
            <input class="wacv-subject" type="text" name="email_settings[subject]" required
                   value="<?php esc_html_e( $subject ) ?>">
        </div>
        <p class="wacv-label"><?php esc_html_e( "Use WC's header & footer default", 'woo-abandoned-cart-recovery' ) ?></p>
        <div class="vi-ui toggle checkbox">
            <input type="checkbox" class="wacv-use-woo-header" name="email_settings[woo_header]"
                   value="1" <?php checked( $woo_header, 1 ) ?>>
            <label></label>
        </div>
        <div class="wacv-email-heading wacv-padding" style="display: <?php echo $heading_stt ?>">
            <p class="wacv-label"><?php esc_html_e( 'Heading', 'woo-abandoned-cart-recovery' ) ?></p>
            <input class="wacv-heading" type="text" name="email_settings[heading]" required
                   value="<?php esc_html_e( $heading ) ?>">
        </div>
        <div class="wacv-send-test-email wacv-padding">
            <p class="wacv-label"><?php esc_html_e( 'Test email', 'woo-abandoned-cart-recovery' ) ?></p>
            <input type="text" class="wacv-admin-email-test" value="<?php echo get_bloginfo( 'admin_email' ) ?>">
            <br>
            <div class="wacv-send-mail-action">
                <button type="button" class="wacv-send-test-email-btn button button-primary button-large">
					<?php esc_html_e( 'Send', 'woo-abandoned-cart-recovery' ) ?>
                </button>
                <span class="wacv-spinner spinner"></span>
                <span class="wacv-result-send-test-email"></span>
            </div>
            <div class="clear"></div>
        </div>
        <hr>
        <div id="wacv-control-panel">
            <!--            <table class="wacv-control-table">-->
            <!--                <tbody>-->
            <!---->
            <!--                </tbody>-->
            <!--            </table>-->
        </div>
		<?php
	}

	public function preview( $post ) {
//		$this->drag_elements();
		?>
        <table class="wacv-email-builder-area">
            <tr>
                <td>
                    <div class="wacv-elements">
                        <div class="wacv-text-drag element"><i class="dashicons dashicons-editor-textcolor"></i> Text
                        </div>
                        <div class="wacv-image-drag element"><i class="dashicons dashicons-format-image"></i> Image
                        </div>
                        <div class="wacv-button-drag element"><i class="dashicons dashicons-video-alt3"></i> Button
                        </div>
                        <div class="wacv-cart-drag element"><i class="dashicons dashicons-cart"></i> Cart</div>
                        <div class="wacv-divider-drag element"><i class="dashicons dashicons-minus"></i> Divider</div>
                    </div>
                    <div class="wacv-template-sample">
                        <select class="wacv-change-template">
                            <option value=""><?php esc_html_e( 'Template', 'woo-abandoned-cart-recovery' ) ?></option>
                            <option value="temp-1"><?php esc_html_e( 'Template 1', 'woo-abandoned-cart-recovery' ) ?></option>
                        </select>
                    </div>
                </td>
                <td>
                    <div id="wacv-preview">
                        <div class="wacv-email-content">
							<?php echo get_post_meta( $post->ID, 'wacv_email_html_edit', true ) ?>
                        </div>
                    </div>
                </td>
            </tr>
        </table>


        <div class="wacv-html-data-edit">
            <textarea style="display: none; width: 100%" class="" name="email_edit"> </textarea>
        </div>
        <div class="wacv-html-data-save">
            <textarea style="display: none;width: 100%" class="wacv-email-content-html" name="content"> </textarea>
        </div>
        <div class="clear"></div>
		<?php
//		$this->drag_elements();
	}


	public function drag_elements() {
		?>
        <div class="wacv-elements">
            <div class="wacv-text-drag element">Text</div>
            <div class="wacv-image-drag element"> Image</div>
            <div class="wacv-button-drag element"> Button</div>
            <div class="wacv-cart-drag element"> Cart</div>
            <div class="wacv-divider-drag element"> Divider</div>
        </div>
		<?php
	}


	public function coupon_setting_box( $post ) {
		$meta_value = ( get_post_meta( $post->ID, 'wacv_email_settings_new', true ) );
		$stt1       = isset( $meta_value['use_coupon_generate'] ) ? 'none' : '';
		$stt2       = ! isset( $meta_value['use_coupon_generate'] ) ? 'none' : '';

		?>
        <div>
			<?php
			$this->checkbox_field( 'use_coupon', $meta_value, __( 'Use coupon', 'woo-abandoned-cart-recovery' ), __( "Don't apply to abandoned order", 'woo-abandoned-cart-recovery' ) );
			//			$this->number_field( 'use_coupon_with_times', $meta_value, __( 'Send coupon with times', 'woo-abandoned-cart-recovery' ) );
			$this->checkbox_field( 'use_coupon_generate', $meta_value, __( 'Generate coupon', 'woo-abandoned-cart-recovery' ) );
			?>
            <div class='wacv-select-wc-coupon' style="display: <?php echo $stt1 ?>">
				<?php
				$this->select_coupon_field( 'wc_coupon', $meta_value, __( 'Select coupon', 'woo-abandoned-cart-recovery' ) );
				?>
            </div>
            <div class='wacv-generate-coupon' style="display: <?php echo $stt2 ?>">
				<?php
				$this->select_field( 'gnr_coupon_type', $meta_value, wc_get_coupon_types(), __( 'Discount type', 'woo-abandoned-cart-recovery' ) );
				$this->number_field( 'gnr_coupon_amount', $meta_value, __( 'Coupon amount', 'woo-abandoned-cart-recovery' ) );
				$this->number_field( 'gnr_coupon_date_expiry', $meta_value, __( 'Coupon expiry after x days', 'woo-abandoned-cart-recovery' ) );
				$this->number_field( 'gnr_coupon_min_spend', $meta_value, __( 'Minimum spend', 'woo-abandoned-cart-recovery' ) );
				$this->number_field( 'gnr_coupon_max_spend', $meta_value, __( 'Maximum spend', 'woo-abandoned-cart-recovery' ) );
				$this->checkbox_field( 'gnr_coupon_individual', $meta_value, __( "Individual use only", 'woo-abandoned-cart-recovery' ) );
				$this->checkbox_field( 'gnr_coupon_exclude_sale_items', $meta_value, __( "Exclude sale items", 'woo-abandoned-cart-recovery' ) );
				$this->multi_select2_field( 'gnr_coupon_products', $this->get_products_for_select_field( $meta_value, 'gnr_coupon_products' ), __( 'Products', 'woo-abandoned-cart-recovery' ) );
				$this->multi_select2_field( 'gnr_coupon_exclude_products', $this->get_products_for_select_field( $meta_value, 'gnr_coupon_exclude_products' ), __( 'Exclude products', 'woo-abandoned-cart-recovery' ) );
				$this->multi_select2_field( 'gnr_coupon_categories', $meta_value, __( 'Product categories', 'woo-abandoned-cart-recovery' ), $this->get_categories() );
				$this->multi_select2_field( 'gnr_coupon_exclude_categories', $meta_value, __( 'Exclude categories', 'woo-abandoned-cart-recovery' ), $this->get_categories() );
				$this->number_field( 'gnr_coupon_limit_per_cp', $meta_value, __( 'Usage limit per coupon', 'woo-abandoned-cart-recovery' ) );
				$this->number_field( 'gnr_coupon_limit_x_item', $meta_value, __( 'Limit usage to X items', 'woo-abandoned-cart-recovery' ) );
				$this->number_field( 'gnr_coupon_limit_user', $meta_value, __( 'Usage limit per user', 'woo-abandoned-cart-recovery' ) );
				?>
            </div>

        </div>
		<?php
	}

	public function checkbox_field( $field_name, $meta_value, $label = '', $explain = '', $col = 6 ) {
		$class = 'wacv-' . str_replace( '_', '-', $field_name );
		?>
        <div class="vlt-row vlt-margin-top">
            <div class="vlt-third vlt-margin-bottom-8">
                <label class=""><?php esc_html_e( $label ) ?></label>
            </div>
            <div class="vlt-twothird vlt-row">
                <div class="vlt-col s<?php echo $col ?>  vi-ui toggle checkbox">
                    <input type="checkbox" <?php echo isset( $meta_value[ $field_name ] ) && $meta_value[ $field_name ] == 1 ? 'checked' : '' ?>
                           name="email_settings[<?php echo $field_name ?>]"
                           value="1"
                           class="<?php echo $class ?> vlt-input vlt-none-shadow"><label
                            class="wacv-explain"><?php echo esc_html( $explain ) ?></label>
                </div>
            </div>
        </div>
		<?php
	}

	public function select_coupon_field( $field_name, $meta_value, $label = '' ) {
		$class = 'wacv-' . str_replace( '_', '-', $field_name );
		?>
        <div class="vlt-row vlt-margin-top">
            <div class="vlt-third vlt-margin-bottom-8 ">
                <label><?php esc_html_e( $label ) ?></label>
            </div>
            <div class="vlt-twothird vlt-row">
                <div class="vlt-col s6 <?php echo $class . '-outer' ?>">
                    <select class="<?php echo $class ?> vlt-select vlt-none-shadow vlt-round "
                            name="<?php echo "email_settings[$field_name]" ?>">
						<?php
						if ( isset( $meta_value[ $field_name ] ) ) {
							$cp_code = wc_get_coupon_code_by_id( $meta_value[ $field_name ] );
							echo "<option value=" . $meta_value[ $field_name ] . ">$cp_code</option>";
						}
						?>
                    </select>
                </div>
            </div>
        </div>
		<?php
	}

	public function select_field( $field_name, $meta_value, $options, $label = '' ) {
		$class = 'wacv-' . str_replace( '_', '-', $field_name );
		?>
        <div class="vlt-row vlt-margin-top">
            <div class="vlt-third vlt-margin-bottom-8 ">
                <label><?php esc_html_e( $label ) ?></label>
            </div>
            <div class="vlt-twothird vlt-row">
                <div class="vlt-col s6 <?php echo $class . '-outer' ?>">
                    <select class="<?php echo $class ?> vlt-select  vlt-none-shadow vlt-round"
                            name="<?php echo "email_settings[$field_name]" ?>">
						<?php
						if ( is_array( $options ) ) {
							foreach ( $options as $value => $view ) {
								$selected = isset( $meta_value[ $field_name ] ) && $value == $meta_value[ $field_name ] ? 'selected' : '';
								echo "<option value='" . $value . "' $selected>$view</option>";
							}
						}
						?>
                    </select>
                </div>
            </div>
        </div>
		<?php
	}

	public function number_field( $field_name, $meta_value, $label = '', $placeholder = '', $col = 6 ) {
		$class = 'wacv-' . str_replace( '_', '-', $field_name );
		?>
        <div class="vlt-row vlt-margin-top">
            <div class="vlt-third vlt-margin-bottom-8">
                <label class=""><?php esc_html_e( $label ) ?></label>
            </div>
            <div class="vlt-twothird vlt-row">
                <div class="vlt-col s<?php echo $col ?>">
                    <input type="number" placeholder="<?php echo $placeholder ?>"
                           name="email_settings[<?php echo $field_name ?>]"
                           value="<?php echo isset( $meta_value[ $field_name ] ) ? $meta_value[ $field_name ] : '' ?>"
                           class="<?php echo $class ?> vlt-input vlt-none-shadow">
                </div>
            </div>
        </div>
		<?php
	}

	public function multi_select2_field( $field_name, $meta_value, $label = '', $options = array() ) {
		$class = 'wacv-' . str_replace( '_', '-', $field_name );
		?>
        <div class="vlt-row vlt-margin-top">
            <div class="vlt-third vlt-margin-bottom-8 ">
                <label><?php esc_html_e( $label ) ?></label>
            </div>
            <div class="vlt-twothird vlt-row">
                <div class="vlt-col s6 <?php echo $class . '-outer' ?>">
                    <select multiple="multiple"
                            class="<?php echo $class ?> vlt-select  vlt-none-shadow vlt-round "
                            name="<?php echo "email_settings[$field_name][]" ?>">
						<?php

						if ( count( $options ) > 0 ) {
							foreach ( $options as $value => $view ) {
								$selected = isset( $meta_value[ $field_name ] ) && in_array( $value, $meta_value[ $field_name ] ) ? 'selected' : '';
								echo "<option value='" . $value . "' $selected>$view</option>";
							}
						} else {
							foreach ( $meta_value as $value => $view ) {
								echo "<option value='" . $value . "' selected>$view</option>";
							}
						}
						?>
                    </select>
                </div>
            </div>
        </div>
		<?php
	}

	public function get_products_for_select_field( $list_id, $field_name ) {
		$options = array();

		if ( isset( $list_id[ $field_name ] ) && is_array( $list_id[ $field_name ] ) && count( $list_id[ $field_name ] ) > 0 ) {
			$products = wc_get_products( array( 'include' => $list_id[ $field_name ] ) );
			foreach ( $products as $product ) {
				$options[ $product->get_id() ] = $product->get_name();
			}
		}

		return $options;
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

	public function text_field( $field_name, $meta_value, $label = '', $placeholder = '', $col = 6 ) {
		$class = 'wacv-' . str_replace( '_', '-', $field_name );
		?>
        <div class="vlt-row vlt-margin-top">
            <div class="vlt-third vlt-margin-bottom-8">
                <label class=""><?php esc_html_e( $label ) ?></label>
            </div>
            <div class="vlt-twothird vlt-row">
                <div class="vlt-col s<?php echo $col ?>">
                    <input type="text" placeholder="<?php echo $placeholder ?>"
                           name="email_settings[<?php echo $field_name ?>]"
                           value="<?php echo isset( $meta_value[ $field_name ] ) ? $meta_value[ $field_name ] : '' ?>"
                           class="<?php echo $class ?> vlt-input vlt-none-shadow">
                </div>
            </div>
        </div>
		<?php
	}

	public function save_post() {

		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		if ( isset( $_POST['ID'] ) && isset( $_POST['email_edit'] ) ) {
			update_post_meta( $_POST['ID'], 'wacv_email_html_edit', trim( sanitize_post( $_POST['email_edit'] ) ) );
		}
		if ( isset( $_POST['ID'] ) && isset( $_POST['email_settings'] ) ) {
			update_post_meta( $_POST['ID'], 'wacv_email_settings_new', wc_clean( $_POST['email_settings'] ) );
		}
		if ( isset( $_POST['ID'] ) && isset( $_POST['wacv_background_color'] ) ) {
			update_post_meta( $_POST['ID'], 'wacv_background_color', sanitize_text_field( $_POST['wacv_background_color'] ) );
		}
	}

	public function add_columns( $cols ) {
		unset( $cols['date'] );
		$cols['used'] = __( 'Recovered', 'woo-abandoned-cart-recovery' );
		$cols['date'] = __( 'Date', 'woo-abandoned-cart-recovery' );

		return $cols;
	}


	public function column_content( $col_id, $id ) {

		switch ( $col_id ) {
			case 'used':
				$template_sent = Query_DB::get_instance()->count_template( $id );
				$used          = get_post_meta( $id, 'wacv_template_used', true );
				if ( $template_sent ) {
					echo( round( ( intval( $used ) / intval( $template_sent ) ) * 100, 1 ) . '%' );
				}
				break;
		}
	}


	public function send_test_email() {
		$result = false;
		if ( isset( $_POST['email'] ) && is_email( $_POST['email'] ) && wp_verify_nonce( $_POST['security'], 'wacv_send_test_mail' ) ) {
			$subject    = ! empty( $_POST['subject'] ) ? sanitize_text_field( $_POST['subject'] ) : 'Send test email';
			$heading    = ! empty( $_POST['heading'] ) ? sanitize_text_field( $_POST['heading'] ) : 'Abandoned Cart Remind';
			$woo_header = ! empty( $_POST['woo_header'] ) ? sanitize_text_field( $_POST['woo_header'] ) : '';
			$coupon     = ! empty( $_POST['coupon'] ) ? sanitize_text_field( $_POST['coupon'] ) : 'j67s4hs8';
			$to         = sanitize_email( $_POST['email'] );
			$mailer     = WC()->mailer();
			$email      = new \WC_Email();
			$message    = trim( stripslashes( sanitize_post( $_POST['content'] ) ) );
			$search     = array(
				'{coupon_code}',
				'{wacv_checkout_btn}',
				'{site_title}',
				'{customer_name}',
				'{site_address}',
				'{admin_email}',
				'{site_url}',
				'{home_url}',
				'{shop_url}',
				'{wacv_coupon_start}',
				'{wacv_coupon_end}',
				'{wacv_cart_detail_start}',
				'{wacv_cart_detail_end}',
				'{wacv_image_product}',
				'{wacv_name_&_qty_product}',
				'{product_name}',
				'{product_quantity}',
				'{product_amount}',
				'{wacv_short_description}',
				'{unsubscribe_link}',
			);
			$replace    = array(
				$coupon,
				wc_get_checkout_url(),
				get_bloginfo(),
				'John Doe',
				WC()->countries->get_base_address(),
				get_bloginfo( 'admin_email' ),
				site_url(),
				home_url(),
				get_permalink( wc_get_page_id( 'shop' ) ),
				'',
				'',
				'',
				'',
				WACVP_IMAGES . 'product-4.jpg',
				'Product name x 2',
				'Product name',
				__( 'Quantity:', 'woo-abandoned-cart-recovery' ) . ' 2',
				__( 'Price:', 'woo-abandoned-cart-recovery' ) . ' $20',
				'This is the best product',
				site_url(),
			);

			$message = str_replace( $search, $replace, $message );
			$subject = str_replace( $search, $replace, $subject );
			$headers = "Content-Type: text/html";


			if ( $woo_header ) {
				$message = $email->style_inline( $mailer->wrap_message( $heading, $message ) );

				$padding     = array( 'style="padding: 12px;', 'padding: 48px 48px 32px' );
				$new_padding = array( 'style="padding:0', 'padding:0' );
				$message     = str_replace( $padding, $new_padding, $message );
			} else {
				$message = $email->style_inline( $this->wrap_message( $message ) );
			}
			$sent_mail = $mailer->send( $to, $subject, $message, $headers, '' );

			if ( $sent_mail ) {
				$result = true;
			}
		}

		wp_send_json( $result );
		wp_die();
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


	public function duplicate_email_template_row_action( $action, $post ) {
		if ( $post->post_type == 'wacv_email_template' && current_user_can( 'edit_posts' ) ) {
			$href   = wp_nonce_url( admin_url( "admin.php?action=duplicate_email&post={$post->ID}" ), 'duplicate_email' );
			$action = array( 'duplicate' => "<a href='$href'>" . __( 'Duplicate', 'woo-abandoned-cart-recovery' ) . "</a>" ) + $action;
		}

		return $action;
	}


	public function duplicate_email_template() {
		if ( ! ( current_user_can( 'edit_posts' ) && isset( $_GET['post'] ) && isset( $_GET['_wpnonce'] ) && wp_verify_nonce( $_GET['_wpnonce'], 'duplicate_email' ) ) ) {
			wp_safe_redirect( admin_url() );
			exit;
		}
		$post_id           = sanitize_text_field( $_GET['post'] );
		$post              = get_post( $post_id );
		$args              = array(
			'post_author'  => $post->post_author,
			'post_content' => $post->post_content,
			'post_title'   => $post->post_title . ' (Duplicate)',
			'post_type'    => $post->post_type,
			'post_status'  => $post->post_status,
		);
		$post_html_edit    = get_post_meta( $post_id, 'wacv_email_html_edit', true );
		$post_settings     = get_post_meta( $post_id, 'wacv_email_settings', true );
		$post_settings_new = get_post_meta( $post_id, 'wacv_email_settings_new', true );
		$post_html_edit    = str_replace( "\\", "\\\\", $post_html_edit );

		$p_id = wp_insert_post( $args );
		update_post_meta( $p_id, 'wacv_email_html_edit', $post_html_edit );
		update_post_meta( $p_id, 'wacv_email_settings', $post_settings );
		update_post_meta( $p_id, 'wacv_email_settings_new', $post_settings_new );

		wp_safe_redirect( admin_url( "post.php?post={$p_id}&action=edit" ) );
		exit();
	}
}

