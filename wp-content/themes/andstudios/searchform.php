<?php
/**
 * The template for displaying search forms in nasatheme
 *
 * @package nasatheme
 */
global $nasa_opt;
$_id = rand();

$postType = apply_filters('nasa_search_post_type', 'post');
$classInput = 'search-field search-input';
$placeHolder = esc_attr__("Start typing ...", 'elessi-theme');
$hotkeys = '';
$classWrap = 'nasa-searchform';
if ($postType === 'product') {
    $classInput .= ' live-search-input';
    $classWrap = 'nasa-ajaxsearchform';
    $placeHolder = esc_attr__("Cerca ...", 'elessi-theme');
    
    if (isset($nasa_opt['hotkeys_search']) && trim($nasa_opt['hotkeys_search']) !== '') {
        $hotkeys = ' data-suggestions="' . esc_attr($nasa_opt['hotkeys_search']) . '"';
    } 
}
?>
<div class="search-wrapper <?php echo esc_attr($classWrap); ?>-container <?php echo esc_attr($_id); ?>_container">
    <div class="nasa-search-form-warp">
        <form method="get" class="<?php echo esc_attr($classWrap); ?>" action="<?php echo esc_url(home_url('/')); ?>">
            <div class="search-control-group control-group">
                <label class="sr-only screen-reader-text">
                    <?php esc_html_e('Search here', 'elessi-theme'); ?>
                </label>
                <input id="nasa-input-<?php echo esc_attr($_id); ?>" type="text" class="<?php echo esc_attr($classInput); ?>" value="<?php echo get_search_query(); ?>" name="s" placeholder="<?php echo $placeHolder; ?>"<?php echo $hotkeys;?> />
                <span class="nasa-icon-submit-page">
                    <button class="nasa-submit-search hidden-tag">
                        <?php esc_attr_e('Search', 'elessi-theme'); ?>
                    </button>
                </span>
                <input type="hidden" name="page" value="search" />
                <input type="hidden" name="post_type" value="<?php echo esc_attr($postType); ?>" />
            </div>
        </form>
    </div>
    
    <a href="javascript:void(0);" title="<?php esc_attr_e('Close search', 'elessi-theme'); ?>" class="nasa-close-search"><i class="pe-7s-close"></i></a>
</div>
