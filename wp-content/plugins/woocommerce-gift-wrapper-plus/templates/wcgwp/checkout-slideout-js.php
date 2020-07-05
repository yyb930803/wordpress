<?php 
/**
 * The template for displaying JavaScript necessary for checkout page slideouts
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/wcgwp/checkout-slideout-js.php
 * 
 * HOWEVER, on occasion we will need to update template files and you
 * (the theme developer) will need to copy the new files to your theme to
 * maintain compatibility. We try to do this as little as possible, but it does
 * happen. When this occurs the version of the template file will be bumped and
 * the readme will list any important changes.
 *
 *
 * @version 2.0
 */
 ?>
<script>
    /* <![CDATA[ */
    jQuery( function( $ ) {
        var wc_checkout_giftwrap = {
            init: function() { 
                $( document.body ).on( 'click', '.show_giftwrap_checkout', this.show_div_checkout );
                $( document.body ).on( 'click', '.show_giftwrap_after_checkout', this.show_div_after_checkout );
                $( document.body ).on( 'click', '.show_giftwrap_before_submit', this.show_div_before_submit );
            },
            show_div_checkout: function( e ) {
                e.preventDefault();                
                $( '.giftwrap_checkout .wcgwp_slideout' ).slideToggle( 250 );
                return false;
            },
            show_div_after_checkout: function( e ) {
                e.preventDefault();                
                $( '.giftwrap_after_checkout .wcgwp_slideout' ).slideToggle( 250 );
                return false;
            },
            show_div_before_submit: function( e ) {
                e.preventDefault();                
                $( '.giftwrap_before_submit .wcgwp_slideout' ).slideToggle( 250 );
                return false;
            },                        
        };
        wc_checkout_giftwrap.init();
    });
/* ]]> */
</script>