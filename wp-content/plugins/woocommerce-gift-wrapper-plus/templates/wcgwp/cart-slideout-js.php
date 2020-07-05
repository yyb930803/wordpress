<?php 
/**
 * The template for displaying JavaScript necessary for cart page slideouts
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/wcgwp/cart-slideout-js.php
 * 
 * HOWEVER, on occasion we will need to update template files and you
 * (the theme developer) will need to copy the new files to your theme to
 * maintain compatibility. We try to do this as little as possible, but it does
 * happen. When this occurs the version of the template file will be bumped and
 * the readme will list any important changes.
 *
 *
 * @version 2.2
 */
 ?>
<script>
    /* <![CDATA[ */				
    jQuery( function( $ ) {
        var wcgwp_slideout = {
            init: function() {
                $( document.body ).on( 'click', '.show_giftwrap_before_cart', this.show_giftwrap_div_before_cart );
                $( document.body ).on( 'click', '.show_giftwrap_coupon', this.show_giftwrap_div_coupon );
                $( document.body ).on( 'click', '.show_giftwrap_after_cart', this.show_giftwrap_div_after_cart );
            },
            show_giftwrap_div_before_cart: function(e) {
                e.preventDefault();
                $( '.giftwrap_before_cart .wcgwp_slideout' ).slideToggle( 250 );
                return false;
            },
            show_giftwrap_div_coupon: function(e) {
                e.preventDefault();
                $( '.giftwrap_coupon .wcgwp_slideout' ).slideToggle( 250 );
                return false;
            },
            show_giftwrap_div_after_cart: function(e) {
                e.preventDefault();
                $( '.giftwrap_after_cart .wcgwp_slideout' ).slideToggle( 250 );
                return false;
            },
        };
        wcgwp_slideout.init();
    });
 
    jQuery("[id^=cart_cancel-]").click(function(e) {
        e.preventDefault();
        var index = parseInt(jQuery(this).attr("id").replace('cart_cancel-',''), 10);
        jQuery( '#wc-giftwrap-' + index ).find( '.gift-wrapper-cancel' ).hide();
        jQuery( '.giftwrapper_products-' + index ).slideToggle( 250 );
    });
    
    function openGifts( index ) {
        var j = jQuery.noConflict();
        var parentdiv = document.getElementById( 'wc-giftwrap-'+ index );
        j( parentdiv ).find( '.gift-wrapper-cancel' ).show();
        var parentwrapper = document.getElementsByClassName( 'giftwrap_header_wrapper-' + index );  
        j( parentwrapper ).next( '.giftwrapper_products-' + index ).slideToggle( 250 );
        return false;
    };
/* ]]> */
</script>