<?php
$_delay_item = (isset($nasa_opt['delay_overlay']) && (int) $nasa_opt['delay_overlay']) ? (int) $nasa_opt['delay_overlay'] : 100;
$_delay = 0;
?>
<div class="row">
    <div class="nasa-main-content-warp products grid">
        <div class="product-outner">
            <div class="product-inner">
                <?php
                while ($main->have_posts()) :
                    $main->the_post();
                    global $product;
                    $product_error = false;
                    $productId = $product->get_id();
                    $productType = $product->get_type();
                    $time_sale = get_post_meta($productId, '_sale_price_dates_to', true);
                    
                    if($productType == 'variation') {
                        $parentId = wp_get_post_parent_id($productId);
                        if(!$parentId) {
                            $product_error = true;
                        }
                        $productParent = !$product_error ? wc_get_product($parentId) : false;
                        $attachment_ids = $productParent ? $productParent->get_gallery_image_ids() : array();
                    } else {
                        $attachment_ids = $product->get_gallery_image_ids();
                    }
                    $count_imgs = count($attachment_ids);
                    $img_thumbs = $img_disp = array();

                    $title = $product->get_title() . ($product_error ? esc_html__(' - Has been error. You need rebuilt this product.', 'nasa-core') : '');
                    $link = $product_error ? '#' : get_the_permalink($productId);
                    
                    $image_pri = array();
                    if ($primaryImg = get_post_thumbnail_id($productId)) {
                        $image_pri['src'] = wp_get_attachment_image_src($primaryImg, apply_filters('single_product_large_thumbnail_size', 'shop_single'));
                        $image_pri['link'] = isset($image_pri['src'][0]) ? $image_pri['src'][0] : '';
                        $image_pri['thumb'] = wp_get_attachment_image_src($primaryImg, apply_filters('single_product_small_thumbnail_size', 'shop_thumbnail'));
                    }

                    if ($count_imgs) :
                        // primary image
                        foreach ($attachment_ids as $key => $img) :
                            $img_disp[$key]['src'] = wp_get_attachment_image_src(
                                $img,
                                apply_filters('catalog_product_large_thumbnail_size', 'shop_single'),
                                array('title' => $title)
                            );
                            $img_disp[$key]['link'] = isset($img_disp[$key]['src'][0]) ? $img_disp[$key]['src'][0] : '';
                        endforeach;
                        
                        // thumbnails
                        foreach ($attachment_ids as $key => $img) :
                            $img_thumbs[$key]['src'] = wp_get_attachment_image_src(
                                $img,
                                apply_filters('single_product_small_thumbnail_size', 'shop_thumbnail'), 
                                array('title' => $title)
                            );
                            $img_thumbs[$key]['link'] = isset($img_thumbs[$key]['src'][0]) ? $img_thumbs[$key]['src'][0] : '';
                        endforeach;

                        $thumbs = nasa_getThumbs($_id . '-' . $productId, $image_pri, $count_imgs, $img_thumbs);
                    endif;
                    ?>

                    <div class="nasa-sc-main-product product-item nasa-quickview-special wow fadeInUp" data-wow-duration="1s" data-wow-delay="<?php echo $_delay_item; ?>ms" data-id="<?php echo $_id . '-' . $productId; ?>">
                        <div class="nasa-main-content-title name">
                            <h3>
                                <a href="<?php echo esc_url($link); ?>" title="<?php echo esc_attr($title); ?>">
                                    <?php echo $title; ?>
                                </a>
                            </h3>
                        </div>
                        <div class="nasa-sc-p-img">
                            <div class="product-images-slider images-popups-gallery">
                                <div class="nasa-product-img-slide-<?php echo $_id . '-' . $productId; ?> owl-carousel">
                                    <?php if ($image_pri): ?>
                                        <a href="<?php echo esc_url($link); ?>" title="<?php echo esc_attr($title); ?>" class="woocommerce-additional-image product-image">
                                            <img class="nasa-pri-img nasa-pri-<?php echo $_id; ?> lazyOwl" src="<?php echo esc_attr($image_pri['link']); ?>" alt="<?php echo esc_attr($title); ?>" />
                                        </a>
                                    <?php endif; ?>
                                    <?php
                                    if ($count_imgs) :
                                        foreach ($img_disp as $key => $img):
                                            ?>
                                            <a href="<?php echo esc_url($link); ?>" title="<?php echo esc_attr($title); ?>" class="woocommerce-additional-image product-image">
                                                <img class="nasa-pri-img nasa-pri-<?php echo $_id; ?> lazyOwl" src="<?php echo esc_attr($img['link']); ?>" alt="<?php echo esc_attr($title); ?>" />
                                            </a>
                                            <?php
                                        endforeach;
                                    else :
                                        echo sprintf('<a href="%s" class="active-thumbnail"><img src="%s" /></a>', wc_placeholder_img_src(), wc_placeholder_img_src());
                                    endif;
                                    ?>
                                </div>
                                
                                <?php
                                wc_get_template('loop/sale-flash.php');
                                /*
                                 * Nasa Gift icon
                                 */
                                do_action('nasa_gift_featured');
                                ?>
                            </div>
                        </div>
                        
                        <?php
                        // Thumbnails imgs
                        echo $thumbs;
                        ?>
                        
                        <div class="nasa-sc-p-info">
                            <div class="row">
                                <div class="large-6 small-12 columns left">
                                    <div class="nasa-sc-price">
                                        <div class="nasa-sc-p-price"><?php echo $product->get_price_html(); ?></div>
                                    </div>
                                </div>
                                <div class="large-6 small-12 columns right">
                                    <?php if ($time_sale): ?>
                                    <span class="nasa-ofter-text"><?php esc_html_e('Offer End In:', 'nasa-core') ?></span>
                                    <div class="nasa-sc-pdeal-countdown">
                                        <?php echo nasa_time_sale($time_sale); ?>
                                    </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                        
                        <?php
                        /**
                         * Group buttons
                         */
                        $buttons = '';
                        $nasa_function = defined('NASA_THEME_PREFIX') && function_exists(NASA_THEME_PREFIX . '_product_group_button') ? NASA_THEME_PREFIX . '_product_group_button' : false;

                        if($nasa_function) :
                            $GLOBALS['product'] = $product;
                            $buttons = $nasa_function('popup');
                        ?>
                            <div class="info columns">
                                <div class="nasa-product-grid">
                                    <?php echo $buttons; ?>
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>
                <?php endwhile; ?>
            </div>
        </div>
    </div>
</div>