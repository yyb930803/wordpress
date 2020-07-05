<?php 
/**
 * The template for displaying gift wrap products on single product pages
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/wcgwp/giftwrap-list-product.php
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
?>

<ul class="giftwrap_ul">
    <?php foreach ( $list as $giftwrapper_product ) {
    
        $product_object     = new WC_Product( $giftwrapper_product->ID );
    
        $price_html         = $product_object->get_price_html();
        $giftwrap_label     = strtolower( preg_replace( '/\s*/', '', $product_object->get_title() ) );
         
        $show_thumbs_class  = ' no_giftwrap_thumbs';
        $image_output       = '';
    
        if ( $show_thumbs == TRUE ) {
            // here you could change thumbnail size with the 'wcgwp_change_thumbnail' filter
            $product_image = wp_get_attachment_image( get_post_thumbnail_id( $giftwrapper_product->ID ), apply_filters( 'wcgwp_change_thumbnail', 'thumbnail' ) );
            $show_link = get_option( 'giftwrap_product_link', 'yes' );
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
            echo '<li class="giftwrap_li' . esc_attr( $show_thumbs_class ) . '"><span><input type="radio" name="wcgwp_single_product" id="' . $giftwrap_label . $label . '" value="' . $giftwrapper_product->ID . '" class="wcgwp_radio">';
            echo '<label for="' . $giftwrap_label . $label . '" class="giftwrap_desc"><span class="giftwrap_title"> ' . $giftwrapper_product->post_title . '</span> ' . $price_html . '</label></span>' . $image_output . '</li>';
        } else {   
            echo '<li class="giftwrap_li' . esc_attr( $show_thumbs_class ) . ' wcgwp_single">';
            echo '<label for="'. esc_attr( $giftwrap_label ) . esc_attr( $label ) . '" class="giftwrap_desc singular_label">';
            echo '<span class="giftwrap_title"> ' . $giftwrapper_product->post_title . '</span> ' . $price_html . '</label>' . $image_output;
            echo '<input type="radio" name="wcgwp_single_product" value="' . $giftwrapper_product->ID . '" id="' . esc_attr( $giftwrap_label ) . esc_attr( $label ) . '"></li>';
        }
    } ?>
</ul>

<div class="wc_giftwrap_notes_container">
    <label for="giftwrapper_notes<?php echo $label ?>"><?php echo apply_filters( 'wcgwp_add_wrap_message', esc_html__( 'Add Gift Wrap Message:', 'woocommerce-gift-wrapper-plus' ) ); ?></label>
    <textarea name="wcgwp_single_product_note" id="giftwrapper_notes_<?php echo $label ?>" cols="50" rows="4" maxlength="<?php echo get_option( 'giftwrap_textarea_limit', '1000' ); ?>" class="wc_giftwrap_notes"></textarea>	
</div>