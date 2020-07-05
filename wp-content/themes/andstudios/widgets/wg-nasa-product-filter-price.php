<?php

if (class_exists('WooCommerce')) {

    add_action('widgets_init', 'elessi_product_filter_price_widget');
    function elessi_product_filter_price_widget() {
        register_widget('Elessi_WC_Widget_Price_Filter');
    }

    /**
     * Price Filter Widget and related functions
     *
     * Generates a range slider to filter products by price.
     *
     * @author   NasaThemes
     * @category Widgets
     * @version  1.0.0
     * @extends  WC_Widget
     */
    class Elessi_WC_Widget_Price_Filter extends WC_Widget {

        /**
         * Constructor
         */
        public function __construct() {
            $this->widget_cssclass = 'woocommerce widget_price_filter';
            $this->widget_description = esc_html__('Shows a price filter slider in a widget which lets you narrow down the list of shown products when viewing product categories.', 'elessi-theme');
            $this->widget_id = 'nasa_woocommerce_price_filter';
            $this->widget_name = esc_html__('Nasa Product Price Filter', 'elessi-theme');
            $this->settings = array(
                'title' => array(
                    'type' => 'text',
                    'std' => esc_html__('Filter by price', 'elessi-theme'),
                    'label' => esc_html__('Title', 'elessi-theme')
                ),
                'btn_filter' => array(
                    'type' => 'checkbox',
                    'std' => 0,
                    'label' => esc_html__('Enable Button filter', 'elessi-theme')
                ),
            );
            
            $pluginURL = WC()->plugin_url();
            $suffix = defined('SCRIPT_DEBUG') && SCRIPT_DEBUG ? '' : '.min';
            wp_register_script('accounting', $pluginURL . '/assets/js/accounting/accounting' . $suffix . '.js', array('jquery'), '0.4.2');
            wp_register_script('wc-jquery-ui-touchpunch', $pluginURL . '/assets/js/jquery-ui-touch-punch/jquery-ui-touch-punch' . $suffix . '.js', array('jquery-ui-slider'), WC_VERSION, true);
            wp_register_script('wc-price-slider', $pluginURL . '/assets/js/frontend/price-slider' . $suffix . '.js', array('jquery-ui-slider', 'wc-jquery-ui-touchpunch', 'accounting'), WC_VERSION, true);
            wp_localize_script('wc-price-slider', 'woocommerce_price_slider_params', array(
                'min_price' => isset($_REQUEST['min_price']) ? esc_attr($_REQUEST['min_price']) : '',
                'max_price' => isset($_REQUEST['max_price']) ? esc_attr($_REQUEST['max_price']) : '',
                'currency_format_num_decimals' => 0,
                'currency_format_symbol' => get_woocommerce_currency_symbol(),
                'currency_format_decimal_sep' => esc_attr(wc_get_price_decimal_separator()),
                'currency_format_thousand_sep' => esc_attr(wc_get_price_thousand_separator()),
                'currency_format' => esc_attr(str_replace(array('%1$s', '%2$s'), array('%s', '%v'), get_woocommerce_price_format())),
            ));
            
            if (is_customize_preview()) {
                wp_enqueue_script('wc-price-slider');
            }

            parent::__construct();
        }

        /**
         * Output the html at the start of a widget.
         *
         * @param  array $args
         * @return string
         */
        public function current_widget_start($args) {

            echo ($args['before_widget']);
        }

        /**
         * Output the html at the end of a widget.
         *
         * @param  array $args
         * @return string
         */
        public function current_widget_end($args) {
            parent::widget_end($args);
        }

        /**
         * widget function.
         *
         * @see WP_Widget
         *
         * @param array $args
         * @param array $instance
         */
        public function widget($args, $instance) {
            wp_enqueue_script('wc-price-slider');
            $this->current_widget_start($args);
            echo elessi_get_content_widget_price($args, $instance);
            $this->current_widget_end($args);
        }
    }
}
