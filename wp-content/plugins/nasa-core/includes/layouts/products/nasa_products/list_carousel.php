<?php
$_delay = 0;
$_delay_item = (isset($nasa_opt['delay_overlay']) && (int) $nasa_opt['delay_overlay']) ? (int) $nasa_opt['delay_overlay'] : 100;

$data_margin = isset($data_margin) ? (int) $data_margin : 10;
$height_auto = !isset($height_auto) ? 'false' : $height_auto;
$auto_slide = isset($auto_slide) ? $auto_slide : 'false';
$style_row = (!isset($style_row) || !in_array((int) $style_row, array(1, 2, 3))) ? 1 : (int) $style_row;
$arrows = isset($arrows) ? $arrows : 0;
$dots = isset($dots) ? $dots : 'false';
?>
<div class="nasa-relative nasa-slider-wrap nasa-slide-style-product-carousel nasa-product-list-carousel">
    <?php if($arrows == 1) : ?>
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

    <div class="group-slider">
        <div
            class="slider products-group nasa-slider owl-carousel products grid<?php echo $style_row > 1 ? ' nasa-slide-double-row' : ''; ?>"
            data-margin="<?php echo esc_attr($data_margin); ?>"
            data-margin-small="0"
            data-margin-medium="0"
            data-columns="<?php echo esc_attr($columns_number); ?>"
            data-columns-small="<?php echo esc_attr($columns_number_small); ?>"
            data-columns-tablet="<?php echo esc_attr($columns_number_tablet); ?>"
            data-autoplay="<?php echo esc_attr($auto_slide); ?>"
            data-loop="<?php echo $auto_slide == 'true' ? 'true' : 'false'; ?>"
            data-height-auto="<?php echo $height_auto; ?>"
            data-dot="<?php echo esc_attr($dots); ?>"
            data-disable-nav="true">
            <?php
            $k = 0;
            echo $style_row > 1 ? '<div class="nasa-wrap-column">' : '';
            while ($loop->have_posts()) :
                $loop->the_post();
                echo ($k && $style_row > 1 && ($k%$style_row == 0)) ? '<div class="nasa-wrap-column">' : '';

                wc_get_template(
                    'content-widget-product.php', 
                    array(
                        'wapper' => 'div',
                        'delay' => $_delay,
                        '_delay_item' => $_delay_item,
                        'list_type' => '1'
                    )
                );

                if($k && $style_row > 1 && (($k+1)%$style_row == 0)) :
                    $_delay += $_delay_item;
                    echo '</div>';
                endif;

                if($style_row == 1) :
                    $_delay += $_delay_item;
                endif; 

                $k++;
            endwhile;
            echo ($k && $style_row > 1 && $k%$style_row != 0) ? '</div>' : '';
            ?>
        </div>
    </div>
</div>
