<?php
/**
 * Cart totals
 *
 * @author 	WooThemes
 * @package 	WooCommerce/Templates
 * @version     2.3.6
 */
if (!defined('ABSPATH')) {
    exit;
}

global $woocommerce;
?>
<div class="cart_totals<?php echo ($woocommerce->customer->has_calculated_shipping()) ? ' calculated_shipping' : ''; ?>">

    <?php do_action('woocommerce_before_cart_totals'); ?>

    <h5 class="heading-title">
        <?php esc_html_e('Totale Carrello', 'elessi-theme'); ?>
    </h5>

    <table>
        <tr class="cart-subtotal">
            <th><?php esc_html_e('Subtotale', 'elessi-theme'); ?></th>
            <td><?php wc_cart_totals_subtotal_html(); ?></td>
        </tr>

        <?php foreach ($woocommerce->cart->get_coupons() as $code => $coupon) : ?>
            <tr class="cart-discount coupon-<?php echo esc_attr(sanitize_title($code)); ?>">
                <th><?php wc_cart_totals_coupon_label($coupon); ?></th>
                <td><?php wc_cart_totals_coupon_html($coupon); ?></td>
            </tr>
        <?php endforeach; ?>

        <?php if ($woocommerce->cart->needs_shipping() && $woocommerce->cart->show_shipping()) : ?>
            <?php do_action('woocommerce_cart_totals_before_shipping'); ?>
            <?php wc_cart_totals_shipping_html(); ?>
            <?php do_action('woocommerce_cart_totals_after_shipping'); ?>
            
        <?php elseif ($woocommerce->cart->needs_shipping()) : ?>
            <tr class="shipping">
                <th><?php esc_html_e('Spedizione', 'elessi-theme'); ?></th>
                <td><?php woocommerce_shipping_calculator(); ?></td>
            </tr>
        <?php endif; ?>

        <?php foreach ($woocommerce->cart->get_fees() as $fee) : ?>
            <tr class="fee">
                <th><?php echo esc_html($fee->name); ?></th>
                <td><?php wc_cart_totals_fee_html($fee); ?></td>
            </tr>
        <?php endforeach; ?>

        <?php if (wc_tax_enabled() && $woocommerce->cart->tax_display_cart == 'excl') : ?>
            <?php if (get_option('woocommerce_tax_total_display') == 'itemized') : ?>
                <?php foreach ($woocommerce->cart->get_tax_totals() as $code => $tax) : ?>
                    <tr class="tax-rate tax-rate-<?php echo sanitize_title($code); ?>">
                        <th><?php echo esc_html($tax->label); ?></th>
                        <td><?php echo wp_kses_post($tax->formatted_amount); ?></td>
                    </tr>
                <?php endforeach; ?>
            <?php else : ?>
                <tr class="tax-total">
                    <th><?php echo esc_html($woocommerce->countries->tax_or_vat()); ?></th>
                    <td><?php wc_cart_totals_taxes_total_html(); ?></td>
                </tr>
            <?php endif; ?>
        <?php endif; ?>

        <?php do_action('woocommerce_cart_totals_before_order_total'); ?>

        <tr class="order-total">
            <th><?php esc_html_e('Totale', 'elessi-theme'); ?></th>
            <td><?php wc_cart_totals_order_total_html(); ?></td>
        </tr>

        <?php do_action('woocommerce_cart_totals_after_order_total'); ?>

    </table>

    <?php if ($woocommerce->cart->get_cart_tax()) : ?>
        <p class="wc-cart-shipping-notice">
            <small>
                <?php
                    $estimated_text = $woocommerce->customer->is_customer_outside_base() && !$woocommerce->customer->has_calculated_shipping() ? sprintf(' ' . esc_html__(' (taxes estimated for %s)', 'elessi-theme'), $woocommerce->countries->estimated_for_prefix() . $woocommerce->countries->countries[$woocommerce->countries->get_base_country()]) : '';

                    printf(esc_html__('Note: Shipping and taxes are estimated%s and will be updated during checkout based on your billing and shipping information.', 'elessi-theme'), $estimated_text);
                ?>
            </small>
        </p>
    <?php endif; ?>

    <div class="wc-proceed-to-checkout">
        <?php do_action('woocommerce_proceed_to_checkout'); ?>
    </div>

    <?php do_action('woocommerce_after_cart_totals'); ?>

</div>
