<?php
defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'WC_Gift_Wrapper_Admin_Notices' ) ) :

    class WC_Gift_Wrapper_Admin_Notices {
        
        public function __construct() {
    
            if ( current_user_can( 'manage_woocommerce' ) ) {

                add_action( 'admin_notices',    array( $this, 'license_notice' ) );
                add_action( 'admin_notices',    array( $this, 'setup_error_notice' ) );
                add_action( 'admin_notices',    array( $this, 'redirect_error_notice' ) );
                add_action( 'admin_notices',    array( $this, 'template_file_check_notice' ) );
    
            }

        }	
        
        /*
        * Compare plugin template files with any user overrides, 
        * looking for outdated versions in order to warn user
        *
        * @param array $outdated_files
        * @return void
        */
        public static function template_file_check_notice() {
            $core_templates = WC_Admin_Status::scan_template_files( WCGWP_PLUGIN_DIR . '/templates' );
            $outdated       = FALSE;
            $outdated_files = array();
            foreach ( $core_templates as $file ) {

                $theme_file = FALSE;
                if ( file_exists( get_stylesheet_directory() . '/' . WC()->template_path() . $file ) ) {
                    $theme_file = get_stylesheet_directory() . '/' . WC()->template_path() . $file;
                } elseif ( file_exists( get_template_directory() . '/' . WC()->template_path() . $file ) ) {
                    $theme_file = get_template_directory() . '/' . WC()->template_path() . $file;
                }

                if ( FALSE !== $theme_file ) {

                    $core_version  = WC_Admin_Status::get_file_version( WCGWP_PLUGIN_DIR . '/templates/' . $file );
                    $theme_version = WC_Admin_Status::get_file_version( $theme_file );

                    if ( $core_version && $theme_version && version_compare( $theme_version, $core_version, '<' ) ) {
                        $outdated = TRUE;
                        $outdated_files[] = $file;
                        // break;
                    }
                }
            }

            if ( $outdated ) {
                $this->outdated_template_notice( $outdated_files );
            }

        } // End template_file_check_notice()

        /*
        * Provide user with heads up if template has been updated
        *
        * @param array $outdated_files
        * @return void
        */           
        public function outdated_template_notice( $outdated_files ) {

            if ( defined( 'DISABLE_NAG_NOTICES' ) && 'DISABLE_NAG_NOTICES' === TRUE ) return;

            $theme = wp_get_theme();
            ?>
            <div id="message" class="updated woocommerce-message notice notice-error is-dismissible">
                <p>
                    <?php /* translators: %s: theme name */ ?>
                    <?php printf( __( '<strong>Your theme (%s) contains outdated copies of some WooCommerce Gift Wrapper template files.</strong>' , 'woocommerce-gift-wrapper-plus' ), esc_html( $theme['Name'] ) ); ?><br />
                    <?php esc_html_e( 'The following files may need updating to ensure they are compatible with the current version of Gift Wrapper.', 'woocommerce-gift-wrapper-plus' ); ?>  
                    <ol>
                    <?php foreach ( $outdated_files as $file ) { ?>
                        <li><?php echo $file; ?></li>
                    <?php } ?>
                    </ol>
                    <?php esc_html_e( 'If you copied over a template file to your theme to change something, then you will need to copy the new version of the template and apply your changes again.', 'woocommerce-gift-wrapper-plus' ); ?>

                </p>

            </div>

        <?php } // End outdated_template_notice()        

        /*
        * Provide user with heads up if trying to use line item wrap with redirect to cart -- doesn't work
        *
        * @param void
        * @return void
        */  
        public function redirect_error_notice() {
        
            if ( 'yes' == get_option( 'woocommerce_cart_redirect_after_add' ) && 'lineitem' == get_option( 'giftwrap_per_product_type' ) ) { ?>
            
                <div class="error">
                    <p><?php echo sprintf( __( 'We noticed you have <a href="%s">WooCommerce set to redirect</a> to the cart when items are added.', 'woocommerce-gift-wrapper-plus' ), admin_url( 'admin.php?page=wc-settings&tab=products' ) ); ?>
                    <br /><?php esc_html_e( 'The WooCommerce Gift Wrapper per-product "line item" setting is not compatible with redirect, and no wrapping will occur.', 'woocommerce-gift-wrapper-plus' ); ?>
                    <br /><?php esc_html_e( ' Please choose "attribute" or do not use the Woo redirect.', 'woocommerce-gift-wrapper-plus' ); ?></p>
                </div>
            
            <?php }
        
        } // End redirect_error_notice()

        /*
        * Provide user with heads up if gift wrap category is not set
        *
        * @param void
        * @return void
        */        
        public function setup_error_notice() {
    
            if ( defined( 'DISABLE_NAG_NOTICES' ) && 'DISABLE_NAG_NOTICES' === TRUE ) return;
        
            $wrap_cat_id = get_option( 'giftwrap_category_id' );
            $display = get_option( 'giftwrap_display' );
            $all = get_option( 'giftwrap_all_products' );
            $simple = get_option( 'giftwrap_simple_product' );
            $line_item = get_option( 'giftwrap_line_item' );
             
            // admin doesn't have a gift wrap category set!
            if ( $wrap_cat_id == 'none' && ( ! in_array( 'none', $display ) || $all == 'yes' || $simple == 'yes' || $line_item == 'yes' ) ) {
                $screen = get_current_screen();
                if ( $screen->id === 'woocommerce_page_wc-settings' || $screen->id === 'woocommerce_page_wc-status' ) { ?>
                    <div id="message" class="notice notice-error is-dismissible">
                        <p><?php echo sprintf( __( 'WooCommerce Gift Wrapper is not set up properly. Please choose a category to represent your gift wrap options <a href="%s">in the settings</a>.', 'woocommerce-gift-wrapper' ), admin_url( 'admin.php?page=wc-settings&tab=products&section=wcgiftwrapper' ) ); ?></p>
                    </div>  
            <?php }
            }
    
        } // End setup_error_notice()  
                
        /*
        * Catch errors from the activation method and display to the customer
        *
        * @param void
        * @return void
        */
        public function license_notice() {

            if ( isset( $_GET['sl_activation'] ) && ! empty( $_GET['message'] ) ) {
                switch( $_GET['sl_activation'] ) {
                    case 'false':
                        $message = urldecode( $_GET['message'] );
                        ?>
                        <div class="error">
                            <p><?php echo $message; ?></p>
                        </div>
                        <?php
                        break;
                    case 'true':
                    default:
                        // Developers can put a custom success message here for when activation is successful if they way.
                        break;
                }
            }

        } // End license_notice()

    }
    
endif;