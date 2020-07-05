<?php
defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'WC_Gift_Wrapper_Product' ) ) :

    class WC_Gift_Wrapper_Product {

        public function __construct() {
    
            add_action( 'init',                     array( $this, 'init' ), 10 );
            add_action( 'wp_loaded',                array( $this, 'check_hooks' ), 30 );

        }

        /*
        * Init
        *
        * @param void
        * @return void
        */ 
        public function init() {     

             // Add gift wrap opt-in before "add to cart" button
            add_action( 'woocommerce_before_add_to_cart_button',        array( $this, 'before_add_to_cart_button' ), 10 );  

            // Add item data to the cart
            // Fires inside add_to_cart() class-wc-cart.php line 1022
            add_filter( 'woocommerce_add_cart_item_data',               array( $this, 'add_cart_item_data' ), 10, 4 );
              
            // Deals with LINE ITEM per-product wrap additions
            add_action( 'wcgwp_product-wrap',                           array( $this, 'add_wrap_to_cart' ) );

            // Reposition display
            add_action( 'woocommerce_before_variations_form',           array( $this, 'reposition_display_for_variable_product' ), 10 );
            
            // add_filter( 'woocommerce_add_to_cart_validation',           array( $this, 'add_to_cart_validation' ), 10, 4 );
            
            /* // what about a gift wrap note?
            if ( get_option( 'giftwrap_simple_product_note', 'no' ) == 'yes' ) {
                add_action( 'woocommerce_after_cart_table', array( $this, 'giftwrap_simple_note_after_coupon' ), 10, 1 );
            } */

        } // End init()
        
        /**
         * Check $_POST for WCGWP hookable actions
         *
         * @param void
         * @return void         
         */
        public function check_hooks() {
        
            $key = ! empty( $_POST['wcgwp_action'] ) ? sanitize_key( $_POST['wcgwp_action'] ) : false;

            if ( ! empty( $key ) ) {                  
                do_action( "wcgwp_{$key}" , $_POST );
            }  
        
        } // End check_hooks()

        /**
         * Output gift wrapping option before add to cart button
         *
         * @param void
         * @return void         
         */
        public function before_add_to_cart_button() {
        
            global $product;
            $product_id = $product->get_id();

            if ( ! is_single() || $this->gift_wrap_it( $product_id ) === FALSE ) return;

            $list_count = FALSE;
            $wrap_products = get_wcgwp_products( $product_id );
            $count = count( $wrap_products ); 
            $list_count = $count > 1 ? TRUE : FALSE;

            // KISS (checkbox) version, let's just add a checkbox (giftwrap note is TODO?)
            // uses first giftwrap option in array
            if ( get_option( 'giftwrap_product_display' ) == 'checkbox' ) {

                $wrap_product = new WC_Product( $wrap_products[0]->ID );
                $price_html = $wrap_product->get_price_html();
                $price = $wrap_product->get_price();

                wc_get_template( 'wcgwp/giftwrap-simple.php', array( 'product' => $wrap_product, 'price' => $price, 'price_html' => $price_html ), '', WCGWP_PLUGIN_DIR . 'templates/'); ?>
                
                <input type="hidden" name="wcgwp_action" value="product-wrap">
                <input type="hidden" name="wcgwp_product_parent_id" value="<?php echo $product_id; ?>">

                <?php 
                // we done here
                return;

            }

            $giftwrap_details = sanitize_textarea_field( get_option( 'giftwrap_details', '' ) );
            
            if ( get_option( 'giftwrap_product_display' ) == 'modal' ) {
            
                wc_get_template( 'wcgwp/modal-product.js', array(), '', plugin_dir_path( __DIR__ ) . 'templates/');
                ?>
                <div id="wc-giftwrap" class="wc-giftwrap giftwrap-single <?php if ( $list_count === FALSE ) { echo 'wcgwp_singular'; } ?>">

                    <?php wc_get_template( 'wcgwp/modal-product.php', array( 'label' => '_product', 'list' => $wrap_products, 'giftwrap_details' => $giftwrap_details, 'show_thumbs' => WCGWrap()->wrapping->show_thumbs( $id = NULL ) ), '', WCGWP_PLUGIN_DIR . 'templates/'); ?>
            
                    <input type="hidden" name="wcgwp_action" value="product-wrap">
                    <input type="hidden" name="wcgwp_product_parent_id" value="<?php echo $product_id; ?>">
                </div>
                
                <?php 
                // done here
                return;
            
            } 
                            
            // finally, deal with slide-down wrap options.
            if ( get_option( 'giftwrap_product_display' ) == 'slide-in' ) { 

                wc_get_template( 'wcgwp/slideout-js.php', array(), '', plugin_dir_path( __DIR__ ) . 'templates/');
                $wrap_singular_parent = get_post_meta( $product_id, '_parent_product_id', TRUE );

                // if KISS checkbox not enabled or we have a number of gift wrap products, do the fancy thing
                if ( get_option( 'giftwrap_simple_product', 'no' ) == 'no' || isset( $wrap_singular_parent ) ) { ?>
    
                    <div id="wc-giftwrap" class="wc-giftwrap giftwrap-single <?php if ( $list_count === FALSE ) { echo 'wcgwp_singular'; } ?>">

                        <div class="giftwrap_header_wrapper gift-wrapper-info">
                            <a href="#" class="show_giftwrap_on_product"><label for=""><?php echo apply_filters( 'wcgwp_add_wrap_prompt', esc_html__( 'Add gift wrap?', 'woocommerce-gift-wrapper-plus' ) ); ?></label></a><span class="gift-wrapper-cancel"> &nbsp; <a href="#" class="cancel_giftwrap"><?php esc_html_e( 'Cancel gift wrap', 'woocommerce-gift-wrapper-plus' ); ?></a></span>
                        </div>                    
            
                        <div class="giftwrapper_products non_modal">
                            <?php if ( $giftwrap_details != '' ) { ?>
                                <p class="giftwrap_details"><?php echo $giftwrap_details; ?></p>
                            <?php }
                            wc_get_template( 'wcgwp/giftwrap-list-product.php', array( 'label' => '_product', 'list' => $wrap_products, 'show_thumbs' => $this->show_thumbs( $product_id ) ), '', WCGWP_PLUGIN_DIR . 'templates/');
                            ?>    
                        </div>
            
                        <input type="hidden" name="wcgwp_action" value="product-wrap">
                        <input type="hidden" name="wcgwp_product_parent_id" value="<?php echo $product_id; ?>">
                
                    </div>	

                <?php }
             }
        
        } // End before_add_to_cart_button()

        /**
         * Filter cart item data just before add_to_cart(), 
         * for adding wrap to existing product as an attribute
         * Adds details like gift wrap selection and note to cart item
         *
         * @param array $cart_item_data
         * @param int   $product_id
         * @param int   $variation_id
         * @param int   $quantity
         *
         * @return array
         */
        public static function add_cart_item_data( $cart_item_data, $product_id, $variation_id, $quantity ) {

            $type = get_option( 'giftwrap_per_product_type', 'attribute' );
            if ( $type != 'attribute' ) return $cart_item_data;
            
            if ( isset( $_POST['wcgwp_single_product'] ) ) {
                $giftwrap_product                                   = wc_get_product( $_POST['wcgwp_single_product'] );
                $cart_item_data['wcgwp_single_product_selection']   = $giftwrap_product->get_title();
                $cart_item_data['wcgwp_single_product_note']        = isset( $_POST['wcgwp_single_product_note'] ) && strlen( $_POST['wcgwp_single_product_note'] ) > 0 ? sanitize_textarea_field( stripslashes( $_POST['wcgwp_single_product_note'] ) ) : '';
                $cart_item_data['wcgwp_single_product_price']       = $giftwrap_product->get_price();
            }

            // Simple (checkbox) wrap - doesn't have note feature yet
            if ( isset( $_POST['wcgwp_simple_checkbox'] ) && $_POST['wcgwp_simple_checkbox'] != '' ) {
                $price = $_POST['wcgwp_simple_checkbox'];
                $cart_item_data['wcgwp_simple_selection']           = apply_filters( 'wcgwp_product_name', $_POST['wcgwp_simple_selection'], $price );
                $cart_item_data['wcgwp_simple_price']               = $price;
            }
            return $cart_item_data;
      
        } // End add_cart_item_data()     
         
        /**
         * Adding gift wrap as its own line item from product page
         *
         * @return bool
         */
        public function add_wrap_to_cart() {
        
            if ( ! isset( $_POST['wcgwp_action'] ) || $_POST['wcgwp_action'] != 'product-wrap' ) return;
            if ( ! isset( $_POST['wcgwp_single_product'] ) && ! isset( $_POST['wcgwp_simple_checkbox'] ) ) return;

            $type = get_option( 'giftwrap_per_product_type', 'attribute' );
            if ( $type == 'attribute' ) return;
  
            // If filter hook 'woocommerce_add_to_cart_redirect' to change redirect URL
            // or redirect on add to cart, leave because due to wp_redirect this function won't fire anyway
//          if ( has_filter( 'woocommerce_add_to_cart_redirect' ) ) return;            
            if ( 'yes' === get_option( 'woocommerce_cart_redirect_after_add' ) ) return;
 
            $product_id = FALSE;
            if ( isset( $_POST['wcgwp_product_parent_id'] ) && $_POST['wcgwp_product_parent_id'] != '' ) {            
                $product_id = $_POST['wcgwp_product_parent_id'];
            }          

            // To help keep track of which wrap goes to which product
            if ( $product_id ) {     
             
                // we don't wrap wrap
                if ( WCGWrap()->check_item_is_giftwrap( $product_id ) ) return;
                 
                $product = wc_get_product( $product_id );
                
                // is this a grouped product?
                $is_grouped = $product->is_type( 'grouped' );
                if ( $is_grouped ) {
                    // $children = array_filter( array_map( 'wc_get_product', $product->get_children() ), 'wc_products_array_filter_visible_grouped' );
                    // array of product IDs
                    $children = $product->get_children();
                }
                               
                $product_name = $product->get_name();            
                $cart_item_data['wcgwp_product_parent_name'] = apply_filters( 'wcgwp_product_parent_name', $product_name, $product_id );
                     
            }

            $ratio = get_option( 'wcgwp_product_quantity', 'ad-lib' ); // one-to-one, only-one, ad-lib   
            $existing_quantity = FALSE;
            
            // Go through existing cart
            // prepare to remove wrap if already wrapped
            $cart_items = WC()->cart->get_cart();            
            foreach ( $cart_items as $key => $item ) {

                // get parent (wrapped) item's key and quantity
                // $product_id is the id of parent product of wrap
                if ( isset( $item['product_id'] ) && $item['product_id'] == $product_id ) {
                    $existing_quantity = $item['quantity'];
                    $parent_key = $key;
                }
                
                // crude. quickly go through grouped item sub-items to look for matches 
                if ( $is_grouped ) {
                    foreach ( $children as $child ) {                 
                        // new grouped item is found in cart, doesn't mean much.
                        if ( $item['product_id'] == $child ) { 
                            $existing_quantity = $item['quantity'];
                            $parent_key = $key; // todo: should become an array when grouped? (wrap attached to each product?)
                            break;
                        }
                    }
                }
            }

            if ( isset( $parent_key ) ) {
                $cart_item_data['wcgwp_product_parent_key'] = $parent_key;
            }
    
            if ( isset( $_POST['wcgwp_single_product'] ) ) {            
                $giftwrap_product                                       = wc_get_product( $_POST['wcgwp_single_product'] );
                $cart_item_data['wcgwp_single_product_selection']       = $giftwrap_product->get_title();
                $cart_item_data['wcgwp_product_parent_id']              = isset( $_POST['wcgwp_product_parent_id'] ) ? $_POST['wcgwp_product_parent_id'] : '';             
                $cart_item_data['wcgwp_single_product_note']            = isset( $_POST['wcgwp_single_product_note'] ) && strlen( $_POST['wcgwp_single_product_note'] ) > 0 ? sanitize_textarea_field( stripslashes( $_POST['wcgwp_single_product_note'] ) ) : '';
                $cart_item_data['wcgwp_single_product_price']           = $giftwrap_product->get_price();                
                $giftwrap_product_id                                    = $giftwrap_product->get_id();
            }

            // Simple (checkbox) wrap doesn't have note yet
            if ( isset( $_POST['wcgwp_simple_checkbox'] ) && $_POST['wcgwp_simple_checkbox'] != '' ) {   
                $price                                                  = $_POST['wcgwp_simple_checkbox'];
                $cart_item_data['wcgwp_simple_price']                   = $price;
                $cart_item_data['wcgwp_simple_selection']               = apply_filters( 'wcgwp_product_name', $_POST['wcgwp_simple_selection'], $price );
                $giftwrap_product                                       = get_page_by_title( $cart_item_data['wcgwp_simple_selection'], OBJECT, 'product' );
                $giftwrap_product_id                                    = $giftwrap_product->ID;
            }

            // Is it evil to clear the "removed cart contents" session?
            WC()->cart->set_removed_cart_contents( array() );
           
            // First we remove parent from cart, then re-add so it is at bottom 
            // of item list, with wrap directly following in cart listing               
            WC()->cart->remove_cart_item( $parent_key ); // move to bottom of cart.
            WC()->cart->restore_cart_item( $parent_key ); // restore_cart_item(), below, hooked to woocommerce_restore_cart_item, will perform more before this line is done            

            $quantity = 1;
            if ( isset( $_POST['quantity'] ) && $ratio == 'one-to-one' ) {
                if ( $existing_quantity ) {
                    $quantity = $existing_quantity;
                } else {
                    $quantity = $_POST['quantity'];
                }
            }
        
        

            // Add gift wrap item to cart
            // add_to_cart( $product_id, $quantity, $variation_id, $variation, $cart_item_data )
            WC()->cart->add_to_cart( $giftwrap_product_id, $quantity, 0, array(), $cart_item_data );

            do_action( 'wcgwp_product_wrap_added', $cart_item_data );            
            
        }             

        /*
        * Whether to show gift wrap product thumbnails on single product page
        *
        * @param string $product_id
        * @return bool
        */	
        public function show_thumbs( $product_id ) {
            
            // per-product drill down
            if ( get_post_meta( $product_id, '_wcgwp_show_thumbs', TRUE ) == 'yes' ) {
                return TRUE;
            }  
            if ( get_post_meta( $product_id, '_wcgwp_show_thumbs', TRUE ) != 'no' && get_option( 'giftwrap_product_show_thumb', 'yes' ) == 'yes' ) {
                return TRUE;
            }
            return FALSE;

        } // End show_thumbs()

        /**
         * Is this product cleared to be gift-wrapped?
         * 
         * @param int $product_id
         * @return bool
         */	
        public function gift_wrap_it( $product_id = NULL ) {

            if ( ! $product_id ) {
                global $product;
                $product_id = $product->get_id();
            } else {
                $_pf = new WC_Product_Factory();  
                $product = $_pf->get_product( $product_id );
            }
            
            // First make sure we're not on gift wrap category item. If so, return
            $gift_item = WCGWrap()->check_item_is_giftwrap( $product_id );
            if ( $gift_item ) return FALSE;

            // for single product page and line item rows in cart            
            if ( $product->is_virtual() && apply_filters( 'giftwrap_single_virtual_products', false ) === FALSE ) return FALSE;

            // Establish if gift wrap set for this single product
            $wrap_it = get_post_meta( $product_id, '_wcgwp_wrap_this', 'default' );

            // If there is a per-product override, honor it
            if ( $wrap_it == 'yes' ) return TRUE;
            if ( $wrap_it == 'no' ) return FALSE;

            // If no override, check if product belongs to an excluded product category
            if ( wcgwp_excluded_from_gift_wrap_by_cat( $product_id ) ) return FALSE;
            
            $cats_to_exclude = get_option( 'giftwrap_exclude_cats', NULL );
            if ( $cats_to_exclude ) {
                $terms = wp_get_post_terms( $product_id, 'product_cat', array( 'fields' => 'ids' ) );
                foreach ( $terms as $term_id ) {
                    if ( in_array( $term_id, $cats_to_exclude ) ) {
                        return FALSE;
                    }
                }
            }

            // Is global gift wrapping enabled?
            $wcgwp_global = get_option( 'giftwrap_all_products', 'no' );
            $line_item = get_option( 'giftwrap_line_item' );

            // $wrap_a_variation = get_post_meta( $product_id, '_wcgwp_data_is_global_override', TRUE );

            // We are probably going to gift wrap this...
            if ( $wcgwp_global == 'yes' || ( is_cart() && $line_item == 'yes' ) || $wrap_it == 'yes' ) return TRUE;

            return FALSE;

        } // End gift_wrap_it()

        /**
         * Fix product addons position on variable products - show them after a
         * single variation description or out of stock message.
         *
         * @param void
         * @return void
         */
        public function reposition_display_for_variable_product() {
        
            remove_action( 'woocommerce_before_add_to_cart_button', array( $this, 'before_add_to_cart_button' ), 10 );
            add_action( 'woocommerce_single_variation', array( $this, 'before_add_to_cart_button' ), 15 );
            
        } // End reposition_display_for_variable_product()


      
        /**
         * NOT IN USE
         * Is this product cleared to be gift-wrapped?
         * 
         * @param int $product_id
         * @return bool
         */	
        public function giftwrap_simple_note_after_coupon( $wrap_product ) { ?>
        
            <div class="wc_giftwrap_notes_container">
                <label for="simple_giftwrapper_notes" class="simple_giftwrapper_notes"><?php echo apply_filters( 'wcgwp_add_wrap_message', esc_html__( 'Add Gift Wrap Message:', 'woocommerce-gift-wrapper-plus' ) ); ?></label>
                    <textarea name="simple_wcgwp_notes" id="simple_giftwrapper_notes" cols="50" rows="4" maxlength="<?php echo get_option( 'giftwrap_textarea_limit', '1000' ); ?>" class="wc_giftwrap_notes"></textarea>	
            </div>
            <button type="submit" id="simple_giftwrap_submit" class="button btn alt giftwrap_submit simple_giftwrap_submit fusion-button fusion-button-default fusion-button-default-size" name="simple_giftwrap_note_btn"><?php esc_html_e( 'Add Gift Wrap Note to Order', 'woocommerce-gift-wrapper-plus' ); ?></button>

        <?php } // End giftwrap_simple_note_after_coupon()

        /**
         * NOT IN USE
         * Adding gift wrap as its own line item from product page
         *
         * @return bool
         */
        public function add_to_cart_validation( $passed, $product_id, $quantity, $variation_id = '' ) {

            $type = get_option( 'giftwrap_per_product_type', 'attribute' );
            if ( $type == 'attribute' || WCGWrap()->check_item_is_giftwrap( $product_id ) ) return $passed;

            if ( ! has_filter( 'woocommerce_add_to_cart_redirect' ) || 'no' === get_option( 'woocommerce_cart_redirect_after_add' ) ) return $passed;

            if ( isset( $_POST['wcgwp_single_product'] ) ) {
                $giftwrap_product = wc_get_product( $_POST['wcgwp_single_product'] );
                $this->cart_item_data['wcgwp_single_product_selection']   = $giftwrap_product->get_title();
                $this->cart_item_data['wcgwp_single_product_note']        = isset( $_POST['wcgwp_single_product_note'] ) && strlen( $_POST['wcgwp_single_product_note'] ) > 0 ? sanitize_textarea_field( stripslashes( $_POST['wcgwp_single_product_note'] ) ) : '';
                $this->cart_item_data['wcgwp_single_product_price']       = $giftwrap_product->get_price();                
                $this->giftwrap_product_id = $giftwrap_product->get_id();
            }

            // Simple (checkbox) wrap doesn't have note yet
            if ( isset( $_POST['wcgwp_simple_checkbox'] ) && $_POST['wcgwp_simple_checkbox'] != '' ) {            
                $price = $_POST['wcgwp_simple_checkbox'];
                $this->cart_item_data['wcgwp_simple_selection']           = apply_filters( 'wcgwp_product_name', $_POST['wcgwp_simple_selection'], $price );
                $this->cart_item_data['wcgwp_simple_price']               = $price;
                
                $giftwrap_product = get_page_by_title( $this->cart_item_data['wcgwp_simple_selection'], OBJECT, 'product' );
                $this->giftwrap_product_id = $giftwrap_product->ID;

            }            
            
            // to help keep track of which wrap goes to which product
            if ( isset( $product_id ) ) {

                $product = wc_get_product( $product_id );
                $product_name = $product->get_name();            
                $this->cart_item_data['wcgwp_product_parent_name'] = apply_filters( 'wcgwp_product_parent_name', $product_name, $product_id );

            }
            
            WC()->cart->add_to_cart( $this->giftwrap_product_id, 1, 0, array(), $this->cart_item_data );

            return $passed;
            
        }
   
    } // End class WC_Gift_Wrapper_Product

endif;