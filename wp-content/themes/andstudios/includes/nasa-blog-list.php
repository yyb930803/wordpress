<?php
$class_col = 'large-12 columns';
?>
<div class="blog-list-style">
    <article id="post-<?php echo (int) $postId; ?>" <?php post_class(); ?>>
        <div class="row">
            <?php if (has_post_thumbnail()) : ?>
                <div class="large-4 columns">
                    <div class="entry-image">
                        <a href="<?php echo esc_url($link); ?>" title="<?php echo esc_attr($title); ?>">
                            <?php the_post_thumbnail('medium'); ?>
                            <div class="image-overlay"></div>
                        </a>
                    </div>
                </div>
            <?php
            $class_col = 'large-8 columns';
            endif; ?>

            <div class="<?php echo esc_attr($class_col); ?>">
                <div class="entry-content">
                    <div class="row">
                        <div class="large-12 columns">
                            <h3 class="entry-title">
                                <a href="<?php echo esc_url($link); ?>" title="<?php echo esc_attr($title); ?>" rel="bookmark">
                                    <?php echo $title; ?>
                                </a>
                            </h3>
                        </div>
                        
                        <?php if($show_author_info || $show_date_info) : ?>
                            <div class="large-12 columns text-left info-wrap margin-bottom-20">
                                <?php if($show_author_info) : ?>
                                    <a href="<?php echo esc_url($link_author); ?>" title="<?php echo esc_attr($author); ?>">
                                        <span class="meta-author inline-block">
                                            <i class="pe-7s-user"></i>
                                            <?php echo $author; ?>
                                        </span>
                                    </a>
                                <?php endif; ?>

                                <?php if($show_date_info) : ?>
                                    <a href="<?php echo esc_url($link_date); ?>" title="<?php echo esc_attr($date_post); ?>">
                                        <span class="post-date inline-block">
                                            <i class="pe-7s-date"></i>
                                            <?php echo $date_post; ?>
                                        </span>
                                    </a>
                                <?php endif; ?>
                            </div>
                        <?php endif; ?>
                        
                        <?php if($show_desc_blog) : ?>
                            <div class="large-12 columns">
                                <div class="entry-summary">
                                    <?php the_excerpt(); ?>
                                </div>
                            </div>
                        <?php endif; ?>

                        <?php if($show_readmore) : ?>
                            <div class="large-12 columns">
                                <div class="entry-readmore">
                                    <a href="<?php echo esc_url($link); ?>">
                                        <?php echo esc_html__('CONTINUE READING  &#10142;', 'elessi-theme'); ?>
                                    </a>
                                </div>
                            </div>
                        <?php endif; ?>
                        
                        <?php
                        $tags_list = $show_tag_info ? get_the_tag_list('', esc_html__(', ', 'elessi-theme')) : false;
                        if($show_cat_info || ($show_tag_info && $tags_list)) : ?>
                            <div class="large-12 columns margin-top-20">
                                <?php if ('post' == get_post_type()) : ?>
                                    <?php if ($show_cat_info) : ?>
                                        <?php $categories_list = get_the_category_list(esc_html__(', ', 'elessi-theme')); ?>
                                        <span class="cat-links">
                                            <?php printf(esc_html__('Posted in %1$s', 'elessi-theme'), $categories_list); ?>
                                        </span>
                                    <?php endif; ?>

                                    <?php
                                    if ($show_tag_info) :
                                        if ($tags_list) :
                                            ?>
                                            <?php if ($show_cat_info) : ?>
                                                <span class="sep"> | </span>
                                            <?php endif; ?>
                                            <span class="tags-links">
                                                <?php printf(esc_html__('Tagged %1$s', 'elessi-theme'), $tags_list); ?>
                                            </span>
                                        <?php endif; ?>
                                    <?php endif; ?>
                                <?php endif; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </article>
</div>
