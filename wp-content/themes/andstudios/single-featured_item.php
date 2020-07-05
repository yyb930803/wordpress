<?php get_header(); ?>

<div class="container-wrap page-left-sidebar page-featured-item">
    <div class="row">
        <div id="content" class="large-3 columns left">
            <header class="entry-header">
                <div class="featured_item_cats">
                    <?php echo get_the_term_list(get_the_ID(), 'featured_item_category', '', ', ', ''); ?> 
                </div>
                <h1 class="entry-title"><?php the_title(); ?></h1>
                <div class="nasa-hr small"></div>
            </header>

            <div class="entry-summary">
                <?php the_excerpt(); ?>
                <?php echo shortcode_exists('share') ? do_shortcode('[share]') : ''; ?>
                <?php if (get_the_term_list(get_the_ID(), 'featured_item_tag')) : ?> 
                    <div class="item-tags">
                        <span><?php echo esc_html__('Tags:', 'elessi-theme'); ?></span><?php echo strip_tags(get_the_term_list(get_the_ID(), 'featured_item_tag', '', ' / ', '')); ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
        <div class="large-9 right columns">
            <div class="page-inner">
                <?php while (have_posts()) :
                    the_post();
                endwhile; ?>
            </div>
        </div>
    </div>
</div>

<?php
global $nasa_opt;
$nasa_opt['featured_items_related'] = isset($nasa_opt['featured_items_related']) ? $nasa_opt['featured_items_related'] : '';
$cat = get_the_terms(get_the_ID(), 'featured_item_category', '', ', ', '');
if ($nasa_opt['featured_items_related'] == 'style1') :
    echo do_shortcode('[featured_items_slider style="1" height="' . $nasa_opt['featured_items_related_height'] . '" cat="' . current($cat)->slug . '"]');
elseif ($nasa_opt['featured_items_related'] == 'style2') :
    echo do_shortcode('[featured_items_slider style="2" height="' . $nasa_opt['featured_items_related_height'] . '" cat="' . current($cat)->slug . '"]');
endif;

get_footer();
