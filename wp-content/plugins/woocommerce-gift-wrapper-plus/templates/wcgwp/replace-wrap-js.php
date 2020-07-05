<?php 
/**
 * The template for displaying JavaScript alert when single wrap choice will be replaced
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/wcgwp/replace-wrap-js.php
 * 
 * HOWEVER, on occasion we will need to update template files and you
 * (the theme developer) will need to copy the new files to your theme to
 * maintain compatibility. We try to do this as little as possible, but it does
 * happen. When this occurs the version of the template file will be bumped and
 * the readme will list any important changes.
 *
 *
 * @version 2.3
 */
 ?>
<script>
/* <![CDATA[ */


    jQuery( '.giftwrap_before_cart .replace_wrap, .giftwrap_coupon .replace_wrap, .giftwrap_after_cart .replace_wrap, .giftwrap_checkout .replace_wrap, .giftwrap_after_checkout .replace_wrap' ).click( function() {
        var $object = jQuery( '.wcgwp-cart-item' );
        if ( $object.length ) {
            if ( window.confirm( "<?php esc_html_e( 'Are you sure you want to replace the gift wrap in your cart?', 'woocommerce-gift-wrapper-plus' ); ?>" ) ) {
                return true;
            }
        }
        return false;
    });
/* ]]> */
</script>