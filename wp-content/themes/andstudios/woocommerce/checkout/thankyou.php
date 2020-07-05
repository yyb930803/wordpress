<?php
/**
 * Thankyou page
 * 
 *
 * @see 	https://docs.woocommerce.com/document/template-structure/
 * @author 	WooThemes
 * @package 	WooCommerce/Templates
 * @version     3.7.0
 */

defined('ABSPATH') or exit;

echo '<div class="woocommerce-order">';

if ($order) : ?>
<div class="row nasa-order-received">
    <div class="large-12 columns nasa-order-received-left">
        <div class="nasa-warper-order margin-bottom-20">
            <?php if ($order->has_status('failed')) : ?>
                <p class="woocommerce-thankyou-order-failed"><?php esc_html_e('Sfortunatamente il tuo ordine non può essere evaso poiché la banca / commerciante di origine ha rifiutato la tua transazione. Tenta nuovamente il pagamento.', 'elessi-theme'); ?></p>

                <p class="woocommerce-thankyou-order-failed-actions">
                    <a href="<?php echo esc_url($order->get_checkout_payment_url()); ?>" class="button pay"><?php esc_html_e('Paga', 'elessi-theme') ?></a>
                    <?php if (NASA_CORE_USER_LOGIGED) : ?>
                        <a href="<?php echo esc_url(wc_get_page_permalink('myaccount')); ?>" class="button pay"><?php esc_html_e('Account', 'elessi-theme'); ?></a>
                    <?php endif; ?>
                </p>

            <?php else : ?>

                <p class="woocommerce-thankyou-order-received"><?php echo apply_filters('woocommerce_thankyou_order_received_text', esc_html__('Grazie. Il tuo ordine è stato ricevuto.', 'elessi-theme'), $order); ?></p>
                <ul class="woocommerce-thankyou-order-details order_details">
                    <li class="order">
                        <?php esc_html_e('Ordine Numero:', 'elessi-theme'); ?>
                        <strong><?php echo (int) $order->get_order_number(); ?></strong>
                    </li>
                    <li class="date">
                        <?php esc_html_e('Data:', 'elessi-theme'); ?>
                        <strong><?php echo wc_format_datetime($order->get_date_created()); ?></strong>
                    </li>
                    <li class="total">
                        <?php esc_html_e('Totale:', 'elessi-theme'); ?>
                        <strong><?php echo ($order->get_formatted_order_total()); ?></strong>
                    </li>
                    <?php if ($order->get_payment_method_title()) : ?>
                        <li class="method">
                            <?php esc_html_e('Metodo di pagamento:', 'elessi-theme'); ?>
                            <strong><?php echo wp_kses_post($order->get_payment_method_title()); ?></strong>
                        </li>
                    <?php endif; ?>
                </ul>
                <div class="clear"></div>

            <?php endif; ?>
        </div>
    </div>
    <div class="large-12 columns nasa-order-received-right">
        <div class="nasa-warper-order">
            <?php do_action('woocommerce_thankyou_' . $order->get_payment_method(), $order->get_id()); ?>
            <?php do_action('woocommerce_thankyou', $order->get_id()); ?>
        </div>
    </div>
</div>
<?php else : ?>
    <p class="woocommerce-thankyou-order-received"><?php echo apply_filters('woocommerce_thankyou_order_received_text', esc_html__('Grazie. Il tuo ordine è stato ricevuto.', 'elessi-theme'), null); ?></p>
<?php
endif;

echo '</div>';
