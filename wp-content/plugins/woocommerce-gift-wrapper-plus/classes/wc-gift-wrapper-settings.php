<?php
defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'WC_Gift_Wrapper_Settings' ) ) :

    class WC_Gift_Wrapper_Settings {

        public function __construct() {

            // Add settings SECTION under Woocommerce->Settings->Products
            add_filter( 'woocommerce_get_sections_products',                array( $this, 'add_section' ), 10, 1 );

            // Add settings to the section we created with add_section()
            add_filter( 'woocommerce_get_settings_products',                array( $this, 'settings' ), 10, 2 );

            // settings link on the plugins listing page
            add_filter( 'plugin_action_links',                              array( $this, 'add_settings_link' ), 10, 2 );

            add_action( 'admin_init',                                       array( $this, 'register_option' ) );
            add_action( 'admin_init',                                       array( $this, 'activate_license' ) );        
            add_action( 'admin_menu',                                       array( $this, 'license_menu' ) );

        }
    
        /*
        * Add settings link to WP plugin listing
        */
        public function add_settings_link( $links, $file ) {

            $license = sprintf( '<a href="%s">%s</a>', admin_url( 'options-general.php?page=wcgwp-license' ), __( 'License', 'woocommerce-gift-wrapper-plus' ) );
            $product_settings = sprintf( '<a href="%s">%s</a>', admin_url( 'admin.php?page=wc-settings&tab=products&section=wcgiftwrapperproduct' ), __( 'Per-Product Settings', 'woocommerce-gift-wrapper-plus' ) );
            $settings = sprintf( '<a href="%s">%s</a>', admin_url( 'admin.php?page=wc-settings&tab=products&section=wcgiftwrapper' ), __( 'Settings', 'woocommerce-gift-wrapper-plus' ) );
	        if ( $file == 'woocommerce-gift-wrapper-plus/woocommerce-gift-wrapper-plus.php' ) {
                array_unshift( $links, $settings, $product_settings, $license );
            }
            return $links;

        }

        /*
        * Add settings SECTION under Woocommerce->Settings->Products
        * @param array $sections
        * @return array
        */
        public function add_section( $sections ) {
    
            $sections['wcgiftwrapper'] = __( 'Cart/Checkout Gift Wrapping', 'woocommerce-gift-wrapper-plus' );
            return $sections;

        }

        /*
        * Add settings to the section we created with add_section()
        * @param array Settings
        * @param string Current Section
        * @return array
        */
        public static function product_cats() {
    
            $selection = array();
            $args = array(
                'orderby' 			=> 'id',
                'order' 			=> 'ASC',
                'taxonomy' 			=> 'product_cat',
                'hide_empty' 		=> '0',
                'hierarchical' 		=> '1'
            );
            
            $cats = get_categories( $args );
            $cats = isset( $cats ) ? $cats : array();
            
            if ( ! empty( $cats ) ) {
                $selection['none'] = __( 'None selected', 'woocommerce-gift-wrapper' );
                foreach ( $cats as $cat ) {
                    $selection[ $cat->term_id ] = $cat->name;
                }
            } else {
                $selection['none'] = __( 'None set up yet', 'woocommerce-gift-wrapper' );
            }
            
            return $selection;

        }        

        /*
        * Add settings to the section we created with add_section()
        * @param array Settings
        * @param string Current Section
        * @return array
        */
        public function settings( $settings, $current_section ) {

            if ( $current_section == 'wcgiftwrapper' ) {
            
                $settings = array();
 
                $settings[] = array( 
                    'name' 				=> __( 'General Gift Wrapping Options', 'woocommerce-gift-wrapper-plus' ), 
                    'type' 				=> 'title', 
                    'desc' 				=> __( 'These settings apply globally, to both cart/checkout and per-product gift wrapping.', 'woocommerce-gift-wrapper-plus' ) . '<br /><strong>1.</strong> ' . sprintf(__( 'Start by <a href="%s" target="_blank">adding at least one product</a> called "Gift Wrapping" or something similar.', 'woocommerce-gift-wrapper-plus' ), admin_url( 'post-new.php?post_type=product' ) ) . '<br /><strong>2.</strong> ' . __( 'Create a unique product category for this/these gift wrapping product(s), and add them to this category.', 'woocommerce-gift-wrapper-plus' ) . '<br /><strong>3.</strong> ' . __( 'Then consider the options below.', 'woocommerce-gift-wrapper-plus' ),
                );

                $settings[]	= array(
                    'id'				=> 'giftwrap_category_id',
                    'title'           	=> __( 'Gift wrap category', 'woocommerce-gift-wrapper-plus' ),
                    'desc_tip'			=> __( 'Define the category which holds your gift wrap product(s).', 'woocommerce-gift-wrapper-plus' ),
                    'type'            	=> 'select',
                    'default'         	=> 'none',
                    'options'         	=> self::product_cats(),
                    'class'           	=> 'chosen_select',				
                    'custom_attributes'	=> array(
                        'data-placeholder' => __( 'Define a Category', 'woocommerce-gift-wrapper-plus' )
                    ),
                );
                        
                $settings[] = array(
                    'id'       			=> 'giftwrap_details',
                    'name'     			=> __( 'Gift wrap details', 'woocommerce-gift-wrapper-plus' ),
                    'desc_tip' 			=> __( 'Optional text to give any details or conditions of your gift wrap offering. This text is not translatable by Wordpress i18n, but can be translated by WPML. Leave this field empty to translate via another means.', 'woocommerce-gift-wrapper-plus' ),
                    'type'     			=> 'textarea',
                    'default'         	=> '',
                    'css'      			=> 'min-height:100px;min-width:400px;',
                );

                $settings[] = array(
                    'id'       			=> 'giftwrap_textarea_limit',
                    'name'     			=> __( 'Textarea character limit', 'woocommerce-gift-wrapper-plus' ),
                    'desc_tip' 			=> __( 'How many characters your customer can type when creating their own note for giftwrapping. Defaults to 1000 characters; lower this number if you want shorter notes from your customers.', 'woocommerce-gift-wrapper-plus' ),
                    'type'     			=> 'number',
                    'default'         	=> '1000',
                    'css'      			=> 'min-width:300px;',
                );
            
                $settings[] = array(
                    'type' => 'sectionend',
                );
                        
                $settings[] = array( 
                    'name' 				=> __( 'Cart/Checkout Gift Wrapping Options', 'woocommerce-gift-wrapper-plus' ), 
                    'type' 				=> 'title', 
                    'desc' 				=> sprintf( __( 'These settings apply to per-order wrap options in the cart and checkout areas, not line item (per-item) wrapping. <br /><a href="%s">Per-product and line item gift wrapping options are in a separate settings tab</a>', 'woocommerce-gift-wrapper-plus' ), admin_url( 'admin.php?page=wc-settings&tab=products&section=wcgiftwrapperproduct' ) ),

                );
 
                $settings[] = array(
                    'id'       			=> 'giftwrap_display',
                    'name'     			=> __( 'Show gift wrapping', 'woocommerce-gift-wrapper-plus' ),
                    'desc_tip' 			=> __( 'Choose where to show gift wrap options to the customer on the cart page. You may choose more than one.', 'woocommerce-gift-wrapper-plus' ),
                    'type'     			=> 'multiselect',
                    'default'         	=> 'none',
                    'options'     		=> array(
                        'none'	            => __( 'None', 'woocommerce-gift-wrapper-plus' ),
                        'after_coupon'		=> __( 'Under Coupon Field in Cart', 'woocommerce-gift-wrapper-plus' ),
                        'before_cart'       => __( 'Before Cart', 'woocommerce-gift-wrapper-plus' ),
                        'after_cart'		=> __( 'After Cart', 'woocommerce-gift-wrapper-plus' ),
                        'before_checkout'	=> __( 'Before Checkout', 'woocommerce-gift-wrapper-plus' ),
                        'after_checkout'	=> __( 'After Checkout', 'woocommerce-gift-wrapper-plus' ),
                    ),
                    'css'      	        => 'min-width:300px;min-height:115px;',
                );

                $settings[] = array(
                    'id'       			=> 'giftwrap_show_thumb',
                    'name'     			=> __( 'Show thumbnails?', 'woocommerce-gift-wrapper-plus' ),
                    'desc_tip' 			=> __( 'Should gift wrap product thumbnail images be visible to customers in the cart/checkout area?', 'woocommerce-gift-wrapper-plus' ),
                    'type'     			=> 'select',
                    'default'         	=> 'yes',
                    'options'     		=> array(
                        'yes'	=> __( 'Yes', 'woocommerce-gift-wrapper-plus' ),
                        'no'	=> __( 'No', 'woocommerce-gift-wrapper-plus' ),
                    ),
                    'css'      			=> 'min-width:100px;',
                );

                $settings[] = array(
                    'id'       			=> 'giftwrap_link',
                    'name'     			=> __( 'Link thumbnails?', 'woocommerce-gift-wrapper-plus' ),
                    'desc_tip' 			=> __( 'Should thumbnail images link to gift wrap product details?', 'woocommerce-gift-wrapper-plus' ),
                    'type'     			=> 'select',
                    'default'         	=> 'yes',
                    'options'     		=> array(
                        'yes'	=> __( 'Yes', 'woocommerce-gift-wrapper-plus' ),
                        'no'	=> __( 'No', 'woocommerce-gift-wrapper-plus' ),
                    ),
                    'css'      			=> 'min-width:100px;',
                );

                $settings[] = array(
                    'id'       			=> 'giftwrap_number',
                    'name'     			=> __( 'Allow more than one gift wrap product in cart?', 'woocommerce-gift-wrapper-plus' ),
                    'desc_tip' 			=> __( 'If yes, customers can buy more than one gift wrapping product in one order.', 'woocommerce-gift-wrapper-plus' ),
                    'type'     			=> 'select',
                    'default'         	=> 'no',
                    'options'     		=> array(
                        'yes'				=> __( 'Yes', 'woocommerce-gift-wrapper-plus' ),
                        'no'				=> __( 'No', 'woocommerce-gift-wrapper-plus' ),
                    ),
                    'css'      			=> 'min-width:100px;',
                );

                $settings[] = array(
                    'id'       			=> 'giftwrap_modal',
                    'name'     			=> __( 'Should options open in pop-up?', 'woocommerce-gift-wrapper-plus' ),
                    'desc_tip' 			=> __( 'If checked, there will be a link ("header") in the cart, which when clicked will open a window for customers to choose gift wrapping options. It can be styled and might be a nicer option for your site.', 'woocommerce-gift-wrapper-plus' ),
                    'type'     			=> 'select',
                    'default'         	=> 'yes',
                    'options'     		=> array(
                        'yes'				=> __( 'Yes', 'woocommerce-gift-wrapper-plus' ),
                        'no'				=> __( 'No', 'woocommerce-gift-wrapper-plus' ),
                    ),
                    'css'      			=> 'min-width:100px;',
                );

                $settings[] = array(
                    'type' => 'sectionend',
                );
                        
                $settings[] = array( 
                    'name' 				=> __( 'Advanced Options', 'woocommerce-gift-wrapper-plus' ), 
                    'type' 				=> 'title', 
                    'desc' 				=> __( 'The nitty gritty...', 'woocommerce-gift-wrapper-plus' ),

                );                
            
                $settings[] = array(
                    'id'       			=> 'giftwrap_bootstrap_off',
                    'name'     			=> __( 'Turn off Gift Wrapper JavaScript', 'woocommerce-gift-wrapper-plus' ),
                    'type'     			=> 'checkbox',
                    'default'         	=> 'no',
                    'desc'     			=> __( 'Check to dequeue Bootstrap JS', 'woocommerce-gift-wrapper-plus' ),
                    'desc_tip'          => __( 'If you\'re having theme conflicts with pop-up windows, or your site already loads Bootstrap JS, try checking this box.', 'woocommerce-gift-wrapper-plus' ),
                    'css'      			=> 'min-width:300px;',
                );	
            
                $settings[] = array(
                    'id'       			=> 'giftwrap_delete_all',
                    'name'     			=> __( 'Leave No Trace', 'woocommerce-gift-wrapper-plus' ),
                    'type'     			=> 'checkbox',
                    'default'         	=> 'no',
                    'desc'     			=> __( 'Delete all settings upon plugin uninstall', 'woocommerce-gift-wrapper-plus' ),
                    'desc_tip'          => __( 'If you plan on deleting this plugin and not coming back, and want to keep your Wordpress database tables tidy, check this box, save settings, then delete the plugin.', 'woocommerce-gift-wrapper-plus' ),
                    'css'      			=> 'min-width:300px;',
                );

                $settings[] = array(
                    'type' => 'sectionend',
                );
            
                $settings[] = array( 
                    'name' 				=> __( 'Thank you!', 'woocommerce-gift-wrapper-plus' ), 
                    'type' 				=> 'title', 
                    'desc' 				=> __( 'We love helping WooCommerce users get the most out of their online shops<br/>' ) . sprintf(__( '<a href="%s" target="_blank">Documentation is here</a>. Need further help? <a href="%s" target="_blank">Send an email to web@little-package.com</a>. Please include as many details as possible.', 'woocommerce-gift-wrapper-plus' ), 'https://www.little-package.com/woocommerce-gift-wrapper-plus-documentation', 'mailto:web@little-package.com' ),
                );
                $settings[] = array(
                    'type' => 'sectionend',
                );
            
            }
            return $settings;
    
        }

        /*
        * Add license key settings to the WP Admin menu (under Settings)
        * @param void
        * @return void
        */        
        public function license_menu() {

	        add_options_page( 'Gift Wrapper License', 'Gift Wrapper License', 'manage_options', 'wcgwp-license', 'wcgwp_license_page' );

        }

        /*
        * Creates our settings in the options table
        * @param void
        * @return void
        */
        public function register_option() {

            register_setting( 'wcgwp_license_group', 'wcgwp_license_key', array ( 'sanitize_callback' => 'wcgwp_sanitize_license' ) );

        }
 
        /*
        * Listens for the activate/deactivate license button to be clicked, calls the API
        * @param void
        * @return void
        */        
        public function activate_license() {
        
            $license = trim( get_option( 'wcgwp_license_key' ) );        
                    
            if ( isset( $_POST['wcgwp_license_activate'] ) ) {  
                $api_params = array(
                    'edd_action' => 'activate_license',
                    'license'    => $license,
                    'item_id'    => 19097,
                    'url'        => home_url()
                ); 
            } else if ( isset( $_POST['wcgwp_license_deactivate'] ) ) {
                $api_params = array(
                    'edd_action' => 'deactivate_license',
                    'license'    => $license,
                    'item_name'  => urlencode( 'WooCommerce Gift Wrapper Plus' ),
                    'url'        => home_url()
                );    
            } else {
                return; // nothing else to do here
            } 
            // Quick security check
            if ( ! check_admin_referer( 'wcgwp_license_nonce', 'wcgwp_license_nonce' ) ) return;
            // Call the EDD Software License API
            $response = wp_remote_post( 'https://web.little-package.com', array( 'timeout' => 15, 'sslverify' => false, 'body' => $api_params ) );
            // Make sure the response came back OK
            if ( is_wp_error( $response ) || 200 !== wp_remote_retrieve_response_code( $response ) ) {
                $message =  ( is_wp_error( $response ) && ! empty( $response->get_error_message() ) ) ? $response->get_error_message() : __( 'An error occurred, please try again.' );
            } else {
                $license_data = json_decode( wp_remote_retrieve_body( $response ) );
                if ( false === $license_data->success && ! $license_data->item_name ) {
                    switch( $license_data->error ) {
                        case 'expired' :
                            $message = sprintf(
                                __( 'Your license key expired on %s.' ),
                                date_i18n( get_option( 'date_format' ), strtotime( $license_data->expires, current_time( 'timestamp' ) ) )
                            );
                            break;
                        case 'revoked' :
                            $message = __( 'Your license key has been disabled.' );
                            break;
                        case 'missing' :
                            $message = __( 'Invalid license key. Please double-check you\'ve entered it correctly.' );
                            break;
                        case 'invalid' :
                        case 'site_inactive' :
                            $message = __( 'Your license is not active for this URL.' );
                            break;
                        case 'item_name_mismatch' :
                            $message = __( 'This appears to be an invalid license key for WooCommerce Gift Wrapper Plus' );
                            break;
                        case 'no_activations_left':
                            $message = __( 'Your license key has reached its activation limit. Try deactivating on another site or purchase another license.' );
                            break;
                        default :
                            $message = __( 'An error occurred, please try again.' );
                            break;
                    }
                }
            }
            
            // Check if anything passed on a message constituting a failure
            if ( ! empty( $message ) ) {
                $base_url = admin_url( 'options-general.php?page=wcgwp-license' );
                $redirect = add_query_arg( array( 'sl_activation' => 'false', 'message' => urlencode( $message ) ), $base_url );
                wp_redirect( $redirect );
                exit();
            }
            // $license_data->license will be either "valid" or "invalid"
            if ( $license_data->license == 'deactivated' ) {
                delete_option( 'wcgwp_license_status' );
            } else {
                update_option( 'wcgwp_license_status', $license_data->license );
            }
            wp_redirect( admin_url( 'options-general.php?page=wcgwp-license' ) );
            exit();

        }
    
    } // end class WC_Gift_Wrapper_Settings

    /*
    * Add license key settings to the WP Admin menu (under Settings)
    * @param void
    * @return void
    */
    function wcgwp_license_page() {

        $license = get_option( 'wcgwp_license_key' );
        $status  = get_option( 'wcgwp_license_status' );
        ?>
        <div class="wrap">
            <h2><?php _e('WooCommerce Gift Wrapper License'); ?></h2>
            <p><?php printf( __( 'You can find your API key and email at <a href="%s" target="_blank">https://web.little-package.com/account</a> under "Purchases."', 'woocommerce-gift-wrapper-plus' ), esc_url( 'https://web.little-package.com/account' ) ); ?></p>      
            <form method="post" action="options.php">

                <?php settings_fields('wcgwp_license_group'); ?>

                <table class="form-table">
                    <tbody>
                        <tr valign="top">
                            <th scope="row" valign="top">License Key</th>
                            <td>
                                <input id="wcgwp_license_key" name="wcgwp_license_key" type="text" class="regular-text" value="<?php esc_attr_e( $license ); ?>" />
                                <label class="description" for="wcgwp_license_key">Enter your license key</label>
                            </td>
                        </tr>
                        <?php if ( false !== $license ) { ?>
                            <tr valign="top">
                                <th scope="row" valign="top">Activate License</th>
                                <td>
                                    <?php if ( $status !== false && $status == 'valid' ) { ?>
                                        <span style="color:green;margin-right:1em;vertical-align:bottom;">&check; Active</span>
                                        <?php wp_nonce_field( 'wcgwp_license_nonce', 'wcgwp_license_nonce' ); ?>
                                        <input type="submit" class="button-secondary" name="wcgwp_license_deactivate" value="Deactivate License"/>
                                    <?php } else { ?>
                                        <span style="color:red;margin-right:1em;vertical-align:bottom;">&#10008; Inactive</span>
                                        <?php wp_nonce_field( 'wcgwp_license_nonce', 'wcgwp_license_nonce' ); ?>
                                        <input type="submit" class="button-secondary" name="wcgwp_license_activate" value="Activate License"/>
                                    <?php } ?>
                                </td>
                            </tr>
                        <?php } ?>
                    </tbody>
                </table>
                <?php submit_button(); ?>
            </form>
            <p><?php echo sprintf( __( '<a href="%s">%s</a> ', 'woocommerce-gift-wrapper-plus' ), admin_url( 'admin.php?page=wc-settings&tab=products&section=wcgiftwrapper' ), __( 'Return to Gift Wrapper settings', 'woocommerce-gift-wrapper-plus' ) ); ?></p>                  
        <?php

    }
    
    function wcgwp_sanitize_license( $new ) {
    
        $old = get_option( 'wcgwp_license_key' );
        if ( $old && $old != $new ) {
            delete_option( 'wcgwp_license_status' ); // new license has been entered, so must reactivate
        }
        return $new;

    }

endif;