<?php
/*
  Template name: My Account
  This templates add Account menu to the sidebar.
 */

$nasa_pageName = get_the_title();
get_header(); ?>

<div class="page-wrapper my-account">
    <div class="row">
        <div id="content" class="large-12 columns">
            <?php if (NASA_CORE_USER_LOGIGED) : ?>
                <div class="nasa-my-acc-content">
                    <h4 class="heading-title hidden-tag">
                        <?php echo esc_html($nasa_pageName); ?>
                    </h4>

                    <?php
                    echo shortcode_exists('woocommerce_my_account') ?
                        do_shortcode('[woocommerce_my_account]') . '<div class="nasa-clear-both"></div>' : '';

                    while (have_posts()) :
                        the_post();
                        the_content();
                    endwhile; // end of the loop.
                    ?>
                </div>
            <?php else : ?>
                <h1 class="margin-top-20 text-center">
                    <?php echo esc_html__('Login/Register', 'elessi-theme'); ?>
                </h1>
                <?php
                echo shortcode_exists('woocommerce_my_account') ?
                    do_shortcode('[woocommerce_my_account]') . '<div class="nasa-clear-both"></div>' : '';
                while (have_posts()) :
                    the_post();
                    the_content();
                endwhile; // end of the loop.
                ?>
            <?php endif; ?>

        </div><!-- end #content large-12 -->
    </div><!-- end row -->
</div><!-- end page-right-sidebar container -->

<?php
get_footer();
