<?php 
/**
 * The template for displaying gift wrap products for each line item in the cart
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/wcgwp/giftwrap-list-line-item.php
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

$product_image      = '';
$list_count         = count( $list ) > 1 ? TRUE : FALSE;
$wrap_count         = 0;
?>

<ul class="giftwrap_ul">  
    <?php foreach ( $list as $giftwrapper_product ) {
    
        $checked = $wrap_count == 0 ? 'checked' : '';
        $product_object = new WC_Product( $giftwrapper_product->ID );
        $price_html     = $product_object->get_price_html();
        $giftwrap_label = strtolower( preg_replace( '/\s*/', '', $product_object->get_title() ) );
        $show_thumbs_class = ' no_giftwrap_thumbs';
        $image_output = '';

        if ( $show_thumbs == TRUE ) {
            // here you could change thumbnail size with the 'wcgwp_change_thumbnail' filter
            $product_image = wp_get_attachment_image( get_post_thumbnail_id( $giftwrapper_product->ID ), apply_filters( 'wcgwp_change_thumbnail', 'thumbnail' ) );
            $show_link = get_option( 'giftwrap_link', 'yes' );
            $image_output = '<div class="giftwrap_thumb">';
            if ( $show_link == 'yes' ) {
                $giftwrapper_product_URL = get_permalink( $giftwrapper_product );
                $image_output .= '<a href="' . $giftwrapper_product_URL . '">';
            }
            $image_output .= $product_image;
            if ( $show_link == 'yes' ) {
                $image_output .= '</a>';
            }
            $image_output .= '</div>';
            $show_thumbs_class = ' show_thumb';
        }
        if ( $list_count === TRUE ) { 
            echo '<li class="giftwrap_li' . esc_attr( $show_thumbs_class ) . '"><input type="radio" name="wcgwp_line_item_product-' .  $count . '" id="' . esc_attr( $label ) . esc_attr( $giftwrap_label ) . $count . '" value="' . $giftwrapper_product->ID . '"' . $checked . '>';
            echo '<label for="' . esc_attr( $label ) . esc_attr( $giftwrap_label ) . $count . '" class="giftwrap_desc"><span class="giftwrap_title"> ' . $giftwrapper_product->post_title . '</span> ' . $price_html . '</label>' . $image_output . '</li>';
        } else {
            echo '<li class="giftwrap_li' . esc_attr( $show_thumbs_class ) . '"><label for="' . $label . $giftwrap_label . '" class="giftwrap_desc singular_label"><span class="giftwrap_title"> ' . $giftwrapper_product->post_title . '</span> ' . $price_html . '</label>' . $image_output . '</li>';
            echo '<input type="hidden" name="wcgwp_line_item_product-' .  $count . '" value="' . $giftwrapper_product->ID . '" id="' . esc_attr( $label ) . esc_attr( $giftwrap_label ) . '">';
        } 
        ++$wrap_count;
    } ?>
</ul>

<div class="wc_giftwrap_notes_container line_item_wcgwp_notes_container">
    <label for="<?php echo $label ?>wcgwp_notes"><?php echo apply_filters( 'wcgwp_add_wrap_message', esc_html__( 'Add Gift Wrap Message:', 'woocommerce-gift-wrapper-plus' ) ); ?></label>
    <textarea name="wcgwp_line_item_note-<?php echo $count ?>" id="<?php echo $label ?>wcgwp_notes_<?php echo $count ?>" cols="30" rows="4" maxlength="<?php echo get_option( 'giftwrap_textarea_limit', '1000' ); ?>" class="wc_giftwrap_notes"></textarea>	
</div>