<?php
/**
 * Checkout coupon form
 *
 * This template can be overridden by copying it to yourtheme/elessi-theme/checkout/form-coupon.php.
 *
 * HOWEVER, on occasion WooCommerce will need to update template files and you
 * (the theme developer) will need to copy the new files to your theme to
 * maintain compatibility. We try to do this as little as possible, but it does
 * happen. When this occurs the version of the template file will be bumped and
 * the readme will list any important changes.
 *
 * @see https://docs.elessi-theme.com/document/template-structure/
 * @package WooCommerce/Templates
 * @version 3.4.4
 */
defined('ABSPATH') || exit;

if (!wc_coupons_enabled()) { // @codingStandardsIgnoreLine.
    return;
}
?>
<div class="woocommerce-form-coupon-toggle nasa-toggle-coupon-checkout">
    <?php wc_print_notice(apply_filters('woocommerce_checkout_coupon_message', __('Hai un coupon?', 'elessi-theme') . ' <a href="#" class="showcoupon">' . __('Clicca qui per inserire il tuo codice', 'elessi-theme') . '</a>'), 'notice'); ?>
</div>

<form class="checkout_coupon woocommerce-form-coupon" method="post" style="display:none">
    <p><?php esc_html_e('Se hai un codice coupon, applicalo di seguito.', 'elessi-theme'); ?></p>

    <div class="form-row form-row-first coupon">
        <input type="text" name="coupon_code" class="input-text" placeholder="<?php esc_attr_e('Codice coupon', 'elessi-theme'); ?>" id="coupon_code" value="" />
        <button type="submit" class="button" name="apply_coupon" value="<?php esc_attr_e('Applica coupon', 'elessi-theme'); ?>"><?php esc_html_e('Applica coupon', 'elessi-theme'); ?></button>
    </div>
</form>
