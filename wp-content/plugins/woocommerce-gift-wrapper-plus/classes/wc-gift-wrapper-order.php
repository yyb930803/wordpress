<?php
defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'WC_Gift_Wrapper_Order' ) ) :

    class WC_Gift_Wrapper_Order {
    
        public function __construct() {
    
            add_action( 'init', array( $this, 'init' ), 10 );          

        }

        /*
        * Init - l10n & hooks
        *
        * @param void
        * @return void
        */
        public function init() {
        
            // Filter the item meta display, such as on order confirmation page
    		add_filter( 'woocommerce_display_item_meta',                        array( $this, 'display_item_meta' ), 10, 3 );
    		
    		// Filter the item meta display key, such as on order confirmation page
    		add_filter( 'woocommerce_order_item_display_meta_key',              array( $this, 'order_item_display_meta_key' ), 10, 3 );
    		    		
            // Filter the meta_data, specifically to remove price meta    		    		
    		add_filter( 'woocommerce_order_item_get_formatted_meta_data',       array( $this, 'order_item_get_formatted_meta_data'), 10, 2 );        

            add_filter( 'woocommerce_order_item_permalink',                     array( $this, 'remove_link_in_order' ), 10, 3 );  

        } // End init()
        
        /**
         * Filter the item meta display, such as on order confirmation page
         *
         * @param string $html      Display HTML
         * @param object $item      WC_Order_Item_Product
         * @param array $args       autop, separator args
         * @return string
         */
        public function display_item_meta( $html, $item, $args ) {
        
            if ( FALSE !== strpos( $html, 'wcgwp_selection' ) ) {
                $html = str_replace( 'wcgwp_selection', __( 'Gift wrap', 'woocommerce-gift-wrapper-plus' ), $html );
            }
            if ( FALSE !== strpos( $html, 'wcgwp_note' ) ) {
                $html = str_replace( 'wcgwp_note', __( 'Note', 'woocommerce-gift-wrapper-plus' ), $html );
            }   
            if ( FALSE !== strpos( $html, 'wcgwp_parent_name' ) ) {
                $html = str_replace( 'wcgwp_parent_name', __( 'For', 'woocommerce-gift-wrapper-plus' ), $html );
            }
            if ( FALSE !== strpos( $html, 'wcgwp_price' ) ) {

                $separator = preg_replace ( '~/~', '\/', $args['separator'] );
                $separators = explode( "><", $separator);
                $separators['0'] = $separators['0'] . '>';
                $separators['1'] = '<' . $separators['1'];
        
                $autop_open = $args['autop'] === FALSE ? '<p>' : '';
                $autop_close = $args['autop'] === FALSE ? '<\/p>' : '';

                $label_before = preg_replace ( '~/~', '\/', $args['label_before'] );
                $label_after = preg_replace ( '~/~', '\/', $args['label_after'] );

                $pattern = '/' . $separators['1'] . $label_before . 'wcgwp_price' . $label_after . $autop_open . '(\d\.\d\d?|\d?)' . $autop_close . $separators['0'] . '/mi';
                $replacement = '';
                $html = preg_replace( $pattern, apply_filters( 'wcgwp_display_item_meta', $replacement , $item, $args ), $html );
                
            }
            return $html;
        
        } // End display_item_meta()

        /**
         * Filter the item meta display key, such as on order confirmation page
         *
         * @param string $display_key   Display key
         * @param object $meta          WC_Meta_Data
         * @param object $order_item    WC_Order_Item_Product
         * @return string
         */        
        public function order_item_display_meta_key( $display_key, $meta, $order_item ) {

            switch ( $display_key ) {
                case 'wcgwp_selection':
                    $display_key = str_replace( 'wcgwp_selection', __( 'Gift wrap', 'woocommerce-gift-wrapper-plus' ), $display_key );
                    break; 
                case 'wcgwp_note':
                    $display_key = str_replace( 'wcgwp_note', __( 'Note', 'woocommerce-gift-wrapper-plus' ), $display_key );
                    break;                           
                case 'wcgwp_parent_name':                
                    $display_key = str_replace( 'wcgwp_parent_name', __( 'For', 'woocommerce-gift-wrapper-plus' ), $display_key );
                    break; 
                case 'wcgwp_price':
                    $replacement = apply_filters( 'wcgwp_simple_price_display', '' );
                    $display_key = str_replace( 'wcgwp_price', $replacement, $display_key );                    
                    break;                
            }
            return $display_key;

        } // End order_item_display_meta_key()

        /**
         * Filter the meta_data, specifically to remove price meta
         * Works on order confirmation page
         *
         * @param array $formatted_meta     Display key
         * @param object $order_item        WC_Order_Item_Product
         * @return array
         */ 
        public function order_item_get_formatted_meta_data( $formatted_meta, $order_item ) {
        
            foreach( $formatted_meta as $key => $meta ) {
            
                if ( $meta->key == 'wcgwp_price' ) {              
                    unset( $formatted_meta[$key] );
                }
                if ( $meta->key == 'wcgwp_parent_key' ) {              
                    unset( $formatted_meta[$key] );
                }
                    if ( ! is_admin() && $meta->key == 'wcgwp_parent_name' ) {              
                        unset( $formatted_meta[$key] );
                    }
            }
            return $formatted_meta;

        } // End order_item_get_formatted_meta_data()
        
        /*
        * Unlink giftwrap item in order if desired
        *
        * @param string $link Order item link, whether URL or blank
        * @param object $item Order item
        * @param object $order Order
        * @return string
        */
        public function remove_link_in_order( $link, $item, $order ) {
            
            // buh bye if we're not dealing with wrap
            if ( ! WCGWrap()->cart->check_item_for_giftwrap_cat( $item ) ) return $link;

            if ( get_option( 'giftwrap_link', 'yes' ) == 'yes' && get_option( 'giftwrap_product_link', 'yes' ) == 'yes' ) {
                return $link;
            }
            
            if ( ( ! empty( $item['wcgwp_parent_name'] ) || ! empty( $item['wcgwp_selection'] ) || ! empty( $item['wcgwp_note'] ) ) && get_option( 'giftwrap_product_link', 'yes' ) == 'yes' ) {
                return $link;
            }

            if ( get_option( 'giftwrap_link', 'yes' ) == 'yes' && ! empty( $item['wcgwp_note'] ) ) {
                return $link;
            }
            
            $link = '';
            return $link;
    
        } // End remove_link_in_order()
          
        
    }  // End class WC_Gift_Wrapper_Order

endif;