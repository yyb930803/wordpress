<?php
global $nasa_animated_products;
if(!isset($nasa_animated_products)) {
    $nasa_animated_products = isset($_REQUEST['effect-product']) && in_array($_REQUEST['effect-product'], array('hover-fade', 'hover-flip', 'hover-bottom-to-top', 'no')) ? $_REQUEST['effect-product'] : (isset($nasa_opt['animated_products']) ? $nasa_opt['animated_products'] : '');
    
    if($nasa_animated_products == 'no') {
        $nasa_animated_products = '';
    }
}

$id_sc = rand(0, 999999);
$arrows = isset($arrows) ? $arrows : 0;
$auto_slide = isset($auto_slide) ? $auto_slide : 'true';
$title = !isset($title) || trim($title) == '' ? esc_html__('Deal of the Day', 'nasa-core') : $title;
?>

<div class="row">
    <div class="large-9 columns margin-top-5 rtl-right">
        <div class="nasa-main-special nasa-slider-wrap">
            <div class="nasa-main-top">
                <h3 class="nasa-sc-title">
                    <?php echo $title; ?>
                </h3>
                
                <?php if ($arrows == 1) : ?>
                    <div class="nasa-nav-slick-wrap" data-id="#nasa-slider-slick-<?php echo esc_attr($id_sc); ?>">
                        <a class="nasa-nav-icon-slick nasa-nav-prev" href="javascript:void(0);" data-do="prev">
                            <span class="nasa-icon pe-7s-angle-left"></span><span class="nasa-text hide-for-small"><?php echo esc_html__('Prev Deal', 'nasa-core'); ?></span>
                        </a>
                        <a class="nasa-nav-icon-slick nasa-nav-next" href="javascript:void(0);" data-do="next">
                            <span class="nasa-text hide-for-small"><?php echo esc_html__('Next Deal', 'nasa-core'); ?></span><span class="nasa-icon pe-7s-angle-right"></span>
                        </a>
                    </div>
                <?php endif; ?>
                
                <hr class="nasa-separator margin-bottom-30" />
            </div>

            <div class="group-slider">
                <div class="nasa-special-deal-style-multi-wrap nasa-type-2">
                    <div
                        id="nasa-slider-slick-<?php echo esc_attr($id_sc); ?>"
                        class="nasa-slick-slider-body slider products-group nasa-slider-deal-has-vertical products grid"
                        data-autoplay="<?php echo esc_attr($auto_slide); ?>"
                        data-nav_items="4"
                        data-speed="600"
                        data-delay="3000"
                        data-id="<?php echo esc_attr($id_sc); ?>">
                        <?php 
                        $vertical_product = array();
                        while ($specials->have_posts()) : $specials->the_post();
                            global $product;
                            $product_error = false;
                            $productId = $product->get_id();
                            $productType = $product->get_type();
                            $postId = $productType == 'variation' ?
                                wp_get_post_parent_id($productId) : $productId;

                            if(!$postId) {
                                $product_error = true;
                            }

                            $vertical_product[] = array('product' => $product);

                            /* Rating reviews */
                            $productRating = $productType == 'variation' ? wc_get_product($postId) : $product;
                            $average = !$product_error ? $productRating->get_average_rating() : 0;
                            $count = !$product_error ? $productRating->get_review_count() : 0;
                            $rating_html = wc_get_rating_html($average, $count);

                            $stock_available = false;
                            if($statistic) :
                                $stock_sold = ($total_sales = get_post_meta($productId, 'total_sales', true)) ? round($total_sales) : 0;
                                $stock_available = ($stock = get_post_meta($productId, '_stock', true)) ? round($stock) : 0;
                                $percentage = $stock_available > 0 ? round($stock_sold/($stock_available + $stock_sold) * 100) : 0;
                            endif;

                            $stock_status = $product->get_stock_status();
                            $stock_label = $stock_status == 'outofstock' ?
                                esc_html__('Out of stock', 'nasa-core') : esc_html__('In stock', 'nasa-core');

                            $time_sale = get_post_meta($productId, '_sale_price_dates_to', true);

                            $attachment_ids = $nasa_animated_products ? $product->get_gallery_image_ids() : false;
                            $product_link = $product_error ? '#' : get_the_permalink();
                            $product_name = $product->get_name() . ($product_error ? esc_html__(' - Has been error. You need rebuilt this product.', 'nasa-core') : '');
                            ?>
                            <div class="nasa-special-deal-item nasa-special-deal-style-multi-2">
                                <div class="wow fadeInUp product-item<?php echo $nasa_animated_products ? ' ' . esc_attr($nasa_animated_products) : ''; ?>" data-wow-duration="1s" data-wow-delay="0ms">
                                    <div class="row product-special-deals">
                                        <div class="large-5 medium-5 columns rtl-right">
                                            <div class="product-img">
                                                <a href="<?php echo esc_url($product_link); ?>" title="<?php echo esc_attr($product_name); ?>">
                                                    <div class="main-img">
                                                        <?php echo $product->get_image('shop_catalog'); ?>
                                                    </div>
                                                    <?php
                                                    if ($attachment_ids) :
                                                        $loop = 0;
                                                        foreach ($attachment_ids as $attachment_id) :
                                                            $image_link = wp_get_attachment_url($attachment_id);
                                                            if (!$image_link):
                                                                continue;
                                                            endif;
                                                            $loop++;
                                                            printf('<div class="back-img back">%s</div>', wp_get_attachment_image($attachment_id, 'shop_catalog'));
                                                            if ($loop == 1):
                                                                break;
                                                            endif;
                                                        endforeach;
                                                    endif;
                                                    ?>
                                                </a>

                                                <?php
                                                /*
                                                 * Nasa Gift icon
                                                 */
                                                do_action('nasa_gift_featured');
                                                ?>
                                            </div>
                                        </div>
                                        <div class="large-7 medium-7 columns rtl-left">
                                            <div class="product-deal-special-wrap-info mobile-margin-top-30">
                                                <?php
                                                echo '<div class="nasa-list-category">';
                                                echo wc_get_product_category_list($productId, ', ');
                                                echo '</div>';
                                                ?>

                                                <div class="product-deal-special-title">
                                                    <a href="<?php echo esc_url($product_link); ?>" title="<?php echo esc_attr($product_name); ?>">
                                                        <?php echo $product_name; ?>
                                                    </a>
                                                </div>

                                                <?php echo $rating_html ? $rating_html : ''; ?>

                                                <div class="product-deal-special-price">
                                                    <span class="price">
                                                        <?php echo $product->get_price_html(); ?>
                                                    </span>
                                                </div>

                                                <div class="nasa-list-stock-status <?php echo esc_attr($stock_status); ?>">
                                                    <?php echo esc_html__('Status: ', 'nasa-core') . '<span>' . $stock_label . '</span>'; ?>
                                                </div>

                                                <div class="product-deal-special-countdown">
                                                    <?php echo nasa_time_sale($time_sale); ?>
                                                </div>

                                                <?php if($stock_available) :?>
                                                    <div class="product-deal-special-progress">
                                                        <div class="deal-stock-label">
                                                            <span class="stock-available text-left"><?php echo esc_html__('Available:', 'nasa-core');?> <strong><?php echo esc_html($stock_available); ?></strong></span>
                                                            <span class="stock-sold text-right"><?php echo esc_html__('Already Sold:', 'nasa-core');?> <strong><?php echo esc_html($stock_sold); ?></strong></span>
                                                        </div>
                                                        <div class="deal-progress">
                                                            <span class="deal-progress-bar" style="<?php echo esc_attr('width:' . $percentage . '%'); ?>"><?php echo $percentage; ?></span>
                                                        </div>
                                                    </div>
                                                <?php endif; ?>

                                                <?php
                                                /**
                                                 * Group buttons
                                                 */
                                                $buttons = '';
                                                $nasa_function = defined('NASA_THEME_PREFIX') && function_exists(NASA_THEME_PREFIX . '_product_group_button') ? NASA_THEME_PREFIX . '_product_group_button' : false;

                                                if($nasa_function) :
                                                    $buttons = $nasa_function('popup');
                                                ?>
                                                    <div class="product-deal-special-buttons">
                                                        <div class="nasa-product-grid">
                                                            <?php echo $buttons; ?>
                                                        </div>
                                                    </div>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php endwhile; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="large-3 columns hide-for-small rtl-left">
        <div class="nasa-slider-deal-vertical-extra-switcher nasa-slider-deal-vertical-extra-<?php echo esc_attr($id_sc); ?> wow fadeInUp<?php echo $nasa_animated_products ? ' ' . esc_attr($nasa_animated_products) : ''; ?> nasa-nav-4-items" data-wow-duration="1s" data-wow-delay="0ms" data-count="<?php echo count($vertical_product); ?>">
            <?php foreach ($vertical_product as $extra) :
                $product_thumb = $extra['product'];
                ?>
                <div class="item-slick">
                    <div class="row">
                        <div class="large-4 columns nasa-slick-img rtl-right">
                            <?php echo $product_thumb->get_image('shop_catalog'); ?>
                        </div>
                        <div class="large-8 columns nasa-slick-info rtl-left">
                            <p class="nasa-product-title"><?php echo $product_thumb->get_name(); ?></p>
                            <p class="nasa-product-price price"><?php echo $product_thumb->get_price_html();  ?></p>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</div>
