<?php
if($type_grid == ''){
    $type_grid = 'best_selling';
}

$product_grid = nasa_woocommerce_query($type_grid, $deal_grid_limit, $catids, 1, array($id));
$products_cats = function_exists('wc_get_product_category_list') ?
    wc_get_product_category_list($id) : $product->get_categories();
$_delay_item = (isset($nasa_opt['delay_overlay']) && (int) $nasa_opt['delay_overlay']) ? (int) $nasa_opt['delay_overlay'] : 100;
$GLOBALS['product'] = $product;

$class_wrap = 'wow fadeInUp product-item grid';
$class_wrap .= $nasa_animated_products ? ' ' . $nasa_animated_products : '';

$cat_info = apply_filters('nasa_loop_categories_show', false);
$description_info = apply_filters('nasa_loop_short_description_show', false);
?>
<div class="row nasa-row-deal-3">
    <div class="columns large-5 main-deal-block">
        <div class="nasa-warper products">
            <div class="nasa-sc-pdeal nasa-sc-pdeal-block nasa-quickview-special no-slide <?php echo esc_attr($class_wrap); ?>" data-wow-duration="1s" data-wow-delay="<?php echo $_delay_item; ?>ms" data-id="<?php echo $_id;?>">
                <div class="product-inner">
                    <div class="row">
                        <div class="nasa-sc-p-img large-5 columns">
                            <div class="images-popups-gallery">
                                <div class="nasa-sc-product-img product-img hover-overlay main-images-<?php echo $_id;?>">
                                    <?php if($image_pri): ?>
                                        <a href="<?php echo esc_url($link);?>" title="<?php echo esc_attr($title);?>" class="woocommerce-additional-image">
                                            <span class="nasa-product-label-stock">
                                                <?php echo esc_html__('Instock: ', 'nasa-core');?>
                                                <span class="label-stock">
                                                    <?php echo $product->is_in_stock() ? esc_html__('Available', 'nasa-core') : esc_html__('Not Available', 'nasa-core'); ?>
                                                </span>
                                            </span>
                                            <div class="main-img">
                                                <img src="<?php echo esc_attr($image_pri['src'][0]);?>" alt="<?php echo esc_attr($title);?>" />
                                            </div>                              
                                            <?php
                                            if($count_imgs) :
                                                foreach($img_disp as $key => $img): ?>
                                                    <div class="back-img back">
                                                        <img src="<?php echo esc_attr($img['src'][0]);?>" alt="<?php echo esc_attr($title);?>" />
                                                    </div>
                                                <?php 
                                                break;
                                                endforeach;
                                            endif; ?>
                                        </a>
                                    <?php endif;?>
                                    <?php
                                    /*
                                     * Nasa Gift icon
                                     */
                                    do_action('nasa_gift_featured');
                                    ?>

                                    <?php
                                    /**
                                     * Group buttons
                                     */
                                    /*
                                    $buttons = '';
                                    $nasa_function = defined('NASA_THEME_PREFIX') && function_exists(NASA_THEME_PREFIX . '_product_group_button') ? NASA_THEME_PREFIX . '_product_group_button' : false;

                                    if($nasa_function) :
                                        $buttons = $nasa_function('popup');
                                    ?>
                                        <div class="info columns nasa-info-main-block">
                                            <div class="nasa-product-grid">
                                                <?php echo $buttons; ?>
                                            </div>
                                        </div>
                                    <?php endif; */
                                    ?>
                                </div>
                            </div>
                        </div>
                        <div class="nasa-sc-p-info large-7 columns">
                            <div class="row">
                                <div class="large-12 columns text-center nasa-categories">
                                    <p><?php echo $products_cats; ?></p>
                                </div>
                                <div class="nasa-product-deal-des">
                                    <?php echo apply_filters('woocommerce_short_description', $post->post_excerpt); ?>
                                </div>
                            </div>

                            <?php if($product->time_sale): ?>
                                <div class="nasa-sc-pdeal-countdown">
                                    <?php echo nasa_time_sale($product->time_sale); ?>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>

                    <div class="row">
                        <div class="large-12 columns nasa-sc-p-title">
                            <h3>
                                <a href="<?php echo esc_url($link); ?>" title="<?php echo esc_attr($title); ?>">
                                    <?php echo $title; ?>
                                </a>
                            </h3>
                        </div>

                        <div class="large-12 columns text-left nasa-sc-p-price">
                            <span class="price"><?php echo $product->get_price_html(); ?></span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="columns large-7 products nasa-sc-product-deals-grid nasa-less-left nasa-deal-right">
        <?php // Content Products grid; ?>
        <?php if ($product_grid->have_posts()) :
            $loop = $product_grid;
            $_total = $product_grid->found_posts;
            $show_rating = ($type_grid == 'top_rate') ? true : false;
            $columns_number = 3;
            $is_deals = ($type_grid == 'deals') ? true : false;
            $type = $type_grid;
            $columns_number_small = 1;
            $columns_number_tablet = 2;
            $classDeal3 = true;
            $_delay = $_delay_item;
            $auto_slide = isset($auto_slide) ? $auto_slide : 'false';
            $arrows = isset($arrows) ? $arrows : 0;
            ?>
            <div class="nasa-relative nasa-slider-wrap nasa-slide-style-product-deal">
                <?php if($arrows == 1) : ?>
                    <div class="nasa-nav-carousel-wrap nasa-nav-carousel-wrap-deal">
                        <div class="nasa-nav-carousel-prev nasa-nav-carousel-div">
                            <a class="nasa-nav-icon-slider" href="javascript:void(0);" data-do="prev">
                                <span class="pe-7s-angle-left"></span>
                            </a>
                        </div>
                        <div class="nasa-nav-carousel-next nasa-nav-carousel-div">
                            <a class="nasa-nav-icon-slider" href="javascript:void(0);" data-do="next">
                                <span class="pe-7s-angle-right"></span>
                            </a>
                        </div>
                    </div>
                <?php endif; ?>
                
                <div class="nasa-no-cols group-slider">
                    <div
                        class="slider products-group nasa-slider owl-carousel products grid"
                        data-margin="0"
                        data-columns="3"
                        data-columns-small="<?php echo esc_attr($columns_number_small); ?>"
                        data-columns-tablet="<?php echo esc_attr($columns_number_tablet); ?>"
                        data-autoplay="<?php echo esc_attr($auto_slide); ?>"
                        data-loop="<?php echo ($auto_slide == 'true') ? 'true' : 'false'; ?>"
                        data-height-auto="false"
                        data-disable-nav="true">
                        <?php
                        while ($loop->have_posts()) :
                            $loop->the_post();

                            wc_get_template('content-product.php', array(
                                'is_deals' => $is_deals,
                                '_delay' => $_delay,
                                '_delay_item' => $_delay_item,
                                'disable_drag' => true,
                                'wrapper' => 'div',
                                'show_in_list' => false,
                                'cat_info' => $cat_info,
                                'description_info' => $description_info
                            ));
                            $_delay += $_delay_item;
                        endwhile;
                        ?>
                    </div>
                </div>
            </div>
        </div>
    <?php endif; ?>
</div>
