<?php
defined('ABSPATH') or die(); // Exit if accessed directly

if(class_exists('WC_AJAX')) :
    class NASA_WC_AJAX extends WC_AJAX {

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
                'nasa_render_variables',
                'nasa_more_product',
                'nasa_more_products_masonry',
            );

            foreach ($ajax_events as $ajax_event) {
                add_action('wp_ajax_woocommerce_' . $ajax_event, array(__CLASS__, $ajax_event));
                add_action('wp_ajax_nopriv_woocommerce_' . $ajax_event, array(__CLASS__, $ajax_event));

                // WC AJAX can be used for frontend ajax requests.
                add_action('wc_ajax_' . $ajax_event, array(__CLASS__, $ajax_event));
            }
        }

        /**
         * Render variations
         */
        public static function nasa_render_variables() {
            if(!isset($_POST['pids']) || empty($_POST['pids'])) {
                $data = array('empty' => '1');
            } else {
                $uxObject = Nasa_WC_Attr_UX::getInstance();
                $products = $uxObject->render_variables($_POST['pids']);
                if(!empty($products)) {
                    $data = array('empty' => '0', 'products' => $products);
                }
            }
            
            wp_send_json($data);
        }
        
        /**
         * Load more products
         */
        public static function nasa_more_product() {
            $type = isset($_REQUEST['type']) ? $_REQUEST['type'] : null;
            $post_per_page = isset($_REQUEST['post_per_page']) ? $_REQUEST['post_per_page'] : 10;
            $page = isset($_REQUEST['page']) ? $_REQUEST['page'] : 1;
            $cat = (isset($_REQUEST['cat']) && $_REQUEST['cat'] != '') ? $_REQUEST['cat'] : null;
            
            $data = array('success' => '0');

            $loop = nasa_woocommerce_query($type, $post_per_page, $cat, $page);
            if ($loop->found_posts):
                global $nasa_opt;
            
                // Use in row_layout.php
                $is_deals = isset($_REQUEST['is_deals']) ?
                    $_REQUEST['is_deals'] : false;
                $columns_number = isset($_REQUEST['columns_number']) ?
                    $_REQUEST['columns_number'] : 5;
                $columns_number_tablet = isset($_REQUEST['columns_number_medium']) ?
                    $_REQUEST['columns_number_medium'] : 2;
                $columns_number_small = isset($_REQUEST['columns_number_small']) ?
                    $_REQUEST['columns_number_small'] : 1;
                $start_row = '';
                $end_row = '';
                
                ob_start();
                include NASA_CORE_PRODUCT_LAYOUTS . 'globals/row_layout.php';
                
                $data['content'] = ob_get_clean();
            endif;
            wp_reset_postdata();
            
            if(isset($data['content'])) {
                $data['success'] = '1';
            }
            
            wp_send_json($data);
        }
        
        /**
         * Load more products MASONRY
         */
        public static function nasa_more_products_masonry() {
            $type = isset($_REQUEST['type']) ? $_REQUEST['type'] : null;
            $limit = isset($_REQUEST['limit']) ? $_REQUEST['limit'] : 18;
            $page = isset($_REQUEST['page']) ? $_REQUEST['page'] : 1;
            $cat = (isset($_REQUEST['cat']) && $_REQUEST['cat'] != '') ? $_REQUEST['cat'] : null;
            $layout = (isset($_REQUEST['layout']) && $_REQUEST['layout'] != '') ? $_REQUEST['layout'] : '1';
            
            $data = array('success' => '0');
            
            $file = NASA_CORE_PRODUCT_LAYOUTS . 'nasa_products_masonry/masonry-' . $layout . '.php';
            if (is_file($file)) :
                $loop = nasa_woocommerce_query($type, $limit, $cat, $page);
                if ($loop->found_posts):
                    global $nasa_opt;
                    $custom_class = "nasa-opacity-0";
                
                    ob_start();
                    include $file;

                    $data['content'] = ob_get_clean();
                endif;
                wp_reset_postdata();

                if(isset($data['content'])) {
                    $data['success'] = '1';
                }
            endif;
            
            
            wp_send_json($data);
        }
    }

    /**
     * Init NASA WC AJAX
     */
    if(isset($_REQUEST['wc-ajax'])) {
        add_action('init', 'nasa_init_wc_ajax');
        if(!function_exists('nasa_init_wc_ajax')) :
            function nasa_init_wc_ajax() {
                NASA_WC_AJAX::nasa_init();
            }
        endif;
    }

endif;
