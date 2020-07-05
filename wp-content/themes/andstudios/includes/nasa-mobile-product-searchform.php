<?php
/**
 * The template for displaying search forms mobile in nasatheme
 *
 * @package     nasatheme
 * @version     1.0.0
 */
$_id = rand();

$url = home_url('/');
$postType = apply_filters('nasa_mobile_search_post_type', 'product');
$classInput = 'search-field search-input';
$placeHolder = esc_attr__("Start typing ...", 'elessi-theme');
$classWrap = 'nasa-searchform';
if ($postType === 'product') {
    $classInput .= ' live-search-input';
    $classWrap = 'nasa-ajaxsearchform';
    $placeHolder = esc_attr__("I'm shopping for ...", 'elessi-theme');
}
?>

<div class="search-wrapper <?php echo esc_attr($classWrap); ?>-container <?php echo esc_attr($_id); ?>_container">
    <form method="get" class="<?php echo esc_attr($classWrap); ?>" action="<?php echo esc_url($url) ?>">
        <div class="search-control-group control-group">
            <label class="hidden-tag"><?php esc_html_e('Search here', 'elessi-theme'); ?></label>
            <input id="nasa-input-<?php echo esc_attr($_id); ?>" type="text" class="<?php echo esc_attr($classInput); ?>" value="<?php echo get_search_query();?>" name="s" placeholder="<?php echo $placeHolder; ?>" />
            <input type="hidden" class="search-param" name="post_type" value="<?php echo esc_attr($postType); ?>" />
            <div class="nasa-vitual-hidden">
                <input type="submit" name="page" value="search" />
            </div>
        </div>
    </form>
</div>
