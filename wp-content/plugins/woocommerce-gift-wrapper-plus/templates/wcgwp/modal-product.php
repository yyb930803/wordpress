<?php 
/**
 * The template for displaying gift wrap modal content on product pages
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/wcgwp/modal-product.php
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
defined( 'ABSPATH' ) || exit;
?>

<div class="giftwrap_header_wrapper">
    <p class="giftwrap_header wcgwp_header_add"><a data-toggle="modal" data-target=".giftwrapper_products_modal<?php echo $label; ?>" class="btn"><?php echo apply_filters( 'wcgwp_add_wrap_prompt', esc_html__( 'Add gift wrap?', 'woocommerce-gift-wrapper-plus' ) ); ?></a></p>
    <p class="giftwrap_header wcgwp_header_remove"><a data-toggle="modal" data-target=".giftwrapper_products_modal<?php echo $label; ?>" class="btn"><?php echo apply_filters( 'wcgwp_change_wrap_prompt', esc_html__( 'Gift wrap added. Change?', 'woocommerce-gift-wrapper-plus' ) ); ?></a></p>
</div>

<div id="giftwrap_modal<?php echo $label; ?>" class="giftwrapper_products_modal giftwrapper_products_modal<?php echo $label; ?> fusion-modal modal" tabindex="-1" role="dialog">
    <div class="modal-dialog <?php echo apply_filters( 'wcgwp_modal_size', 'modal-lg'); ?> modal-dialog-centered" role="document">
        <div class="modal-content fusion-modal-content">

            <div class="modal-body">
               <?php if ( ! apply_filters( 'wcgwp_hide_details', FALSE ) ) { ?>
                    <p class="giftwrap_details">
                    <?php if ( ! empty( $giftwrap_details ) ) {
                        echo esc_html( $giftwrap_details );
                    } else {
                        esc_html_e( 'We offer the following gift wrap options:', 'woocommerce-gift-wrapper' );
                    } ?>
                    </p>
                <?php } ?>

                <ul class="giftwrap_ul">
                    <?php 
                    $count = count( $list ); 
                    $product_image  = ''; 
                    $show_link = get_option( 'giftwrap_link', 'yes' );
                    foreach ( $list as $giftwrapper_product ) {
                    
                        /* if ( ! is_object( $giftwrapper_product ) ) { 
                            $giftwrapper_product = get_post( $giftwrapper_product );
                        } */
                        $product_object = new WC_Product( $giftwrapper_product->ID );
                        $price_html     = apply_filters( 'wcgwp_price_html', $product_object->get_price_html(), $product_object );
                        $giftwrap_label = strtolower( preg_replace( '/\s*/', '', $product_object->get_title() ) );
                        $show_thumbs_class = ' no_giftwrap_thumbs';
                        $image_output = '';

                        if ( $show_thumbs == TRUE ) {
                            // here you could change thumbnail size with the 'wcgwp_change_thumbnail' filter
                            $product_image = wp_get_attachment_image( get_post_thumbnail_id( $giftwrapper_product->ID ), apply_filters( 'wcgwp_change_thumbnail', 'thumbnail' ) );
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
                        if ( $count > 1 ) { 
                            echo '<li class="giftwrap_li' . $show_thumbs_class . '"><input type="radio" name="wcgwp_single_product" id="' . $giftwrap_label . $label . '" value="' . $giftwrapper_product->ID . '"' . '>';
                            echo '<label for="' . $giftwrap_label . $label . '" class="giftwrap_desc"><span class="giftwrap_title"> ' . $giftwrapper_product->post_title . '</span> ' . $price_html . '</label>' . $image_output . '</li>';
                        } else {
                            echo '<li class="giftwrap_li' . $show_thumbs_class . '"><label for="' . $giftwrap_label . $label . '" class="giftwrap_desc singular_label"><span class="giftwrap_title"> ' . $giftwrapper_product->post_title . '</span> ' . $price_html . '</label>' . $image_output . '</li>';
                            echo '<input type="hidden" name="wcgwp_single_product" value="' . $giftwrapper_product->ID . '" id="' . $giftwrap_label . $label . '">';
                        }
                    } 
                    ?>
                </ul>

                <div class="wc_giftwrap_notes_container">
                    <label for="wcgwp_notes<?php echo $label; ?>">
                        <?php echo apply_filters( 'wcgwp_add_wrap_message', esc_html__( 'Add Gift Wrap Message:', 'woocommerce-gift-wrapper-plus' ) ); ?>
                    </label>
                    <textarea name="wcgwp_single_product_note" id="wcgwp_notes<?php echo $label; ?>" cols="30" rows="4" maxlength="<?php echo get_option( 'giftwrap_textarea_limit', '1000' ); ?>" class="wc_giftwrap_notes"></textarea>	
                </div>

            </div>

            <div class="modal-footer">
                <button type="button" class="button btn giftwrap_cancel fusion-button fusion-button-default fusion-button-default-size" type="button" data-dismiss="modal" aria-label="Close"><?php esc_html_e( 'Cancel/Remove', 'woocommerce-gift-wrapper-plus' ); ?></button>&nbsp;
                <button type="button" class="button btn alt giftwrap_submit add_wrap fusion-button fusion-button-default fusion-button-default-size" data-dismiss="modal" aria-label="Submit"><?php echo apply_filters( 'wcgwp_add_wrap_button_text', esc_html__( 'Add Gift Wrap to Order', 'woocommerce-gift-wrapper-plus' ) ); ?></button>&nbsp;
            </div>
        </div>
    </div>
</div>