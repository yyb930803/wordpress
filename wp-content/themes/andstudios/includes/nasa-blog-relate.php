<?php
if($relate) :
$image_size = 'medium';
$_delay = 0;
$_delay_item = (isset($nasa_opt['delay_overlay']) && (int) $nasa_opt['delay_overlay']) ? (int) $nasa_opt['delay_overlay'] : 100;
$auto_slide = isset($auto_slide) ? $auto_slide : 'false';
$dots = isset($dots) ? $dots : 'false';
$arrows = isset($arrows) ? $arrows : 1;

$cats_enable = $author_enable = $date_enable = $date_author = 'no';
$des_enable = 'no';
?>

<div class="nasa-blogs-relate nasa-relative nasa-slide-style-blogs nasa-slider-wrap">
    <h3 class="nasa-shortcode-title-slider text-center"><?php esc_html_e('Related posts', 'elessi-theme'); ?></h3>
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
    
    <div class="nasa-blog-sc nasa-no-cols group-slider">
        <div
            class="group-blogs nasa-blog-carousel nasa-slider owl-carousel"
            data-columns="<?php echo esc_attr($columns_number); ?>"
            data-columns-small="<?php echo esc_attr($columns_number_small); ?>"
            data-columns-tablet="<?php echo esc_attr($columns_number_tablet); ?>"
            data-autoplay="<?php echo esc_attr($auto_slide); ?>"
            data-loop="<?php echo ($auto_slide == 'true') ? 'true' : 'false'; ?>"
            data-dot="<?php echo ($dots == 'true') ? 'true' : 'false'; ?>"
            data-disable-nav="true">
            <?php
            foreach ($relate as $post_relate) :
                $title = $post_relate->post_title;
                $link = get_the_permalink($post_relate);
                $postId = $post_relate->ID;
                $categories = ($cats_enable == 'yes') ? get_the_category_list(esc_html__(', ', 'elessi-theme')) : '';
                
                if($author_enable == 'yes') :
                    $author = get_the_author();
                    $author_id = get_the_author_meta('ID');
                    $link_author = get_author_posts_url($author_id);
                endif;
                
                if($date_enable == 'yes') :
                    $day = get_the_time('d', $postId);
                    $month = get_the_time('m', $postId);
                    $year = get_the_time('Y', $postId);
                    $link_date = get_day_link($year, $month, $day);
                    $date_post = get_the_time('d F', $postId);
                endif;
                
                ?>
                <div class="blog_item wow fadeInUp" data-wow-duration="1s" data-wow-delay="<?php echo esc_attr($_delay); ?>ms">
                    <div class="large-12 columns">
                        <div class="nasa-content-group">
                            <?php if (has_post_thumbnail($post_relate)): ?>
                                <a href="<?php echo esc_url($link); ?>" title="<?php echo esc_attr($title); ?>">
                                    <div class="entry-blog">
                                        <div class="blog-image img_left">
                                            <div class="blog-image-attachment" style="overflow:hidden;">
                                                <?php echo get_the_post_thumbnail($post_relate, $image_size, array(
                                                    'alt' => trim(strip_tags(get_the_title()))
                                                )); ?>
                                                <div class="image-overlay"></div>
                                            </div>
                                        </div>
                                    </div>
                                </a>
                            <?php endif; ?>
                            <div class="nasa-blog-info-slider">
                                <?php echo ($cats_enable == 'yes') ? '<div class="nasa-post-cats-wrap">' . $categories . '</div>' : ''; ?>
                                <div class="blog_title">
                                    <h5>
                                        <a href="<?php echo esc_url($link); ?>" title="<?php echo esc_attr($title); ?>">
                                            <?php echo ($title); ?>
                                        </a>
                                    </h5>
                                </div>
                                
                                <?php if($date_author == 'top') : ?>
                                    <div class="nasa-post-date-author-wrap">
                                        <?php if($date_enable == 'yes') : ?>
                                            <a href="<?php echo esc_url($link_date); ?>" title="<?php echo esc_html__('Posts at ', 'elessi-theme') . esc_attr($date_post); ?>" class="nasa-post-date-author-link">
                                                <span class="nasa-post-date-author">
                                                    <i class="pe-7s-timer"></i>
                                                    <?php echo ($date_post); ?>
                                                </span>
                                            </a>
                                        <?php endif; ?>

                                        <?php if($author_enable == 'yes') : ?>
                                            <a href="<?php echo esc_url($link_author); ?>" title="<?php echo esc_html__('Posted By ', 'elessi-theme') . esc_attr($author); ?>" class="nasa-post-date-author-link">
                                                <span class="nasa-post-date-author nasa-post-author">
                                                    <i class="pe-7s-user"></i>
                                                    <?php echo ($author); ?>
                                                </span>
                                            </a>
                                        <?php endif; ?>

                                        <?php if($readmore == 'yes') : ?>
                                            <a href="<?php echo esc_url($link); ?>" title="<?php echo esc_html__('Read more', 'elessi-theme'); ?>" class="nasa-post-date-author-link hide-for-mobile nasa-post-read-more">
                                                <span class="nasa-post-date-author nasa-post-author">
                                                    <i class="pe-7s-more margin-right-5"></i>
                                                    <?php echo esc_html__('Read more', 'elessi-theme'); ?>
                                                </span>
                                            </a>
                                        <?php endif; ?>
                                    </div>
                                <?php endif; ?>
                                
                                <?php if($des_enable == 'yes') : ?>
                                    <div class="nasa-info-short">
                                        <?php echo get_the_excerpt($post_relate); ?>
                                    </div>
                                <?php endif; ?>

                                <?php if($date_author == 'bot') : ?>
                                    <div class="nasa-post-date-author-wrap">
                                        <?php if($date_enable == 'yes') : ?>
                                            <a href="<?php echo esc_url($link_date); ?>" title="<?php echo esc_html__('Posts at ', 'elessi-theme') . esc_attr($date_post); ?>" class="nasa-post-date-author-link">
                                                <span class="nasa-post-date-author bottom">
                                                    <i class="pe-7s-timer"></i>
                                                    <?php echo ($date_post); ?>
                                                </span>
                                            </a>
                                        <?php endif; ?>

                                        <?php if($author_enable == 'yes') : ?>
                                            <a href="<?php echo esc_url($link_author); ?>" title="<?php echo esc_html__('Posted By ', 'elessi-theme') . esc_attr($author); ?>" class="nasa-post-date-author-link">
                                                <span class="nasa-post-date-author nasa-post-author bottom">
                                                    <i class="pe-7s-user"></i>
                                                    <?php echo ($author); ?>
                                                </span>
                                            </a>
                                        <?php endif; ?>

                                        <?php if($readmore == 'yes') : ?>
                                            <a href="<?php echo esc_url($link); ?>" title="<?php echo esc_html__('Read more', 'elessi-theme'); ?>" class="nasa-post-date-author-link hide-for-mobile nasa-post-read-more">
                                                <span class="nasa-post-date-author nasa-post-author bottom">
                                                    <i class="pe-7s-more margin-right-5"></i>
                                                    <?php echo esc_html__('Read more', 'elessi-theme'); ?>
                                                </span>
                                            </a>
                                        <?php endif; ?>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
                <?php $_delay += $_delay_item; ?>
            <?php endforeach; ?>
        </div>
    </div>
</div>

<?php
endif;
