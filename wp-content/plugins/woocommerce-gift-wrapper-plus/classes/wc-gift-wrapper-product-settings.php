<?php
defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'WC_Gift_Wrapper_Product_Settings' ) ) :

    class WC_Gift_Wrapper_Product_Settings {

        public function __construct() {

            // Add settings SECTION under Woocommerce->Settings->Products
            add_filter( 'woocommerce_get_sections_products',                    array( $this, 'add_section' ), 10, 1 );

            // Add settings to the section we created with add_section()
            add_filter( 'woocommerce_get_settings_products',                    array( $this, 'settings' ), 10, 2);
        
            // Add settings to WooCommerce simple product
            add_filter( 'woocommerce_product_data_tabs',                        array( $this, 'product_write_panel_tab' ) );
            add_action( 'woocommerce_product_data_panels',                      array( $this, 'product_data_panel' ) );
            add_filter( 'woocommerce_process_product_meta',                     array( $this, 'product_data_save' ) );

            // Add settings to WooCommerce variable product - not yet a thing because would need page reload or AJAX
            // add_action( 'woocommerce_product_after_variable_attributes',     array( $this, 'product_after_variable_attributes' ), 10, 3 );
            // add_action( 'woocommerce_save_product_variation',                array( $this, 'save_product_variation' ), 10, 2 );

        }

        /*
        * Add settings SECTION under Woocommerce->Settings->Products
        *
        * @param array $sections
        * @return array
        */
        public function add_section( $sections ) {
    
            $sections['wcgiftwrapperproduct'] = __( 'Product Gift Wrapping', 'woocommerce-gift-wrapper-plus' );
            return $sections;

        }
    
        /**
         * Output queued JavaScript code in the footer inline.
         *
         * @param  string $wc_queued_js JavaScript
         * @return string Inline JavaScript
         */
        public function print_js( $wc_queued_js ) {

            if ( ! empty( $wc_queued_js ) ) {

                echo "<!-- WooCommerce Gift Wrapper Plus JavaScript -->\n<script type=\"text/javascript\">\njQuery(function($) {";
                /* Sanitize */
                $wc_queued_js = wp_check_invalid_utf8( $wc_queued_js );
                $wc_queued_js = preg_replace( '/&#(x)?0*(?(1)27|39);?/i', "'", $wc_queued_js );
                $wc_queued_js = str_replace( "\r", '', $wc_queued_js );
                echo $wc_queued_js . "});\n</script>\n";
                unset( $wc_queued_js );
            }
        }

        /*
        * Add settings to the section we created with add_section()
        *
        * @param array Settings
        * @param string Current Section
        * @return array
        */
        public function settings( $settings, $current_section ) {

            if ( $current_section == 'wcgiftwrapperproduct' ) {
          
                $settings = array();
 
                $settings[] = array( 
                    'name' 				=> __( 'Product Gift Wrapping Options', 'woocommerce-gift-wrapper-plus' ), 
                    'type' 				=> 'title', 
                    'desc' 				=> sprintf(__( 'Note: A gift wrap category will need to be set under the "<a href="%s">Cart/Checkout Gift Wrapping</a>" tab.', 'woocommerce-gift-wrapper-plus' ), admin_url( 'admin.php?page=wc-settings&tab=products&section=wcgiftwrapper' ) ),
                );
                $settings[] = array(
                    'id'       			=> 'giftwrap_all_products',
                    'name'     			=> __( 'Gift wrapping', 'woocommerce-gift-wrapper-plus' ),
                    'desc'     			=> __( 'Enable Gift Wrap for All Products', 'woocommerce-gift-wrapper-plus' ),
                    'desc_tip' 			=> __( 'If you check this box, gift wrapping will be enabled for your entire catalog, except for any excluded product categories, or individually excluded products.', 'woocommerce-gift-wrapper-plus' ),
                    'type'     			=> 'checkbox',
                    'default'         	=> 'no',
                );
                $settings[] = array(
                    'id'       			=> 'giftwrap_exclude_cats',
                    'name'     			=> __( 'Exclude product categories', 'woocommerce-gift-wrapper-plus' ),
                    'desc_tip' 			=> __( 'Choose product categories to exclude from gift wrapping. You may choose more than one.', 'woocommerce-gift-wrapper-plus' ),
                    'type'     			=> 'multiselect',
                    'default'         	=> 0,
                    'options'         	=> WC_Gift_Wrapper_Settings::product_cats(),
                    'class'           	=> 'chosen_select',
                    'custom_attributes'	=> array(
                        'data-placeholder' => __( 'Exclude these categories (optional)', 'woocommerce-gift-wrapper-plus' )
                    ),
                );
                $settings[] = array(
                    'type' => 'sectionend',
                );
                
                $settings[] = array( 
                    'name' 				=> __( 'Product page settings', 'woocommerce-gift-wrapper-plus' ), 
                    'type' 				=> 'title', 
                );
                $settings[] = array(
                    'id'       			=> 'giftwrap_product_display',
                    'name'     			=> __( 'Choose opt-in display type', 'woocommerce-gift-wrapper-plus' ),
                    'desc_tip' 			=> __( 'A slide-down menu of options appears on the page (almost from out of nowhere) when a user asks to wrap. If modal, when gift wrap links are clicked, they will open a window for customers to choose gift wrapping options. It can be styled and might be a nicer option for your site; however a simple checkbox can be just as effective.', 'woocommerce-gift-wrapper-plus' ),
                    'type'     			=> 'select',
                    'default'         	=> 'checkbox',
                    'options'     		=> array(
                        'checkbox'				=> __( 'Checkbox', 'woocommerce-gift-wrapper-plus' ),
                        'slide-in'				=> __( 'Slide-down', 'woocommerce-gift-wrapper-plus' ),
                        'modal'				    => __( 'Modal/Popup', 'woocommerce-gift-wrapper-plus' ),
                    ),
                );    
                $settings[] = array(
                    'id'       			=> 'giftwrap_per_product_type',
                    'name'     			=> __( 'How to add wrap to order?', 'woocommerce-gift-wrapper-plus' ),
                    'desc_tip' 			=> __( 'Should product gift wrap be an attribute of parent product or its own line item when added to cart? If an attribute, separate tax rates cannot be used and gift wrap will not be inventoried.', 'woocommerce-gift-wrapper-plus' ),
                    'type'     			=> 'select',
                    'default'         	=> 'attribute',
                    'options'     		=> array(
                        'attribute'				=> __( 'Product Attribute', 'woocommerce-gift-wrapper-plus' ),
                        'lineitem'				=> __( 'Line Item', 'woocommerce-gift-wrapper-plus' ),
                    ),
                );
                $settings[] = array(
                    'type' => 'sectionend',
                );

                
                $settings[] = array( 
                    'name' 				=> __( 'Cart/checkout per-product wrap settings', 'woocommerce-gift-wrapper-plus' ), 
                    'type' 				=> 'title', 
                );
                $settings[] = array(
                    'id'       			=> 'giftwrap_line_item',
                    'name'     			=> __( 'Gift wrapping in cart', 'woocommerce-gift-wrapper-plus' ),
                    'desc'     			=> __( 'Enable Gift Wrap Per Product Inside Cart', 'woocommerce-gift-wrapper-plus' ),
                    'desc_tip' 			=> __( 'If you check this box, gift wrapping will be enabled inside the cart, per product. The customer will be able to add giftwrapping to products during checkout, except for any excluded product categories, or individually excluded products.', 'woocommerce-gift-wrapper-plus' ),
                    'type'     			=> 'checkbox',
                    'default'         	=> 'no',
                );
                $settings[] = array(
                    'id'       			=> 'giftwrap_line_item_modal',
                    'name'     			=> __( 'Should cart per product gift options open in pop-up?', 'woocommerce-gift-wrapper-plus' ),
                    'desc_tip' 			=> __( 'If checked, when gift wrap links are clicked, they will open a window for customers to choose gift wrapping options. It can be styled and might be a nicer option for your site.', 'woocommerce-gift-wrapper-plus' ),
                    'type'     			=> 'select',
                    'default'         	=> 'yes',
                    'options'     		=> array(
                        'yes'				=> __( 'Yes', 'woocommerce-gift-wrapper-plus' ),
                        'no'				=> __( 'No', 'woocommerce-gift-wrapper-plus' ),
                    ),
                    'css'      			=> 'min-width:100px;',
                );  
        		$settings[] = array(
                    'id'       			=> 'giftwrap_product_num',
                    'name'     			=> __( 'Allow more than one wrap type per product?', 'woocommerce-gift-wrapper-plus' ),
                    'desc_tip' 			=> __( 'Allows items in cart to have numerous wraps. Recommend default no setting, but for custom/unusual plugin uses, this setting could come handy.', 'woocommerce-gift-wrapper-plus' ),
                    'type'     			=> 'select',
                    'default'         	=> 'no',
                    'options'     		=> array(
                        'yes'	=> __( 'Yes', 'woocommerce-gift-wrapper-plus' ),
                        'no'	=> __( 'No', 'woocommerce-gift-wrapper-plus' ),
                    ),
                    'css'      			=> 'min-width:100px;',
			    );                  
                $settings[] = array(
                    'type' => 'sectionend',
                );
                         
                $settings[] = array( 
                    'name' 				=> __( 'Beyond the basic settings', 'woocommerce-gift-wrapper-plus' ), 
                    'type' 				=> 'title', 
                );
                $settings[] = array(
                    'id'       			=> 'giftwrap_product_show_thumb',
                    'name'     			=> __( 'Show thumbnails?', 'woocommerce-gift-wrapper-plus' ),
                    'desc_tip' 			=> __( 'Should gift wrap product thumbnail images be visible?', 'woocommerce-gift-wrapper-plus' ),
                    'type'     			=> 'select',
                    'default'         	=> 'yes',
                    'options'     		=> array(
                        'yes'	=> __( 'Yes', 'woocommerce-gift-wrapper-plus' ),
                        'no'	=> __( 'No', 'woocommerce-gift-wrapper-plus' ),
                    ),
                    'css'      			=> 'min-width:100px;',
                );
        		$settings[] = array(
                    'id'       			=> 'giftwrap_product_link',
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
                    'id'       			=> 'wcgwp_product_quantity',
                    'name'     			=> __( 'Wrap Quantities', 'woocommerce-gift-wrapper-plus' ),
                    'desc_tip' 			=> __( '(Beta setting) Dictate how many giftwraps can be in cart for each line item product. Loose control, in development.', 'woocommerce-gift-wrapper-plus' ),
                    'type'     			=> 'select',
                    'default'         	=> 'ad-lib',
                    'options'     		=> array(
                        'ad-lib'	    => __( 'Any quantity of wrap', 'woocommerce-gift-wrapper-plus' ),
                        'one-to-one'	=> __( 'One-to-one wrap per product', 'woocommerce-gift-wrapper-plus' ),
                        'only-one'	    => __( 'One wrap per product', 'woocommerce-gift-wrapper-plus' ),
                    ),
                    'css'      			=> 'min-width:100px;',
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

        /**
         * Adds a new tab to the product interface
         *
         * @param array $tabs
         * @param return array
         */
        public function product_write_panel_tab( $tabs ) { 
            global $post; 
            
            $post_id = $post->ID;
            // Exit if we are on Gift Wrap product page
            if ( WCGWrap()->check_item_is_giftwrap( $post_id ) ) return $tabs;
            
            $product = wc_get_product( $post_id );
            ?>
        
            <style>#woocommerce-product-data ul.wc-tabs li.wcgiftwrapper_tab a::before{content:"\f328"}</style>

            <?php if ( isset( $tabs['wcgiftwrapperproduct'] ) ) {
                return $tabs;
            }
            $tabs['wcgiftwrapperproduct'] = array(
                'label'  => __( 'Gift Wrapper', 'woocommerce-gift-wrapper-plus' ),
                'target' => 'giftwrapper_product_options',
                'class'  => array( 'wcgiftwrapper_tab' ), // hide_if_external, hide_if_grouped, hide_if_virtual
            );
            return $tabs;
        
        }
  
        /**
         * Product category arguments, used in settings
         * @return array
         */  
        public function category_args( $product, $variable_product = FALSE, $loop = '' ) {
    
            $name = '_wcgwp_category';
            $id = '_wcgwp_category';
            if ( $variable_product === TRUE ) {
                $name = '_wcgwp_category_var[' . $loop . ']';            
                $id = '_wcgwp_category' . $loop;
            }
            $args = array(
                'taxonomy' 			=> 'product_cat',
                'show_option_none'  => __( 'Use global settings', 'woocommerce-gift-wrapper-plus' ),
                'orderby' 			=> 'id',
                'order' 			=> 'ASC',
                'hide_empty' 		=> '0',
                'hierarchical' 		=> '1',
                'name'              => $name,
                'id'                => $id,
                'class'             => 'select short',
                'selected'          => get_post_meta( $product->ID, '_wcgwp_category', TRUE ),
            );
            return $args;
    
        }   

        /**
         * Adds the panel to the product interface
         */
        public function product_data_panel() {
            global $post; 

            // Exit if we are on Gift Wrap product page
            if ( WCGWrap()->check_item_is_giftwrap( $post->ID ) ) return;
            ?>

            <div id="giftwrapper_product_options" class="panel woocommerce_options_panel">

                <div class="options_group">
                    <p class="form-field">
                        <label for="wcgwp_wrap_this"><?php esc_html_e( 'Override global settings.', 'woocommerce-gift-wrapper-plus' ); ?></label>
                        <select id="wcgwp_wrap_this" name="_wcgwp_wrap_this" class="wcgwp_select select short">
                            <?php $selected = get_post_meta( $post->ID, '_wcgwp_wrap_this', TRUE ); ?>
                            <option value="default" <?php selected( $selected, 'default' ); ?>><?php esc_html_e( 'Use global settings', 'woocommerce-gift-wrapper-plus' ); ?></option>
                            <option value="yes" <?php selected( $selected, 'yes' ); ?>><?php esc_html_e( 'Yes, gift wrap this (override)', 'woocommerce-gift-wrapper-plus' ); ?></option>
                            <option value="no" <?php selected( $selected, 'no' ); ?>><?php esc_html_e( 'No, do not gift wrap (override)', 'woocommerce-gift-wrapper-plus' ); ?></option>
                        </select>
                    </p>
                    <p class="form-field">
                        <label for="_wcgwp_category"><?php esc_html_e( 'Gift wrap category to use', 'woocommerce-gift-wrapper-plus' ); ?></label>
                        <?php wp_dropdown_categories( $this->category_args( $post ) ); ?>
                        <?php echo wc_help_tip( __( 'Define the category which holds your gift wrap product(s). Default is the global gift wrap settings category.', 'woocommerce-gift-wrapper-plus' ) ); ?>
                    </p>
                    
                    <p class="form-field">
                        <label for="_wcgwp_product"><?php esc_html_e( 'Gift wrap product to use', 'woocommerce-gift-wrapper-plus' ); ?></label>
                        <textarea name="_wcgwp_product_id" id="_wcgwp_product" placeholder="Product IDs, one per line" class="short" rows="2" cols="20"><?php echo get_post_meta( $post->ID, '_wcgwp_product_id', TRUE ); ?></textarea>
                        <?php echo wc_help_tip( __( 'Define a specific wrap product for this product, using its product ID. Overrides the category setting above.', 'woocommerce-gift-wrapper-plus' ) ); ?>
                    </p>                    
                
                    <p class="form-field">
                        <label for="wcgwp_show_thumbs"><?php esc_html_e( 'Show thumbnails?', 'woocommerce-gift-wrapper-plus' ); ?></label>
                        <select id="wcgwp_show_thumbs" name="_wcgwp_show_thumbs" class="wcgwp_select select short">
                            <?php $selected = get_post_meta( $post->ID, '_wcgwp_show_thumbs', TRUE ); ?>
                            <option value="default" <?php selected( $selected, 'default' ); ?>><?php esc_html_e( 'Not set', 'woocommerce-gift-wrapper-plus' ); ?></option>
                            <option value="yes" <?php selected( $selected, 'yes' ); ?>><?php esc_html_e( 'Yes', 'woocommerce-gift-wrapper-plus' ); ?></option>
                            <option value="no" <?php selected( $selected, 'no' ); ?>><?php esc_html_e( 'No', 'woocommerce-gift-wrapper-plus' ); ?></option>
                        </select>
                        <?php echo wc_help_tip( __( 'Should gift wrap product thumbnail images be visible to customers on product pages?', 'woocommerce-gift-wrapper-plus' ) ); ?>
                    </p>
                    <p class="form-field">
                        <label for="wcgwp_quantity"><?php esc_html_e( 'Wrap Quantities', 'woocommerce-gift-wrapper-plus' ); ?></label>
                        <select id="wcgwp_quantity" name="_wcgwp_product_quantity" class="wcgwp_select select">
                            <?php $selected = get_post_meta( $post->ID, '_wcgwp_product_quantity', TRUE ); ?>                        
                            <option value="default" <?php selected( $selected, 'default' ); ?>><?php esc_html_e( 'Not set', 'woocommerce-gift-wrapper-plus' ); ?></option>
                            <option value="ad-lib" <?php selected( $selected, 'ad-lib' ); ?>><?php esc_html_e( 'Any quantity of wrap', 'woocommerce-gift-wrapper-plus' ); ?></option>
                            <option value="one-to-one" <?php selected( $selected, 'one-to-one' ); ?>><?php esc_html_e( 'One-to-one wrap per product', 'woocommerce-gift-wrapper-plus' ); ?></option>
                            <option value="only-one" <?php selected( $selected, 'only-one' ); ?>><?php esc_html_e( 'One wrap per product', 'woocommerce-gift-wrapper-plus' ); ?></option>
                        </select>
                        <?php echo wc_help_tip( __( '(Beta setting) Dictate how many giftwraps can be in cart for each line item product. Loose control, in development.', 'woocommerce-gift-wrapper-plus' ) ); ?>
                    </p>
                </div>
            </div>

        <?php

        } // End product_data_panel()

        /**
         * Saves the data for the Watermark Tab product writepanel input boxes
         */
        public function product_data_save( $post_id ) {
        
            // Exit if we are on Gift Wrap product page
            if ( WCGWrap()->check_item_is_giftwrap( $post_id ) ) return;

            /* The parent product ID, same as post_id */
            update_post_meta( $post_id, '_parent_product_id', $post_id );
            $override = $reset = FALSE;

            if ( isset( $_POST['_wcgwp_wrap_this'] ) ) {
                update_post_meta( $post_id, '_wcgwp_wrap_this', $_POST['_wcgwp_wrap_this'] );
                if ( $_POST['_wcgwp_wrap_this'] != 'default' ) {
                    $override = TRUE;
                } else {
                    $reset = TRUE;
                }
            }
            if ( $override ) {          
                if ( isset( $_POST['_wcgwp_category'] ) && $_POST['_wcgwp_category'] != '-1' ) {
                    update_post_meta( $post_id, '_wcgwp_category', $_POST['_wcgwp_category'] );
                    delete_post_meta( $post_id, '_wcgwp_product_id' );
                }
                if ( $_POST['_wcgwp_category'] == '-1' ) {
                    delete_post_meta( $post_id, '_wcgwp_category' );
                }
                if ( isset( $_POST['_wcgwp_product_id'] ) ) {
                    // sanitize a bit in case user enters some funny business
                    $product_id = str_replace( ",", "\n", sanitize_textarea_field( $_POST['_wcgwp_product_id'] ) );
                    $product_id = str_replace( " ", "", $product_id );
                    update_post_meta( $post_id, '_wcgwp_product_id', $product_id );
                }                
                if ( isset( $_POST['_wcgwp_show_thumbs'] ) && $_POST['_wcgwp_show_thumbs'] != 'default') {
                    update_post_meta( $post_id, '_wcgwp_show_thumbs', $_POST['_wcgwp_show_thumbs'] );
                }
                if ( isset( $_POST['_wcgwp_product_quantity'] ) && $_POST['_wcgwp_product_quantity'] != 'default') {
                    update_post_meta( $post_id, '_wcgwp_product_quantity', $_POST['_wcgwp_product_quantity'] );
                }
            }
            if ( $reset ) { // keep things tidy
                delete_post_meta( $post_id, '_wcgwp_category' );
                delete_post_meta( $post_id, '_wcgwp_product_id' );
                delete_post_meta( $post_id, '_wcgwp_show_thumbs' );
                delete_post_meta( $post_id, '_wcgwp_product_quantity' );
            }

        } // End product_data_save()
    

    /************************************************
     *
     * Variable products
     *
     ***********************************************/

        /**
         * Writepanel for variable product fields
         */
        public function product_after_variable_attributes( $loop, $variation_data, $variation ) {

            // Exit if we are on Gift Wrap product page
            if ( WCGWrap()->check_item_is_giftwrap( $variation->post_parent ) ) return;

            /* Checkboxes */
            $_global_watermark = get_post_meta( $variation->post_parent, '_wcgwp_data_is_global', true );
            $is_variable_product = TRUE;
            ?>

            <div class="show_if_variation">

                <div class="form-row form-row-full">
                    <h3 class="giftwrap_heading"><?php esc_html_e( 'Gift Wrapping Options', 'woocommerce-gift-wrapper-plus' ); ?></h3>
                    <p><?php echo __( 'Tip: Use the "<strong>Save changes</strong>" button below to save these settings.', 'woocommerce-gift-wrapper-plus' ); ?></p>
                </div>

                <p class="form-row form-row-full">
                    <label for="wcgwp_data_is_global_override">
                        <span><input id="wcgwp_data_is_global_override" type="checkbox" class="wcgwp_checkbox wcgwp_global_data_set_var<?php echo $loop; ?>" name="_wcgwp_data_is_global_override[<?php echo $loop; ?>]" value='yes' <?php checked( sanitize_text_field( get_post_meta( $variation->ID, '_wcgwp_data_is_global_override', true ) ), 'yes' ); ?> /> <?php ( $_global_watermark == 'yes' ) ? esc_html_e( 'Override global to set watermark for this specific variable product.', 'woocommerce-gift-wrapper-plus' ) : esc_html_e( 'Set different watermark options for this product variation only', 'woocommerce-gift-wrapper-plus' ); ?> <?php echo '(#' . $variation->ID . ')'; ?></span>
                    </label>
                </p>
            
                <div id="wcgwp_override_chkbx<?php echo $loop; ?>">
                
                    <p class="form-row form-row-full show_if_wcgwp_global_data_set_var<?php echo $loop; ?> wcgwp_global_data_set_hide_onload_var<?php echo $loop; ?>">
                        <label for="wcgwp_wrap_this<?php echo $loop; ?>" class="tips"><?php esc_html_e( 'Turn on Gift Wrapping', 'woocommerce-gift-wrapper-plus' ); ?></label>
                        <input type="checkbox" id="wcgwp_wrap_this<?php echo $loop; ?>" name="_wcgwp_wrap_this_var[<?php echo $loop; ?>]" value="yes" <?php checked( sanitize_text_field( get_post_meta( $variation->ID, '_wcgwp_wrap_this', true ) ), 'yes' ); ?> />
                        <span class="description"><?php esc_html_e( 'Check to gift wrap this product.', 'woocommerce-gift-wrapper-plus' ); ?></span>
                    </p>
                    <p class="form-row form-row-full show_if_wcgwp_global_data_set_var<?php echo $loop; ?> wcgwp_global_data_set_hide_onload_var<?php echo $loop; ?>">
                        <label for="wcgwp_dont_wrap_this<?php echo $loop; ?>" class="tips"><?php esc_html_e( 'Turn off Gift Wrapping', 'woocommerce-gift-wrapper-plus' ); ?></label>
                        <input type="checkbox" id="wcgwp_dont_wrap_this<?php echo $loop; ?>" name="_wcgwp_dont_wrap_this_var[<?php echo $loop; ?>]" value="yes" <?php checked( sanitize_text_field( get_post_meta( $variation->ID, '_wcgwp_dont_wrap_this', true ) ), 'yes' ); ?> />
                        <span class="description"><?php esc_html_e( 'Do NOT gift wrap this product.', 'woocommerce-gift-wrapper-plus' ); ?></span>
                    </p>
                    
                    <p class="form-row form-row-full show_if_wcgwp_global_data_set_var<?php echo $loop; ?> wcgwp_global_data_set_hide_onload_var<?php echo $loop; ?>">
                        <label for="_wcgwp_category<?php echo $loop; ?>"><?php esc_html_e( 'Gift wrap category to use', 'woocommerce-gift-wrapper-plus' ); ?></label>
                        <?php wp_dropdown_categories( $this->category_args( $variation, TRUE, $loop ) ); ?>
                        <?php echo wc_help_tip( __( 'Define the category which holds your gift wrap product(s). Default is the global gift wrap settings category.', 'woocommerce-gift-wrapper-plus' ) ); ?>
                    </p>
                    
                    <p class="form-row form-row-full show_if_wcgwp_global_data_set_var<?php echo $loop; ?> wcgwp_global_data_set_hide_onload_var<?php echo $loop; ?>">
                        <label for="_wcgwp_product<?php echo $loop; ?>"><?php esc_html_e( 'Gift wrap product to use', 'woocommerce-gift-wrapper-plus' ); ?></label>
                        <textarea name="_wcgwp_product_id_var[<?php echo $loop; ?>]" id="_wcgwp_product<?php echo $loop; ?>" placeholder="Product IDs, one per line" class="short" rows="2" cols="20"><?php echo get_post_meta( $variation->ID, '_wcgwp_product_id', TRUE ); ?></textarea>
                        <?php echo wc_help_tip( __( 'Define a specific wrap product for this product, using its product ID. Overrides the category setting above.', 'woocommerce-gift-wrapper-plus' ) ); ?>
                    </p>                    
                
                    <p class="form-row form-row-full show_if_wcgwp_global_data_set_var<?php echo $loop; ?> wcgwp_global_data_set_hide_onload_var<?php echo $loop; ?>">
                        <label for="wcgwp_show_thumbs<?php echo $loop; ?>"><?php esc_html_e( 'Show thumbnails?', 'woocommerce-gift-wrapper-plus' ); ?></label>
                        <select id="wcgwp_show_thumbs<?php echo $loop; ?>" name="_wcgwp_show_thumbs_var[<?php echo $loop; ?>]" class="wcgwp_select select short">
                            <?php $selected = get_post_meta( $variation->ID, '_wcgwp_show_thumbs', TRUE ); ?>
                            <option value="default" <?php selected( $selected, 'default' ); ?>><?php esc_html_e( 'Not set', 'woocommerce-gift-wrapper-plus' ); ?></option>
                            <option value="yes" <?php selected( $selected, 'yes' ); ?>><?php esc_html_e( 'Yes', 'woocommerce-gift-wrapper-plus' ); ?></option>
                            <option value="no" <?php selected( $selected, 'no' ); ?>><?php esc_html_e( 'No', 'woocommerce-gift-wrapper-plus' ); ?></option>
                        </select>
                        <?php echo wc_help_tip( __( 'Should gift wrap product thumbnail images be visible to customers on product pages?', 'woocommerce-gift-wrapper-plus' ) ); ?>
                    </p>
                    <p class="form-row form-row-full show_if_wcgwp_global_data_set_var<?php echo $loop; ?> wcgwp_global_data_set_hide_onload_var<?php echo $loop; ?>">
                        <label for="wcgwp_quantity<?php echo $loop; ?>"><?php esc_html_e( 'Wrap Quantities', 'woocommerce-gift-wrapper-plus' ); ?></label>
                        <select id="wcgwp_quantity<?php echo $loop; ?>" name="_wcgwp_product_quantity_var[<?php echo $loop; ?>]" class="wcgwp_select select">
                            <?php $selected = get_post_meta( $variation->ID, '_wcgwp_product_quantity', TRUE ); ?>                        
                            <option value="default" <?php selected( $selected, 'default' ); ?>><?php esc_html_e( 'Not set', 'woocommerce-gift-wrapper-plus' ); ?></option>
                            <option value="ad-lib" <?php selected( $selected, 'ad-lib' ); ?>><?php esc_html_e( 'Any quantity of wrap', 'woocommerce-gift-wrapper-plus' ); ?></option>
                            <option value="one-to-one" <?php selected( $selected, 'one-to-one' ); ?>><?php esc_html_e( 'One-to-one wrap per product', 'woocommerce-gift-wrapper-plus' ); ?></option>
                            <option value="only-one" <?php selected( $selected, 'only-one' ); ?>><?php esc_html_e( 'One wrap per product', 'woocommerce-gift-wrapper-plus' ); ?></option>
                        </select>
                        <?php echo wc_help_tip( __( '(Beta setting) Dictate how many giftwraps can be in cart for each line item product. Loose control, in development.', 'woocommerce-gift-wrapper-plus' ) ); ?>
                    </p>
                
                </div>

            </div>
            <?php

            $this->print_js("
                /**
                 * Gift Wrapper Variable Product Writepanel Checkboxes
                 */
                jQuery('input.wcgwp_global_data_set_var".$loop."').change(function() {
                    if (jQuery('input.wcgwp_global_data_set_var".$loop."').is(':checked')) {
                        jQuery('.wcgwp_global_data_set_hide_onload_var".$loop."').show();
                    } else {
                        jQuery('.wcgwp_global_data_set_hide_onload_var".$loop."').hide();
                    }
                });
                jQuery('input.wcgwp_global_data_set_var".$loop."').trigger('change');
                jQuery('#wcgwp_override_chkbx".$loop."').on('change', 'input.wcgwp_global_data_set_var".$loop."', function(){
                    jQuery('.show_if_wcgwp_global_data_set_var".$loop."').hide();
                    if (jQuery(this).is(':checked')) {
                        jQuery('.show_if_wcgwp_global_data_set_var".$loop."').show();
                    }
                });
            ");

        } // End product_after_variable_attributes()

        /**
         * Save variable product info - AJAX since WC 2.4
         */
        public function save_product_variation( $variation_id, $loop ) {

            $post_id = wp_get_post_parent_id( $variation_id );
            
            // Exit if we are on Gift Wrap product page
            if ( WCGWrap()->check_item_is_giftwrap( $post_id ) ) return;

            /* Check if checkbox on variable product is on for "Set different watermark options for this product variation only" */
            update_post_meta( $variation_id, '_wcgwp_data_is_global_override', 'no' );
            if ( isset( $_POST['_wcgwp_data_is_global_override'][ $loop ] ) ) {
                update_post_meta( $variation_id, '_wcgwp_data_is_global_override', 'yes' );
            }
        
            $_wcgwp_data_is_global_override  = get_post_meta( $variation_id, '_wcgwp_data_is_global_override', true );

            /* Save variable product data directly. Ignore Gift Wrapper Tab global settings */
            if ( $_wcgwp_data_is_global_override == 'yes' ) {

                if ( isset( $_POST['_wcgwp_wrap_this_var'][ $loop ] ) ) {
                    update_post_meta( $variation_id, '_wcgwp_wrap_this', 'yes' );
                } else {
                    update_post_meta( $variation_id, '_wcgwp_wrap_this', 'no' );
                }
        
                if ( isset( $_POST['_wcgwp_dont_wrap_this_var'][ $loop ] ) ) {
                    update_post_meta( $variation_id, '_wcgwp_dont_wrap_this', 'yes' );
                } else {
                    update_post_meta( $variation_id, '_wcgwp_dont_wrap_this', 'no' );
                }
                foreach ( array(
                    '_wcgwp_product_quantity',
                    '_wcgwp_show_thumbs',
                    '_wcgwp_category',
                ) as $option) {
                    if ( ! empty( $_POST[ $option . '_var' ][ $loop ] ) ) {
                        update_post_meta( $variation_id, $option, $_POST[ $option . '_var' ][ $loop ] );
                    }
                }
                if ( isset( $_POST['_wcgwp_product_id_var'][ $loop ] ) ) {
                    $product_id = str_replace( ",", "\n", sanitize_textarea_field( $_POST['_wcgwp_product_id_var'][ $loop ] ) );
                    $product_id = str_replace( " ", "", $product_id );                                    
                    update_post_meta( $variation_id, '_wcgwp_product_id', $product_id );
                } else {
                    delete_post_meta( $variation_id, '_wcgwp_product_id' );
                }

            }

        } // End save_variable_postmeta()
        
        /* 
         * NOT IN USE
         * 
         * Use watermark GLOBAL product settings for variable products - an override
         */
        public function save_global_variable_postmeta( $post_id ) {

            // Exit if we are on Gift Wrap product page
            if (  WCGWrap()->check_item_is_giftwrap( $post_id ) ) return;

            $args = array(
                'post_type'     => 'product_variation',
                'post_status'   => array( 'private', 'publish', 'draft', 'future' ),
                'numberposts'   => -1,
                'orderby'       => 'menu_order',
                'order'         => 'asc',
                'post_parent'   => $post_id
            );

            $variations = get_posts( $args ); 

            $product_variations = array();

            foreach ( $variations as $variation ) {
                $product_variations[] = $variation->ID;
            }

            foreach ( $product_variations as $variation_id ) {

                /* The parent product ID, same as post_id */
                update_post_meta( $variation_id, '_parent_product_id', $post_id );

                /* The parent product ID, same as post_id for the child product */
                update_post_meta( $variation_id, '_variable_product_id', $variation_id );

                $giftwrap_wrap_this         = get_post_meta( $post_id, '_wcgwp_wrap_this', true );
                $giftwrap_dont_wrap_this    = get_post_meta( $post_id, '_wcgwp_dont_wrap_this', true );

                $giftwrap_fields = array(
                    '_wcgwp_wrap_this'              => $giftwrap_wrap_this,
                    '_wcgwp_dont_wrap_this' 		=> $giftwrap_dont_wrap_this,
                );

                foreach ( $giftwrap_fields as $key => $gf ) {
                    update_post_meta( $variation_id, $key, $gf );                         
                }
            
            }

        } // End save_global_variable_postmeta()        
    
    } // end class WC_Gift_Wrapper_Product_Settings
    
endif;