<?php
/**
 * Single product short description
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/single-product/short-description.php.
 *
 * HOWEVER, on occasion WooCommerce will need to update template files and you
 * (the theme developer) will need to copy the new files to your theme to
 * maintain compatibility. We try to do this as little as possible, but it does
 * happen. When this occurs the version of the template file will be bumped and
 * the readme will list any important changes.
 *
 * @see     https://docs.woocommerce.com/document/template-structure/
 * @package WooCommerce/Templates
 * @version 3.3.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

global $post;
global $product;
$short_description = apply_filters( 'woocommerce_short_description', $post->post_excerpt );

if ( ! $short_description ) {
	return;
}
$titoloreward = (!isset($nasa_opt['enable_specifications']) || $nasa_opt['enable_specifications'] == '1') ? elessi_get_product_meta_value($product->get_id(), 'titoloreward') : '';
$titoloreward2 = (!isset($nasa_opt['enable_specifications']) || $nasa_opt['enable_specifications'] == '1') ? elessi_get_product_meta_value($product->get_id(), 'titoloreward2') : '';
$titoloreward3 = (!isset($nasa_opt['enable_specifications']) || $nasa_opt['enable_specifications'] == '1') ? elessi_get_product_meta_value($product->get_id(), 'titoloreward3') : '';
$titoloreward4 = (!isset($nasa_opt['enable_specifications']) || $nasa_opt['enable_specifications'] == '1') ? elessi_get_product_meta_value($product->get_id(), 'titoloreward4') : '';
$titoloreward5 = (!isset($nasa_opt['enable_specifications']) || $nasa_opt['enable_specifications'] == '1') ? elessi_get_product_meta_value($product->get_id(), 'titoloreward5') : '';
$titoloreward6 = (!isset($nasa_opt['enable_specifications']) || $nasa_opt['enable_specifications'] == '1') ? elessi_get_product_meta_value($product->get_id(), 'titoloreward6') : '';
$titoloreward7 = (!isset($nasa_opt['enable_specifications']) || $nasa_opt['enable_specifications'] == '1') ? elessi_get_product_meta_value($product->get_id(), 'titoloreward7') : '';
?>
<div class="woocommerce-product-details__short-description">
	<?php if($titoloreward !== '') { ?>
		<p class="titoloriconoscimenti">
			Riconoscimenti
		</p>
	<?php } ?>
	<p class="riconoscimenti">
		<?php if($titoloreward !== '') { ?> 
			<span class="riconoscimento"><?php echo ($titoloreward); ?></span>
		<?php } ?>
		<?php if($titoloreward2 !== '') { ?> 
			<span class="riconoscimento"><?php echo ($titoloreward2); ?></span>
		<?php } ?>
		<?php if($titoloreward3 !== '') { ?> 
			<span class="riconoscimento"><?php echo ($titoloreward3); ?></span>
		<?php } ?>
		<?php if($titoloreward4 !== '') { ?> 
			<span class="riconoscimento"><?php echo ($titoloreward4); ?></span>
		<?php } ?>
		<?php if($titoloreward5 !== '') { ?> 
			<span class="riconoscimento"><?php echo ($titoloreward5); ?></span>
		<?php } ?>
		<?php if($titoloreward6 !== '') { ?> 
			<span class="riconoscimento"><?php echo ($titoloreward6); ?></span>
		<?php } ?>
		<?php if($titoloreward7 !== '') { ?> 
			<span class="riconoscimento"><?php echo ($titoloreward7); ?></span>
		<?php } ?>
	</p>
	<p class="descrizioneprodotto">
		Descrizione
	</p>
	<?php echo $short_description; // WPCS: XSS ok. ?>
</div>
