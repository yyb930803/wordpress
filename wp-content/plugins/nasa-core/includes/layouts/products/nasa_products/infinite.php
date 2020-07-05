<?php
$columns_number = (int) $columns_number < 2 || (int) $columns_number > 5 ? 5 : (int) $columns_number;
?>
<div class="row nasa-products-infinite-wrap">
    <div class="large-12 columns">
        <div class="products grid nasa-wrap-all-rows products-infinite nasa-products-infinite products-group"
            data-next-page="2"
            data-product-type="<?php echo $type; ?>"
            data-post-per-page="<?php echo $number; ?>"
            data-post-per-row="<?php echo $columns_number; ?>"
            data-post-per-row-medium="<?php echo $columns_number_tablet; ?>"
            data-post-per-row-small="<?php echo $columns_number_small; ?>"
            data-max-pages="<?php echo $loop->max_num_pages; ?>"
            data-cat="<?php echo esc_attr($cat); ?>">
            <?php include NASA_CORE_PRODUCT_LAYOUTS . 'globals/row_layout.php'; ?>
        </div>
    </div>
    
    <div class="large-12 columns text-center desktop-margin-top-40 margin-bottom-20">
        <?php if ($loop->max_num_pages > 1) :
            $style_viewmore = ' nasa-more-type-' . (isset($style_viewmore) ? $style_viewmore : '1');
            ?>
            <a href="javascrip:void(0);" class="load-more-btn load-more<?php echo esc_attr($style_viewmore); ?>" data-nodata="<?php esc_attr_e('ALL PRODUCTS LOADED', 'nasa-core'); ?>">
                <div class="load-more-content">
                    <span class="load-more-icon icon-nasa-refresh"></span>
                    <span class="load-more-text"><?php esc_html_e('LOAD MORE ...', 'nasa-core'); ?></span>
                </div>
            </a>
        <?php endif; ?>
    </div>
</div>
