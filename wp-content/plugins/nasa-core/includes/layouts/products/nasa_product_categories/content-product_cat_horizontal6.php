<?php
$data_margin = (int) $margin_item;
$class_hozi = 'nasa-category-horizontal-6';
$disable_nav = 'false';
?>

<div class="group-slider category-slider nasa-category-slider-horizontal-6 nasa-category-slider-horizontal<?php echo $el_class; ?>">
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
                <a class="nasa-cat-link" href="<?php echo get_term_link($category, 'product_cat'); ?>" title="<?php echo esc_attr($category->name); ?>">
                    <div class="nasa-cat-thumb">
                        <?php nasa_category_thumbnail($category, 'nasa-medium'); ?>
                    </div>
                    <h3 class="header-title text-center nasa-cat-title">
                        <?php echo $category->name; ?>
                    </h3>
                    <?php do_action('woocommerce_after_subcategory_title', $category); ?>
                </a>
            </div>
        <?php
            $delay_animation_product += $_delay_item;
        endforeach;
        ?>
    </div> 
</div>
