<?php 
/**
 * The template for displaying a simple gift wrap checkbox (opt-in) on single product pages
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/wcgwp/giftwrap-simple.php
 * 
 * HOWEVER, on occasion we will need to update template files and you
 * (the theme developer) will need to copy the new files to your theme to
 * maintain compatibility. We try to do this as little as possible, but it does
 * happen. When this occurs the version of the template file will be bumped and
 * the readme will list any important changes.
 *
 *
 * @version 2.0
 */
defined( 'ABSPATH' ) || exit;

$label = sprintf( __( 'Add gift wrapping for %s?', 'woocommerce-gift-wrapper-plus' ), $price_html );
?>

<div class="wc-giftwrap giftwrap-simple">
    <fieldset>
        <legend class="screen-reader-text"><span><?php echo $label; ?></span></legend>
        <label for="giftwrap_simple"><input name="wcgwp_simple_checkbox" type="checkbox" value="<?php echo $price; ?>" id="giftwrap_simple"> <?php echo $label; ?></label>
        <input name="wcgwp_simple_selection" type="hidden" value="<?php echo $product->get_name(); ?>">
    </fieldset>
    <div class="clear clearfix"></div>
</div>
