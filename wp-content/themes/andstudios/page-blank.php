<?php
/*
  Template name: Full Width (100%)
 */
get_header(); ?>

<?php if (has_excerpt()) : ?>
    <div class="page-header">
        <?php the_excerpt(); ?>
    </div>
<?php endif; ?>

<?php while (have_posts()) :
    the_post();
    the_content();
endwhile; ?>

<?php
get_footer();
