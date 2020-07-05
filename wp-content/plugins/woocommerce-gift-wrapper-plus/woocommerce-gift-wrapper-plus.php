<?php
/*
 * Plugin Name: Woocommerce Gift Wrapper Plus
 * Description: This plugin shows gift wrap options on the WooCommerce cart and/or checkout page, and adds gift wrapping to the order
 * Version: 2.3.1-beta
 * WC requires at least: 3.0
 * WC tested up to: 4.0.1
 * Author: Little Package
 * Text Domain: woocommerce-gift-wrapper-plus
 * Domain path: /lang
 * 
 * Woocommerce Gift Wrapper Plus
 * Copyright: (c) 2020 Little Package 
 *
 * License: GNU General Public License v3.0
 * License URI: http://www.gnu.org/licenses/gpl-3.0.html
 *
 * Thank you for using Woocommerce Gift Wrapper Plus!
 * Find support for this plugin at web.little-package.com
 *
 * Do not use wordpress.org for support or to leave reviews for paid plugins
 *
 * check translation files
 *
 */
defined( 'ABSPATH' ) || exit; // Exit if accessed directly

if ( ! class_exists( 'WC_Gift_Wrapper_Plus' ) ) :

    if ( ! defined( 'WCGWP_PLUGIN_DIR' ) ) {
        define( 'WCGWP_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
    }
    if ( ! defined( 'WCGWP_VERSION' ) ) {
        define( 'WCGWP_VERSION', '2.3.1-beta' );
    }
    
    class WC_Gift_Wrapper_Plus {
            
        private static $instance;
        public static function instance() {

            if ( ! isset( self::$instance ) && ! ( self::$instance instanceof WC_Gift_Wrapper_Plus ) ) {
                self::$instance = new WC_Gift_Wrapper_Plus;
            }
            return self::$instance;

        }

        public function __construct() {

            register_activation_hook( __FILE__, array( $this, 'activation_hook' ) );

            // Autoloader
            require_once WCGWP_PLUGIN_DIR . '/autoloader.php';
            new WCGWP_Autoloader();
                 
            add_action( 'plugins_loaded', array( $this, 'plugins_loaded' ) );
            
        }

		/**
         * Cloning is forbidden.
         */
		public function __clone() {
			_doing_it_wrong( __FUNCTION__, __( 'Cloning is forbidden.', 'woocommerce' ), WCGWP_VERSION );
		}

        /**
         * Unserializing instances of this class is forbidden.
         */		
		public function __wakeup() {
			_doing_it_wrong( __FUNCTION__, __( 'Unserializing instances of this class is forbidden.', 'woocommerce' ), WCGWP_VERSION );
		}
        
        /**
         * Deactivate the free version of Gift Wrapper if active
         *
         * @param void
         * @return void
         */
        public function activation_hook() {

            if ( is_plugin_active( 'woocommerce-gift-wrapper/woocommerce-gift-wrapper.php' ) ) {
                deactivate_plugins( 'woocommerce-gift-wrapper/woocommerce-gift-wrapper.php' );
            }

        }

        /**
        * Load the localization 
        */
        public function plugins_loaded() {

            if ( ! class_exists( 'WooCommerce' ) ) return;        

            load_plugin_textdomain( 'woocommerce-gift-wrapper-plus', false, dirname( plugin_basename( __FILE__ ) ) . '/lang' );	

            if ( is_admin() ) {
                add_action( 'admin_enqueue_scripts', array( $this, 'admin_enqueue_scripts' ) );            
                // software license API
                $this->sl(); 
                // database update (to version 2.3) 
                $this->update_db();
                // admin notices
                new WC_Gift_Wrapper_Admin_Notices();
                // backend settings
                new WC_Gift_Wrapper_Settings();
                new WC_Gift_Wrapper_Product_Settings();
            }

            add_action( 'wp_enqueue_scripts', array( $this, 'wp_enqueue_scripts' ) );

            $this->wrapping = new WC_Gift_Wrapper_Wrapping;
            $this->product  = new WC_Gift_Wrapper_Product();
            $this->lineitem = new WC_Gift_Wrapper_Line_Item();
            $this->cart     = new WC_Gift_Wrapper_Cart();
            $this->order    = new WC_Gift_Wrapper_Order();            
            
        }	
        
		public function sl() {
		
		    $key = '';
            // retrieve our license key from the DB if exists
            $key = trim( get_option( 'wwpdf_license_key' ) );
    
            if ( ( empty( $key ) || $key == '' ) && FALSE !== get_option( 'wcgwp_data' ) ) { 
                $key = $this->get_license_key();
            }
            // setup the updater.
            new WC_Gift_Wrapper_SL(
                'https://web.little-package.com',
                'woocommerce-gift-wrapper-plus/woocommerce-gift-wrapper-plus.php',
                array(
                    'version' => WCGWP_VERSION,
                    'license' => $key,
                    'item_id' => 19097,
                    'author'  => 'Little Package',
                    'url'     => home_url(),
                    'beta'    => false,
                )
            );  	
	
		}        

        public function get_license_key() {
    
            $data = get_option( 'wcgwp_data' ); // returns FALSE if doesn't exist

            // updating from older API system
            if ( $data ) {              
                $key = trim( $data['api_key'] );
                delete_option( 'wcgwp_product_id' );  
                delete_option( 'wcgwp_delete_checkbox' );
                delete_option( 'wcgwp_instance' );
                delete_option( 'wcgwp_deactivate_checkbox' );
                delete_option( 'wcgwp_activated' );
                delete_option( 'wcgwp_data_instance' );
                delete_option( 'wcgwp_data_deactivate_checkbox' );
                delete_option( 'wcgwp_data_activated' );
                delete_option( 'wcgwp_data' );
                update_option( 'wcgwp_license_key', $key );
                return $key;
            }
            $key = trim( get_option( 'wcgwp_license_key' ) );
            return $key;
        
        }    

        public function admin_enqueue_scripts( $hook ) {

            // Load only on order (post) edit pages
            if ( $hook != 'post.php' ) return;
            wp_enqueue_style( 'wcgwp_admin_css', plugins_url( '/assets/css/wcgwp-admin.css', __FILE__ ) );

        } // End admin_enqueue_scripts()
        
                
        /*
        * Enqueue scripts
        *
        * @param void
        * @return void
        */
        public function wp_enqueue_scripts() {
        
            if ( ! is_cart() && ! is_checkout() && ! is_product() ) return;
    
            wp_register_style( 'wcgiftwrap-css', plugins_url( '/assets/css/wcgiftwrap.css', __FILE__ ), array(), null );
            wp_enqueue_style( 'wcgiftwrap-css' );

            if ( get_option( 'giftwrap_modal' ) == 'yes' 
            || get_option( 'giftwrap_line_item_modal' ) == 'yes'
            || get_option( 'giftwrap_product_display', 'checkbox' ) == 'modal' ) {
                wp_register_style( 'wcgiftwrap-modal-css', plugins_url( '/assets/css/wcgwp-modal.css', __FILE__ ), array(), null );
                wp_enqueue_style( 'wcgiftwrap-modal-css' );
                if ( get_option( 'giftwrap_bootstrap_off', 'no'  ) == 'no' ) {
                    wp_register_script( 'wcgwp-bootstrap', plugins_url( 'assets/js/wcgwp-bootstrap.min.js', __FILE__ ), 'jquery', null, TRUE );	
                    wp_enqueue_script( 'wcgwp-bootstrap' );	
                }
            } 
            // if we are *only* doing modals, dequeue slideout CSS
            if ( get_option( 'giftwrap_modal', 'yes' ) == 'yes' && get_option( 'giftwrap_line_item_modal', 'yes' ) == 'yes' && get_option( 'giftwrap_product_display', 'checkbox' ) == 'modal' ) {
                wp_dequeue_style( 'wcgiftwrap-css' );
            }

        } // End enqueue_scripts()    
        
        public function update_db() {
            global $wpdb;
            
            if ( get_option( 'wcgwp_version' ) == '2.2' ) {

                if ( get_option( 'giftwrap_simple_product' )  == 'yes' ) {
                    update_option( 'giftwrap_product_display', 'checkbox' );
                } else if ( get_option( 'giftwrap_simple_product' )  == 'no' ) {
                    update_option( 'giftwrap_product_display', 'slide-in' );
                }
                delete_option( 'giftwrap_simple_product' );
                update_option( 'wcgwp_version', '2.3', FALSE ); 

            }
            
            if ( get_option( 'wcgwp_version' ) === FALSE ) {

                $list = array ( 
                    'gift_wrap_note'                => 'wcgwp_note',
                    'line_item_wcgwp_note'          => 'wcgwp_note',
                    'product_wcgwp_note'            => 'wcgwp_note',
                    'simple_wcgwp_selection'        => 'wcgwp_selection',
                    'product_wcgwp_selection'       => 'wcgwp_selection',
                    'wcgwp_simple_price'            => 'wcgwp_price',
                );
                foreach ( $list as $old => $new ) {
                    $wpdb->query( $wpdb->prepare(
                        "UPDATE {$wpdb->prefix}woocommerce_order_itemmeta SET meta_key = REPLACE( meta_key, %s, %s )", $old, $new
                    ) );
                }
                $parent_key = 'wcgwp_line_item_parent_key';
                $wpdb->query( $wpdb->prepare(
                    "DELETE FROM {$wpdb->prefix}woocommerce_order_itemmeta WHERE meta_key = %s", $parent_key
                ) );
                // let's only do this update once
                update_option( 'wcgwp_version', '2.3', FALSE ); 

            }
            return;
        
        }

        /*
        * Discover gift wrap products in cart
        *
        * @param $cart_item
        * @return bool
        */	
        public function check_item_is_giftwrap( $product_id ) {
            
            $terms = get_the_terms( $product_id, 'product_cat' );
            if ( $terms ) {
                $giftwrap_category = get_option( 'giftwrap_category_id', 'none' );	
                foreach ( $terms as $term ) {
                    if ( $term->term_id == $giftwrap_category ) {
                        return TRUE;
                        break;
                    }
                }
                unset( $terms );
            }
            return FALSE;

        }

        /**
         * Display prices according to shop settings.
         *
         * @param  float $price
         *
         * @return float
         */
        public function get_price_for_display( $price ) {

            if ( '' === $price || '0' == $price ) return;
            $neg = false;
            if ( $price < 0 ) {
                $neg = true;
                $price *= -1;
            }
            if ( $neg ) {
                $price = '-' . $price;
            }
            return $price;

        } // end get_price_for_display()
        

    }

endif; // End if class_exists()

function WCGWrap() {
    return WC_Gift_Wrapper_Plus::instance();
}
WCGWrap();