<?php
/**
 * Carousel slide for gift products
 */
$id_sc = rand(0, 9999999);
$_delay = 0;
$_delay_item = (isset($nasa_opt['delay_overlay']) && (int) $nasa_opt['delay_overlay']) ? (int) $nasa_opt['delay_overlay'] : 100;
$columns_title = isset($_REQUEST['title_columns']) && (int) $_REQUEST['title_columns'] <= 4 ? (int) $_REQUEST['title_columns'] : 2;
$coulums_slide = 12 - $columns_title;

?>
<div class="large-<?php echo esc_attr($columns_title); ?> columns">
    <div class="nasa-slide-left-info-wrap">
        <h4 class="nasa-combo-gift"><?php echo esc_html__('Bundle product for', 'elessi-theme'); ?></h4>
        <h3><?php echo ($product->get_name()); ?><span class="nasa-count-items">(<?php echo count($combo) . ' ' . esc_html__('Items', 'elessi-theme'); ?>)</span></h3>
        <div class="nasa-nav-carousel-wrap" data-id="#nasa-slider-<?php echo esc_attr($id_sc); ?>">
            <div class="nasa-nav-carousel-prev nasa-nav-carousel-div">
                <a class="nasa-nav-icon-slider" href="javascript:void(0);" data-do="prev">
                    <span class="icon-nasa-left-arrow"></span>
                </a>
            </div>
            <div class="nasa-nav-carousel-next nasa-nav-carousel-div">
                <a class="nasa-nav-icon-slider" href="javascript:void(0);" data-do="next">
                    <span class="icon-nasa-right-arrow"></span>
                </a>
            </div>
        </div>
        
        <?php if(!isset($nasa_viewmore) || $nasa_viewmore == true) : ?>
            <a class="nasa-view-more-slider" href="<?php echo esc_url($product->get_permalink()); ?>" title="<?php echo esc_attr__('View more', 'elessi-theme'); ?>"><?php echo esc_html__('View more', 'elessi-theme'); ?></a>
        <?php endif; ?>
    </div>
</div>

<div class="large-<?php echo esc_attr($coulums_slide); ?> columns">
    <div class="row group-slider">
        <div id="nasa-slider-<?php echo esc_attr($id_sc); ?>" class="slider products-group nasa-combo-slider owl-carousel" data-margin="10px" data-columns="4" data-columns-small="1" data-columns-tablet="2" data-padding="65px" data-disable-nav="true">
            <?php
            $file_content = ELESSI_CHILD_PATH . '/includes/nasa-content-product-gift.php';
            $file_content = is_file($file_content) ? $file_content : ELESSI_THEME_PATH . '/includes/nasa-content-product-gift.php';
            foreach ($combo as $bundle_item) :
                include $file_content;
                $_delay += $_delay_item;
            endforeach;
            ?>
        </div>
    </div>
</div>
