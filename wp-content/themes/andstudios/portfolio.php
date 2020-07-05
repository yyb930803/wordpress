<?php
/**
 * Template Name: Portfolio
 *
 */

if(!NASA_CORE_ACTIVED || !isset($nasa_opt['enable_portfolio']) || !$nasa_opt['enable_portfolio']) :
    include_once ELESSI_THEME_PATH . '/404.php';
    exit(); // Exit if nasa-core has not actived OR disable Fortfolios
endif;

$nasa_columns = (isset($nasa_opt['portfolio_columns']) && (int)$nasa_opt['portfolio_columns']) ?
    (int) $nasa_opt['portfolio_columns'] : 5;

if (isset($_GET['columns'])):
    switch ($_GET['columns']) :
        case '2' :
        case '3' :
        case '5' :
            $nasa_columns = (int) $_GET['columns'];
            break;
        case '4':
        default :
            $nasa_columns = 4;
            break;
    endswitch;
endif;

$cat = get_query_var('portfolio_category') ? get_queried_object_id() : 0;
$categories = get_terms('portfolio_category');
$catsCount = count($categories);

get_header();
?>

<div class="row">
    <div class="content large-12 columns margin-top-35 margin-bottom-40">
        <div class="nasa-tabs-content nasa-classic-style nasa-classic-2d nasa-tabs-no-border">
            <div class="nasa-portfolio-wrap margin-bottom-20">
                <?php if(!$cat):?>
                    <div class="nasa-tabs-wrap margin-bottom-15 text-left rtl-text-right">
                        <ul class="nasa-tabs portfolio-tabs">
                            <li class="description_tab nasa-tab first active">
                                <a href="javascript:void(0);" data-filter="*" class="nasa-a-tab">
                                    <h5 class="nasa-uppercase"><?php esc_html_e('Show All', 'elessi-theme'); ?></h5>
                                </a>
                            </li>
                            <li class="separator"></li>
                            <?php if($catsCount > 0):
                                foreach($categories as $category) :?>
                                    <li class="description_tab nasa-tab">
                                        <a href="javascript:void(0);" data-filter=".sort-<?php echo esc_attr($category->slug); ?>" class="nasa-a-tab">
                                            <h5 class="nasa-uppercase"><?php echo $category->name; ?></h5>
                                        </a>
                                    </li>
                                    <li class="separator"></li>
                                <?php endforeach;?>
                            <?php endif;?>
                        </ul>
                    </div>
                <?php endif;?>

                <div class="row">
                    <div class="large-12 columns">
                        <ul class="margin-left-0 margin-right-0 portfolio portfolio-list large-block-grid-<?php echo (int) $nasa_columns; ?> small-block-grid-2 medium-block-grid-3" data-columns="<?php echo (int) $nasa_columns; ?>">
                        </ul>
                    </div>
                </div>
                
                <div class="row">
                    <div class="large-12 columns">
                        <div class="text-center load-more loadmore-portfolio margin-top-20" data-category="<?php echo (int) $cat; ?>">
                            <span><?php esc_html_e('LOAD MORE ...', 'elessi-theme'); ?></span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php if(!$cat): ?>
    <div id="content">
        <?php while (have_posts()) :
            the_post();
            the_content();
        endwhile; ?>
    </div>
<?php endif; ?>

<?php
get_footer();
