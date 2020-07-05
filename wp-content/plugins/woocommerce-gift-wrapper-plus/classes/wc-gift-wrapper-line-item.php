<?php
defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'WC_Gift_Wrapper_Line_Item' ) ) :

    class WC_Gift_Wrapper_Line_Item {
    
        // count per item in cart
        var $count = 0;

        public function __construct() {
    
            add_action( 'init', array( $this, 'init' ), 10 );      

        }

        /*
        * Init
        *
        * @param void
        * @return void
        */ 
        public function init() {
        
            // process line item gift wrap selection on page load
            if ( get_option( 'giftwrap_line_item' )  == 'yes' ) {
            
                // Line item gift wrapping - add to cart link after product name in cart
                add_action( 'woocommerce_after_cart_item_name',                     array( $this, 'after_cart_item_name' ), 10, 2 );

                // Wrap line item based on $_POST variable
                add_action( 'wp',                                                   array( $this, 'add_giftwrap_to_product' ), 10 ); 

                // add class to table row in cart when it's giftwrap
                add_filter( 'woocommerce_cart_item_class',                          array( $this, 'cart_item_class' ), 11, 3 );

                // add class to <li> in mini cart when it's giftwrap
                add_filter( 'woocommerce_mini_cart_item_class',                     array( $this, 'mini_cart_item_class' ), 11, 3 );

                // add class to table row in order item review when gift wrap
                add_filter( 'woocommerce_order_item_class',                         array( $this, 'order_item_class' ), 11, 3 );
                add_filter( 'woocommerce_admin_html_order_item_class',              array( $this, 'order_item_class' ), 15, 3 );

          
            }            
            
        }
        
        /*
        * Add gift wrap select options below each eligible line item name in cart
        *
        * @param array $cart_item
        * @param string $cart_item_key
        * @return void
        */
        public function after_cart_item_name( $cart_item, $cart_item_key ) {

            // First make sure we're not on gift wrap category item. If so, return
            $gift_item = WCGWrap()->check_item_is_giftwrap( $cart_item['product_id'] );
            if ( $gift_item ) return;
            
            // return if there isn't giftwrap product or this item isn't supposed to be wrapped (excluded category, etc)
            if ( WCGWrap()->wrapping->count_giftwrapped_products() < 1 || WCGWrap()->product->gift_wrap_it( $cart_item['product_id'] ) === FALSE ) return;
    
            // If gift wrap not already attached to this product, offer to add
            if ( ! isset( $cart_item['wcgwp_line_item_selection'] ) && ! isset( $cart_item['wcgwp_single_product_selection'] ) && ! isset( $cart_item['wcgwp_simple_selection'] ) ) {        
                $count = $this->count++;
                $this->line_item_gift_wrap( $cart_item, $cart_item_key, $count );
            }
        
        } // End after_cart_item_name() 

       /*
        * Add gift wrap options, loops through each product in the cart,
        * by line item
        *
        * @param array $cart_item
        * @param string $cart_item_key
        * @param int $count int
        * @return void
        */
        public function line_item_gift_wrap( $cart_item, $cart_item_key, $count ) {
    
            $giftwrap_details = get_option( 'giftwrap_details', '' );
            $show_thumbs = WCGWrap()->wrapping->show_thumbs( $cart_item['product_id'] );
            ?>

            <div id="wc-giftwrap-<?php echo $count; ?>" class="wc-giftwrap giftwrap-cart giftwrap-cart<?php echo WCGWrap()->wrapping->extra_class(); ?> giftwrap-line-item">
            
            <?php // modal version
            if ( get_option( 'giftwrap_line_item_modal', 'no' ) == 'yes' ) {

                // print the header
                wc_get_template( 'wcgwp/modal-line-item-header.php', array( 'label' => '_line_item', 'count' => $count ), '', WCGWP_PLUGIN_DIR . 'templates/');
    
                // we need to pass some arguments to this hooked function using a closure:
                $args = array( $giftwrap_details, $cart_item, $cart_item_key, $show_thumbs, $count );
                
                add_action( 'woocommerce_after_cart', function() use ( $args ) { $this->wcgwp_modal_move_dom( $args ); });

            // non-modal version
            } else { ?>
            
                <div id="line_item_header_wrapper" class="giftwrap_header_wrapper-<?php echo $count; ?> giftwrap_header_wrapper gift-wrapper-info">
                    <a class="show_giftwrap show_giftwrap_cart show_giftwrap_cart-<?php echo $count; ?>" href="#" onclick="openGifts( <?php echo $count; ?> );return false;"><?php echo apply_filters( 'wcgwp_add_wrap_prompt', esc_html__( 'Add gift wrap?', 'woocommerce-gift-wrapper-plus' ) ); ?></a><span class="gift-wrapper-cancel">&nbsp; <a href="#" id="cart_cancel-<?php echo $count; ?>" class="cart_cancel_giftwrap"><?php esc_html_e( 'Cancel gift wrap', 'woocommerce-gift-wrapper-plus' ); ?></a></span>
                </div>
                <div class="giftwrapper_products-<?php echo $count; ?> giftwrapper_products wcgwp_slideout non_modal">
                    <div class="wcgwp_form"> 
                        <?php if ( $giftwrap_details != '' ) { ?>
                            <p class="giftwrap_details"><?php echo $giftwrap_details; ?></p>
                        <?php }                        
                        wc_get_template( 'wcgwp/giftwrap-list-line-item.php', array( 'label' => 'line_item_', 'list' => get_wcgwp_products( $cart_item['product_id'] ), 'show_thumbs' => $show_thumbs, 'count' => $count ), '', WCGWP_PLUGIN_DIR . 'templates/');
                        ?>
                        <input type="hidden" value="<?php echo $cart_item_key; ?>" name="wcgwp_line_item_parent_key-<?php echo $count; ?>">
                        <input type="hidden" value="<?php echo $cart_item['product_id']; ?>" name="wcgwp_line_item_parent_id-<?php echo $count; ?>">
                        <input type="hidden" value="<?php echo $cart_item['quantity']; ?>" name="wcgwp_line_item_quantity-<?php echo $count; ?>">
                        <button type="submit" id="giftwrap_submit_cart" class="button btn alt giftwrap_submit fusion-button fusion-button-default fusion-button-default-size" name="wcgwp_line_item_submit-<?php echo $count; ?>"><?php esc_html_e( 'Add Gift Wrap', 'woocommerce-gift-wrapper-plus' ); ?></button>
                    </div>
                </div>
 
            <?php } ?>

            </div>
    
        <?php } // End line_item_gift_wrap()        

        /*
        * Modal HTML for line item gift wrapping
        * Needs moving outside <form> tags in DOM, moved to 'woocommerce_after_cart'
        *
        * @param $args arr
        * @return void
        */
        public function wcgwp_modal_move_dom( $args ) { 
        
            wc_get_template( 'wcgwp/modal-line-item.php', array( 'label' => '_line_item', 'giftwrap_details' => $args[0], 'cart_item' => $args[1], 'cart_item_key' => $args[2], 'list' => get_wcgwp_products( $args[1]['product_id'] ), 'show_thumbs' => $args[3], 'count' => $args[4] ), '', WCGWP_PLUGIN_DIR . 'templates/');

        } // End wcgwp_modal_move_dom()
                
        /*
        * Add line item gift wrap to cart
        * Adds a new unique (wrap) product on its own line
        *
        * @param void
        * @return void
        */
        public function add_giftwrap_to_product() {

            // this function loads on the 'wp' hook. Only continue if needed.
            if ( ! isset( $_POST['wcgwp_line_item_submit'] ) && ! isset( $_POST['wcgwp_line_item_parent_key-0'] ) ) return;

            // get the index number of the line item submitted for wrapping, store to $num for later
            if ( isset( $_POST['wcgwp_line_item_parent_key-0'] ) ) { // at least one line item present (array starts at 0)
                foreach ( $_POST as $k => $v ) {
                    if ( is_array( $_POST[$k] ) ) continue;
                    // set $num here, equal to line item row number, essentially
                    if ( preg_match( '/wcgwp_line_item_submit-([0-9])/', $k, $num ) ) {
                        break;
                    }
                }
            }
            
            $cart_item_data = array();
                   
            // non-modal line-item handling, each line item is indexed       
            if ( isset( $num ) && isset( $_POST[ $num[0] ] ) ) { 
            
                $parent_key = isset( $_POST['wcgwp_line_item_parent_key-' . $num[1] ] ) ? $_POST['wcgwp_line_item_parent_key-' . $num[1] ] : NULL;

                if ( ! $parent_key ) return;   
                $parent_id  = isset( $_POST['wcgwp_line_item_parent_id-' . $num[1] ] ) ? $_POST['wcgwp_line_item_parent_id-' . $num[1] ] : NULL;                             
                $quantity   = isset( $_POST['wcgwp_line_item_quantity-' . $num[1] ] ) ? $_POST['wcgwp_line_item_quantity-' . $num[1] ] : 1;

                if ( isset( $_POST['wcgwp_line_item_product-' . $num[1] ] ) ) {
                    $giftwrap_product = wc_get_product( $_POST['wcgwp_line_item_product-' . $num[1] ] );
                }
                
                $cart_item_data['wcgwp_line_item_note'] = isset( $_POST['wcgwp_line_item_note-' . $num[1] ] ) && strlen( $_POST['wcgwp_line_item_note-' . $num[1] ] ) > 0 ? sanitize_textarea_field( stripslashes( $_POST['wcgwp_line_item_note-' . $num[1] ] ) ) : '';

            // modal line-item handling
            } else { 

                $parent_key = NULL;
                if ( isset( $_POST['wcgwp_product_parent_key'] ) ) {
                    $parent_key = $_POST['wcgwp_product_parent_key'];
                }
                if ( isset( $_POST['wcgwp_line_item_parent_key'] ) ) {
                    $parent_key = $_POST['wcgwp_line_item_parent_key'];
                }
                if ( ! $parent_key ) return;   
                $parent_id  = isset( $_POST['wcgwp_line_item_parent_id'] ) ? $_POST['wcgwp_line_item_parent_id'] : NULL;                             
                $quantity   = isset( $_POST['wcgwp_line_item_quantity'] ) ? $_POST['wcgwp_line_item_quantity'] : 1;

                if ( isset( $_POST['wcgwp_line_item_product'] ) ) {
                    $giftwrap_product = wc_get_product( $_POST['wcgwp_line_item_product'] );
                }            
                
                $cart_item_data['wcgwp_line_item_note'] = isset( $_POST['wcgwp_line_item_note'] ) && strlen( $_POST['wcgwp_line_item_note'] ) > 0 ? sanitize_textarea_field( stripslashes( $_POST['wcgwp_line_item_note'] ) ) : '';
            
            }
            
            // what quantity ratio does seller dictate?
            $ratio = get_option( 'wcgwp_product_quantity', 'ad-lib' ); // one-to-one, only-one, ad-lib
            // is ratio overridden by a per-product setting?
            if ( $parent_id ) {
                $override_ratio = get_post_meta( $parent_id, '_wcgwp_product_quantity', TRUE ); // one-to-one, only-one, ad-lib
            }
            $ratio = isset( $override_ratio ) && $override_ratio != '' ? $override_ratio : $ratio;

            // set up more cart item data to be added to cart
            $cart_item_data['wcgwp_line_item_selection']    = $giftwrap_product->get_title();
            $cart_item_data['wcgwp_line_item_parent_key']   = $parent_key; // important later for if parent removed from cart, remove wrap also
            $cart_item_data['wcgwp_line_item_parent_id']    = $parent_id; // important later for admin in helping match wrap to wrapped

            // to help keep track of which wrap goes to which product
            if ( isset( $parent_key ) ) {
                $wrapped_item = WC()->cart->cart_contents[ $parent_key ];
                if ( isset( $wrapped_item ) ) {                      
                    $cart_item_data['wcgwp_line_item_parent_name'] = apply_filters( 'wcgwp_line_item_parent_name', $wrapped_item['data']->get_name(), $parent_key );
                    $wrapped_item_quantity = $wrapped_item['quantity'];
                }
            }

            // Is it evil to clear the "removed cart contents" session?
            WC()->cart->set_removed_cart_contents( array() );            
            
            // first we remove parent from cart, then re-add so it is at bottom 
            // of item list, with wrap directly following in cart listing               
            WC()->cart->remove_cart_item( $parent_key ); // move to bottom of cart.
            WC()->cart->restore_cart_item( $parent_key ); // restore_cart_item(), below, hooked to woocommerce_restore_cart_item, will perform more before this line is done
            
            if ( $ratio != 'one-to-one' ) {
                $quantity = 1;
            } else if ( $ratio == 'one-to-one' ) {
                $quantity = $wrapped_item_quantity;
            }

            // add gift wrap item to cart
            // add_to_cart( $product_id, $quantity, $variation_id, $variation, $cart_item_data )
            WC()->cart->add_to_cart( $giftwrap_product->get_id(), $quantity, 0, array(), $cart_item_data );

            do_action( 'wcgwp_line_item_wrap_added', $cart_item_data );
            
            // POST/REDIRECT/GET to prevent wrap from showing back up after delete + refresh
            if ( isset( $_POST['wcgwp_line_item_submit'] ) || isset( $_POST['wcgwp_line_item_parent_key-0'] ) ) {
                wp_safe_redirect( wc_get_cart_url(), 303 );
                exit; // not die() because inside hook
            }
            
        } // End add_giftwrap_to_product()
        
        /**
         * Changes the tr class of cart items.
         *
         * @param  string  $class
         * @param  array   $values
         * @param  string  $values_key
         * @return string
         */
        public function cart_item_class( $class, $values, $values_key ) {

            if ( isset( $values[ 'wcgwp_line_item_selection' ] ) || isset( $values['wcgwp_product_parent_name'] ) ) {
                $class .= ' wcgwp-line-item ';
            }
            return $class;
            
        } // End cart_item_class()
  
        /**
         * Changes the li class of mini cart items.
         *
         * @param  string  $class
         * @param  object  $cart_item
         * @param  string  $cart_item_key
         * @return string
         */
        public function mini_cart_item_class( $class, $cart_item, $cart_item_key ) { 
            
            // Is it a gift item?            
            if ( isset( $cart_item[ 'wcgwp_line_item_selection' ] ) ) {
                $class .= ' wcgwp-line-item ';
            }
            return $class;
            
        } // End mini_cart_item_class()         

        /**
         * Changes the tr class of cart items.
         *
         * @param  string  $class
         * @param  array   $order
         * @param  string  $item
         * @return string
         */
        public function order_item_class( $class, $item, $order ) {
        
            // Is it a line item wrap?
            if ( ! empty( $item['wcgwp_parent_name'] ) ) {
                $class .=  'wcgwp-line-item';
            }      
            return $class;
            
        } // End order_item_class()
                
    }

endif;