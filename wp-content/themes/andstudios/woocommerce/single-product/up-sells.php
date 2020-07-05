<?php
/**
 * Single Product Up-Sells
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/single-product/up-sells.php.
 *
 * HOWEVER, on occasion WooCommerce will need to update template files and you
 * (the theme developer) will need to copy the new files to your theme to
 * maintain compatibility. We try to do this as little as possible, but it does
 * happen. When this occurs the version of the template file will be bumped and
 * the readme will list any important changes.
 *
 * @see 	https://docs.woocommerce.com/document/template-structure/
 * @author 	WooThemes
 * @package 	WooCommerce/Templates
 * @version     3.0.0
 */
if (!defined('ABSPATH')) :
    exit;
endif;

if ($upsells) :
    global $nasa_opt;

    $columns_desk = !isset($nasa_opt['relate_columns_desk']) || !(int) $nasa_opt['relate_columns_desk'] ? 5 : (int) $nasa_opt['relate_columns_desk'];
    $columns_tablet = !isset($nasa_opt['relate_columns_tablet']) || !(int) $nasa_opt['relate_columns_tablet'] ? 3 : (int) $nasa_opt['relate_columns_tablet'];
    $columns_small = !isset($nasa_opt['relate_columns_small']) || !(int) $nasa_opt['relate_columns_small'] ? 1 : (int) $nasa_opt['relate_columns_small'];
    
    $_delay = 0;
    $_delay_item = (isset($nasa_opt['delay_overlay']) && (int) $nasa_opt['delay_overlay']) ? (int) $nasa_opt['delay_overlay'] : 100;
    ?>
    <hr class="margin-bottom-50" />
    <div class="related-product nasa-slider-wrap">
        <div class="related up-sell products grid margin-bottom-40">
            <div class="row nasa-warp-slide-nav-side">
                <div class="large-12 columns">
                    <div class="nasa-slide-style-product-carousel">
                        <h3 class="nasa-shortcode-title-slider text-center"><?php esc_html_e('You may also like&hellip;', 'elessi-theme'); ?></h3>
                        <div class="nasa-nav-carousel-wrap nasa-carousel-related text-right">
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
                    </div>
                </div>
                
                <div class="large-12 columns">
                    <div class="group-slider">
                        <div id="nasa-slider-upsell-product" class="nasa-slider owl-carousel products-group" data-columns="<?php echo (int) $columns_desk; ?>" data-columns-small="<?php echo (int) $columns_small; ?>" data-columns-tablet="<?php echo (int) $columns_tablet; ?>" data-margin="10" data-margin-small="0" data-margin-medium="0" data-padding="0px" data-disable-nav="true">
                            <?php
                            foreach ($upsells as $upsell):
                                $post_object = get_post($upsell->get_id());
                                setup_postdata($GLOBALS['post'] = & $post_object);
                                // Product Item -->
                                wc_get_template('content-product.php', array(
                                    '_delay' => $_delay,
                                    'wrapper' => 'div',
                                    'combo_show_type' => 'popup',
                                    'disable_drag' => true
                                ));
                                // End Product Item -->
                                $_delay += $_delay_item;
                            endforeach;
                            ?>
                        </div>  
                    </div>
                </div>
            </div>
        </div>
    </div>
    <?php

endif;
