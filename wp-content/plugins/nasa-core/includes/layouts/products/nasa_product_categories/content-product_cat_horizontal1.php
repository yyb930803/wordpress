<?php
$data_margin = '10';
$class_hozi = 'nasa-category-horizontal-1';
$disable_nav = 'false';
?>

<div class="group-slider category-slider nasa-category-slider-horizontal<?php echo $el_class; ?>">
    <div
        class="nasa-slider products-group owl-carousel <?php echo $class_hozi; ?>"
        data-autoplay="<?php echo $auto_slide; ?>"
        data-loop="<?php echo $auto_slide; ?>"
        data-disable-nav="<?php echo $disable_nav; ?>"
        data-columns="<?php echo esc_attr($columns_number); ?>"
        data-columns-small="<?php echo esc_attr($columns_number_small); ?>"
        data-columns-tablet="<?php echo esc_attr($columns_number_tablet); ?>"
        data-margin="<?php echo $data_margin; ?>">
        <?php foreach ($product_categories as $category) : ?>
            <div class="product-category wow fadeInUp" data-wow-duration="1s" data-wow-delay="<?php echo esc_attr($delay_animation_product); ?>ms">
                <div class="hover-overlay">
                    <a href="<?php echo get_term_link($category, 'product_cat'); ?>" title="<?php echo esc_attr($category->name); ?>">
                        <?php nasa_category_thumbnail($category, '480x900'); ?>
                        <div class="header-title">
                            <h3><?php echo $category->name; ?></h3>
                            <?php echo apply_filters('woocommerce_subcategory_count_html', ' <span class="count">' . $category->count . ' ' . esc_html__('items', 'nasa-core') . '</span>', $category); ?>
                        </div>
                        <?php do_action('woocommerce_after_subcategory_title', $category); ?>
                    </a>
                </div>
            </div>
        <?php
            $delay_animation_product += $_delay_item;
        endforeach;
        ?>
    </div> 
</div>
