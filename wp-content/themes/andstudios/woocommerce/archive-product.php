<?php
/**
 *
 * Override this template by copying it to yourtheme/woocommerce/archive-product.php
 *
 * @author      WooThemes
 * @package     WooCommerce/Templates
 * @version     3.4.0
 */
if (!defined('ABSPATH')){
    exit; // Exit if accessed directly
}
global $product, $nasa_opt, $wp_query;
$typeView = !isset($nasa_opt['products_type_view']) ?
    'grid' : ($nasa_opt['products_type_view'] == 'list' ? 'list' : 'grid');https://winefully.com/wp-admin/theme-editor.php?file=style.css&theme=andstudios

$nasa_opt['products_per_row'] = isset($nasa_opt['products_per_row']) && (int) $nasa_opt['products_per_row'] ?
    (int) $nasa_opt['products_per_row'] : 4;
$nasa_opt['products_per_row'] = $nasa_opt['products_per_row'] > 5 || $nasa_opt['products_per_row'] < 3 ? 4 : $nasa_opt['products_per_row'];
$nasa_change_view = !isset($nasa_opt['enable_change_view']) || $nasa_opt['enable_change_view'] ? true : false;
$typeShow = $typeView == 'grid' ? ($typeView . '-' . ((int) $nasa_opt['products_per_row'])) : 'list';
$typeShow = $nasa_change_view && isset($_COOKIE['gridcookie']) ? $_COOKIE['gridcookie'] : $typeShow;

$nasa_cat_obj = $wp_query->get_queried_object();
$nasa_term_id = 0;
$nasa_type_page = 'product_cat';
$nasa_href_page = '';
if (isset($nasa_cat_obj->term_id) && isset($nasa_cat_obj->taxonomy)) {
    $nasa_term_id = (int) $nasa_cat_obj->term_id;
    $nasa_type_page = $nasa_cat_obj->taxonomy;
    $nasa_href_page = esc_url(get_term_link($nasa_cat_obj, $nasa_type_page));
}
$nasa_ajax_product = true;
if((isset($nasa_opt['disable_ajax_product']) && $nasa_opt['disable_ajax_product']) || get_option('woocommerce_shop_page_display', '') != '' || get_option('woocommerce_category_archive_display', '') != '') :
    $nasa_ajax_product = false;
endif;
defined('NASA_AJAX_SHOP') or define('NASA_AJAX_SHOP', $nasa_ajax_product);
$nasa_sidebar = isset($nasa_opt['category_sidebar']) ? $nasa_opt['category_sidebar'] : 'left-classic';
$nasa_has_get_sidebar = false;

if (isset($_REQUEST['sidebar'])):
    $nasa_has_get_sidebar = true;

    switch ($_REQUEST['sidebar']) :
        case 'left' :
            $nasa_sidebar = 'left';
            break;
        
        case 'right' :
            $nasa_sidebar = 'right';
            break;
        
        case 'right-classic' :
            $nasa_sidebar = 'right-classic';
            break;
        
        case 'no' :
            $nasa_sidebar = 'no';
            break;
        
        case 'top' :
            $nasa_sidebar = 'top';
            break;
        
        case 'top-2' :
            $nasa_sidebar = 'top-2';
            break;
        
        case 'left-classic' :
        default:
            $nasa_sidebar = 'left-classic';
            break;
    endswitch;
endif;

$hasSidebar = true;
$topSidebar = false;
$topSidebar2 = false;
$topbarWrap_class = 'row filters-container nasa-filter-wrap';
$attr = 'nasa-products-page-wrap ';
switch ($nasa_sidebar):
    case 'right':
    case 'left':
        $attr .= 'large-12 columns has-sidebar';
        break;
    
    case 'right-classic':
        $attr .= 'large-9 columns left has-sidebar';
        break;
    
    case 'no':
        $hasSidebar = false;
        $attr .= 'large-12 columns no-sidebar';
        break;
    
    case 'top':
        $hasSidebar = false;
        $topSidebar = true;
        $attr .= 'large-12 columns no-sidebar top-sidebar';
        break;
    
    case 'top-2':
        $hasSidebar = false;
        $topSidebar2 = true;
        $topbarWrap_class .= ' top-bar-wrap-type-2';
        $attr .= 'large-12 columns no-sidebar top-sidebar-2';
        break;
    
    case 'left-classic':
    default :
        $attr .= 'large-9 columns right has-sidebar';
        break;
endswitch;

$nasa_recom_pos = isset($nasa_opt['recommend_product_position']) ? $nasa_opt['recommend_product_position'] : 'bot';

$layout_style = '';
if(
    (isset($_REQUEST['layout-style']) && $_REQUEST['layout-style'] == 'masonry') ||
    (isset($nasa_opt['products_layout_style']) && $nasa_opt['products_layout_style'] == 'masonry-isotope')
) :
    $layout_style = ' nasa-products-masonry-isotope';
endif;

get_header('shop');
?>
<div class="row fullwidth category-page">
	<div class="descrizionecategoria">
	
    <?php do_action('woocommerce_before_main_content'); ?>
    
		<div class="nasa_shop_description-wrap">
			<?php
			/**
			 * Hook: woocommerce_archive_description.
			 *
			 * @hooked woocommerce_taxonomy_archive_description - 10
			 * @hooked woocommerce_product_archive_description - 10
			 */
			do_action('woocommerce_archive_description');
			?>
		</div>
    	
	</div>
	<?php if ( is_product_category() ) { ?>
		<div class="fotocategoria2">
			<?php
	    $cat = $wp_query->get_queried_object();
	    $thumbnail_id = get_term_meta( $cat->term_id, 'cat_breadcrumb_bg', true );
	    $image = wp_get_attachment_url( $thumbnail_id );

	    if ( $image ) {
		    echo '<img src="' . $image . '" alt="' . $cat->name . '" />';
		}
		?>
		</div>
	<?php } ?>
	
    <div class="large-12 columns">
        <div class="<?php echo esc_attr($topbarWrap_class); ?>">
            <?php
            /**
             * Top Side bar Type 1
             */
            if($topSidebar) :
                $topSidebar_wrap = $nasa_change_view ? 'large-10 ' : 'large-12 ';

                if(!isset($nasa_opt['showing_info_top']) || $nasa_opt['showing_info_top']) :
                    echo '<div class="showing_info_top hidden-tag">';
                    do_action('nasa_shop_category_count');
                    echo '</div>';
                endif;
                ?>

                <div class="large-12 columns nasa-topbar-filter-wrap">
                    <div class="row">
						<div class="large-3 medium-3 columns colonnafiltrata">
							<p class="filtri">Filtri</p>
							<p class="filtrimobile">Filtri</p>
							<p class="filtrimobile2">Chiudi Filtri</p>
							<div class="allfiltri">
								<p class="all">Tutti</p>
							</div>
							<p class="back"><a class="cliccaback">&lt; Indietro</a></p>
						</div>
                        <div class="large-9 medium-9 columns nasa-filter-action">
                            <div class="nasa-labels-filter-top">
                                <input name="nasa-labels-filter-text" type="hidden" value="<?php echo (!isset($nasa_opt['top_bar_archive_label']) || $nasa_opt['top_bar_archive_label'] == 'Filter by:') ? esc_attr__('Filter by:', 'elessi-theme') : esc_attr($nasa_opt['top_bar_archive_label']); ?>" />
                                <input name="nasa-widget-show-more-text" type="hidden" value="<?php echo esc_attr__('More +', 'elessi-theme'); ?>" />
                                <input name="nasa-widget-show-less-text" type="hidden" value="<?php echo esc_attr__('Less -', 'elessi-theme'); ?>" />
                                <input name="nasa-limit-widgets-show-more" type="hidden" value="<?php echo (!isset($nasa_opt['limit_widgets_show_more']) || (int) $nasa_opt['limit_widgets_show_more'] < 0) ? '2' : (int) $nasa_opt['limit_widgets_show_more']; ?>" />
                                <a class="toggle-topbar-shop-mobile hidden-tag" href="javascript:void(0);">
                                    <i class="pe-7s-filter"></i><?php echo esc_attr__('&nbsp;Filters', 'elessi-theme'); ?>
                                </a>
                                <span class="nasa-labels-filter-accordion hidden-tag"></span>
                            </div>
                        </div>
						<?php
                do_action('nasa_top_sidebar_shop');
                
                ?>
						
                    </div>
                </div>
                

                <?php
                /* Sidebar TOP */
                
            /**
             * Top Side bar type 2
             */
            elseif ($topSidebar2) :
                ?>
                <div class="large-12 columns">
                    <div class="row">
                        <div class="large-4 medium-6 small-6 columns nasa-toggle-top-bar rtl-right">
                            <a class="nasa-toggle-top-bar-click" href="javascript:void(0);">
                                <i class="pe-7s-angle-down"></i> <?php esc_html_e('Filters', 'elessi-theme'); ?>
                            </a>
                        </div>
                        
                        <div class="large-4 columns nasa-topbar-change-view-wrap hide-for-medium hide-for-small text-center rtl-right">
                            <?php if($nasa_change_view) : ?>
                                <?php /* Change view ICONS */
                                do_action('nasa_change_view', $nasa_change_view, $typeShow); ?>
                            <?php endif; ?>
                        </div>
                        
                        <div class="large-4 medium-6 small-6 columns nasa-sort-by-action nasa-clear-none text-right rtl-text-left">
                            <ul class="sort-bar nasa-float-none margin-top-0">
                                <li class="sort-bar-text nasa-order-label hidden-tag">
                                    <?php esc_html_e('Sort by: ', 'elessi-theme'); ?>
                                </li>
                                <li class="nasa-filter-order filter-order">
                                    <?php do_action('woocommerce_before_shop_loop'); ?>
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>
                
                <div class="large-12 columns nasa-top-bar-2-content hidden-tag">
                    <?php do_action('nasa_top_sidebar_shop', '2'); ?>
                </div>
            
            <?php
            /**
             * TOGGLE Side bar in side (Off-Canvas)
             */
            elseif ($hasSidebar && in_array($nasa_sidebar, array('left', 'right'))) : ?>
                <div class="large-4 medium-6 small-6 columns nasa-toggle-layout-side-sidebar">
                    <div class="li-toggle-sidebar">
                        <a class="toggle-sidebar-shop" href="javascript:void(0);">
                            <i class="pe-7s-filter"></i><?php esc_html_e('Filters', 'elessi-theme'); ?>
                        </a>
                    </div>
                </div>
                
                <div class="large-4 columns hide-for-medium hide-for-small nasa-change-view-layout-side-sidebar nasa-min-height">
                    <?php /* Change view ICONS */
                    do_action('nasa_change_view', $nasa_change_view, $typeShow); ?>
                </div>
            
                <div class="large-4 medium-6 small-6 columns nasa-sort-bar-layout-side-sidebar nasa-clear-none nasa-min-height">
                    <ul class="sort-bar">
                        <li class="sort-bar-text nasa-order-label hidden-tag">
                            <?php esc_html_e('Sort by: ', 'elessi-theme'); ?>
                        </li>
                        <li class="nasa-filter-order filter-order">
                            <?php do_action('woocommerce_before_shop_loop'); ?>
                        </li>
                    </ul>
                </div>
            <?php
            
            /**
             * No | left-classic | right-classic side bar
             */
            else : ?>
                <div class="large-4 medium-6 columns hide-for-small">
                    <?php
                        if(!isset($nasa_opt['showing_info_top']) || $nasa_opt['showing_info_top']) :
                            echo '<div class="showing_info_top">';
                            do_action('nasa_shop_category_count');
                            echo '</div>';
                        else :
                            echo '&nbsp;';
                        endif;
                    ?>
                </div>
                
                <div class="large-4 columns hide-for-medium hide-for-small nasa-change-view-layout-side-sidebar nasa-min-height">
                    <?php /* Change view ICONS */
                    do_action('nasa_change_view', $nasa_change_view, $typeShow, $nasa_sidebar);
                    ?>
                </div>
            
                <div class="large-4 medium-6 small-12 columns nasa-clear-none nasa-sort-bar-layout-side-sidebar">
                    <ul class="sort-bar">
                        <?php if ($hasSidebar): ?>
                            <li class="li-toggle-sidebar">
                                <a class="toggle-sidebar" href="javascript:void(0);">
                                    <i class="pe-7s-filter"></i> <?php esc_html_e('Filters', 'elessi-theme'); ?>
                                </a>
                            </li>
                        <?php endif; ?>
                        
                        <li class="sort-bar-text nasa-order-label hidden-tag">
                            <?php esc_html_e('Sort by: ', 'elessi-theme'); ?>
                        </li>
                        <li class="nasa-filter-order filter-order">
                            <?php do_action('woocommerce_before_shop_loop'); ?>
                        </li>
                    </ul>
                </div>
            <?php endif; ?>
        </div>
    </div>
    
    <div class="nasa-archive-product-content">
        <?php if($topSidebar && (!isset($nasa_opt['top_bar_cat_pos']) || $nasa_opt['top_bar_cat_pos'] == 'left-bar')) :
            $attr .= ' nasa-has-push-cat';
            $class_cat_top = 'nasa-push-cat-filter';
            if(isset ($_REQUEST['push_cat_filter']) && $_REQUEST['push_cat_filter']) :
                $class_cat_top .= ' nasa-push-cat-show';
                $attr .= ' nasa-push-cat-show';
            endif;
            ?>
            <div class="<?php echo esc_attr($class_cat_top); ?>"></div>
        <?php endif; ?>
        
        <div class="<?php echo esc_attr($attr); ?>">

            <?php if(!isset($nasa_opt['disable_ajax_product_progress_bar']) || $nasa_opt['disable_ajax_product_progress_bar'] != 1) : ?>
                <div class="nasa-progress-bar-load-shop"><div class="nasa-progress-per"></div></div>
            <?php endif; ?>

            <?php if($nasa_recom_pos !== 'bot' && defined('NASA_CORE_ACTIVED') && NASA_CORE_ACTIVED) : ?>
                <span id="position-nasa-recommend-product" class="hidden-tag"></span>
                <?php do_action('nasa_recommend_product', $nasa_term_id); ?>
            <?php endif; ?>

            <div class="nasa-archive-product-warp<?php echo esc_attr($layout_style); ?>">
                <?php
                if (woocommerce_product_loop()) :
                    // Content products in shop
                    if(NASA_WOO_ACTIVED && version_compare(WC()->version, '3.3.0', "<")) :
                        do_action('nasa_archive_get_sub_categories');
                    endif;
                    
                    woocommerce_product_loop_start();
                    do_action('nasa_get_content_products', $nasa_sidebar);
                    woocommerce_product_loop_end();
                else :
                    echo '<div class="row"><div class="large-12 columns">';
                    do_action('woocommerce_no_products_found');
                    echo '</div></div>';
                endif;
                ?>
            </div>

            <?php
            /**
             * Hook: woocommerce_after_shop_loop.
             *
             * @hooked woocommerce_pagination - 10
             */
            do_action('woocommerce_after_shop_loop');
            ?>

            <?php if($nasa_recom_pos == 'bot' && defined('NASA_CORE_ACTIVED') && NASA_CORE_ACTIVED) :?>
                <span id="position-nasa-recommend-product" class="hidden-tag"></span>
                <?php do_action('nasa_recommend_product', $nasa_term_id); ?>
            <?php endif; ?>
        </div>

        <?php /* Sidebar LEFT | RIGHT */
        if ($hasSidebar && !$topSidebar && !$topSidebar2) :
            do_action('nasa_sidebar_shop', $nasa_sidebar);
        endif;
        
        do_action('woocommerce_after_main_content');
        ?>
    </div>
</div>

<?php
if($nasa_ajax_product) : ?>
    <div class="nasa-has-filter-ajax hidden-tag">
        <div class="current-cat hidden-tag">
            <a data-id="<?php echo (int) $nasa_term_id; ?>" href="<?php echo esc_url($nasa_href_page); ?>" class="nasa-filter-by-cat" id="nasa-hidden-current-cat" data-taxonomy="<?php echo esc_attr($nasa_type_page); ?>" data-sidebar="<?php echo esc_attr($nasa_sidebar); ?>"></a>
        </div>
        <p><?php esc_html_e('No products were found matching your selection.', 'elessi-theme'); ?></p>
        <?php if ($s = get_search_query()): ?>
            <input type="hidden" name="nasa_hasSearch" id="nasa_hasSearch" value="<?php echo esc_attr($s); ?>" />
        <?php endif; ?>
        <?php if($nasa_has_get_sidebar) : ?>
            <input type="hidden" name="nasa_getSidebar" id="nasa_getSidebar" value="<?php echo esc_attr($nasa_sidebar); ?>" />
        <?php endif; ?>
    </div>
<?php endif;

get_footer('shop');
