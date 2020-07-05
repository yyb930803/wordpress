<?php if($count = wp_count_posts()->publish):
    $_delay = 0;
    $_delay_item = (isset($nasa_opt['delay_overlay']) && (int) $nasa_opt['delay_overlay']) ? (int) $nasa_opt['delay_overlay'] : 100;
    ?>
    <div class="nasa-blog-sc nasa-sc-blogs-list nasa-blog-wrap-all-items">
        <?php while($recentPosts->have_posts()) :
            $recentPosts->the_post();
            $id = get_the_ID();
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
            <div class="row nasa-sc-blogs-row wow fadeInUp" data-wow-duration="1s" data-wow-delay="<?php echo esc_attr($_delay); ?>ms">
                <div class="large-2 columns">
                    <a href="<?php echo esc_url($link); ?>" title="<?php echo esc_attr($title); ?>">
                        <div class="blog-image">
                            <div class="blog-image-attachment" style="overflow:hidden;">
                                <?php
                                if (has_post_thumbnail()):
                                    the_post_thumbnail('medium', array(
                                        'alt' => esc_attr($title)
                                    ));
                                else:
                                    echo '<img src="' . NASA_CORE_PLUGIN_URL . 'assets/images/placeholder.png" alt="' . esc_attr($title) . '" />';
                                endif;
                                ?>
                                
                                <div class="image-overlay"></div>
                            </div>
                        </div>
                    </a>
                </div>
                <div class="large-10 columns">
                    <div class="post-content">
                        <?php echo ($cats_enable == 'yes') ? '<div class="nasa-post-cats-wrap">' . $categories . '</div>' : ''; ?>
                        <h5 class="nasa-post-title margin-top-0 margin-bottom-5">
                            <a href="<?php echo esc_url($link); ?>" title="<?php echo esc_attr($title); ?>">
                                <?php echo $title;?>
                            </a>
                        </h5>
                        
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
                            <div class="entry-blog">
                                <div class="clearfix"></div>
                                <div class="nasa-info-short">
                                    <?php the_excerpt(); ?>
                                </div>
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
<?php
endif;