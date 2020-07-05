<?php
$_delay = 0;
$_delay_item = (isset($nasa_opt['delay_overlay']) && (int) $nasa_opt['delay_overlay']) ? (int) $nasa_opt['delay_overlay'] : 100;
$display_type = !isset($display_type) ? 'slide' : $display_type;

if($display_type === 'slide') :
    if(isset($nasa_viewed_products->post_count) && $nasa_viewed_products->post_count > 0) :
        $auto_slide = isset($auto_slide) ? $auto_slide : 'false';
        $title = $title == '' ? esc_html__("You're recently viewed", 'nasa-core') : $title;
        ?>
        <div class="nasa-viewed-product-sc nasa-slider-wrap viewed products grid">
            <?php if ($title != '') : ?>
                <div class="row">
                    <div class="viewed-block-title large-12 columns">
                        <h3 class="nasa-shortcode-title-slider">
                            <?php echo $title; ?>
                        </h3>
                        <hr class="nasa-separator" />
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
                    </div>
                </div>
            <?php endif; ?>
            <div class="row nasa-content-shortcode">
                <?php if ($title == '') : ?>
                    <div class="large-12 columns nasa-nav-carousel-wrap">
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

                <div class="large-12 columns">
                    <div class="group-slider">
                        <div
                            class="nasa-slider owl-carousel products-group"
                            data-columns="<?php echo esc_attr($columns_number); ?>"
                            data-columns-small="<?php echo esc_attr($columns_number_small); ?>"
                            data-columns-tablet="<?php echo esc_attr($columns_number_tablet); ?>"
                            data-autoplay="<?php echo esc_attr($auto_slide); ?>"
                            data-loop="<?php echo $auto_slide == 'true' ? 'true' : 'false'; ?>"
                            data-disable-nav="true">
                            <?php
                            while ($nasa_viewed_products->have_posts()) {
                                $nasa_viewed_products->the_post();

                                wc_get_template(
                                    'content-widget-product.php', 
                                    array(
                                        'wapper' => 'div',
                                        'delay' => $_delay,
                                        'list_type' => '1',
                                        'animation' => $animation
                                    )
                                );

                                $_delay += $_delay_item;
                            }
                            ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    <?php
    endif;
elseif($display_type === 'sidebar') :
    /**
     * Sidebar viewed
     */
    ?>
    <div class="row nasa-viewed-product-sc margin-top-40">
        <div class="large-12 columns">
            <?php
                if(isset($nasa_viewed_products->post_count) && $nasa_viewed_products->post_count > 0) :
                    while ($nasa_viewed_products->have_posts()) {
                        $nasa_viewed_products->the_post();

                        wc_get_template(
                            'content-widget-product.php', 
                            array(
                                'wapper' => 'div',
                                'delay' => $_delay,
                                'list_type' => '1',
                                'animation' => $animation
                            )
                        );

                        $_delay += $_delay_item;
                    }
                else : ?>
                    <p class="empty"><i class="nasa-empty-icon pe-icon pe-7s-look"></i><?php esc_html_e('No products were viewed.', 'nasa-core'); ?><a href="javascript:void(0);" class="button nasa-sidebar-return-shop"><?php esc_html_e('RETURN TO SHOP', 'nasa-core'); ?></a></p>
                <?php
                endif;
            ?>
        </div>
    </div>
    <?php
endif;
