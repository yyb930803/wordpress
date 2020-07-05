<?php 
/**
 * The template for displaying gift wrap modal content for each line item in cart
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/wcgwp/modal-line-item.php
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

<div id="giftwrap_modal_<?php echo $cart_item['product_id']; ?>" class="giftwrapper_products_modal giftwrapper_products_modal<?php echo $label . '-' . $count; ?> fusion-modal modal" tabindex="-1" role="dialog">
    <div class="modal-dialog <?php echo apply_filters( 'wcgwp_modal_size', 'modal-lg'); ?> modal-dialog-centered" role="document">
        <div class="modal-content fusion-modal-content">

            <form class="giftwrapper_products modal_form wcgwp_form" method="post">

                <div class="modal-header wcgwp_modal_header">
                    <button class="button btn giftwrap_cancel fusion-button fusion-button-default fusion-button-default-size" type="button" data-dismiss="modal" aria-label="Close"><i class="pe-7s-close"></i></button>
                </div>

                <div class="modal-body wcgwp_modal_body">
                    <?php if ( ! apply_filters( 'wcgwp_hide_details', FALSE ) ) { ?>
                        <p class="giftwrap_details">
                        <?php if ( ! empty( $giftwrap_details ) ) {
                            echo esc_html( $giftwrap_details );
                        } else {
                            esc_html_e( 'Aggiungi l’elegante confezione regalo Winefully accompagnata dal tuo messaggio personalizzato:', 'woocommerce-gift-wrapper' );
                        } ?>
                        </p>
                    <?php }

                    $product_image = '';
                    $list_count = count( $list ) > 1 ? TRUE : FALSE;
                    $wrap_count = 0;
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
                                $product_image = wp_get_attachment_image( get_post_thumbnail_id( $giftwrapper_product->ID ), apply_filters( 'wcgwp_change_thumbnail', 'large' ) );
				$imggift2 = get_post_meta( $giftwrapper_product->ID, 'imggift2', true );
				$imggift3 = get_post_meta( $giftwrapper_product->ID, 'imggift3', true );
                                $show_link = get_option( 'giftwrap_product_link', 'yes' );
                                $image_output = '<div class="giftwrap_thumb">';
                                $image_output .= $product_image;
                                $image_output .= '<img src="' . $imggift2 . '"><img src="' . $imggift3 . '"></div>';
                                $show_thumbs_class = ' show_thumb';
                            }
                            if ( $list_count === TRUE ) { 
                                echo '<li class="giftwrap_li' . $show_thumbs_class . '"><input type="radio" name="wcgwp_line_item_product" id="' . $giftwrap_label . $label . '-' . $count . '" value="' . $giftwrapper_product->ID . '"' . $checked . '>';
                                echo '<label for="' . $giftwrap_label . $label . '-' . $count . '" class="giftwrap_desc"><span class="giftwrap_title"> ' . $giftwrapper_product->post_title . '</span> ' . $price_html . '</label>' . $image_output . '</li>';
                            } else {
                                echo '<li class="giftwrap_li' . $show_thumbs_class . '"><label for="' . $giftwrap_label . $label . '-' . $count . '" class="giftwrap_desc singular_label"><span class="giftwrap_title"> ' . $giftwrapper_product->post_title . '</span> ' . $price_html . '</label>' . $image_output . '</li>';
                                echo '<input type="hidden" name="wcgwp_line_item_product" value="' . $giftwrapper_product->ID . '" id="' . $giftwrap_label . $label . '-' . $count . '">';
                            }
                            ++$wrap_count;
                        } ?>
                    </ul>

                    <div class="wc_giftwrap_notes_container">
                        <label for="wcgwp_notes<?php echo $label . '-' . $count; ?>"><?php echo apply_filters( 'wcgwp_add_wrap_message', esc_html__( 'Messaggio personalizzato:', 'woocommerce-gift-wrapper-plus' ) ); ?></label>
                        <textarea name="wcgwp_line_item_note" id="wcgwp_notes<?php echo $label . '-' . $count; ?>" cols="30" rows="4" maxlength="<?php echo get_option( 'giftwrap_textarea_limit', '1000' ); ?>" class="wc_giftwrap_notes"></textarea>	
                    </div>

                </div>

                <div class="modal-footer wcgwp_modal_footer">
                    <?php $cart_item_variation = json_encode( $cart_item['variation'] ); ?>
                    <input type="hidden" value="<?php echo $cart_item_key; ?>" name="wcgwp_line_item_parent_key">
                    <input type="hidden" value="<?php echo $cart_item['product_id']; ?>" name="wcgwp_line_item_parent_id">
                    <input type="hidden" value="<?php echo $cart_item['quantity']; ?>" name="wcgwp_line_item_quantity">
                    <button type="submit" class="button btn alt giftwrap_submit fusion-button fusion-button-default fusion-button-default-size" name="wcgwp_line_item_submit">
                        <?php echo apply_filters( 'wcgwp_add_wrap_button_text', esc_html__( 'Aggiungi al carrello', 'woocommerce-gift-wrapper-plus' ) ); ?>
                    </button>
                </div>
            </form>
            
        </div>
    </div>
</div>