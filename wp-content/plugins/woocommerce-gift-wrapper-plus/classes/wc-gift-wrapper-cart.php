<?php
defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'WC_Gift_Wrapper_Cart' ) ) :

    class WC_Gift_Wrapper_Cart {
        
        public function __construct() {
    
            add_action( 'init', array( $this, 'init' ), 10 );          

        }

        /*
        * Init - l10n & hooks
        * @param void
        * @return void
        */
        public function init() {

            // Modify cart item
            // Fires inside add_to_cart() class-wc-cart.php line 1082
            add_filter( 'woocommerce_add_cart_item',                            array( $this, 'add_cart_item' ), 20, 1 );
            
            // Load session data into Woo array
            add_filter( 'woocommerce_get_cart_item_from_session',               array( $this, 'get_cart_item_from_session' ), 20, 2 );

            // remove gift wrap (if attached) when product removed
            add_action( 'woocommerce_remove_cart_item',                         array( $this, 'remove_cart_item' ), 10, 2 );

            // maybe adjust number of gift wrap after parent product quantity changed
            add_action( 'woocommerce_after_cart_item_quantity_update',          array( $this, 'after_cart_item_quantity_update' ), 20, 4 );

            // Add more item data to the default Woo array, for cart display
            add_filter( 'woocommerce_get_item_data',                            array( $this, 'get_item_data' ), 10, 2 );

            // Add line items to order - adjust item before saving to order
            // Fires inside class-wc-checkout.php line 422
            add_action( 'woocommerce_checkout_create_order_line_item',          array( $this, 'checkout_create_order_line_item' ), 10, 3 );

            // If thumbnail links aren't desired, remove them from cart as well
            add_filter( 'woocommerce_cart_item_permalink',                      array( $this, 'cart_item_permalink' ), 10, 3 );                      

            // maybe disable COD if gift wrap is in cart
            add_filter( 'woocommerce_available_payment_gateways',               array( $this, 'available_payment_gateways' ), 10, 1);

            // restore gift wrap as well when item restored to cart
            add_action( 'woocommerce_restore_cart_item',                        array( $this, 'restore_cart_item' ), 10, 2 );

            // add class to table row in cart when it's giftwrap
            add_filter( 'woocommerce_cart_item_class',                          array( $this, 'cart_item_class' ), 11, 3 );

            // Most the time, gift wrap will be a hidden product
            // Let's maybe show the permalink thumbnail anyway
            // add_filter( 'woocommerce_order_item_permalink',                  array( $this, 'order_item_permalink' ), 10, 3 );

            // Reduce gift wrapper product stock
            // add_action( 'woocommerce_payment_complete',                      array( $this, 'payment_complete' ), 10, 1 );
            // add_action( 'woocommerce_order_status_completed',                array( $this, 'payment_complete' ), 10, 1 );
            // add_action( 'woocommerce_order_status_processing',               array( $this, 'payment_complete' ), 10, 1 );
            // add_action( 'woocommerce_order_status_on-hold',                  array( $this, 'payment_complete' ), 10, 1 );

            // order again functionality.
            // add_filter( 'woocommerce_order_again_cart_item_data',            array( $this, 'order_again_cart_item_data' ), 10, 3 );
            
        } // End init()

        /**
         * Adjust total item price (item + giftwrap) if giftwrap added to line item as an attribute.
         *
         * @param array $cart_item Cart item data.
         * @return array
         */
        public function add_cart_item( $cart_item ) {

            $type = get_option( 'giftwrap_per_product_type', 'attribute' );
            if ( $type != 'attribute' ) return $cart_item;
            
            // prices of gift wrap will be added to product price
            $wcgwp_prices = array( 'wcgwp_single_product_price', 'wcgwp_simple_price' );
            
            foreach ( $wcgwp_prices as $wcgwp_price ) {
            
                if ( ! isset( $cart_item[ $wcgwp_price ] ) ) continue;
                            
                $price = (float) $cart_item['data']->get_price( 'edit' );
                // Compatibility with Smart Coupons self declared gift amount purchase.
                if ( empty( $price ) && ! empty( $_POST['credit_called'] ) ) {
                    // $_POST['credit_called'] is an array.
                    if ( isset( $_POST['credit_called'][ $cart_item['data']->get_id() ] ) ) {
                        $price = (float) $_POST['credit_called'][ $cart_item['data']->get_id() ];
                    }
                }
                if ( empty( $price ) && ! empty( $cart_item['credit_amount'] ) ) {
                    $price = (float) $cart_item['credit_amount'];
                }
                if ( $cart_item[ $wcgwp_price ] ) {
                    $price += (float) $cart_item[ $wcgwp_price ];
                }
                $cart_item['data']->set_price( $price );
            
            }
            return $cart_item;

        } // End add_cart_item()
    
    
        /**
         * Get cart item from session.
         *
         * @param array $cart_item Cart item data.
         * @param array $values    Cart item values.
         * @return array
         */
        public function get_cart_item_from_session( $cart_item, $values ) {

            // cart/checkout hooked general gift wrapping
            if ( isset( $values['wcgwp_cart_selection'] ) ) {
                $cart_item['wcgwp_cart_selection'] = $values['wcgwp_cart_selection'];
                if ( isset( $values['wcgwp_cart_note'] ) ) {
                    $cart_item['wcgwp_cart_note'] = $values['wcgwp_cart_note'];
                }
            }  

            // for gift-wrap added to line item on cart page
            if ( isset( $values['wcgwp_line_item_selection'] ) ) {
                $cart_item['wcgwp_line_item_selection'] = $values['wcgwp_line_item_selection'];

                if ( isset( $values['wcgwp_line_item_parent_key'] ) ) {
                    $cart_item['wcgwp_line_item_parent_key'] = $values['wcgwp_line_item_parent_key'];
                }
                if ( isset( $values['wcgwp_line_item_note'] ) ) {
                    $cart_item['wcgwp_line_item_note'] = $values['wcgwp_line_item_note'];
                }
                if ( isset( $values['wcgwp_line_item_parent_name'] ) ) {
                    $cart_item['wcgwp_line_item_parent_name'] = $values['wcgwp_line_item_parent_name'];
                }
                if ( isset( $values['wcgwp_line_item_parent_id'] ) ) {
                    $cart_item['wcgwp_line_item_parent_id'] = $values['wcgwp_line_item_parent_id'];
                } 
            }
            
            // for gift-wrapped product added from single product page
            if ( isset( $values['wcgwp_single_product_selection'] ) ) {
                $cart_item['wcgwp_single_product_selection'] = $values['wcgwp_single_product_selection'];
                if ( isset ( $values['wcgwp_single_product_note'] ) ) {
                    $cart_item['wcgwp_single_product_note'] = $values['wcgwp_single_product_note'];
                }
                if ( isset( $values['wcgwp_single_product_price'] ) ) {
                    $cart_item['wcgwp_single_product_price'] = $values['wcgwp_single_product_price'];
                }
                if ( isset( $values['wcgwp_product_parent_name'] ) ) {
                    $cart_item['wcgwp_product_parent_name'] = $values['wcgwp_product_parent_name'];
                }                
                if ( isset( $values['wcgwp_product_parent_id'] ) ) {
                    $cart_item['wcgwp_product_parent_id'] = $values['wcgwp_product_parent_id'];
                }         
                $cart_item = $this->add_cart_item( $cart_item );
            }

            // for gift-wrap added to product via simple checkbox on product page
            if ( isset( $values['wcgwp_simple_selection'] ) ) {
                $cart_item['wcgwp_simple_selection'] = $values['wcgwp_simple_selection'];
                if ( isset( $values['wcgwp_simple_price'] ) ) {
                    $cart_item['wcgwp_simple_price'] = $values['wcgwp_simple_price'];
                }
                if ( isset( $values['wcgwp_product_parent_name'] ) ) {
                    $cart_item['wcgwp_product_parent_name'] = $values['wcgwp_product_parent_name'];
                }                
                $cart_item = $this->add_cart_item( $cart_item );
            }
            return $cart_item;
        
        } // End get_cart_item_from_session()
                      
         /**
         * Remove gift wrap if parent product being removed from cart
         *
         * @param  string  $cart_item_key
         * @param  object  $cart
         * @return void
         */ 
        public function remove_cart_item( $cart_item_key, $cart ) {      

            if ( 
                ! isset( $_REQUEST['remove_item'] )
                && ! isset( $_POST['wcgwp_line_item_submit'] )
                && ! isset( $_POST['wcgwp_line_item_parent_key-0'] )
                && ! isset( $_POST['wcgwp_single_product'] )
                && ! isset( $_POST['wcgwp_simple_checkbox'] )
                && ! isset( $_POST['wcgwp_submit_before_cart'] )
                && ! isset( $_POST['wcgwp_submit_coupon'] )
                && ! isset( $_POST['wcgwp_submit_after_cart'] )
                && ! isset( $_POST['wcgwp_submit_checkout'] )
                && ! isset( $_POST['wcgwp_submit_after_checkout'] ) ) return;

            // Go through cart, see if any items have parent_key attribute matching $cart_item_key, 
            // if so remove them as well.

            foreach ( $cart->get_cart() as $key => $item ) {
               
                // not wrap, continue in loop
                if ( ! isset( $item['wcgwp_product_parent_key'] ) && ! isset( $item['wcgwp_line_item_parent_key'] ) ) continue;

                // item key isn't set in cart, skip
                if ( ! isset( $cart->cart_contents[ $key ] ) ) continue;
                  
                $parent_key = '';
                if ( isset( $item['wcgwp_product_parent_key'] ) ) {
                    $parent_key = $item['wcgwp_product_parent_key'];
                } else if ( isset( $item['wcgwp_line_item_parent_key'] ) ) {
                    $parent_key = $item['wcgwp_line_item_parent_key'];
                }  

                // going through cart, we have landed on a wrap product for the item to be deleted        
                if ( $parent_key == $cart_item_key ) { 
                
                    // remove, but save for later
                    $cart->removed_cart_contents[ $key ] = $cart->cart_contents[ $key ];
                    unset( $cart->cart_contents[ $key ] );
                    
                    // if we are adding (another/a new) wrap to existing line item,
                    // remove existing wrap if new/different wrap added.
                    if ( 'no' == get_option( 'giftwrap_product_num', 'no' ) && ! isset( $_REQUEST['remove_item'] ) ) {
                        unset( $cart->removed_cart_contents[ $key ] );
                    } else if ( isset( $_REQUEST['remove_item'] ) || isset( $_POST['wcgwp_line_item_submit'] ) || isset( $_POST['wcgwp_line_item_parent_key-0'] ) || isset( $_POST['wcgwp_product_parent_id'] ) ) {                                
                        unset( $cart->removed_cart_contents[ $key ]['data'] );
                    } else {
                        unset( $cart->removed_cart_contents[ $key ] );
                    }

                }
           
            }            
            
        } // End remove_cart_item()
 
       /**
         * Restore gift wrap as well when item restored to cart, in correct proportion
         *
         * @param  string   $cart_item_key
         * @param  obj      $cart
         * @return void
         */
        public function restore_cart_item( $cart_item_key, $cart ) {
        
            // maybe make sure to not run this function in cart/checkout for line items 
            // because it could cause restore_cart_item hook endless loop?
            // if ( isset( $_POST['wcgwp_line_item_submit'] ) ) return;
            
            // what quantity ratio does seller dictate? 
            $ratio = get_option( 'wcgwp_product_quantity', 'ad-lib' ); // one-to-one, only-one, ad-lib
            $removed_cart_contents = $cart->removed_cart_contents;

            foreach ( $removed_cart_contents as $key => $item ) {

                if ( $item['product_id'] ) {
                    $override_ratio = get_post_meta( $item['product_id'], '_wcgwp_product_quantity', TRUE );
                }
                $ratio = ! empty( $override_ratio ) && $override_ratio != 'default' ? $override_ratio : $ratio;            
            
                // restore gift wrap WITH its parent product
                if ( isset( $item['wcgwp_line_item_parent_key'] ) && $item['wcgwp_line_item_parent_key'] == $cart_item_key
                    || isset( $item['wcgwp_product_parent_key'] ) && $item['wcgwp_product_parent_key'] == $cart_item_key ) {   

                    $restore_wrap                                   = $cart->removed_cart_contents[ $key ];
			        $cart->cart_contents[ $key ]                    = $restore_wrap;
			        if ( $ratio == 'one-to-one' ) { 
    			        $cart->cart_contents[ $key ]['quantity']    = $cart->cart_contents[ $cart_item_key ]['quantity'];
    			    } else if ( $ratio == 'only-one' ) {
    			        $cart->cart_contents[ $key ]['quantity']    = 1;    			    
    			    } 
			        $cart->cart_contents[ $key ]['data']            = wc_get_product( $restore_wrap['variation_id'] ? $restore_wrap['variation_id'] : $restore_wrap['product_id'] );
                    unset( $cart->removed_cart_contents[ $key ] );
                    
                }

            }

            return;
        
        } // End restore_cart_item()        

        /*
        * Maybe adjust number of gift wrap after parent product quantity changed
        *
        * @param string $cart_item_key
        * @param int $quantity
        * @param int $old_quantity
        * @param object $quantity
        * @return void
        */
        public function after_cart_item_quantity_update( $cart_item_key, $quantity, $old_quantity, $cart ) {
                    
            // what quantity ratio does seller dictate? 
            $ratio = get_option( 'wcgwp_product_quantity', 'ad-lib' ); // one-to-one, only-one, ad-lib
     
            // parent product is being changed, parent key is $cart_item_key                    
            // look through cart, match 'wcgwp_line_item_parent_key' with $cart_item_key?
            foreach ( $cart->get_cart() as $key => $item ) {
        
                $parent_quantity = NULL;
                if ( $item['product_id'] ) {
                    $override_ratio = get_post_meta( $item['product_id'], '_wcgwp_product_quantity', TRUE );
                }
                $ratio = ! empty( $override_ratio ) && $override_ratio != 'default' ? $override_ratio : $ratio;
                // don't bother continuing if we're talking ad lib ratio
                if ( $ratio != 'one-to-one' && $ratio != 'only-one' ) continue;
                
                if ( isset( $item['wcgwp_line_item_parent_key'] ) ) {
                    $parent_key = $item['wcgwp_line_item_parent_key'];
                } else if ( isset( $item['wcgwp_product_parent_key'] ) ) {
                    $parent_key = $item['wcgwp_product_parent_key'];
                } else {
                    continue;
                }
                // when changing parent product, adjust wrap product quantity
                // we have a relationship: product/wrap
                if ( $parent_key == $cart_item_key ) { 
                    // now adjust quantity of wrap item
                    if ( $ratio == 'one-to-one' ) {
                        $cart->cart_contents[$key]['quantity'] = $quantity;
                        continue;
                    }
                    if ( $ratio == 'only-one' ) {
                        $cart->cart_contents[$key]['quantity'] = 1;
                        continue;
                    }
                }
                // we are adjusting starting at gift wrap change
                if ( $cart_item_key == $key ) {
                    // when changing wrap product, maybe change parent product quantity
                    if ( isset( $cart->cart_contents[$parent_key] ) ) {
                        $parent_quantity = $cart->cart_contents[$parent_key]['quantity'];
                        // if wrap item is being set to diff # than parent item, ignore
                        if ( $ratio == 'one-to-one' && $item['quantity'] != $parent_quantity ) {
                            // now adjust quantity of wrap item
                            $cart->cart_contents[ $key ]['quantity'] = $parent_quantity;
                        }
                        if ( $ratio == 'only-one' && $item['quantity'] != 1 ) {
                            $cart->cart_contents[ $key ]['quantity'] = 1;
                        }
                    }
                }
                
            }
                        
        } // End after_cart_item_quantity_update()
        
        /**
         * Add more to the formatted list of cart item data + variations for display on the frontend.
         * Shows in the cart
         *
         * @param array $item_data Item data.
         * @param array $cart_item  Cart item data.
         * @return array
         */
        public function get_item_data( $item_data, $cart_item ) {
        
            // cart/checkout hooked general gift wrapping
            if ( isset( $cart_item['wcgwp_cart_selection'] ) ) {

                $note = ! empty( $cart_item['wcgwp_cart_note'] ) ? $cart_item['wcgwp_cart_note'] : NULL;
                if ( $note ) {
                    do_action( 'wcgwp_note_added', $note );
                    $item_data[] = array(
                        'key'   => __( 'Note', 'woocommerce-gift-wrapper-plus' ),
                        'value' => $note,
                    );
                }
            }

            $type = get_option( 'giftwrap_per_product_type', 'attribute' );            
        
            // single product page
            if ( isset( $cart_item['wcgwp_single_product_selection'] ) ) {
        
                if ( $type == 'attribute' ) {
                    $name = $cart_item['wcgwp_single_product_selection'];
                    if ( $cart_item['wcgwp_single_product_price'] && apply_filters( 'wcgwp_add_price_to_name', TRUE ) ) {
                        $name .= ' (' . wc_price( WCGWrap()->get_price_for_display( $cart_item['wcgwp_single_product_price'] ) ) . ')';
                    }
                    $item_data[] = array(
                        'key'       => __( 'Gift wrap', 'woocommerce-gift-wrapper-plus'),
                        'value'     => $name,
                    );
                }
                $single_note = ! empty( $cart_item['wcgwp_single_product_note'] ) ? $cart_item['wcgwp_single_product_note'] : NULL;
                if ( $single_note ) {
                    do_action( 'wcgwp_single_note_added', $single_note );
                    $item_data[] = array(
                        'key'   => __( 'Note', 'woocommerce-gift-wrapper-plus' ),
                        'value' => $single_note,
                    );
                }
            }

            // simple (checkbox-style) single product page
            if ( isset( $cart_item['wcgwp_simple_price'] ) && $type == 'attribute' ) {
            
                $name = $cart_item['wcgwp_simple_selection'];
                if ( apply_filters( 'wcgwp_add_price_to_name', TRUE ) ) {
                    $name .= ' (' . wc_price( WCGWrap()->get_price_for_display( $cart_item['wcgwp_simple_price'] ) ) . ')';
                }
                $item_data[] = array(
                    'key'           => __( 'Gift wrap', 'woocommerce-gift-wrapper-plus'),
                    'value'         => $name,
                );

            }
                    
            // line item
            if ( isset( $cart_item['wcgwp_line_item_selection'] ) ) {
                
                $line_item_note = ! empty( $cart_item['wcgwp_line_item_note'] ) ? $cart_item['wcgwp_line_item_note'] : NULL;
                
                if ( $line_item_note ) {
                    do_action( 'wcgwp_line_item_note_added', $line_item_note );
                    $item_data[] = array(
                        'key'       => __( 'Note', 'woocommerce-gift-wrapper-plus' ),
                        'value'     => $line_item_note,
                    );
                    
                }

                if ( isset( $cart_item['line_item_parent_name'] ) ) {
                
                    $item_data[] = array(
                        'key'       => __( 'For', 'woocommerce-gift-wrapper-plus' ),
                        'value'     => $cart_item['line_item_parent_name'],
                    );
                    
                }
            }
            return $item_data;
        
        } // End get_item_data()

        /**
         * Action hook. Include gift wrap line item meta.
         *
         * @param object $item             WC_Order_Item_Product
         * @param string $cart_item_key    Cart item key.
         * @param array $values            Order item values.
         */
        public function checkout_create_order_line_item( $item, $cart_item_key, $values ) {
            
            if ( isset( $values['wcgwp_cart_note'] ) ) {
                $item->add_meta_data( 'wcgwp_note', $values['wcgwp_cart_note'] );
            }

            if ( isset( $values['wcgwp_single_product_selection'] ) ) {

                $select_value = $values['wcgwp_single_product_selection'];
                if ( $values['wcgwp_single_product_price'] && apply_filters( 'wcgwp_add_price_to_name', true ) ) {
                    $select_value .= ' (' . strip_tags( wc_price( WCGWrap()->get_price_for_display( $values['wcgwp_single_product_price'], $values['data'], true ) ) ) . ')';
                }
                $item->add_meta_data( 'wcgwp_selection', $select_value );
                $item->add_meta_data( 'wcgwp_note', $values['wcgwp_single_product_note'] );
                $item->add_meta_data( 'wcgwp_parent_name', $values['wcgwp_product_parent_name'] );                

            }
                        
            if ( isset( $values['wcgwp_simple_selection'] ) ) {

                $select_value = $values['wcgwp_simple_selection'];
                if ( $values['wcgwp_simple_price'] && apply_filters( 'wcgwp_add_price_to_name', true ) ) {
                    $select_value .= ' (' . strip_tags( wc_price( WCGWrap()->get_price_for_display( $values['wcgwp_simple_price'], $values['data'], true ) ) ) . ')';
                }
                $item->add_meta_data( 'wcgwp_selection', $select_value );
                $item->add_meta_data( 'wcgwp_price', $values['wcgwp_simple_price'] );
                $item->add_meta_data( 'wcgwp_parent_name', $values['wcgwp_product_parent_name'] );

            }
            
            if ( isset( $values['wcgwp_line_item_selection'] ) ) {

                $item->add_meta_data( 'wcgwp_note', $values['wcgwp_line_item_note'] );
                $item->add_meta_data( 'wcgwp_parent_name', $values['wcgwp_line_item_parent_name'] );

            } 

        } // End checkout_create_order_line_item()
  
        /*
        * Remove item permalink in cart if desired
        *
        * @param string $link Cart item link, whether URL or blank
        * @param object $cart_item Cart item
        * @param string $cart_item_key Cart item key
        * @return string
        */
        public function cart_item_permalink( $link, $cart_item, $cart_item_key ) {

            if ( get_option( 'giftwrap_product_link', 'yes' ) == 'yes' ) {
                return $link;
            }
            
            if ( $this->check_item_for_giftwrap_cat( $cart_item ) ) {
                $link = '';
                return $link;
            }
            return $link;
    
        } // End remove_link_in_cart()
      
        /*
        * Discover gift wrap products in cart
        *
        * @param $cart_item
        * @return bool
        */	
        public function check_item_for_giftwrap_cat( $item ) {

            $product_id = is_a( $item, 'WC_Order_Item_Product' ) ? $item->get_product_id() : $item['data']->get_id();
            
            $giftwrap_category = get_option( 'giftwrap_category_id', '' );	
            $terms = get_the_terms( $product_id, 'product_cat' );
            if ( $terms ) {
                foreach ( $terms as $term ) {
                    if ( $term->term_id == $giftwrap_category ) {
                      return TRUE;
                    }
                }
            }
            return FALSE;
            
        } // End check_item_for_giftwrap_cat()

        /**
         * Changes the tr class of cart items.
         *
         * @param  string  $class
         * @param  array   $values
         * @param  string  $values_key
         * @return string
         */
        public function cart_item_class( $class, $values, $values_key ) {

            if ( isset( $values[ 'wcgwp_cart_selection' ] ) || isset( $values['wcgwp_cart_selection'] ) ) {
                $class .= ' wcgwp-cart-item ';
            }
            return $class;
            
        } // End cart_item_class()
        
        /*
        * If 'wcgwp_remove_cod_gateway' filter set to TRUE,
        * removes COD option in checkout
        *
        * @param array $gateways        
        * @return array gateways
        */
        public function available_payment_gateways( $gateways ) {
            
            if ( ! WCGWrap()->wrapping->giftwrap_in_cart ) return $gateways;

            if ( apply_filters( 'wcgwp_remove_cod_gateway', FALSE ) ) {
                if ( isset( $gateways['cod'] ) ) {
                    unset( $gateways['cod'] );
                }
            }
            return $gateways;

        } // End available_payment_gateways()
        
        /*
        * NOT IN USE
        *
        * We need to know what gift wrap products are in cart
        * and what parent products they're attached to
        *
        * @return array
        */        
        public function is_line_item_wrapped( $cart_item_key ) {

            $wrap_in_cart = FALSE;    
            $keys = $gift_keys = array();
            // Go through each cart item and add to array with 3 keys (key/parent key, is giftwrap, original sequence)
            // Also, check if gift wrap is in the cart
            foreach ( WC()->cart->get_cart() as $key => $item ) {   
                $keys[] = $item['key'];
                if ( isset( $item['wcgwp_line_item_parent_key'] ) ) {
                    $gift_keys[] = $item['wcgwp_line_item_parent_key'];
                    $wrap_in_cart = TRUE;
                }
            }
            $matches = array_intersect( $keys, $gift_keys );
            // if $matches array has values, we have to decide whether to replace or not replace existing gift wrap. Hm...
            if ( empty( $matches) ) {
                return FALSE;
            } else {
                return TRUE;
            }
        
        }    
        
        /**
         * NOT IN USE
         * Restore gift wrap as well when item restored to cart
         *
         * @param  string   $cart_item_key
         * @param  obj      $cart
         * @return void
         */
        public function cart_item_restored( $cart_item_key, $cart ) {
        
            $removed_cart_contents = WC()->cart->get_removed_cart_contents();
            
            if ( ! isset( $removed_cart_contents[ $cart_item_key ] ) ) return;
            
            // cart_item_key is PARENT cart_item_key             
            
            foreach ( $removed_cart_contents as $key => $item ) {
            
                if ( isset( $item['wcgwp_line_item_parent_key'] ) && $item['wcgwp_line_item_parent_key'] == $cart_item_key ) { 

			        unset( $this->removed_cart_contents[ $item['key'] ] );

                }
            }
        
        } // End restore_cart_item()
        
        
        /*
        * NOT IN USE
        * Maybe reduce stock levels for simple and single gift wrapping
        *
        * @param int $order_id
        * @return void
        */
        public function payment_complete( $order_id ) {
        
            $order = wc_get_order( $order_id );
            if ( ! $order ) return;

            $stock_reduced  = $order->get_data_store()->get_stock_reduced( $order_id );
            $trigger_reduce = apply_filters( 'woocommerce_payment_complete_reduce_order_stock', ! $stock_reduced, $order_id );

            // Only continue if we're reducing stock.
            if ( ! $trigger_reduce ) return;
            
            $items = $order->get_items();

            foreach ( $items as $item ) {
            
                $formatted_meta_data = $item->get_formatted_meta_data();
                // focus on wrap products which weren't their own cart items
                if ( isset( $item['wcgwp_single_product_selection'] ) ) {
                   $this->manage_stock( $item );
				}
                if ( isset( $item['wcgwp_simple_selection'] ) ) {
                   $this->manage_stock( $item );
                }
                
            }
        
        } // End payment_complete()           

        /*
        * NOT IN USE
        *
        * @param obt $item
        * @return void
        */        
        private function manage_stock( $item ) {
        
            $product_id = absint( $item['id'] );
            $product    = wc_get_product( $product_id );
            $qty        = wc_stock_amount( $item['qty'] );
            
            if ( $product->managing_stock() ) {
                $new_stock               = wc_update_product_stock( $product, $qty, 'decrease' );
                $order_notes[ $item_id ] = $product->get_formatted_name() . ' &ndash; ' . ( $new_stock + $qty ) . '&rarr;' . $new_stock;
                $item->add_meta_data( '_reduced_stock', $qty, true );
                $item->save();
            }
        }

    }  // End class WC_Gift_Wrapper_Cart

endif;