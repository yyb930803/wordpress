<?php
$arrows = isset($arrows) ? $arrows : 0;
$auto_slide = isset($auto_slide) ? $auto_slide : 'true';

$_delay = 0;
$_delay_item = (isset($nasa_opt['delay_overlay']) && (int) $nasa_opt['delay_overlay']) ? (int) $nasa_opt['delay_overlay'] : 100;

$data_margin = isset($data_margin) ? (int) $data_margin : 10;
$height_auto = !isset($height_auto) ? 'true' : $height_auto;
$dots = isset($dots) ? $dots : 'false';

$cat_info = apply_filters('nasa_loop_categories_show', false);
$description_info = apply_filters('nasa_loop_short_description_show', false);
?>

<div class="row nasa-warp-slide-nav-top title-align-left nasa-slider-wrap">
    <div class="large-12 columns">
        <div class="nasa-title">
            <h2 class="nasa-title-heading">
                <?php echo $title; ?>
            </h2>

            <?php if ($arrows == 1) : ?>
                <div class="nasa-nav-carousel-wrap">
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
        </div>
        
        <?php if($desc_shortcode) : ?>
            <p class="nasa-desc">
                <?php echo $desc_shortcode; ?>
            </p>
        <?php endif; ?>
        
        <div class="nasa-deal-for-time">
            <div class="nasa-sc-pdeal-countdown">
                <?php echo nasa_time_sale($deal_time, false); ?>
            </div>
        </div>
    </div>
        
    <div class="large-12 columns nasa-slide-special-product-deal-for-time">
        <div class="group-slider">
            <div
                class="slider products-group nasa-slider owl-carousel products grid"
                data-autoplay="<?php echo esc_attr($auto_slide); ?>"
                data-loop="<?php echo $auto_slide == 'true' ? 'true' : 'false'; ?>"
                data-margin="<?php echo $data_margin; ?>"
                data-columns="<?php echo (int) $columns_number; ?>"
                data-columns-small="<?php echo (int) $columns_number_small; ?>"
                data-columns-tablet="<?php echo (int) $columns_number_tablet; ?>"
                data-height-auto="false"
                data-disable-nav="true">
            <?php
                while ($specials->have_posts()) : $specials->the_post();
                    global $product;
                    wc_get_template(
                        'content-product.php',
                        array(
                            'is_deals' => false,
                            '_delay' => $_delay,
                            'wrapper' => 'div',
                            'show_in_list' => false,
                            'cat_info' => $cat_info,
                            'description_info' => $description_info
                        )
                    );

                    $_delay += $_delay_item;
                endwhile;
            ?>
            </div>
        </div>
    </div>
</div>
