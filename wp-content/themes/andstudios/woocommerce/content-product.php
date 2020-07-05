<?php
/**
 *
 * The template for displaying product content within loops
 *
 * 
 * @see     https://docs.woocommerce.com/document/template-structure/
 * @author  WooThemes
 * @package WooCommerce/Templates
 * @version 3.6.0
 */
global $product, $nasa_opt, $nasa_animated_products;
if (empty($product) || !$product->is_visible()) :
    return;
endif;
$nasa_link = $product->get_permalink(); // permalink
$productId = $product->get_id();
$titolocasavinicola = (!isset($nasa_opt['enable_specifications']) || $nasa_opt['enable_specifications'] == '1') ? elessi_get_product_meta_value($product->get_id(), 'titolocasavinicola') : '';
$titoloreward = (!isset($nasa_opt['enable_specifications']) || $nasa_opt['enable_specifications'] == '1') ? elessi_get_product_meta_value($product->get_id(), 'titoloreward') : '';
$siglatitoloreward = (!isset($nasa_opt['enable_specifications']) || $nasa_opt['enable_specifications'] == '1') ? elessi_get_product_meta_value($product->get_id(), 'siglatitoloreward') : '';
$titoloreward2 = (!isset($nasa_opt['enable_specifications']) || $nasa_opt['enable_specifications'] == '1') ? elessi_get_product_meta_value($product->get_id(), 'titoloreward2') : '';
$siglatitoloreward2 = (!isset($nasa_opt['enable_specifications']) || $nasa_opt['enable_specifications'] == '1') ? elessi_get_product_meta_value($product->get_id(), 'siglatitoloreward2') : '';
$titoloreward3 = (!isset($nasa_opt['enable_specifications']) || $nasa_opt['enable_specifications'] == '1') ? elessi_get_product_meta_value($product->get_id(), 'titoloreward3') : '';
$siglatitoloreward3 = (!isset($nasa_opt['enable_specifications']) || $nasa_opt['enable_specifications'] == '1') ? elessi_get_product_meta_value($product->get_id(), 'siglatitoloreward3') : '';
$titoloreward4 = (!isset($nasa_opt['enable_specifications']) || $nasa_opt['enable_specifications'] == '1') ? elessi_get_product_meta_value($product->get_id(), 'titoloreward4') : '';
$siglatitoloreward4 = (!isset($nasa_opt['enable_specifications']) || $nasa_opt['enable_specifications'] == '1') ? elessi_get_product_meta_value($product->get_id(), 'siglatitoloreward4') : '';
$titoloreward5 = (!isset($nasa_opt['enable_specifications']) || $nasa_opt['enable_specifications'] == '1') ? elessi_get_product_meta_value($product->get_id(), 'titoloreward5') : '';
$siglatitoloreward5 = (!isset($nasa_opt['enable_specifications']) || $nasa_opt['enable_specifications'] == '1') ? elessi_get_product_meta_value($product->get_id(), 'siglatitoloreward5') : '';
$titoloreward6 = (!isset($nasa_opt['enable_specifications']) || $nasa_opt['enable_specifications'] == '1') ? elessi_get_product_meta_value($product->get_id(), 'titoloreward6') : '';
$siglatitoloreward6 = (!isset($nasa_opt['enable_specifications']) || $nasa_opt['enable_specifications'] == '1') ? elessi_get_product_meta_value($product->get_id(), 'siglatitoloreward6') : '';
$titoloreward7 = (!isset($nasa_opt['enable_specifications']) || $nasa_opt['enable_specifications'] == '1') ? elessi_get_product_meta_value($product->get_id(), 'titoloreward7') : '';
$siglatitoloreward7 = (!isset($nasa_opt['enable_specifications']) || $nasa_opt['enable_specifications'] == '1') ? elessi_get_product_meta_value($product->get_id(), 'siglatitoloreward7') : '';
$titoloetichetta = (!isset($nasa_opt['enable_specifications']) || $nasa_opt['enable_specifications'] == '1') ? elessi_get_product_meta_value($product->get_id(), 'titoloetichetta') : '';
$sottotitoloformatobottiglia = (!isset($nasa_opt['enable_specifications']) || $nasa_opt['enable_specifications'] == '1') ? elessi_get_product_meta_value($product->get_id(), 'sottotitoloformatobottiglia') : '';
$stock_status = $product->get_stock_status();
$stock_label = $stock_status == 'outofstock' ?
    esc_html__('Out of stock', 'elessi-theme') : esc_html__('In stock', 'elessi-theme');

$time_sale = false;
if($product->is_on_sale() && $product->get_type() != 'variable') {
    $time_from = get_post_meta($productId, '_sale_price_dates_from', true);
    $time_to = get_post_meta($productId, '_sale_price_dates_to', true);
    $time_sale = ((int) $time_to < NASA_TIME_NOW || (int) $time_from > NASA_TIME_NOW) ? false : (int) $time_to;
}

$show_in_list = isset($show_in_list) ? $show_in_list : true;

$class_wrap = 'product-item grid';
if (!isset($nasa_opt['nasa_in_mobile']) || !$nasa_opt['nasa_in_mobile']) {
    $class_wrap .= ' wow fadeIn';
}
$class_wrap .= $nasa_animated_products ? ' ' . $nasa_animated_products : '';
$class_wrap .= $stock_status == "outofstock" ? ' out-of-stock' : '';

if (isset($nasa_opt['loop_layout']) && $nasa_opt['loop_layout']) {
    $class_wrap .= ' nasa-layout-' . $nasa_opt['loop_layout'];
}

$class_wrap .= $time_sale ? ' product-deals' : '';

if(!isset($_delay)) {
    $_delay = 0;
}

/**
 * Show Categories info
 */
$cat_info = isset($cat_info) ? $cat_info : true;

/**
 * Show Short Description info
 */
$description_info = isset($description_info) ? $description_info : true;

$attributes = 'data-wow="fadeInUp" data-wow-duration="1s"';
echo (!isset($wrapper) || $wrapper == 'li') ? '<li class="product-warp-item">' : '';
echo '<div class="' . esc_attr($class_wrap) . '" ' . $attributes . '>';

do_action('woocommerce_before_shop_loop_item');
?>

<div class="product-img-wrap">

	<div class="popup iconamedaglia">
			<div class="popupmedaglia1">
				<div class="iconamedagliaattiva">
					<span class="popuptext popupreward" id="myPopup"><?php echo ($titoloreward); ?></span>
				</div>
				<span class="siglareward"><?php echo ($siglatitoloreward); ?></span>
			</div>
		
		<?php if($titoloreward2 !== '') { ?> 
			<div class="popupmedaglia2">
				<div class="iconamedagliaattiva">
					<span class="popuptext popupreward" id="myPopup"><?php echo ($titoloreward2); ?></span>
				</div>
					<span class="siglareward"><?php echo ($siglatitoloreward2); ?></span>
			</div>
		<?php } ?>
		
		<?php if($titoloreward3 !== '') { ?> 
			<div class="popupmedaglia3">
				<div class="iconamedagliaattiva">
					<span class="popuptext popupreward" id="myPopup"><?php echo ($titoloreward3); ?></span>
				</div>
					<span class="siglareward"><?php echo ($siglatitoloreward3); ?></span>
			</div>
		<?php } ?>
		
		<?php if($titoloreward4 !== '') { ?> 
			<div class="popupmedaglia4">
				<div class="iconamedagliaattiva">
					<span class="popuptext popupreward" id="myPopup"><?php echo ($titoloreward4); ?></span>
				</div>
					<span class="siglareward"><?php echo ($siglatitoloreward4); ?></span>
			</div>
		<?php } ?>
		
		<?php if($titoloreward5 !== '') { ?> 
			<div class="popupmedaglia4">
				<div class="iconamedagliaattiva">
					<span class="popuptext popupreward" id="myPopup"><?php echo ($titoloreward5); ?></span>
				</div>
					<span class="siglareward"><?php echo ($siglatitoloreward5); ?></span>
			</div>
		<?php } ?>
		
		<?php if($titoloreward6 !== '') { ?> 
			<div class="popupmedaglia6">
				<div class="iconamedagliaattiva">
					<span class="popuptext popupreward" id="myPopup"><?php echo ($titoloreward6); ?></span>
				</div>
					<span class="siglareward"><?php echo ($siglatitoloreward6); ?></span>
			</div>
		<?php } ?>
		
		<?php if($titoloreward7 !== '') { ?> 
			<div class="popupmedaglia4">
				<div class="iconamedagliaattiva">
					<span class="popuptext popupreward" id="myPopup"><?php echo ($titoloreward7); ?></span>
				</div>
					<span class="siglareward"><?php echo ($siglatitoloreward7); ?></span>
			</div>
		<?php } ?>
		
	</div>
	
	
	
	
	
	
	
	
	
	
	<a href="<?php echo esc_url($nasa_link); ?>"><div class="opacitaimmagine"></div></a>
	
    <?php do_action('woocommerce_before_shop_loop_item_title'); ?>
</div>

<div class="product-info-wrap info">
	<?php do_action('woocommerce_shop_loop_item_title', $cat_info); ?>
    <?php do_action('woocommerce_after_shop_loop_item_title', $description_info); ?>
</div>

<?php if ($show_in_list && (!isset($nasa_opt['nasa_in_mobile']) || !$nasa_opt['nasa_in_mobile'])) : ?>
    <!-- Clone Group btns for layout List -->
    <div class="hidden-tag nasa-list-stock-wrap">
        <p class="nasa-list-stock-status <?php echo esc_attr($stock_status); ?>">
            <?php echo esc_html__('AVAILABILITY: ', 'elessi-theme') . '<span>' . $stock_label . '</span>'; ?>
        </p>
    </div>

    <div class="group-btn-in-list-wrap hidden-tag">
        <div class="group-btn-in-list"></div>
    </div>
<?php endif; ?>

<?php
echo $time_sale ? elessi_time_sale($time_sale) : '<div class="nasa-sc-pdeal-countdown hidden-tag"></div>';

do_action('woocommerce_after_shop_loop_item');

echo '</div>';

echo (!isset($wrapper) || $wrapper == 'li') ? '</li>' : '';
