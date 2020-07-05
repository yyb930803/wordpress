<?php
$_delay = 0;
$_delay_item = (isset($nasa_opt['delay_overlay']) && (int) $nasa_opt['delay_overlay']) ? (int) $nasa_opt['delay_overlay'] : 100;

$auto_slide = isset($auto_slide) ? $auto_slide : 'false';
$arrows = isset($arrows) ? $arrows : 0;
$shop_url = isset($shop_url) ? $shop_url : false;
$term = (int) $cat ? get_term_by('id', (int) $cat, 'product_cat') : null;
$link_shortcode = null;
$parent_term = null;
$parent_term_link = '#';
if($shop_url == 1) {
    if($term) {
        $parent_term = $term->parent ? get_term_by("id", $term->parent, "product_cat") : $parent_term;
        $parent_term_link = $parent_term ? get_term_link($parent_term, 'product_cat') : $parent_term_link;
        $link_shortcode = get_term_link($term, 'product_cat');
    } else {
        $permalinks = get_option('woocommerce_permalinks');
        $shop_page_id = wc_get_page_id('shop');
        $shop_page = get_post($shop_page_id);

        $shop_page_url = get_permalink($shop_page_id);
        $shop_page_title = get_the_title($shop_page_id);
        // If permalinks contain the shop page in the URI prepend the breadcrumb with shop
        if ($shop_page_id > 0 && strstr($permalinks['product_base'], '/' . $shop_page->post_name) && get_option('page_on_front') !== $shop_page_id) {
            $link_shortcode = get_permalink($shop_page);
        }
    }
}

?>
<div class="nasa-wrap-slick-slide-products nasa-slider-wrap nasa-nav-slick-wrap">
    <?php if($arrows == 1 || (isset($title_shortcode) && $title_shortcode != '')) : ?>
        <div class="row nasa-warp-slide-nav-top text-center">
            <div class="large-12 columns">
                <div class="nasa-title nasa_type_2">
                    <h3 class="nasa-heading-title">
                        <span class="nasa-title-wrap">
                            <?php if($arrows == 1) : ?>
                                <a class="nasa-nav-icon-slick nasa-nav-prev" href="javascript:void(0);" data-do="prev">
                                    <span class="icon-nasa-left-arrow"></span>
                                </a>
                            <?php endif; ?>

                            <span><?php echo (isset($title_shortcode) && $title_shortcode != '') ? esc_attr($title_shortcode) : '&nbsp;'; ?></span>

                            <?php if($arrows == 1) : ?>
                                <a class="nasa-nav-icon-slick nasa-nav-next" href="javascript:void(0);" data-do="next">
                                    <span class="icon-nasa-right-arrow"></span>
                                </a>
                            <?php endif; ?>
                        </span>
                    </h3>
                </div>
            </div>
        </div>
    <?php endif; ?>
    
    <div class="nasa-row">
        <div class="nasa-col large-12 columns">
            <div class="group-slider">
                <div
                    class="nasa-slick-slider-body nasa-slick-slider-title-wrap products grid"
                    data-items="<?php echo esc_attr($columns_number); ?>"
                    data-scroll="1"
                    data-itemSmall="1"
                    data-itemTablet="1"
                    data-center_mode="true"
                    data-center_padding="<?php echo $columns_number == 1 ? '25' : '0'; ?>%"
                    data-autoplay="<?php echo esc_attr($auto_slide); ?>">
                    <?php
                    $k = 0;
                    while ($loop->have_posts()) :
                        $loop->the_post();
                        global $product;
                        $nasa_title = $product->get_name();
                        $attach_id = nasa_get_product_meta_value($product->get_id(), '_product_image_simple_slide');
                        $image = false;
                        if((int) $attach_id) :
                            $image_src = wp_get_attachment_url((int) $attach_id);
                            $image = $image_src ? 
                                '<img src="' . esc_url($image_src) . '" alt="' . esc_attr($nasa_title) . '" />' : false;
                        endif;
                    ?>

                        <div class="nasa-product-slick-item-wrap nasa-product-slick-item-<?php echo esc_attr($k); ?>">
                            <div class="row">
                                <div class="large-12 columns">
                                    <div class="image-wrap">
                                        <?php echo !$image ? $product->get_image('large') : $image; ?>
                                    </div>

                                    <div class="title-wrap text-center">
                                        <a title="<?php echo esc_attr($nasa_title); ?>" href="<?php echo esc_url($product->get_permalink()); ?>">
                                            <h5><?php echo $nasa_title; ?></h5>
                                        </a>
                                        <span class="price">
                                            <?php echo $product->get_price_html(); ?>
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <?php
                        $_delay += $_delay_item;
                        $k++;
                    endwhile;
                    ?>
                </div>
            </div>
        </div>
        
        <?php if($link_shortcode) :
            $catName = isset($term->name) ? ' ' . $term->name : '';
            ?>
            <div class="row">
                <div class="large-12 columns text-center margin-top-20">
                    <a href="<?php echo esc_url($link_shortcode); ?>" title="<?php echo esc_html__('View more', 'nasa-core') . ($catName != '' ? ' ' . esc_attr($catName) : ''); ?>" class="nasa-view-more-slider button">
                        <?php echo esc_html__('View more', 'nasa-core') . ($catName != '' ? ' ' . esc_attr($catName) : ''); ?>
                    </a>
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>
