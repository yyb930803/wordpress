<?php
/**
 * Cart Page
 *
 * @author  WooThemes
 * @package WooCommerce/Templates
 * @version 3.7.0
 */
if (!defined('ABSPATH')) :
    exit; // Exit if accessed directly
endif;
$nasa_cart = WC()->cart;
do_action('woocommerce_before_cart');
?>

<div class="row">
    <div class="large-8 columns rtl-right desktop-padding-right-30 rtl-desktop-padding-right-10 rtl-desktop-padding-left-30">
        <form class="woocommerce-cart-form nasa-shopping-cart-form" action="<?php echo esc_url(wc_get_cart_url()); ?>" method="post">
            <div class="cart-wrapper">
                <?php do_action('woocommerce_before_cart_table'); ?>
				
                <table class="shop_table cart responsive woocommerce-cart-form__contents">
                    <thead>
                        <tr>
                            <th class="product-name" colspan="3"><?php esc_html_e('Prodotto', 'elessi-theme'); ?></th>
                            <th class="product-price hide-for-small"><?php esc_html_e('Prezzo', 'elessi-theme'); ?></th>
                            <th class="product-quantity"><?php esc_html_e('Quantità', 'elessi-theme'); ?></th>
                            <th class="product-subtotal hide-for-small"><?php esc_html_e('Totale', 'elessi-theme'); ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        do_action('woocommerce_before_cart_contents');
                        
                        $cart_items = $nasa_cart->get_cart();
                        foreach ($cart_items as $cart_item_key => $cart_item) :
                            $_product = apply_filters('woocommerce_cart_item_product', $cart_item['data'], $cart_item, $cart_item_key);
                            $product_id = apply_filters('woocommerce_cart_item_product_id', $cart_item['product_id'], $cart_item, $cart_item_key);

                            if ($_product && $_product->exists() && $cart_item['quantity'] > 0 && apply_filters('woocommerce_cart_item_visible', true, $cart_item, $cart_item_key)) :
                                $product_permalink = apply_filters('woocommerce_cart_item_permalink', $_product->is_visible() ? $_product->get_permalink($cart_item) : '', $cart_item, $cart_item_key);
                                
                                $priceProduct = apply_filters(
                                    'woocommerce_cart_item_price',
                                    $nasa_cart->get_product_price($_product),
                                    $cart_item,
                                    $cart_item_key
                                );
                                ?>
                        
                                <tr class="woocommerce-cart-form__cart-item <?php echo esc_attr(apply_filters('woocommerce_cart_item_class', 'cart_item', $cart_item, $cart_item_key)); ?>">
                                    <td class="product-remove remove-product">
                                        <?php echo apply_filters(
                                            'woocommerce_cart_item_remove_link',
                                            sprintf('<a href="%s" class="remove" title="%s" data-product_id="%s" data-product_sku="%s">%s</a>',
                                                esc_url(function_exists('wc_get_cart_remove_url') ? wc_get_cart_remove_url($cart_item_key) : $nasa_cart->get_remove_url($cart_item_key)),
                                                esc_attr__('Rimuovi item', 'elessi-theme'),
                                                esc_attr($product_id),
                                                esc_attr($_product->get_sku()),
                                                esc_html__('Rimuovi item', 'elessi-theme')
                                            ), $cart_item_key
                                        ); ?>
                                    </td>
                                    <td class="product-thumbnail">
                                        <?php
                                        $thumbnail = apply_filters('woocommerce_cart_item_thumbnail', str_replace(array('http:', 'https:'), '', $_product->get_image()), $cart_item, $cart_item_key);
                                        if (!$product_permalink) :
                                            echo $thumbnail;
                                        else :
                                            printf('<a href="%s">%s</a>', esc_url($product_permalink), $thumbnail);
                                        endif;
                                        ?>
                                    </td>

                                    <td class="product-name" data-title="<?php esc_attr_e('Prodotto', 'elessi-theme'); ?>">
                                        <?php
                                        if (!$product_permalink):
                                            echo wp_kses_post(apply_filters('woocommerce_cart_item_name', $_product->get_name(), $cart_item, $cart_item_key ) . '&nbsp;');
                                        else:
                                            echo wp_kses_post(apply_filters('woocommerce_cart_item_name', sprintf( '<a href="%s">%s</a>', esc_url($product_permalink), $_product->get_name()), $cart_item, $cart_item_key));
                                        endif;
                                        
//                                         do_action('woocommerce_after_cart_item_name', $cart_item, $cart_item_key);

                                        // Meta data
                                        echo function_exists('wc_get_formatted_cart_item_data') ? wc_get_formatted_cart_item_data($cart_item) : $nasa_cart->get_item_data($cart_item);

                                        // Backorder notification
                                        if ($_product->backorders_require_notification() && $_product->is_on_backorder($cart_item['quantity'])) :
                                            echo wp_kses_post(apply_filters('woocommerce_cart_item_backorder_notification', '<p class="backorder_notification">' . esc_html__('Available on backorder', 'elessi-theme') . '</p>', $product_id));
                                        endif;
                                        ?>
                                        <div class="mobile-price show-for-small" data-title="<?php esc_attr_e('Prezzo', 'elessi-theme'); ?>">
                                            <?php echo $priceProduct; ?>
                                        </div>
                                    </td>

                                    <td class="product-price hide-for-small" data-title="<?php esc_attr_e('Prezzo', 'elessi-theme'); ?>">
                                        <?php echo $priceProduct; ?>
                                    </td>

                                    <td class="product-quantity" data-title="<?php esc_attr_e('Quantità', 'elessi-theme'); ?>">
                                        <?php
                                        if ($_product->is_sold_individually()) :
                                            $product_quantity = sprintf('1 <input type="hidden" name="cart[%s][qty]" value="1" />', $cart_item_key);
                                        else :
                                            $product_quantity = woocommerce_quantity_input(
                                                array(
                                                    'input_name'   => "cart[{$cart_item_key}][qty]",
                                                    'input_value'  => $cart_item['quantity'],
                                                    'max_value'    => $_product->get_max_purchase_quantity(),
                                                    'min_value'    => '0',
                                                    'product_name' => $_product->get_name(),
                                                ),
                                                $_product,
                                                false
                                            );
                                        endif;
                                        echo apply_filters('woocommerce_cart_item_quantity', $product_quantity, $cart_item_key, $cart_item); // PHPCS: XSS ok.
                                        ?>
                                    </td>

                                    <td class="product-subtotal hide-for-small" data-title="<?php esc_attr_e('Totale', 'elessi-theme'); ?>">
                                        <?php
                                            echo apply_filters('woocommerce_cart_item_subtotal', $nasa_cart->get_product_subtotal($_product, $cart_item['quantity']), $cart_item, $cart_item_key); // PHPCS: XSS ok.
                                        ?>
                                    </td>
                                </tr>
                                <?php
                            endif;
                        endforeach;
                        
                        do_action('woocommerce_cart_contents'); ?>
						
						<tr class="nasa-no-border">
							<td colspan="12" style="height: 80px;">
<!-- 								<div id="wc-giftwrap-2" class="wc-giftwrap giftwrap-cart giftwrap-cart wcgwp_could_giftwrap giftwrap-line-item" style="text-align: center;">
									<div class="giftwrap_header_wrapper">
										<p class="giftwrap_header">
											<a data-toggle="modal" data-target=".giftwrapper_products_modal_line_item-2" class="btn">+ Add giftbox</a>
										</p>
									</div>
								</div> -->
								<?php
									do_action('woocommerce_after_cart_item_name', $cart_item, $cart_item_key); 
									echo function_exists('wc_get_formatted_cart_item_data') ? wc_get_formatted_cart_item_data($cart_item) : $nasa_cart->get_item_data($cart_item);

									// Backorder notification
									if ($_product->backorders_require_notification() && $_product->is_on_backorder($cart_item['quantity'])) :
									echo wp_kses_post(apply_filters('woocommerce_cart_item_backorder_notification', '<p class="backorder_notification">' . esc_html__('Available on backorder', 'elessi-theme') . '</p>', $product_id));
									endif;
								?>
							</td>
						</tr>
						
						<tr class="nasa-no-border">
                            <td colspan="6" class="actions nasa-actions">
                                <div class="row">
                                    <div class="large-7 columns mobile-margin-top-20 left rtl-right nasa-min-height">
                                        <?php if (wc_coupons_enabled()) : ?>
                                            <div class="coupon">
                                                <label class="hidden-tag" for="coupon_code">
                                                    <?php esc_html_e('Coupon:', 'elessi-theme'); ?>
                                                </label>
                                                <input type="text" name="coupon_code" class="input-text" id="coupon_code" value="" placeholder="<?php esc_attr_e('Inserisci Codice Coupon', 'elessi-theme'); ?>" />
                                                <button type="submit" class="button" name="apply_coupon" value="<?php esc_attr_e('Apply coupon', 'elessi-theme'); ?>">
                                                    <?php esc_attr_e('Applica coupon', 'elessi-theme'); ?>
                                                </button>
                                                
                                                <?php do_action('woocommerce_cart_coupon'); ?>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                    <div class="large-5 columns mobile-margin-top-20 text-right rtl-text-left rtl-left">
                                        <button type="submit" class="button" name="update_cart" value="<?php esc_attr_e('Update Cart', 'elessi-theme'); ?>">
                                            <?php esc_html_e('Aggiorna Carrello', 'elessi-theme'); ?>
                                        </button>
                                    </div>
                                </div>
                                <?php do_action('woocommerce_cart_actions'); ?>

                                <?php wp_nonce_field('woocommerce-cart', 'woocommerce-cart-nonce'); ?>
                            </td>
						</tr>

			<?php do_action('woocommerce_after_cart_contents'); ?>
                    </tbody>
                </table>

                <?php do_action('woocommerce_after_cart_table'); ?>
            </div><!-- .cart-wrapper -->
        </form>
    </div>
    
    <?php do_action('woocommerce_before_cart_collaterals'); ?>
            
    <div class="large-4 columns cart-collaterals rtl-left">
        <?php
        /**
         * Cart collaterals hook.
         *
         * @hooked woocommerce_cross_sell_display
         * @hooked woocommerce_cart_totals - 10
         */
        do_action('woocommerce_cart_collaterals');
        ?>
    </div><!-- .large-12 -->
    
</div><!-- .row -->

<?php
do_action('woocommerce_after_cart');
