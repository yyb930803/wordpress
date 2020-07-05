<article id="post-<?php echo (int) $postId; ?>" <?php post_class(); ?>>
    <?php if (has_post_thumbnail()) : ?>
        <div class="entry-image nasa-blog-img">
            <a href="<?php echo esc_url($link); ?>" title="<?php echo esc_attr($title); ?>">
                <?php the_post_thumbnail('large'); ?>
                <div class="image-overlay"></div>
            </a>
        </div>
    <?php endif; ?>

    <header class="entry-header">
        <div class="row">

            <div class="large-12 columns">
                <h3 class="entry-title">
                    <a href="<?php echo esc_url($link); ?>" title="<?php echo esc_attr($title); ?>" rel="bookmark">
                        <?php echo $title; ?>
                    </a>
                </h3>
            </div>

            <?php if($show_author_info || $show_date_info) : ?>
                <div class="large-12 columns text-left info-wrap margin-bottom-10">
                    <?php if($show_author_info) : ?>
                        <a href="<?php echo esc_url($link_author); ?>" title="<?php echo esc_attr__('Posted By ', 'elessi-theme') . esc_attr($author); ?>">
                            <span class="meta-author inline-block">
                                <i class="pe-7s-user"></i> <?php esc_html_e('Posted by ', 'elessi-theme'); ?><?php echo ($author); ?>
                            </span>
                        </a>
                    <?php endif; ?>

                    <?php if($show_date_info) : ?>
                        <a href="<?php echo esc_url($link_date); ?>" title="<?php echo esc_attr__('Posts in ', 'elessi-theme') . esc_attr($date_post); ?>">
                            <span class="post-date inline-block">
                                <i class="pe-7s-date"></i>
                                <?php echo $date_post; ?>
                            </span>
                        </a>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
            
            <div class="large-12 columns">
                <div class="entry-summary">
                    <?php the_excerpt(); ?>
                </div>
            </div>

            <?php if($show_readmore) : ?>
                <div class="large-12 columns">
                    <div class="entry-readmore">
                        <a href="<?php echo esc_url($link); ?>">
                            <?php echo esc_html__('CONTINUE READING  &#10142;', 'elessi-theme'); ?>
                        </a>
                    </div>
                </div>
            <?php endif; ?>

        </div>
    </header>

    <?php
    $tags_list = $show_tag_info ? get_the_tag_list('', esc_html__(', ', 'elessi-theme')) : false;
    if($show_cat_info || $tags_list) : ?>
        <footer class="entry-meta">
            <?php if ('post' == get_post_type()) : ?>
                <?php if ($show_cat_info) : ?>
                    <?php $categories_list = get_the_category_list(esc_html__(', ', 'elessi-theme')); ?>
                    <span class="cat-links">
                        <?php printf(esc_html__('Posted in %1$s', 'elessi-theme'), $categories_list); ?>
                    </span>
                <?php endif; ?>

                <?php if ($tags_list) : ?>
                    <?php if ($show_cat_info) : ?>
                        <span class="sep"> | </span>
                    <?php endif; ?>
                    <span class="tags-links">
                        <?php printf(esc_html__('Tagged %1$s', 'elessi-theme'), $tags_list); ?>
                    </span>
                <?php endif; ?>
            <?php endif; ?>
        </footer>
    <?php endif; ?>
</article>
