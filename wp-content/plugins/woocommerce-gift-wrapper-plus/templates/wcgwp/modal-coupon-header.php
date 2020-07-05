<?php 
/**
 * (DEPRECATED) The template for displaying gift wrap modal toggle in cart/checkout areas
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/wcgwp/modal-coupon-header.php
 * 
 * HOWEVER, on occasion we will need to update template files and you
 * (the theme developer) will need to copy the new files to your theme to
 * maintain compatibility. We try to do this as little as possible, but it does
 * happen. When this occurs the version of the template file will be bumped and
 * the readme will list any important changes.
 *
 *
 * @version 2.2.1
 */
defined( 'ABSPATH' ) || exit; 
?>

<div class="giftwrap_header_wrapper">
    <p class="giftwrap_header"><a data-toggle="modal" data-target=".giftwrapper_products_modal<?php echo $label; ?>" class="btn"><?php echo apply_filters( 'wcgwp_add_wrap_prompt', esc_html__( 'Add gift wrap?', 'woocommerce-gift-wrapper-plus' ) ); ?></a></p>
</div>