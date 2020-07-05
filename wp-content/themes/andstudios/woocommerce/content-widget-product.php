<?php
/**
 *
 * @author WooThemes
 * @package WooCommerce/Templates
 * @version 3.5.5
 */
if (!defined('ABSPATH')) {
    exit;
}

global $product, $nasa_opt;

if (!is_a($product, 'WC_Product')) {
    return;
}

$productId = $product->get_id();
$link = $product->get_permalink();
$title = $product->get_name();
$show_rating = isset($show_rating) ? $show_rating : true;
$animation = !isset($animation) ? true : $animation;

$class = 'row item-product-widget clearfix';
$class .= $animation ? ' wow fadeInUp' : '';

$list_type = isset($list_type) ? $list_type : '1';

$class_img = 'large-4 medium-6 small-4 columns images';
$class_info = 'large-8 medium-6 small-8 columns product-meta';
$nasa_quickview = $nasa_compare = $nasa_add_to_cart = false;
$nasa_wishlist = true;
switch ($list_type) :
    case '2':
        $nasa_add_to_cart = true;
        $class .= ' nasa-list-type-2';
        break;
    
    case 'list_main':
        $class .= ' nasa-list-type-main';
        $class_img = 'large-12 columns images';
        $class_info = 'large-12 columns images';
        break;
    
    case 'list_extra' :
        $class .= ' nasa-list-type-extra';
        break;
    
    case '1':
    default:
        $nasa_quickview = true;
        $class .= ' nasa-list-type-1';
        $class_img = 'large-3 medium-3 small-4 columns images';
        $class_info = 'large-9 medium-9 small-8 columns product-meta';
        $list_type = '1';
        break;
endswitch;

if (isset($nasa_opt['disable-quickview']) && $nasa_opt['disable-quickview']) {
    $nasa_quickview = false;
}

if(!isset($delay)){
    global $delay;
    $_delay_item = (isset($nasa_opt['delay_overlay']) && (int) $nasa_opt['delay_overlay']) ? (int) $nasa_opt['delay_overlay'] : 100;
    $delay = !$delay ? 0 : $delay;
    $delay += $_delay_item;
}

$class_warp = isset($class_column) ? ' ' . $class_column : '';
$wapper = (isset($wapper) && $wapper == 'div') ? 'div' : 'li';
$start_wapper = ($wapper == 'div') ? '<div class="li_wapper' . $class_warp . '">' : '<li class="li_wapper' . $class_warp . '">';
$end_warp = '</' . $wapper . '>';

echo $start_wapper;
do_action('woocommerce_widget_product_item_start', $args);
?>
<div class="<?php echo esc_attr($class); ?>" data-wow-duration="1s" data-wow-delay="<?php echo (int) $delay; ?>ms">
    <div class="<?php echo esc_attr($class_img); ?>">
        <div class="nasa-product-widget-image-wrap">
            <a href="<?php echo esc_url($link); ?>" title="<?php echo esc_attr($title); ?>">
                <?php echo $product->get_image('thumbnail'); ?>
                <div class="overlay"></div>
            </a>

            <?php if($list_type == '1') : ?>
                <div class="product-interactions">
                    <?php if ($nasa_quickview): ?>
                        <div class="nasa-space"></div>
                        <a href="javascript:void(0);" class="quick-view btn-link quick-view-icon" data-prod="<?php echo esc_attr($productId); ?>" title="<?php esc_attr_e('Quick View', 'elessi-theme'); ?>">
                            <i class="icon-nasa-search"></i>
                            <span class="nasa-icon-text"><?php echo esc_html__('Quick view', 'elessi-theme');?></span>
                        </a>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
    
    <div class="<?php echo esc_attr($class_info); ?>">
        <div class="product-title separator">
            <a href="<?php echo esc_url($link); ?>" title="<?php echo esc_attr($title); ?>">
                <?php echo ($title); ?>
            </a>
        </div>
        
        <?php echo $show_rating ? wp_kses_post(wc_get_rating_html($product->get_average_rating())) : ''; ?>
        
        <div class="price">
            <?php echo $product->get_price_html(); ?>
        </div>
        
        <?php if($list_type != '1') : ?>
            <?php /* Group btns */?>
            <?php echo $nasa_add_to_cart ? elessi_add_to_cart_btn() : ''; ?>
            <?php /* End Group btns */?>
            
            <div class="product-interactions">
                <?php if ($nasa_quickview): ?>
                    <div class="nasa-space"></div>
                    <a href="javascript:void(0);" class="quick-view btn-link quick-view-icon" data-prod="<?php echo esc_attr($productId); ?>" title="<?php esc_attr_e('Quick View', 'elessi-theme'); ?>">
                        <i class="pe-icon pe-7s-look"></i>
                        <span class="nasa-icon-text"><?php echo esc_html__('Quick view', 'elessi-theme');?></span>
                    </a>
                <?php endif; ?>

                <?php if(NASA_WISHLIST_ENABLE && $nasa_wishlist) : ?>
                    <div class="<?php echo ($list_type == 'list_extra') ? 'btn-wishlist btn-link wishlist-icon btn-wishlist-main-list' : 'btn-wishlist'; ?>" data-prod="<?php echo esc_attr($productId); ?>">
                        <i class="nasa-icon icon-nasa-like"></i>
                        <span class="nasa-icon-text not-added"><?php echo esc_html__('Wishlist', 'elessi-theme');?></span>
                        <span class="nasa-icon-text hidden-tag has-added"><?php echo esc_html__('Added', 'elessi-theme');?></span>
                    </div>
                    <div class="hidden-tag add-to-link">
                        <?php echo do_shortcode('[yith_wcwl_add_to_wishlist]'); ?>
                    </div>
                <?php endif; ?>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php
do_action('woocommerce_widget_product_item_end', $args);
echo $end_warp;
