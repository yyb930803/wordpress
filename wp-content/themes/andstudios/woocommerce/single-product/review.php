<?php
/**
 * Review Comments Template
 *
 * Closing li is left out on purpose!
 *
 * @author 		WooThemes
 * @package 	WooCommerce/Templates
 * @version     2.6.0
 */
if (!defined('ABSPATH')):
    exit; // Exit if accessed directly
endif;

$rating = intval(get_comment_meta($comment->comment_ID, 'rating', true));
?>
<li <?php comment_class(); ?> id="li-comment-<?php comment_ID() ?>">
    <div id="comment-<?php comment_ID(); ?>" class="review-item">

        <?php echo get_avatar($comment, apply_filters('woocommerce_review_gravatar_size', '60'), '', get_comment_author()); ?>
        <?php if ($rating && get_option('woocommerce_enable_review_rating') == 'yes') : ?>
            <div class="star-rating" title="<?php echo sprintf(esc_html__('Rated %d out of 5', 'elessi-theme'), $rating) ?>">
                <span style="width:<?php echo (intval(get_comment_meta($GLOBALS['comment']->comment_ID, 'rating', true)) / 5) * 100; ?>%"><strong><?php echo intval(get_comment_meta($GLOBALS['comment']->comment_ID, 'rating', true)); ?></strong> <?php esc_html_e('out of 5', 'elessi-theme'); ?></span>
            </div>
        <?php endif; ?>

        <?php if ($comment->comment_approved == '0') : ?>
            <p class="meta"><em><?php esc_html_e('Your comment is awaiting approval', 'elessi-theme'); ?></em></p>
        <?php else : ?>
            <p class="meta">
                <strong><?php comment_author(); ?></strong>
                <?php
                if (get_option('woocommerce_review_rating_verification_label') === 'yes'):
                    if (wc_customer_bought_product($comment->comment_author_email, $comment->user_id, $comment->comment_post_ID)):
                        echo '<em class="verified">(' . esc_html__('verified owner', 'elessi-theme') . ')</em> ';
                    endif;
                endif;
                ?>&ndash; <time datetime="<?php echo get_comment_date('c'); ?>"><?php echo get_comment_date(get_option('date_format')); ?></time>:
            </p>
        <?php endif; ?>

        <div class="description"><?php comment_text(); ?></div>
    </div>
