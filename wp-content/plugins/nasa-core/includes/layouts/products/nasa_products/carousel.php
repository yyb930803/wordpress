<?php
$_delay = 0;
$_delay_item = (isset($nasa_opt['delay_overlay']) && (int) $nasa_opt['delay_overlay']) ? (int) $nasa_opt['delay_overlay'] : 100;

$cat_info = apply_filters('nasa_loop_categories_show', false);
$description_info = apply_filters('nasa_loop_short_description_show', false);

$data_margin = isset($data_margin) ? (int) $data_margin : 10;
$height_auto = !isset($height_auto) ? 'false' : $height_auto;
$auto_slide = isset($auto_slide) ? $auto_slide : 'false';
$style_row = (!isset($style_row) || !in_array((int) $style_row, array(1, 2, 3))) ? 1 : (int) $style_row;
$is_deals = $type == 'deals' ? true : false;
$shop_url = isset($shop_url) ? $shop_url : false;
$arrows = isset($arrows) ? $arrows : 0;
$dots = isset($dots) ? $dots : 'false';

$term = (isset($cat) && (int)$cat) ? get_term_by('id', (int) $cat, 'product_cat') : null;
$link_shortcode = null;
$parent_term = null;
$parent_term_link = '#';
if($shop_url == 1) {
    if($term) {
        $parent_term = $term->parent ? get_term_by("id", $term->parent, "product_cat") : $parent_term;
        $parent_term_link = $parent_term ? get_term_link($parent_term, 'product_cat') : $parent_term_link;
        $link_shortcode = get_term_link($term, 'product_cat');
    } else {
        $permalinks = get_option('woocommerce_permalinks');
        $shop_page_id = wc_get_page_id('shop');
        $shop_page = get_post($shop_page_id);

        $shop_page_url = get_permalink($shop_page_id);
        $shop_page_title = get_the_title($shop_page_id);
        // If permalinks contain the shop page in the URI prepend the breadcrumb with shop
        if ($shop_page_id > 0 && strstr($permalinks['product_base'], '/' . $shop_page->post_name) && get_option('page_on_front') !== $shop_page_id) {
            $link_shortcode = get_permalink($shop_page);
        }
    }
}

$catName = isset($term->name) ? ' ' . $term->name : '';
$pos_nav = (!isset($pos_nav) || $pos_nav != 'left') ? 'top' : 'left';

if($pos_nav == 'left' && $style_row == 1) :
    if((!isset($title_shortcode) || trim($title_shortcode) == '') && $pos_nav == 'left') {
        switch ($type):
            case 'best_selling':
                $title_shortcode = esc_html__('Best Selling', 'nasa-core');
                break;
            case 'featured_product':
                $title_shortcode = esc_html__('Featured', 'nasa-core');
                break;
            case 'top_rate':
                $title_shortcode = esc_html__('Top Rate', 'nasa-core');
                break;
            case 'on_sale':
                $title_shortcode = esc_html__('On Sale', 'nasa-core');
                break;
            case 'recent_review':
                $title_shortcode = esc_html__('Recent Review', 'nasa-core');
                break;
            case 'deals':
                $title_shortcode = esc_html__('Deals', 'nasa-core');
                break;
            case 'recent_product':
            default:
                $title_shortcode = esc_html__('Recent', 'nasa-core');
                break;
        endswitch;

        $title_shortcode = $catName != '' ? $title_shortcode . ' ' . $catName : $title_shortcode;
    }
    ?>
    <div class="row nasa-slider-wrap nasa-warp-slide-nav-side">
        <div class="large-3 columns nasa-rtl">
            <div class="nasa-slide-left-info-wrap">
                <?php if($parent_term) : ?>
                    <h4 class="nasa-shortcode-parent-term">
                        <a href="<?php echo esc_url($parent_term_link); ?>" title="<?php echo esc_attr($parent_term->name); ?>">
                            <?php echo $parent_term->name; ?>
                        </a>
                    </h4>
                <?php endif; ?>
                <h3 class="nasa-shortcode-title-slider">
                    <?php echo $title_shortcode; ?>
                </h3>

                <?php if($arrows == 1) : ?>
                    <div class="nasa-nav-carousel-wrap nasa-clear-both">
                        <div class="nasa-nav-carousel-prev nasa-nav-carousel-div">
                            <a class="nasa-nav-icon-slider" href="javascript:void(0);" data-do="prev">
                                <span class="icon-nasa-left-arrow"></span>
                            </a>
                        </div>
                        <div class="nasa-nav-carousel-next nasa-nav-carousel-div">
                            <a class="nasa-nav-icon-slider" href="javascript:void(0);" data-do="next">
                                <span class="icon-nasa-right-arrow"></span>
                            </a>
                        </div>
                    </div>
                <?php endif; ?>

                <?php if($link_shortcode) : ?>
                    <a href="<?php echo esc_url($link_shortcode); ?>" title="<?php echo esc_html__('View more', 'nasa-core') . ($catName != '' ? ' ' . esc_attr($catName) : ''); ?>" class="nasa-view-more-slider">
                        <?php echo esc_html__('View more', 'nasa-core'); ?>
                    </a>
                <?php endif; ?>
            </div>
        </div>
        <div class="large-9 columns">
            <div class="group-slider">
                <div
                    class="slider products-group nasa-slider owl-carousel products grid"
                    data-margin="<?php echo esc_attr($data_margin); ?>"
                    data-margin-small="0"
                    data-margin-medium="0"
                    data-columns="<?php echo esc_attr($columns_number); ?>"
                    data-columns-small="<?php echo esc_attr($columns_number_small); ?>"
                    data-columns-tablet="<?php echo esc_attr($columns_number_tablet); ?>"
                    data-autoplay="<?php echo esc_attr($auto_slide); ?>"
                    data-loop="<?php echo $auto_slide == 'true' ? 'true' : 'false'; ?>"
                    data-height-auto="<?php echo $height_auto; ?>"
                    data-dot="<?php echo esc_attr($dots); ?>"
                    data-disable-nav="true">
                    <?php
                    while ($loop->have_posts()) :
                        $loop->the_post();

                        wc_get_template('content-product.php', array(
                            'is_deals' => $is_deals,
                            '_delay' => $_delay,
                            '_delay_item' => $_delay_item,
                            'wrapper' => 'div',
                            'show_in_list' => false,
                            'cat_info' => $cat_info,
                            'description_info' => $description_info
                        ));
                        $_delay += $_delay_item;
                    endwhile;
                    ?>
                </div>
            </div>
        </div>
    </div>
<?php else :
    $title_align = !isset($title_align) || $title_align != 'right' ? 'left' : 'right';
    if(isset($title_shortcode) && $title_shortcode != '') : ?>
        <div class="row margin-bottom-20 nasa-warp-slide-nav-top<?php echo ' title-align-' . $title_align; ?>">
            <div class="large-12 columns">
                <div class="nasa-title nasa_type_2">
                    <h3 class="nasa-heading-title">
                        <?php if($parent_term) : ?>
                            <span class="hidden-tag nasa-parent-cat">
                                <a href="<?php echo esc_url($parent_term_link); ?>" title="<?php echo esc_attr($parent_term->name); ?>"><?php echo $parent_term->name; ?></a>
                            </span>
                        <?php endif; ?>
                        <span class="nasa-title-wrap nasa-shortcode-title">
                            <?php echo esc_attr($title_shortcode); ?>
                        </span>
                    </h3>
                    <hr class="nasa-separator" />
                </div>
            </div>
        </div>
    <?php endif; ?>

    <div class="nasa-relative nasa-slider-wrap nasa-slide-style-product-carousel nasa-warp-slide-nav-top<?php echo ' title-align-' . $title_align; ?>">
        <?php if($link_shortcode) : ?>
            <div class="nasa-sc-product-btn">
                <a href="<?php echo esc_url($link_shortcode); ?>" title="<?php echo esc_html__('Shop all', 'nasa-core') . ($catName != '' ? ' ' . esc_attr($catName) : ''); ?>" class="nasa-view-more-slider">
                    <?php echo esc_html__('Shop all', 'nasa-core') . ($catName != '' ? ' ' . esc_attr($catName) : ''); ?>
                </a>
            </div>
        <?php endif; ?>
        
        <?php if($arrows == 1) : ?>
            <div class="nasa-nav-carousel-wrap">
                <div class="nasa-nav-carousel-prev nasa-nav-carousel-div">
                    <a class="nasa-nav-icon-slider" href="javascript:void(0);" data-do="prev">
                        <span class="pe-7s-angle-left"></span>
                    </a>
                </div>
                <div class="nasa-nav-carousel-next nasa-nav-carousel-div">
                    <a class="nasa-nav-icon-slider" href="javascript:void(0);" data-do="next">
                        <span class="pe-7s-angle-right"></span>
                    </a>
                </div>
            </div>
        <?php endif; ?>
        
        <div class="group-slider">
            <div
                class="slider products-group nasa-slider owl-carousel products grid<?php echo $style_row > 1 ? ' nasa-slide-double-row' : ''; ?>"
                data-margin="<?php echo esc_attr($data_margin); ?>"
                data-margin-small="0"
                data-margin-medium="0"
                data-columns="<?php echo esc_attr($columns_number); ?>"
                data-columns-small="<?php echo esc_attr($columns_number_small); ?>"
                data-columns-tablet="<?php echo esc_attr($columns_number_tablet); ?>"
                data-autoplay="<?php echo esc_attr($auto_slide); ?>"
                data-loop="<?php echo $auto_slide == 'true' ? 'true' : 'false'; ?>"
                data-height-auto="<?php echo $height_auto; ?>"
                data-dot="<?php echo esc_attr($dots); ?>"
                data-disable-nav="true">
                <?php
                $k = 0;
                echo $style_row > 1 ? '<div class="nasa-wrap-column">' : '';
                while ($loop->have_posts()) :
                    $loop->the_post();
                    echo ($k && $style_row > 1 && ($k%$style_row == 0)) ? '<div class="nasa-wrap-column">' : '';

                    wc_get_template('content-product.php', array(
                        'is_deals' => $is_deals,
                        '_delay' => $_delay,
                        '_delay_item' => $_delay_item,
                        'wrapper' => 'div',
                        'show_in_list' => false,
                        'cat_info' => $cat_info,
                        'description_info' => $description_info
                    ));

                    if($k && $style_row > 1 && (($k+1)%$style_row == 0)) :
                        $_delay += $_delay_item;
                        echo '</div>';
                    endif;

                    if($style_row == 1) :
                        $_delay += $_delay_item;
                    endif; 

                    $k++;
                endwhile;
                echo ($k && $style_row > 1 && $k%$style_row != 0) ? '</div>' : '';
                ?>
            </div>
        </div>
    </div>
<?php
endif;
