<?php
/**
 * Single Product Thumbnails
 *
 * @see     https://docs.woocommerce.com/document/template-structure/
 * @author  WooThemes
 * @package WooCommerce/Templates
 * @version 3.5.1
 */
if (!defined('ABSPATH')) :
    exit; // Exit if accessed directly
endif;

global $product;

$productId = $product->get_id();
$attachment_ids = $product->get_gallery_image_ids();
$has_thumbnail = has_post_thumbnail();
?>
<div class="nasa-thumbnail-default-wrap">
    <div class="product-thumbnails images-popups-gallery nasa-single-product-thumbnails nasa-thumbnail-default">
        <?php
        if ($has_thumbnail) :
            $thumbId = get_post_thumbnail_id();
            $data_rel = '';
            $image_title = esc_attr(get_the_title($thumbId));
            $image_link = wp_get_attachment_url($thumbId);
            $image_thumb = wp_get_attachment_image_src($thumbId, 'thumbnail');
            $thumb_src = isset($image_thumb['0']) ? $image_thumb['0'] : wc_placeholder_img_src();
            $image = get_the_post_thumbnail($productId, apply_filters('single_product_small_thumbnail_size', 'thumbnail'), array('alt' => $image_title));

            echo sprintf('<div class="nasa-wrap-item-thumb nasa-active" data-main="#nasa-main-image-0" data-key="0" data-thumb_org="%s"><a href="javascript:void(0);" data-current_img="%s" title="%s" class="active-thumbnail" %s>%s</a></div>', $thumb_src, $image_link, $image_title, $data_rel, $image);
        else :
            $noimage = wc_placeholder_img_src();
            echo sprintf('<div class="nasa-wrap-item-thumb nasa-active" data-main="#nasa-main-image-0" data-key="0" data-thumb_org="%s"><a href="javascript:void(0);" data-current_img="%s" class="active-thumbnail"><img src="%s" /></a></div>', $noimage, $noimage, $noimage);
        endif;

        if(!empty($attachment_ids)) :
            $loop = 0;

            foreach ($attachment_ids as $attachment_id) :
                $key = $loop + 1;
                $classes = array('zoom');

                if ($loop == 0) :
                    $classes[] = 'first';
                endif;

                if (!$image_link = wp_get_attachment_url($attachment_id)) :
                    continue;
                endif;

                $image_class = esc_attr(implode(' ', $classes));
                $image = wp_get_attachment_image($attachment_id, apply_filters('single_product_small_thumbnail_size', 'thumbnail'));

                echo '<div class="nasa-wrap-item-thumb" data-main="#nasa-main-image-' . (int) $key . '" data-key="' . (int) $key . '">';
                echo apply_filters('woocommerce_single_product_image_thumbnail_html', sprintf('%s', $image), $attachment_id, $productId, $image_class);
                echo '</div>';

                $loop++;
            endforeach;

        endif;
        ?>
    </div>
</div>
