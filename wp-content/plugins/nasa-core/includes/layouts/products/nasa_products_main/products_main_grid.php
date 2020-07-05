<?php
$_delay_item = (isset($nasa_opt['delay_overlay']) && (int) $nasa_opt['delay_overlay']) ? (int) $nasa_opt['delay_overlay'] : 100;
$_delay = 0;

$cat_info = apply_filters('nasa_loop_categories_show', false);
$description_info = apply_filters('nasa_loop_short_description_show', false);
?>
<div class="row">
    <?php /* Extra products */?>
    <?php if($others->post_count) : ?>
        <div class="large-3 columns">
            <div class="nasa-product-main-aside first products">
                <?php
                $key_other = 0;
                while ($key_other < 2 && $others->have_posts()) :
                    $others->the_post();
                    wc_get_template('content-product.php', array(
                        'is_deals' => false,
                        '_delay' => $_delay,
                        '_delay_item' => $_delay_item,
                        'wrapper' => 'div',
                        'show_in_list' => false,
                        'cat_info' => $cat_info,
                        'description_info' => $description_info
                    ));
                    $_delay += $_delay_item;
                    $key_other++;
                endwhile;
                ?>
            </div>
        </div>
    <?php endif; ?>
    
    <?php /* Main products */?>
    <div class="large-6 columns">
        <div class="nasa-product-main-center">
            <?php include NASA_CORE_PRODUCT_LAYOUTS . 'nasa_products_main/products_main_content.php'; ?>
        </div>
    </div>
    
    <?php /* Extra products */?>
    <?php if($others->post_count) : ?>
        <div class="large-3 columns">
            <div class="nasa-product-main-aside last products">
                <?php
                $key_other = 0;
                while ($key_other < 2 && $others->have_posts()) :
                    $others->the_post();
                    wc_get_template('content-product.php', array(
                        'is_deals' => false,
                        '_delay' => $_delay,
                        '_delay_item' => $_delay_item,
                        'wrapper' => 'div',
                        'combo_show_type' => 'popup'
                    ));
                    $_delay += $_delay_item;
                    $key_other++;
                endwhile;
                ?>
            </div>
        </div>
    <?php endif; ?>
</div>
