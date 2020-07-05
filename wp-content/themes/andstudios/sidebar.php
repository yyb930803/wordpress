<?php
/**
 * The Sidebar containing the main widget areas.
 *
 * @package nasatheme
 */
?>

<div id="secondary" class="widget-area" role="complementary">
    <?php
    do_action('before_sidebar');
    
    if (is_active_sidebar('blog-sidebar')) :
        dynamic_sidebar('blog-sidebar');
    endif;
    
    do_action('after_sidebar');
    ?>
</div>