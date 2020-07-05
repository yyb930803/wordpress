<script>
/**
 * @version 2.3
 */
/* <![CDATA[ */				
jQuery( function( $ ) {
$( '.wcgwp_header_add' ).show();
$( '.wcgwp_header_remove' ).hide();
    $( '#giftwrap_modal_product .giftwrap_cancel' ).click(function ( e ) {
        e.preventDefault();
        if ( $( '#giftwrap_modal_product .giftwrap_li input' ).is( ':checked' ) ) {
            $( '#giftwrap_modal_product .giftwrap_li input' ).removeAttr('checked');
            
        }
        if ( $.trim( $( '#giftwrap_modal_product textarea' ).val() ) ) {
            $( '#giftwrap_modal_product textarea' ).val('');
        }
        $( '.wcgwp_header_add' ).show();
        $( '.wcgwp_header_remove' ).hide();
        $( '.giftwrapper_products_modal_product' ).modal('hide');         
        return false;
    });
    $( '#giftwrap_modal_product .giftwrap_submit' ).click(function ( e ) {
        e.preventDefault();   
        if ( ! $('#giftwrap_modal_product .giftwrap_li input').is( ':checked' ) ) {
            $( '.wcgwp_header_remove' ).hide();
            $( '.wcgwp_header_add' ).show();
        } else {
            $( '.wcgwp_header_add' ).hide();
            $( '.wcgwp_header_remove' ).show();
        }
        return;
    });
});
/* ]]> */
</script>