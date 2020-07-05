<?php
$_delay = 0;
$_delay_item = (isset($nasa_opt['delay_overlay']) && (int) $nasa_opt['delay_overlay']) ? (int) $nasa_opt['delay_overlay'] : 100;
$data= get_post_meta($post->ID, 'data', true);
$luogo= get_post_meta($post->ID, 'luogo', true);
?>
<div class="row group-blogs nasa-blog-wrap-all-items">
    <div class="large-12 columns">
        <div class="nasa-blog-sc blog-grid blog-grid-style">
            <ul class="small-block-grid-<?php echo esc_attr($columns_number_small); ?> medium-block-grid-<?php echo esc_attr($columns_number_tablet); ?> large-block-grid-<?php echo esc_attr($columns_number); ?> grid" data-product-per-row="<?php echo esc_attr($columns_number); ?>">
                <?php
                $k = 0;
                $count = wp_count_posts()->publish;
                if ($count > 0) {
                    while ($recentPosts->have_posts()) {
			global $nasa_opt;
                        $recentPosts->the_post();
                        $title = get_the_title();
                        $link = get_the_permalink();
                        $postId = get_the_ID();
                        $categories = ($cats_enable == 'yes') ? get_the_category_list(esc_html__(', ', 'nasa-core')) : '';
			
			$luogo= get_post_meta($postId, 'luogo', true);
                        $data= get_post_meta($postId, 'data', true);
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
                        
                        echo '<li class="wow fadeIn" data-wow-duration="1s"><div class="nasa-item-blog-grid">';
                        ?>
                            <a class="linkoverlay" href="<?php echo esc_url($link); ?>" title="<?php echo esc_attr($title); ?>">
                                <div class="entry-blog">
                                    <div class="blog-image">
                                        <div class="blog-image-attachment" style="overflow:hidden;">
                                            <?php
                                            if (has_post_thumbnail()):
                                                the_post_thumbnail('800x800', array(
                                                    'alt' => esc_attr($title)
                                                ));
                                            else:
                                                echo '<img src="' . NASA_CORE_PLUGIN_URL . 'assets/images/placeholder.png" alt="' . esc_attr($title) . '" />';
                                            endif;
                                            ?>
                                            <a href="<?php echo esc_url($link); ?>"><div class="image-overlay"></div></a>
                                        </div>
                                    </div>
                                </div>
                            </a>
                            <div class="nasa-blog-info nasa-blog-img-top">
                                <?php echo ($cats_enable == 'yes') ? '<div class="nasa-post-cats-wrap">' . $categories . '</div>' : ''; ?>
                                <div class="blog_title">
                                    <h5>
                                        <a href="<?php echo esc_url($link); ?>" title="<?php echo esc_attr($title); ?>"><?php echo $title; ?></a>
                                    </h5>
				    
                                </div>
                                <?php if($readmore == 'yes') : ?>
                                            <a href="<?php echo esc_url($link); ?>" title="<?php echo esc_html__('Dettagli', 'nasa-core'); ?>" class="nasa-post-date-author-link nasa-post-read-more">
                                                <span class="nasa-post-date-author nasa-post-author">
                                                    <?php echo esc_html__('Dettagli', 'nasa-core'); ?>
                                                </span>
                                            </a>
                                <?php endif; ?>

                                <?php if($date_author == 'top') : ?>
                                    <div class="nasa-post-date-author-wrap">
					

                                        <?php if($author_enable == 'yes') : ?>
					 <?php if($luogo !== '') { ?> 
                                           <p class="luogoagglomerativa"> <?php echo ($luogo); ?> </p>
					<?php } ?>
					<?php if($data !== '') { ?> 
                                           <p class="dataagglomerativa"> <?php echo ($data); ?></p>
					<?php } ?>
                                        <?php endif; ?>

                                        
                                    </div>
                                <?php endif; ?>
                                
                                <div class="clearfix"></div>
                                
                                <?php if($des_enable == 'yes') : ?>
                                    <div class="nasa-info-short">
                                        <a class="readfullarticle" href="<?php echo esc_url($link); ?>">READ FULL ARTICLE</a>
                                    </div>
                                <?php endif; ?>
                                
                                <?php if($date_author == 'bot') : ?>
                                    <div class="nasa-post-date-author-wrap">
                                        <?php if($author_enable == 'yes') : ?>
					<?php if($luogo !== '') { ?> 
                                            <span class="luogo"><?php echo ($luogo); ?></span><br>
					<?php } ?>
                                        <?php endif; ?>

					  <?php if($date_enable == 'yes') : ?>
					<?php if($data !== '') { ?> 
                                            <?php echo ($data); ?>
					<?php } ?>
                                        <?php endif; ?>
                                    </div>
                                <?php endif; ?>
                            </div>
                        <?php
                        echo '</div></li>';
                        $k++;
                        $_delay += $_delay_item;
                    }
                }
                ?>
            </ul>
        </div>
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
<?php

endif;