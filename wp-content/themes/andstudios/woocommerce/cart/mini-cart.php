<?php
/**
 * Mini-cart
 * @see     https://docs.woocommerce.com/document/template-structure/
 * @package WooCommerce/Templates
 * @version 3.5.0
 */
if (!defined('ABSPATH')) {
    exit;
}

do_action('woocommerce_before_mini_cart');
?>

<?php if (!WC()->cart->is_empty()) : ?>
    <div class="woocommerce-mini-cart cart_list product_list_widget <?php echo esc_attr($args['list_class']); ?>">
        
        <?php
        do_action('woocommerce_before_mini_cart_contents');

        foreach (WC()->cart->get_cart() as $cart_item_key => $cart_item) {
            $_product = apply_filters('woocommerce_cart_item_product', $cart_item['data'], $cart_item, $cart_item_key);
            $product_id = apply_filters('woocommerce_cart_item_product_id', $cart_item['product_id'], $cart_item, $cart_item_key);

            if ($_product && $_product->exists() && $cart_item['quantity'] > 0 && apply_filters('woocommerce_widget_cart_item_visible', true, $cart_item, $cart_item_key)) {
                $product_name = apply_filters('woocommerce_cart_item_name', $_product->get_name(), $cart_item, $cart_item_key);
				$product_name2 = apply_filters('woocommerce_cart_item_name', elessi_get_product_meta_value($_product->get_id(),'sottotitoloformatobottiglia'), $cart_item, $cart_item_key);
                $thumbnail = apply_filters('woocommerce_cart_item_thumbnail', $_product->get_image(), $cart_item, $cart_item_key);
                $product_price = apply_filters('woocommerce_cart_item_price', WC()->cart->get_product_price($_product), $cart_item, $cart_item_key);
                $product_permalink = apply_filters('woocommerce_cart_item_permalink', $_product->is_visible() ? $_product->get_permalink($cart_item) : '', $cart_item, $cart_item_key);
                ?>
                <div class="row mini-cart-item woocommerce-mini-cart-item collapse <?php echo esc_attr(apply_filters('woocommerce_mini_cart_item_class', 'mini_cart_item', $cart_item, $cart_item_key)); ?>" id="item-<?php echo (int) $product_id; ?>">
                    <div class="small-3 large-3 columns nasa-image-cart-item">
                        <?php echo $thumbnail; ?>
                    </div>
                    
                    <div class="small-7 large-8 columns nasa-info-cart-item">
                        <div class="mini-cart-info">
                            <?php if (empty($product_permalink)) : ?>
                                <?php echo $product_name; ?>
                            <?php else : ?>
                                <a href="<?php echo esc_url($product_permalink); ?>">
                                    <?php echo $product_name; ?>
                                </a>
                            <?php endif; ?>
                            
                            <?php echo wc_get_formatted_cart_item_data($cart_item); ?>
                            <?php echo apply_filters('woocommerce_widget_cart_item_quantity', '<div class="cart_list_product_quantity">' . sprintf('%s &times; %s', $cart_item['quantity'], $product_price) . '</div>', $cart_item, $cart_item_key); ?>
                        </div>
                    </div>
                    
                    <div class="small-2 large-1 columns text-right">
                        <?php
                        echo apply_filters('woocommerce_cart_item_remove_link', sprintf(
                                '<a href="%s" class="remove remove_from_cart_button item-in-cart" aria-label="%s" data-product_id="%s" data-cart_item_key="%s" data-product_sku="%s"><i class="pe-7s-close"></i></a>',
                                esc_url(wc_get_cart_remove_url($cart_item_key)),
                                esc_attr__('Remove this item', 'elessi-theme'),
                                esc_attr($product_id ),
                                esc_attr($cart_item_key ),
                                esc_attr($_product->get_sku())
                            ), $cart_item_key);
                        ?>
                    </div>
                </div>
            <?php
            }
        }

        do_action('woocommerce_mini_cart_contents');
        ?>
        <?php /* p class="woocommerce-mini-cart__total total"><strong><?php _e('Subtotal', 'elessi-theme'); ?>:</strong> <?php echo WC()->cart->get_cart_subtotal(); ?></p>

        <?php do_action('woocommerce_widget_shopping_cart_before_buttons'); ?>

        <p class="woocommerce-mini-cart__buttons buttons"><?php do_action('woocommerce_widget_shopping_cart_buttons'); ?></p */?>
    </div>

    <div class="minicart_total_checkout">
        <span class="total-price-label"><?php esc_html_e('Subtotale', 'elessi-theme'); ?></span>
        <span class="total-price right"><?php wc_cart_totals_subtotal_html(); ?></span>
    </div>

    <div class="btn-mini-cart inline-lists text-center">
        <div class="row collapse">
            <div class="small-6 large-6 columns">
                <a href="javascript:void(0);" class="button nasa-sidebar-return-shop btn-viewcart" title="<?php esc_attr_e('BACK TO SHOP', 'elessi-theme'); ?>"><?php esc_html_e('TORNA AL NEGOZIO', 'elessi-theme'); ?></a>
            </div>
            <div class="small-6 large-6 columns">
                <a href="<?php echo esc_url(wc_get_cart_url()); ?>" class="button btn-checkout" title="<?php esc_attr_e('CARRELLO', 'elessi-theme'); ?>"><?php esc_html_e('CARRELLO', 'elessi-theme'); ?></a>
            </div>
        </div>
    </div>

<?php
/**
 * Empty cart
 */
else :
    
    echo '<img class="logocarrello" src="https://winefully.com/wp-content/uploads/2019/11/logo_home.png"><p class="empty woocommerce-mini-cart__empty-message">' . esc_html__('Nessun prodotto nel carrello.', 'elessi-theme') . '<a href="javascript:void(0);" class="button nasa-sidebar-return-shop">' . esc_html__('TORNA AL NEGOZIO', 'elessi-theme') . '</a></p>';

endif;

do_action('woocommerce_after_mini_cart');