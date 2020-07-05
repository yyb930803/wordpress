<?php
/*
  Template name: Page Shopping cart
 */

get_header();
?>

<div class="container-wrap page-shopping-cart">
    <div class="order-steps">
        <div class="row">
            <div class="large-12 columns">
                <?php if (function_exists('is_wc_endpoint_url')) : ?>
                    <?php if (!is_wc_endpoint_url('order-received')) : ?>
                        <div class="checkout-breadcrumb">
                            <div class="title-cart">
                                <a href="<?php echo esc_url(wc_get_cart_url()); ?>">
                                    <h1>01</h1>
                                    <h4><?php esc_html_e('Carrello', 'elessi-theme'); ?></h4>
                                    <p><?php esc_html_e('Gestisci i tuoi acquisti', 'elessi-theme'); ?></p>
                                </a>
                                <span class="pe-7s-angle-right"></span>
                            </div>

                            <div class="title-checkout">
                                <a href="<?php echo esc_url(wc_get_checkout_url()); ?>">
                                    <h1>02</h1>
                                    <h4><?php esc_html_e('Dettagli Checkout', 'elessi-theme'); ?></h4>
                                    <p><?php esc_html_e('Completa il tuo ordine', 'elessi-theme'); ?></p>
                                </a>
                                <span class="pe-7s-angle-right"></span>
                            </div>
                            
                            <div class="title-thankyou">
                                <h1>03</h1>
                                <h4><?php esc_html_e('Ordine Completato', 'elessi-theme'); ?></h4>
                                <p><?php esc_html_e('Rivedi e invia il tuo ordine', 'elessi-theme'); ?></p>
                            </div>
                        </div>
                    <?php else : ?>
                        <div class="checkout-breadcrumb">
                            <div class="title-cart">
                                <a href="#">
                                    <h1>01</h1>
                                    <h4><?php esc_html_e('Carrello', 'elessi-theme'); ?></h4>
                                    <p><?php esc_html_e('Gestisci i tuoi acquisti', 'elessi-theme'); ?></p>
                                </a>
                                <span class="pe-7s-angle-right"></span>
                            </div>
                            <div class="title-checkout">
                                <a href="#">
                                    <h1>02</h1>
                                    <h4><?php esc_html_e('Dettagli Checkout', 'elessi-theme'); ?></h4>
                                    <p><?php esc_html_e('Completa il tuo ordine', 'elessi-theme'); ?></p>
                                </a>
                                <span class="pe-7s-angle-right"></span>
                            </div>
                            <div class="title-thankyou nasa-complete">
                                <h1>03</h1>
                                <h4><?php esc_html_e('Ordine Completato', 'elessi-theme'); ?></h4>
                                <p><?php esc_html_e('Rivedi e invia il tuo ordine', 'elessi-theme'); ?></p>
                            </div>
                        </div>
                    <?php endif; ?>
                <?php else : ?> 
                    <div class="checkout-breadcrumb">
                        <div class="title-cart">
                            <span>01</span>
                            <p><?php esc_html_e('Carrello', 'elessi-theme'); ?></p>
                        </div>
                        <div class="title-checkout">
                            <span>02</span>
                            <p><?php esc_html_e('Dettagli Checkout', 'elessi-theme'); ?></p>
                        </div>
                        <div class="title-thankyou">
                            <span>03</span>
                            <p><?php esc_html_e('Ordine Completato', 'elessi-theme'); ?></p>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
    <div class="row">
        <div id="content" class="large-12 columns">
            <?php
            if(class_exists('WooCommerce') && shortcode_exists('woocommerce_cart')):
                echo do_shortcode('[woocommerce_cart]');
            endif;
            while (have_posts()) :
                the_post();
                the_content();
            endwhile;
            ?>
        </div>
    </div>
</div>

<?php
get_footer();
