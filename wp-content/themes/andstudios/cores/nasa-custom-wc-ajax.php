<?php
/**
 * Since 1.6.5
 */
defined('ABSPATH') or die(); // Exit if accessed directly

if(class_exists('WC_AJAX')) :
    class ELESSI_WC_AJAX extends WC_AJAX {

        /**
         * Hook in ajax handlers.
         */
        public static function nasa_init() {
            add_action('init', array(__CLASS__, 'define_ajax'), 0);
            add_action('template_redirect', array(__CLASS__, 'do_wc_ajax'), 0);
            self::nasa_add_ajax_events();
        }

        /**
         * Hook in methods - uses WordPress ajax handlers (admin-ajax).
         */
        public static function nasa_add_ajax_events() {
            /**
             * Register ajax event
             */
            $ajax_events = array(
                'nasa_static_content',
                'nasa_quick_view',
                'nasa_quickview_gallery_variation',
                'nasa_get_gallery_variation',
                'nasa_get_deal_variation',
                'nasa_single_add_to_cart',
                'nasa_combo_products',
                'nasa_add_compare_product',
                'nasa_remove_compare_product',
                'nasa_remove_all_compare',
                'nasa_reload_fragments',
                'nasa_refresh_accessories_price',
                'nasa_add_to_cart_accessories',
                'nasa_after_add_to_cart'
            );

            foreach ($ajax_events as $ajax_event) {
                add_action('wp_ajax_woocommerce_' . $ajax_event, array(__CLASS__, $ajax_event));
                add_action('wp_ajax_nopriv_woocommerce_' . $ajax_event, array(__CLASS__, $ajax_event));

                // WC AJAX can be used for frontend ajax requests.
                add_action('wc_ajax_' . $ajax_event, array(__CLASS__, $ajax_event));
            }
        }
        
        /**
	 * Reload a refreshed cart fragment, including the mini cart HTML.
	 */
	public static function nasa_reload_fragments() {
            self::get_refreshed_fragments();
	}

        /**
         * Static content
         * 
         * @global type $nasa_opt
         */
        public static function nasa_static_content() {
            global $nasa_opt;
            
            $data = array('success' => '0', 'content' => array());
            $data['content']['#nasa-wishlist-sidebar-content'] = elessi_mini_wishlist_sidebar(true);

            ob_start();
            do_action('nasa_show_mini_compare');
            $data['content']['#nasa-compare-sidebar-content'] = ob_get_clean();

            if(defined('NASA_PLG_CACHE_ACTIVE') && NASA_PLG_CACHE_ACTIVE) {
                $data['content']['.nasa-compare-count.compare-number'] = elessi_get_count_compare();
                $data['content']['.nasa-wishlist-count.wishlist-number'] = elessi_get_count_wishlist();

                if (NASA_CORE_USER_LOGIGED) {
                    $data['content']['.nasa-menus-account'] = elessi_tiny_account(true);
                }
            }

            // Load compare product
            if(isset($_REQUEST['compare']) && $_REQUEST['compare']) {
                $data['content']['#nasa-view-compare-product'] = elessi_products_compare_content();
            }

            /**
             * Get viewed product
             */
            if(defined('NASA_COOKIE_VIEWED') && shortcode_exists('nasa_products_viewed') && (!isset($nasa_opt['disable-viewed']) || !$nasa_opt['disable-viewed'])) {
                $data['content']['#nasa-viewed-sidebar-content'] = do_shortcode('[nasa_products_viewed is_ajax="no" columns_number="1" columns_small="1" columns_number_tablet="1" default_rand="false" display_type="sidebar" animation="0"]');
            }

            /**
             * Login form ajax
             */
            if(!NASA_CORE_USER_LOGIGED && shortcode_exists('woocommerce_my_account') && (!isset($nasa_opt['login_ajax']) || $nasa_opt['login_ajax'] == 1)) {
                ob_start();
                do_action('nasa_login_register_form', true);
                $data['content']['#nasa_customer_login'] = ob_get_clean();
            }
            
            if(!empty($data['content'])) {
                $data['success'] = '1';
            }

            wp_send_json($data);
        }

        /**
         * Get a refreshed cart fragment, including the mini cart HTML.
         */
        public static function nasa_quick_view() {
            $result = array(
                'mess_unavailable' => esc_html__('Sorry, this product is unavailable.', 'elessi-theme'),
                'content' => ''
            );

            if(isset($_REQUEST["product"])) {
                $prod_id = $_REQUEST["product"];
                $GLOBALS['post'] = get_post($prod_id);
                $GLOBALS['product'] = wc_get_product($prod_id);
                $product_lightbox = $GLOBALS['product'];

                if($product_lightbox) {
                    $product_type = $product_lightbox->get_type();
                    if($product_type == 'variation') {
                        $variation_data = wc_get_product_variation_attributes($prod_id);
                        $prod_id = wp_get_post_parent_id($prod_id);
                        $GLOBALS['post'] = get_post($prod_id);
                        $GLOBALS['product'] = wc_get_product($prod_id);
                        if(!empty($variation_data)) {
                            foreach ($variation_data as $key => $value) {
                                if($value != '') {
                                    $_REQUEST[$key] = $value;
                                }
                            }
                        }
                    } 

                    if ($product_type == 'grouped') {
                        $GLOBALS['product_lightbox'] = $product_lightbox;
                    }

                    $file = ELESSI_CHILD_PATH . '/includes/nasa-single-product-lightbox.php';
                    ob_start();
                    include is_file($file) ? $file : ELESSI_THEME_PATH . '/includes/nasa-single-product-lightbox.php';

                    $result['content'] = ob_get_clean();
                }
            }

            wp_send_json($result);
        }

        /**
         * Quick view gallery variation
         */
        public static function nasa_quickview_gallery_variation() {
            $data = isset($_REQUEST['data']) ? $_REQUEST['data'] : array();

            if(!isset($data['variation_id'])) {
                die;
            }

            $productId = $data['variation_id'];

            $main_id = isset($data['main_id']) && (int) $data['main_id'] ? (int) $data['main_id'] : 0;
            $image_large = $main_id ? wp_get_attachment_image_src($main_id, 'shop_single') : null;
            $src_large = isset($image_large[0]) ? $image_large[0] : null;
            $imageMain = $src_large ? '<img src="' . esc_url($src_large) . '" />' : '';
            $hasThumb = (bool) $imageMain;

            $attachment_ids = array();
            if(isset($data['gallery'])) {
                $attachments = $data['gallery'] ? explode(',', $data['gallery']) : array();

                if($attachments) {
                    foreach ($attachments as $img_id) {
                        $img_id = (int) trim($img_id);
                        if($img_id) {
                            $attachment_ids[] = $img_id;
                        }
                    }
                }
            }

            $show_images = isset($data['show_images']) ? $data['show_images'] : apply_filters('nasa_quickview_number_imgs', 2);

            $result = array();

            /**
             * Main images
             */
            $file = ELESSI_CHILD_PATH . '/includes/nasa-single-product-lightbox-gallery.php';
            ob_start();
            include is_file($file) ? $file : ELESSI_THEME_PATH . '/includes/nasa-single-product-lightbox-gallery.php';

            $result['quickview_gallery'] = ob_get_clean();

            /**
             * Deal time
             */
            if(isset($data['is_purchasable']) && $data['is_purchasable'] && isset($data['is_in_stock']) && $data['is_in_stock']) {
                $time_from = get_post_meta($productId, '_sale_price_dates_from', true);
                $time_to = get_post_meta($productId, '_sale_price_dates_to', true);
                $time_sale = ((int) $time_to < NASA_TIME_NOW || (int) $time_from > NASA_TIME_NOW) ?
                    false : (int) $time_to;

                if ($time_sale) {
                    $result['deal_variation'] = elessi_time_sale($time_sale);
                }
            }

            wp_send_json($result);
        }
        
        /**
         * Gallery variation
         */
        public static function nasa_get_gallery_variation() {
            $data = isset($_REQUEST['data']) ? $_REQUEST['data'] : array();

            if(!isset($data['variation_id'])) {
                die;
            }

            $productId = $data['variation_id'];

            $main_id = isset($data['main_id']) && (int) $data['main_id'] ? (int) $data['main_id'] : 0;
            $gallery_id = array();
            if(isset($data['gallery'])) {
                $attachments = $data['gallery'] ? explode(',', $data['gallery']) : array();

                if($attachments) {
                    foreach ($attachments as $img_id) {
                        $img_id = (int) trim($img_id);
                        if($img_id) {
                            $gallery_id[] = $img_id;
                        }
                    }
                }
            }

            $attachment_count = count($gallery_id);

            $result = array();

            /**
             * Main images
             */
            $file = ELESSI_CHILD_PATH . '/includes/nasa-variation-main-images.php';
            ob_start();
            include is_file($file) ? $file : ELESSI_THEME_PATH . '/includes/nasa-variation-main-images.php';

            $result['main_image'] = ob_get_clean();

            /**
             * Thumb images
             */
            $file2 = ELESSI_CHILD_PATH . '/includes/nasa-variation-thumb-images.php';
            ob_start();
            include is_file($file2) ? $file2 : ELESSI_THEME_PATH . '/includes/nasa-variation-thumb-images.php';

            $result['thumb_image'] = ob_get_clean();

            /**
             * Deal time
             */
            if(isset($data['deal_variation']) && $data['deal_variation']) {
                $time_from = get_post_meta($productId, '_sale_price_dates_from', true);
                $time_to = get_post_meta($productId, '_sale_price_dates_to', true);
                $time_sale = ((int) $time_to < NASA_TIME_NOW || (int) $time_from > NASA_TIME_NOW) ?
                    false : (int) $time_to;

                if ($time_sale) {
                    $result['deal_variation'] = elessi_time_sale($time_sale);
                }
            }

            wp_send_json($result);
        }
        
        /**
         * Get Deal variation
         */
        public static function nasa_get_deal_variation() {
            $result = array('success' => '0');
            
            if(isset($_REQUEST["pid"])) {
                $productId = $_REQUEST["pid"];
                $time_from = get_post_meta($productId, '_sale_price_dates_from', true);
                $time_to = get_post_meta($productId, '_sale_price_dates_to', true);
                $time_sale = ((int) $time_to < NASA_TIME_NOW || (int) $time_from > NASA_TIME_NOW) ?
                    false : (int) $time_to;

                $result['content'] = elessi_time_sale($time_sale);
                if($result['content'] !== '') {
                    $result['success'] = '1';
                }
            }

            wp_send_json($result);
        }
        
        /**
         * validate variation
         */
        protected static function nasa_validate_variation($product, $variation_id, $variation, $quantity) {
            if (empty($variation_id) || empty($product)) {
                return false;
            }

            $missing_attributes = array();
            $variations         = array();
            $attributes         = $product->get_attributes();
            $variation_data     = wc_get_product_variation_attributes($variation_id);

            foreach ($attributes as $attribute) {
                if (!$attribute['is_variation']) {
                    continue;
                }

                $taxonomy = 'attribute_' . sanitize_title($attribute['name']);

                if (isset($variation[$taxonomy])) {
                    // Get value from post data
                    if ($attribute['is_taxonomy']) {
                        // Don't use wc_clean as it destroys sanitized characters
                        $value = sanitize_title(stripslashes($variation[$taxonomy]));
                    } else {
                        $value = wc_clean(stripslashes($variation[$taxonomy]));
                    }

                    // Get valid value from variation
                    $valid_value = isset($variation_data[$taxonomy]) ? $variation_data[$taxonomy] : '';

                    // Allow if valid or show error.
                    if ($valid_value === $value || ('' === $valid_value && in_array($value, $attribute->get_slugs()))) {
                        $variations[$taxonomy] = $value;
                    } else {
                        return false;
                    }
                } else {
                    $missing_attributes[] = wc_attribute_label($attribute['name']);
                }
            }
            if (!empty($missing_attributes)) {
                return false;
            }

            $passed_validation = apply_filters('woocommerce_add_to_cart_validation', true, $product->get_id(), $quantity, $variation_id, $variations);

            return $passed_validation;
        }

        /**
         * Single add to cart | Quick view add to cart
         */
        public static function nasa_single_add_to_cart() {
            global $woocommerce;

            if(!$woocommerce || !isset($_REQUEST['product_id']) || (int)$_REQUEST['product_id'] <= 0){
                wp_send_json(array(
                    'error' => true,
                    'message' => esc_html__('Sorry, Product is not existing.', 'elessi-theme')
                ));
                
                wp_die();
            }

            $error      = false;
            $product_id = apply_filters('woocommerce_add_to_cart_product_id', absint($_REQUEST['product_id']));
            $quantity   = empty($_REQUEST['quantity']) ? 1 : wc_stock_amount($_REQUEST['quantity']);
            $type       = (!isset($_REQUEST['product_type']) || !in_array($_REQUEST['product_type'], array('simple', 'variation', 'variable', NASA_COMBO_TYPE))) ? 'simple' : $_REQUEST['product_type'];

            $variation = isset($_REQUEST['variation']) ? $_REQUEST['variation'] : array();
            $validate_attr = true;
            if($type == 'variation') {
                $variation_id = $product_id;
                $product_id = wp_get_post_parent_id($product_id);
                $type = 'variable';
            } else {
                $variation_id = (int) $_REQUEST['variation_id'];
            }

            $passed_validation = apply_filters('woocommerce_add_to_cart_validation', true, $product_id, $quantity);
            $product_status    = get_post_status($product_id);

            $product = wc_get_product((int) $product_id);
            if(!$product) {
                $error = true;
            } else {
                $product_type = $product->get_type();
                if(((!$variation || !$variation_id) && $product_type == 'variable') || $type != $product_type){
                    $error = true;
                }

                elseif($product_type == NASA_COMBO_TYPE && function_exists('YITH_WCPB_Frontend')) {
                    YITH_WCPB_Frontend();
                }

                if(!$error && $product_type == 'variable') {
                    $validate_attr = self::nasa_validate_variation($product, $variation_id, $variation, $quantity);
                }
            }

            if (!$error && $validate_attr && $passed_validation && 'publish' === $product_status && $woocommerce->cart->add_to_cart($product_id, $quantity, $variation_id, $variation)) {

                do_action('woocommerce_ajax_added_to_cart', $product_id);
                if (get_option('woocommerce_cart_redirect_after_add') == 'yes') {
                    wc_add_to_cart_message($product_id);
                }

                // Return fragments
                ob_start();
                woocommerce_mini_cart();
                $mini_cart = ob_get_clean();

                // Fragments and mini cart are returned
                $data = array(
                    'fragments' => apply_filters(
                        'woocommerce_add_to_cart_fragments',
                        array(
                            'div.widget_shopping_cart_content' => '<div class="widget_shopping_cart_content">' . $mini_cart . '</div>'
                        )
                    ),
                    'cart_hash' => $woocommerce->cart->get_cart_hash()
                );

                // Remove wishlist
                $nasa_logined_id = get_current_user_id();
                if(class_exists('YITH_WCWL') && $nasa_logined_id) {
                    $detail = isset($_REQUEST['data_wislist']) ? $_REQUEST['data_wislist'] : array();
                    if(!empty($detail) && isset($detail['from_wishlist']) && $detail['from_wishlist'] == '1') {
                        $detail['remove_from_wishlist'] = $product_id;
                        $detail['user_id'] = $nasa_logined_id ? $nasa_logined_id : false;
                        $nasa_wishlist = new YITH_WCWL($detail);
                        $data['wishlist'] = '';
                        $data['wishlistcount'] = 0;
                        if(elessi_remove_wishlist_item($nasa_wishlist)) {
                            $data['wishlist'] = elessi_mini_wishlist_sidebar(true);
                            $data['wishlistcount'] = $nasa_wishlist->count_products();
                        }
                    }
                }

                wp_send_json($data);
            } else {
                // If there was an error adding to the cart, redirect to the product page to show any errors
                $data = array(
                    'error' => true,
                    'message' => esc_html__('Sorry, Maybe product empty in stock.', 'elessi-theme'),
                    'product_url' => apply_filters('woocommerce_cart_redirect_after_error', get_permalink($product_id), $product_id)
                );
                wp_send_json($data);
            }
        }
        
        /**
         * Add To Cart All Product + Accessories
         */
        public static function nasa_add_to_cart_accessories() {
            $error = array(
                'error' => true,
                'message' => esc_html__('Sorry, Maybe product empty in stock.', 'elessi-theme')
            );
            
            if(!isset($_REQUEST['product_ids']) || empty($_REQUEST['product_ids'])) {
                wp_send_json($error);
                
                return;
            }
            
            foreach ($_REQUEST['product_ids'] as $productId) {
                $product_id = (int) $productId;
                $product = wc_get_product($product_id);
                
                /**
                 * Check Product
                 */
                if (!$product) {
                    wp_send_json($error);
                
                    return;
                }
                
                $type = $product->get_type();
                
                /**
                 * Check type
                 */
                if (!in_array($type, array('simple', 'variation'))) {
                    wp_send_json($error);
                
                    return;
                }
                
                $quantity = 1;
                $passed_validation = apply_filters('woocommerce_add_to_cart_validation', true, $product_id, $quantity);
                $product_status    = get_post_status($product_id);
                $variation_id      = 0;
		$variation         = array();
                
                /**
                 * Check validate for variation product
                 */
                if ('variation' === $type) {
                    $variation_id = $product_id;
                    $product_id   = $product->get_parent_id();
                    $variation    = $product->get_variation_attributes();
                }
                
                /**
                 * Add To Cart
                 */
                if ($passed_validation && false !== WC()->cart->add_to_cart($product_id, $quantity, $variation_id, $variation ) && 'publish' === $product_status) {
                    do_action('woocommerce_ajax_added_to_cart', $product_id);
		} else {
                    wp_send_json($error);
                
                    return;
		}
            }
            
            self::get_refreshed_fragments();
        }
        
        /**
         * Get Total Price Accessories
         */
        public static function nasa_refresh_accessories_price() {
            $price = 0;
            if(isset($_REQUEST['total_price']) && $_REQUEST['total_price']) {
                $price = $_REQUEST['total_price'];
            }
            
            wp_send_json(array('total_price' => wc_price($price)));
        }
        
        /**
         * Combo product
         */
        public static function nasa_combo_products(){
            $output = array();

            if(!defined('YITH_WCPB')) {
                wp_send_json($output);
            }

            global $woocommerce, $nasa_opt;

            if(!$woocommerce || !isset($_REQUEST['id']) || !(int) $_REQUEST['id']){
                wp_send_json($output);
            }

            $product = wc_get_product((int) $_REQUEST['id']);
            if ($product->get_type() != NASA_COMBO_TYPE || !$combo = $product->get_bundled_items()) {
                wp_send_json($output);
            }

            $file = ELESSI_CHILD_PATH . '/includes/nasa-combo-products.php';
            $file = is_file($file) ? $file : ELESSI_THEME_PATH . '/includes/nasa-combo-products.php';
            ob_start();
            include $file;
            $output['content'] = ob_get_clean();

            wp_send_json($output);
        }
        
        /**
         * Add compare item
         */
        public static function nasa_add_compare_product() {
            $result = array(
                'result_compare' => 'error',
                'mess_compare' => esc_html__('Error !', 'elessi-theme'),
                'mini_compare' => 'no-change',
                'count_compare' => 0
            );
            if (!isset($_REQUEST['pid']) || !(int) $_REQUEST['pid']) {
                wp_send_json($result);
                wp_die();
            }

            global $nasa_opt, $yith_woocompare;
            $nasa_compare = isset($yith_woocompare->obj) ? $yith_woocompare->obj : $yith_woocompare;
            if(!$nasa_compare) {
                wp_send_json($result);
                wp_die();
            }

            $max_compare = isset($nasa_opt['max_compare']) ? (int) $nasa_opt['max_compare'] : 4;
            if(!in_array((int) $_REQUEST['pid'], $nasa_compare->products_list)) {
                if(count($nasa_compare->products_list) >= $max_compare) {
                    while (count($nasa_compare->products_list) >= $max_compare) {
                        array_shift($nasa_compare->products_list);
                    }
                }

                $nasa_compare->add_product_to_compare((int) $_REQUEST['pid']);
                $result['mess_compare'] = esc_html__('Product added to compare !', 'elessi-theme');

                ob_start();
                do_action('nasa_show_mini_compare');
                $result['mini_compare'] = ob_get_clean();

                if (isset($_REQUEST['compare_table']) && $_REQUEST['compare_table']) {
                    $result['result_table'] = elessi_products_compare_content();
                }
            } else {
                $result['mess_compare'] = esc_html__('Product already exists in Compare list !', 'elessi-theme');
            }

            $result['count_compare'] = count($nasa_compare->products_list);
            $result['result_compare'] = 'success';

            wp_send_json($result);
        }
        
        /**
         * Remove compare item
         */
        public static function nasa_remove_compare_product() {
            $result = array(
                'result_compare' => 'error',
                'mess_compare' => esc_html__('Error !', 'elessi-theme'),
                'mini_compare' => 'no-change',
                'count_compare' => 0
            );
            if (!isset($_REQUEST['pid']) || !(int) $_REQUEST['pid']) {
                wp_send_json($result);
                wp_die();
            }

            global $yith_woocompare;
            $nasa_compare = isset($yith_woocompare->obj) ? $yith_woocompare->obj : $yith_woocompare;
            if(!$nasa_compare) {
                wp_send_json($result);
                wp_die();
            }

            if(in_array((int) $_REQUEST['pid'], $nasa_compare->products_list)) {
                $nasa_compare->remove_product_from_compare((int) $_REQUEST['pid']);
                $result['mess_compare'] = esc_html__('Removed product from compare !', 'elessi-theme');

                ob_start();
                do_action('nasa_show_mini_compare');
                $result['mini_compare'] = ob_get_clean();

                if (isset($_REQUEST['compare_table']) && $_REQUEST['compare_table']) {
                    $result['result_table'] = elessi_products_compare_content();
                }
            } else {
                $result['mess_compare'] = esc_html__('Product not already exists in Compare list !', 'elessi-theme');
            }

            $result['count_compare'] = count($nasa_compare->products_list);
            $result['result_compare'] = 'success';

            wp_send_json($result);
        }
        
        /**
         * Remove all item compare
         */
        public static function nasa_remove_all_compare() {
            $result = array(
                'result_compare' => 'error',
                'mess_compare' => esc_html__('Error !', 'elessi-theme'),
                'mini_compare' => 'no-change',
                'count_compare' => 0
            );

            global $yith_woocompare;
            $nasa_compare = isset($yith_woocompare->obj) ? $yith_woocompare->obj : $yith_woocompare;
            if(!$nasa_compare) {
                wp_send_json($result);
                wp_die();
            }

            if(!empty($nasa_compare->products_list)) {
                $nasa_compare->remove_product_from_compare('all');
                $result['mess_compare'] = esc_html__('Removed all products from compare !', 'elessi-theme');
                ob_start();
                do_action('nasa_show_mini_compare');

                $result['mini_compare'] = ob_get_clean();
            } else {
                $result['mess_compare'] = esc_html__('Compare products were empty !', 'elessi-theme');
            }

            $result['count_compare'] = count($nasa_compare->products_list);
            $result['result_compare'] = 'success';
            if (isset($_REQUEST['compare_table']) && $_REQUEST['compare_table']) {
                $result['result_table'] = elessi_products_compare_content();
            }

            wp_send_json($result);
        }
        
        /**
         * After Add To Cart
         * 
         * Pop-up Cross-Sells product
         */
        public static function nasa_after_add_to_cart() {
            $result = array(
                'success' => '0',
                'content' => ''
            );
            
            $nasa_cart = WC()->cart;
            $cart_items = $nasa_cart->get_cart();
            
            /**
             * Empty items
             */
            if (empty($cart_items)) {
                wp_send_json($result);
                wp_die();
            }
            
            $file = ELESSI_CHILD_PATH . '/includes/nasa-after-add-to-cart.php';
            $file = is_file($file) ? $file : ELESSI_THEME_PATH . '/includes/nasa-after-add-to-cart.php';
            
            ob_start();
            include $file;
            
            $result['content'] = ob_get_clean();
            $result['success'] = '1';
            
            wp_send_json($result);
        }
    }

    /**
     * Init ELESSI WC AJAX
     */
    if(isset($_REQUEST['wc-ajax'])) {
        add_action('init', 'elessi_init_wc_ajax');
        if(!function_exists('elessi_init_wc_ajax')) :
            function elessi_init_wc_ajax() {
                ELESSI_WC_AJAX::nasa_init();
            }
        endif;
    }

endif;
