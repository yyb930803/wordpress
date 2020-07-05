<?php

/**
 * Additional Information tab
 *
 * @see     https://docs.woocommerce.com/document/template-structure/
 * @author  WooThemes
 * @package WooCommerce/Templates
 * @version 3.0.0
 */
if (!defined('ABSPATH')) :
    exit; // Exit if accessed directly
endif;


global $product;
do_action('woocommerce_product_additional_information', $product);
