<?php
$class_ul = 'large-block-grid-' . ((int) $columns_number) . ' medium-block-grid-' . ((int) $columns_number_tablet) . ' small-block-grid-' . ((int) $columns_number_small);
?>
<div class="product_list_widget row">
    <div class="large-12 columns">
        <ul class="<?php echo $class_ul; ?>">
            <?php
            $_delay = 0;
            $_delay_item = (isset($nasa_opt['delay_overlay']) && (int) $nasa_opt['delay_overlay']) ? (int) $nasa_opt['delay_overlay'] : 100;

            while ($loop->have_posts()) : 
                $loop->the_post();
                echo '<li>';
                wc_get_template(
                    'content-widget-product.php', 
                    array(
                        'wapper' => 'div',
                        'delay' => $_delay,
                        '_delay_item' => $_delay_item,
                        'list_type' => '1'
                    )
                );
                echo '</li>';
                $_delay += $_delay_item;
            endwhile; ?>
        </ul>
    </div>
</div>