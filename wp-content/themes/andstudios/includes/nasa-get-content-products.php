<?php
if($wp_query->post_count) :
    $_delay = $count = 0;
    $_delay_item = (isset($nasa_opt['delay_overlay']) && (int) $nasa_opt['delay_overlay']) ? (int) $nasa_opt['delay_overlay'] : 100;

    while ($wp_query->have_posts()) :
        $wp_query->the_post();
        wc_get_template(
            'content-product.php',
            array(
                '_delay' => $_delay,
                'wrapper' => 'li'
            )
        );
        $_delay += $_delay_item;
        $count++;
    endwhile;
endif;
