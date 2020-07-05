<?php 
/**
 * The template for displaying JavaScript necessary for slideouts
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/wcgwp/slideout-js.php
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
        var wcgwp_single_slideout = {
            init: function() {
                $( document.body ).on( 'click', '.show_giftwrap_on_product', this.show_giftwrap_div_product );
            },
            show_giftwrap_div_product: function( e ) {
                e.preventDefault();
                $( 'body' ).addClass( 'wrapper_open' );
                $( '.wcgwp_singular' )
                        .find(':radio, :checkbox').prop('checked', true);
                $( '.gift-wrapper-cancel' ).show();
                $( '.giftwrap-single .giftwrapper_products' ).slideToggle( 250 );
                $( '.wcgwp_singular .show_giftwrap_on_product' ).addClass( 'hide_giftwrap_on_product' ).removeClass( 'show_giftwrap_on_product' );
                $( '.wcgwp_singular .hide_giftwrap_on_product' ).removeAttr('href');

                return false;
            },
        };
        wcgwp_single_slideout.init();
        $( '.cancel_giftwrap' ).click(function ( e ) {
            e.preventDefault();
            $('.wcgwp_singular .hide_giftwrap_on_product').addClass( 'show_giftwrap_on_product').removeClass( 'hide_giftwrap_on_product');
            $( '.wcgwp_singular .hide_giftwrap_on_product' ).attr( 'href', '#');
            $( '#wc-giftwrap' )
                .find(':radio, :checkbox').removeAttr('checked').end()
                .find('textarea, :text, select').val('');
            $( '.gift-wrapper-cancel' ).hide();
            $( '.giftwrap-single .giftwrapper_products' ).slideToggle( 250 );
            $( 'body' ).removeClass( 'wrapper_open' );
            return false;
        });
    });
/* ]]> */
</script>