<?php
$_delay = 0;
$_delay_item = (isset($nasa_opt['delay_overlay']) && (int) $nasa_opt['delay_overlay']) ? (int) $nasa_opt['delay_overlay'] : 100;
$auto_slide = isset($auto_slide) ? $auto_slide : 'false';
$dots = isset($dots) ? $dots : 'false';
$arrows = isset($arrows) ? $arrows : 0;
?>

<div class="nasa-relative nasa-slider-wrap nasa-slide-style-blogs">
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
            data-loop="<?php echo $auto_slide == 'true' ? 'true' : 'false'; ?>"
            data-dot="<?php echo $dots == 'true' ? 'true' : 'false'; ?>"
            data-disable-nav="true">
            <?php
            while ($recentPosts->have_posts()) :
                $recentPosts->the_post();
                $title = get_the_title();
                $link = get_the_permalink();
                $postId = get_the_ID();
                $categories = ($cats_enable == 'yes') ? get_the_category_list(esc_html__(', ', 'nasa-core')) : '';
                
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

                    <div class="nasa-content-group">
                        <a href="<?php echo esc_url($link); ?>" title="<?php echo esc_attr($title); ?>">
                            <div class="entry-blog">
                                <div class="blog-image img_left">
                                    <div class="blog-image-attachment" style="overflow:hidden;">
                                        <?php
                                        if (has_post_thumbnail()):
                                            the_post_thumbnail('380x380', array(
                                                'alt' => trim(strip_tags(get_the_title()))
                                            ));
                                        else:
                                            echo '<img src="' . NASA_CORE_PLUGIN_URL . 'assets/images/placeholder.png" alt="' . esc_attr($title) . '" />';
                                        endif;
                                        ?>
                                        <div class="image-overlay"></div>
                                    </div>
                                </div>
                            </div>
                        </a>
                        <div class="nasa-blog-info-slider">
                            <?php echo ($cats_enable == 'yes') ? '<div class="nasa-post-cats-wrap">' . $categories . '</div>' : ''; ?>
                            <div class="blog_title">
                                <h5>
                                    <a href="<?php echo esc_url($link); ?>" title="<?php echo esc_attr($title); ?>">
                                        <?php echo $title; ?>
                                    </a>
                                </h5>
                            </div>

                            <?php if($date_author == 'top') : ?>
                                <div class="nasa-post-date-author-wrap">
                                    <?php if($date_enable == 'yes') : ?>
                                        <a href="<?php echo esc_url($link_date); ?>" title="<?php echo esc_html__('Posts at ', 'nasa-core') . esc_attr($date_post); ?>" class="nasa-post-date-author-link">
                                            <span class="nasa-post-date-author">
                                                <i class="pe-7s-date"></i>
                                                <?php echo $date_post; ?>
                                            </span>
                                        </a>
                                    <?php endif; ?>

                                    <?php if($author_enable == 'yes') : ?>
                                        <a href="<?php echo esc_url($link_author); ?>" title="<?php echo esc_html__('Posted By ', 'nasa-core') . esc_attr($author); ?>" class="nasa-post-date-author-link">
                                            <span class="nasa-post-date-author nasa-post-author">
                                                <i class="pe-7s-user"></i>
                                                <?php echo $author; ?>
                                            </span>
                                        </a>
                                    <?php endif; ?>

                                    <?php if($readmore == 'yes') : ?>
                                        <a href="<?php echo esc_url($link); ?>" title="<?php echo esc_html__('Read more', 'nasa-core'); ?>" class="nasa-post-date-author-link hide-for-mobile nasa-post-read-more">
                                            <span class="nasa-post-date-author nasa-post-author">
                                                <i class="pe-7s-news-paper margin-right-5"></i>
                                                <?php echo esc_html__('Read more', 'nasa-core'); ?>
                                            </span>
                                        </a>
                                    <?php endif; ?>
                                </div>
                            <?php endif; ?>

                            <?php if($des_enable == 'yes') : ?>
                                <div class="nasa-info-short">
                                    <?php the_excerpt(); ?>
                                </div>
                            <?php endif; ?>

                            <?php if($date_author == 'bot') : ?>
                                <div class="nasa-post-date-author-wrap">
                                    <?php if($date_enable == 'yes') : ?>
                                        <a href="<?php echo esc_url($link_date); ?>" title="<?php echo esc_html__('Posts at ', 'nasa-core') . esc_attr($date_post); ?>" class="nasa-post-date-author-link">
                                            <span class="nasa-post-date-author bottom">
                                                <i class="pe-7s-date"></i>
                                                <?php echo $date_post; ?>
                                            </span>
                                        </a>
                                    <?php endif; ?>

                                    <?php if($author_enable == 'yes') : ?>
                                        <a href="<?php echo esc_url($link_author); ?>" title="<?php echo esc_html__('Posted By ', 'nasa-core') . esc_attr($author); ?>" class="nasa-post-date-author-link">
                                            <span class="nasa-post-date-author nasa-post-author bottom">
                                                <i class="pe-7s-user"></i>
                                                <?php echo $author; ?>
                                            </span>
                                        </a>
                                    <?php endif; ?>

                                    <?php if($readmore == 'yes') : ?>
                                        <a href="<?php echo esc_url($link); ?>" title="<?php echo esc_html__('Read more', 'nasa-core'); ?>" class="nasa-post-date-author-link hide-for-mobile nasa-post-read-more">
                                            <span class="nasa-post-date-author nasa-post-author bottom">
                                                <i class="pe-7s-news-paper margin-right-5"></i>
                                                <?php echo esc_html__('Read more', 'nasa-core'); ?>
                                            </span>
                                        </a>
                                    <?php endif; ?>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>

                </div>
                <?php $_delay += $_delay_item; ?>
            <?php endwhile; ?>
        </div>
    </div>
    
    <?php if($page_blogs == 'yes') : ?>
        <div class="row">
            <div class="large-12 columns text-center margin-bottom-40">
                <a href="<?php echo esc_url(get_permalink(get_option('page_for_posts'))); ?>" title="<?php echo esc_html__('All blogs', 'nasa-core'); ?>" class="nasa-view-more button">
                    <?php echo esc_html__('All blogs', 'nasa-core'); ?>
                </a>
            </div>
        </div>
    <?php endif; ?>
</div>
