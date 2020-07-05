<?php
defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'WC_Gift_Wrapper_Wrapping' ) ) :

    class WC_Gift_Wrapper_Wrapping {
    
        /*
        * Is there gift wrap in the cart?
        * @var string
        */
        var $giftwrap_in_cart = FALSE;
        /*
        * Cart full of virtual products only
        * @var boolean
        */
        var $cart_virtual_products_only = FALSE;
        
        public function __construct() {

            add_action( 'wp',                                           array( $this, 'setup_wrap_placements' ), 10 );            		
            add_action( 'woocommerce_cart_loaded_from_session',         array( $this, 'check_cart_for_giftwrap' ), 10, 1  );

        }  

        /*
        * Init - l10n & hooks
        *
        * @param void
        * @return void
        */
        public function setup_wrap_placements() {
        
            // check to make sure we should bother with wrap placements in cart/checkout
            if ( is_admin() || ( ! is_cart() && ! is_checkout() && ! is_product() ) ) return;
            if ( $this->count_giftwrapped_products() < 1 || ( $this->cart_virtual_products_only && $this->only_virtual_products_in_cart() === TRUE ) ) return;
            if ( wcgwp_excluded_from_gift_wrap_by_cat() ) return;
 
            // more checking
            $giftwrap_display = get_option( 'giftwrap_display', array( 'none' ) );
            if ( $giftwrap_display == array('none') ) return;
           
            if ( apply_filters( 'giftwrap_exclude_virtual_products', false ) ) {
                $this->cart_virtual_products_only = TRUE;
            }            
            $before = $collaterals = $after = $before_checkout = $after_checkout = FALSE;
                
            if ( ! is_array( $giftwrap_display ) ) {
                $giftwrap_display = str_split( $giftwrap_display, 17 );
            }
            if ( in_array( "before_cart", $giftwrap_display ) ) {
                $before = add_action( 'woocommerce_before_cart', function() { $this->gift_wrap_action( $label = '_before_cart' ); } );
            }
            if ( in_array( "after_coupon", $giftwrap_display ) ) {
                $collaterals = add_action( 'woocommerce_before_cart_collaterals', function() { $this->gift_wrap_action( $label = '_coupon' ); } );
            }
            if ( in_array( "after_cart", $giftwrap_display ) ) {
                $after = add_action( 'woocommerce_after_cart', function() { $this->gift_wrap_action( $label = '_after_cart' ); } );
            }
            if ( ( $before === TRUE || $collaterals === TRUE || $after === TRUE ) || get_option( 'giftwrap_line_item' ) == 'yes' ) {
                add_action( 'wp_footer', array( $this, 'cart_footer_js' ), 10 );
            }
            if ( in_array( "before_checkout", $giftwrap_display ) ) {
                $before_checkout = add_action( 'woocommerce_before_checkout_form', function() { $this->gift_wrap_action( $label = '_checkout' ); } );
            }
            if ( in_array( "after_checkout", $giftwrap_display ) ) {
                $after_checkout = add_action( 'woocommerce_after_checkout_form', function() { $this->gift_wrap_action( $label = '_after_checkout' ); } );
            }
            if ( $before_checkout === TRUE || $after_checkout === TRUE || get_option( 'giftwrap_line_item' ) == 'yes' ) {
                add_action( 'wp_footer', array( $this, 'checkout_footer_js' ) );
            }
            
            $this->add_giftwrap_to_order();
            
        } // End init()
        
        /*
        * Put JavaScript inline in footer for cart
        *
        * @param void
        * @return void
        */
        public function cart_footer_js() {
         
            if ( ! is_cart() ) return;

            if ( get_option( 'giftwrap_modal' ) == 'no' || ( get_option( 'giftwrap_line_item' ) == 'yes' && get_option( 'giftwrap_line_item_modal' ) == 'no' ) ) {
                wc_get_template( 'wcgwp/cart-slideout-js.php', array(), '', plugin_dir_path(__DIR__) . 'templates/');
            }

            // if replacing the only giftwrap item allowed in cart
            if ( "no" == get_option( 'giftwrap_number' ) && $this->giftwrap_in_cart === TRUE && apply_filters( 'wcgwp_load_replace_wrap_js', true ) ) {
                wc_get_template( 'wcgwp/replace-wrap-js.php', array(), '', plugin_dir_path(__DIR__) . 'templates/');            
            }
            
        } // End cart_footer_js()

        /*
        * Put JavaScript inline in footer for checkout
        *
        * @param void
        * @return void
        */
        public function checkout_footer_js() {

            if ( ! is_checkout() || is_wc_endpoint_url( 'order-received' ) ) return;
                       
            if ( get_option( 'giftwrap_modal' ) == 'no' || ( get_option( 'giftwrap_line_item' ) == 'yes' && get_option( 'giftwrap_line_item_modal' ) == 'no' ) ) {
                wc_get_template( 'wcgwp/checkout-slideout-js.php', array(), '', plugin_dir_path(__DIR__) . 'templates/');
            }
            // if replacing the only giftwrap item allowed in cart
            if ( "no" == get_option( 'giftwrap_number' ) && $this->giftwrap_in_cart === TRUE && apply_filters( 'wcgwp_load_replace_wrap_js', true ) ) {
                wc_get_template( 'wcgwp/replace-wrap-js.php', array(), '', plugin_dir_path(__DIR__) . 'templates/');            
            }

        } // End checkout_footer_js()


        /*
        * Whether to show gift wrap product thumbnails...
        *
        * @param string $product_id
        * @return bool
        */	
        public function show_thumbs( $product_id ) {
            
            if ( $product_id ) {

                // per-product drill down
                if ( get_post_meta( $product_id, '_wcgwp_show_thumbs', TRUE ) == 'yes' ) return TRUE; 
                if ( ( is_cart() || is_checkout() ) && get_option( 'giftwrap_show_thumb', 'yes' ) == 'yes' && get_option( 'giftwrap_product_show_thumb', 'yes' ) == 'yes' ) {
                    return TRUE;
                }

            } else { // we are coming from general cart/checkout wrapping
            
                // show thumbnail in giftwrap listings
                if ( get_option( 'giftwrap_show_thumb', 'yes' ) == 'yes' ) return TRUE;
        
            }
            return FALSE;

        } // End show_thumbs()

        /*
        * Discover gift wrap products in cart
        *
        * @param $cart
        * @return void
        */	
        public function check_cart_for_giftwrap( $cart ) {
        
            foreach ( $cart->cart_contents as $value ) {
                $product_id = $value['product_id'];        
                $terms = get_the_terms( $product_id , 'product_cat' );
                if ( $terms ) {
                    $giftwrap_category = get_option( 'giftwrap_category_id', '' );	
                    foreach ( $terms as $term ) {
                        if ( $term->term_id == $giftwrap_category ) {
                            $this->giftwrap_in_cart = TRUE;
                            break;
                        }
                    }
                }
            }
            return;
        }

        /*
        * Add gift wrapping to cart
        *
        * @param void
        * @return void
        */
        public function add_giftwrap_to_order() {
            
            $placements = array(
                'wcgwp_submit_before_cart' => array( 'wcgwp_product_before_cart', 'wcgwp_note_before_cart' ),
                'wcgwp_submit_coupon' => array( 'wcgwp_product_coupon', 'wcgwp_note_coupon' ),
                'wcgwp_submit_after_cart' => array( 'wcgwp_product_after_cart', 'wcgwp_note_after_cart' ),
                'wcgwp_submit_checkout' => array( 'wcgwp_product_checkout', 'wcgwp_note_checkout' ),
                'wcgwp_submit_after_checkout' => array( 'wcgwp_product_after_checkout', 'wcgwp_note_after_checkout' ),
            );
            foreach ( $placements as $placement => $submit ) {
                if ( isset( $_POST[ $placement ] ) ) {
                    $product = isset( $_POST[ $submit[0] ] ) ? (int) $_POST[ $submit[0] ] : FALSE;
                    if ( ! $product ) return;
                    $notes = sanitize_text_field( stripslashes( $_POST[ $submit[1] ] ) );            
                    $this->add_giftwrap( $product, $notes );
                }
            }
            // POST/REDIRECT/GET to prevent wrap from showing back up after delete + refresh
            if ( isset( $_POST['wcgwp_submit_before_cart'] ) || isset( $_POST['wcgwp_submit_coupon'] ) || isset( $_POST['wcgwp_submit_after_cart'] ) ) {
                wp_safe_redirect( wc_get_cart_url(), 303 );
                exit; // not die() because inside hook
            }
            if ( isset( $_POST['wcgwp_submit_after_checkout'] ) || isset( $_POST['wcgwp_submit_checkout'] ) ) {
                wp_safe_redirect( wc_get_checkout_url(), 303 );
                exit; // not die() because inside hook
            }
            /* todo            
            if ( isset( $_POST['simple_giftwrap_note_btn'] ) ) {
                $giftwrap_notes = $_POST['simple_wcgwp_notes'] != '' ? array( 'giftwrapper_notes' => sanitize_text_field( $_POST['simple_wcgwp_notes'] ) ) : FALSE;
            }*/
            
        }

        /*
        * Use WC add_to_cart method to add cart/checkout giftwrap to order
        *
        * @param int $product
        * @param string $notes
        * @return void
        */            
        private function add_giftwrap( $product, $notes ) {
        
            // default add ONE, however, can be filtered (and templates edited) to allow quantity field
            $quantity = apply_filters( 'wwpdf_add_to_cart_quantity', 1, $_POST );

            // check if allowed more than one gift wrap to cart, if not, maybe remove existing item(s)
            $giftwrap_num = get_option( 'giftwrap_number', 'no' );
            if ( $giftwrap_num == 'no' && $this->giftwrap_in_cart === TRUE ) {

                foreach ( WC()->cart->get_cart() as $cart_item_key => $cart_item ) {

                    // don't remove line-item wrapping, or per-product wrapping                    
                    if ( isset( $cart_item['wcgwp_line_item_selection'] ) || isset( $cart_item['wcgwp_single_product_selection'] ) || isset( $cart_item['wcgwp_simple_selection'] ) ) continue;
                    
                    $product_id = $cart_item['product_id'];   
                    $it_matches = FALSE;       
                    $terms = get_the_terms( $product_id, 'product_cat' );
                    if ( $terms ) {
                        $giftwrap_cat = get_option( 'giftwrap_category_id', TRUE );	
                        foreach ( $terms as $term ) {
                            if ( $term->term_id == $giftwrap_cat ) {
                                $it_matches = TRUE;    
                                break;                    
                            }
                        }
                        if ( $it_matches ) {
                            WC()->cart->remove_cart_item( $cart_item_key );
                        }
                    }
                }
            } 

            // Parameters: add_to_cart( $product_id = 0, $quantity = 1, $variation_id = 0, $variation = array(), $cart_item_data = array() )
            // $cart_item_data is important for display and bookkeeping later...
            WC()->cart->add_to_cart( $product, $quantity, 0, array(), array( 'wcgwp_cart_note' => $notes, 'wcgwp_cart_selection' => $product ) );	
                            
        } // End add_giftwrap()

        /*
        * Count array of products in gift wrap category
        *
        * @param void
        * @return int
        */ 
        public function count_giftwrapped_products() {
        
            $count = count( get_wcgwp_products() );
            return $count;
            
        }

        /*
        * Add conditional classes to giftwrap wrapper div
        *
        * @param void
        * @return string
        */             
        public function extra_class() {
            $extra_class = '';
            if ( $this->giftwrap_in_cart === FALSE || ( $this->giftwrap_in_cart === TRUE && get_option( 'giftwrap_number', 'no' ) == 'yes' ) ) {
                 $extra_class = ' wcgwp_could_giftwrap';
            }
            $extra_class = apply_filters( 'wcgwp_extra_wrapper_class', $extra_class );
            return $extra_class;
        }

        /*
        * Add gift wrap options
        *
        * @param string $label
        * @return void
        */	
        public function gift_wrap_action( $label ) {
            
            $giftwrap_details = get_option( 'giftwrap_details', '' );

            ob_start(); ?>
            <div class="wc-giftwrap giftwrap<?php echo $label; ?> giftwrap-before-cart giftwrap-coupon giftwrap-after-cart giftwrap-checkout <?php echo $this->extra_class(); ?> ">
                <?php
                // if modal version
                if ( get_option( 'giftwrap_modal', 'yes' ) == 'yes' ) {

                    wc_get_template( 'wcgwp/modal.php', array( 'label' => $label, 'list' => get_wcgwp_products(), 'giftwrap_details' => $giftwrap_details, 'show_thumbs' => $this->show_thumbs( $product_id = NULL ) ), '', WCGWP_PLUGIN_DIR . 'templates/');

                // non-modal version
                } else { 
                
                    $version = get_option( 'wcgwp_version' );
                    if ( $version < '2.3' || ! isset( $version ) ) { ?>

                        <div class="giftwrap_header_wrapper gift-wrapper-info">
                            <a href="#" class="show_giftwrap show_giftwrap<?php echo $label; ?>"><?php echo apply_filters( 'wcgwp_add_wrap_prompt', esc_html__( 'Add gift wrap?', 'woocommerce-gift-wrapper-plus' ) ); ?></a>
                        </div>
                        <form method="post" class="giftwrap_products giftwrapper_products non_modal wcgwp_slideout wcgwp_form">
                            <?php if ( ! apply_filters( 'wcgwp_hide_details', FALSE ) ) { ?>
                                <p class="giftwrap_details">
                                <?php if ( ! empty( $giftwrap_details ) ) {
                                    echo esc_html( $giftwrap_details );
                                } else {
                                    esc_html_e( 'We offer the following gift wrap options:', 'woocommerce-gift-wrapper' );
                                } ?>
                                </p>
                            <?php }
                        
                            wc_get_template( 'wcgwp/giftwrap-list-cart.php', array( 'label' => $label, 'list' => get_wcgwp_products(), 'show_thumbs' => $this->show_thumbs( $product_id = NULL ) ), '', WCGWP_PLUGIN_DIR . 'templates/');
                        ?>
                        </form>
                    <?php } else { 
                        wc_get_template( 'wcgwp/giftwrap-list-cart-checkout.php', array( 'label' => $label, 'list' => get_wcgwp_products(), 'giftwrap_details' => $giftwrap_details, 'show_thumbs' => $this->show_thumbs( $product_id = NULL ) ), '', WCGWP_PLUGIN_DIR . 'templates/');
                    }
                    
                } ?>
            </div>
            
            <?php 
            $template = ob_get_contents();
	        ob_end_clean();
	        echo $template;
	
	    } // End gift_wrap_action()

        /**
         * Check if the cart contains virtual product
         * forked via Remi Corson, 10/2013
         * @return bool
        */
        public function only_virtual_products_in_cart() {

            $has_virtual_products = FALSE;
            $virtual_products = 0;
            $products = WC()->cart->get_cart();

            foreach ( $products as $product ) {
                // Get product ID and '_virtual' post meta
                $product_id = $product['product_id'];
                $is_virtual = get_post_meta( $product_id, '_virtual', TRUE );
                // Update $has_virtual_product if product is virtual
                if ( $is_virtual == 'yes' ) {
                    $virtual_products += 1;
                }
            }

            if ( count( $products ) == $virtual_products ) {
                $has_virtual_products = TRUE;
            }
            return apply_filters( 'wcgwp_virtual_products_only', $has_virtual_products );

        } // End only_virtual_products_in_cart()
        

    }  // End class WC_Gift_Wrapper_Plus

endif;

/*
 * Check if products/cart are in excluded categories
 *
 * @return bool
*/ 
if ( ! function_exists( 'wcgwp_excluded_from_gift_wrap_by_cat' ) ) {

    function wcgwp_excluded_from_gift_wrap_by_cat( $product_id = FALSE ) {

        // what are the excluded product categories?
        $excluded_cats = get_option( 'giftwrap_exclude_cats', array() );
        // no excluded categories
        if ( ! $excluded_cats || ! isset( $excluded_cats ) ) return FALSE;
        
        // change str values to int
        $excluded_cats = array_map( 'intval', $excluded_cats );

        // get the chosen gift wrap category, to remove it from arrays later
        $giftwrap_cat = explode( ',', get_option( 'giftwrap_category_id', '' ) );
        $giftwrap_cat = get_option( 'giftwrap_category_id', '' );
        
        $cats = array();
        
        if ( is_product() ) { // get categories of individual product
            
            $term_ids = wp_get_post_terms( $product_id, 'product_cat', array( 'fields' => 'ids' ) );
            // if it's in giftwrap category, remove its other categories
            if ( has_term( $giftwrap_cat, 'product_cat', $product_id ) ) {
                $cats = array( $giftwrap_cat );
            } else {
                $cats = $term_ids;
            } 

        } else { // get IDs/cats of ALL cart items   

            // get categories of every item in cart (including gift wrap items)
            foreach( WC()->cart->get_cart() as $cart_item ) {
                $term_ids = wp_get_post_terms( $cart_item['product_id'], 'product_cat', array( 'fields' => 'ids' ) );
             
                // if it's in giftwrap category, remove its other categories
                if ( has_term( $giftwrap_cat, 'product_cat', $cart_item['product_id'] ) ) {
                    $cats[] = array( $giftwrap_cat );
                } else {
                    $cats[] = $term_ids;
                }
            }
            $cats = array_unique( array_reduce( $cats, 'array_merge', array() ) );
            $cats = array_diff( $cats, array( $giftwrap_cat ) ); // remove giftwrap category
            $cats = array_values( $cats ); // re-index, probably not necessary
            
        }

        if ( $giftwrap_cat ) {
            // remove giftwrap cat from excluded cats
            $excluded_cats = array_diff( $excluded_cats, array( $giftwrap_cat ) ); 
        }
        
        // if we still have excluded cats after gift wrap cat removed
        // get categories of line item to compare to excluded cats:
        if ( $excluded_cats ) {

            // add children of parent cats to array to cover all bases:
            $kids = array();
            foreach ( $excluded_cats as $key => $exclude_cat ) {
                $categories = get_categories( array( 'child_of' => $exclude_cat, 'taxonomy' => 'product_cat' ) );
                if ( $categories ) {                 
                    $kids[] = wp_list_pluck( $categories, 'term_id' );
                }
            }
            $kids = array_unique( array_reduce( $kids, 'array_merge', array() ) );
            $excluded_cats = array_merge( $excluded_cats, $kids );              

            if ( ! empty( $cats ) ) {
                // the whole cart is excluded!
                if ( ! is_product() && empty( array_diff( $cats, $excluded_cats ) ) ) {                                
                    return TRUE;
                }
                // product cat is in list of excluded categories
                if ( is_product() && ! empty( array_intersect( $excluded_cats, $cats ) ) ) {                  
                   return TRUE;
                }
            }       
        }
        return FALSE;

    } // End wcgwp_excluded_from_gift_wrap_by_cat()

}

/*
* Return array of products in gift wrap category
*
* @param string $product_id
* @return array
*/ 
if ( ! function_exists( 'get_wcgwp_products' ) ) {

    function get_wcgwp_products( $product_id = NULL ) {

        $giftwrap_cat_id = get_option( 'giftwrap_category_id', 'none' );
        
        // product wrapping - single & variable
        if ( $product_id ) {
        
            // bottom line. we either wrap it or we don't, single or variation
            if ( get_post_meta( $product_id, '_wcgwp_wrap_this', TRUE ) == 'yes' ) {
           
                $giftwrap_product_cat_id = get_post_meta( $product_id, '_wcgwp_category', TRUE );
                if ( isset( $giftwrap_product_cat_id ) && $giftwrap_product_cat_id != '1' ) {                    
                    $giftwrap_cat_id = $giftwrap_product_cat_id;
                }
                $giftwrap_product_id = get_post_meta( $product_id, '_wcgwp_product_id', TRUE );
                // check if there isn't a single wrap product override of a category for this item
                if ( $giftwrap_product_id ) {
                    $post = get_post( $giftwrap_product_id );
                    return array( $post );
                }                
            }
        }
        
        // admin doesn't have a gift wrap category set!
        if ( empty( $giftwrap_cat_id ) ) {
            $wcgwp_error = new WP_Error( 'wcgwp_setup', 'WooCommerce Gift Wrapper Plus is not set up properly.' );
            $wcgwp_error->add( 'wcgwp_setup', 'Please choose a product category to represent your gift wrap options in the WCGWP settings.' );
            return array();
        }

        $giftwrap_cat_term = get_term( $giftwrap_cat_id, 'product_cat' );
        $orderby = 'date';
        $order = 'DESC';
        $args = array(
            'post_type'         => 'product',
            'post_status'       => 'publish',
            'posts_per_page'    => '-1',
            'orderby'           => apply_filters( 'wcgwp_orderby', $orderby ),
            'order'             => apply_filters( 'wcgwp_order', $order ),
            'suppress_filters'  => FALSE, // for WPML
            'tax_query'         => array(
                array(
                    'taxonomy'  => 'product_cat',
                    'field'     => 'slug',
                    'terms'     =>  $giftwrap_cat_term->slug,
                )
            ),
            'meta_query'        => array(
                array(
                    'key'       => '_stock_status',
                    'value'     => 'instock',
                )
            ),
        );
        return get_posts( $args );	

    } // End get_wcgwp_products()

}