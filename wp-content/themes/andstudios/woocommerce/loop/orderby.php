<?php
/**
 * Show options for ordering
 *
 * @author 	WooThemes
 * @package 	WooCommerce/Templates
 * @version     3.6.0
 */
if (!defined('ABSPATH')) :
    exit; // Exit if accessed directly
endif;

$default_sort = get_option('woocommerce_default_catalog_orderby', 'menu_order');
if(version_compare(WC()->version, '3.3.0', ">=")) : ?>
    <form class="woocommerce-ordering custom" method="get">
        <div class="select-wrapper">
            <select name="orderby" class="orderby" aria-label="<?php esc_attr_e('Shop order', 'elessi-theme'); ?>" data-default="<?php echo esc_attr($default_sort); ?>">
                <?php foreach ($catalog_orderby_options as $id => $name) : ?>
                    <option value="<?php echo esc_attr($id); ?>" <?php selected($orderby, $id); ?>><?php echo esc_html($name); ?></option>
                <?php endforeach; ?>
            </select>
            <input type="hidden" name="paged" value="1" />
            <?php wc_query_string_form_fields(null, array('orderby', 'submit', 'paged', 'product-page')); ?>
        </div>
    </form>
<?php
else:
    global $woocommerce, $wp_query;

    if (1 == $wp_query->found_posts || !woocommerce_products_will_display()) :
        return;
    endif;
    
    ?>
    <form class="woocommerce-ordering custom" method="get">
        <div class="select-wrapper">
            <select name="orderby" class="orderby" data-default="<?php echo esc_attr($default_sort); ?>">
                <?php
                $catalog_orderby = apply_filters('woocommerce_catalog_orderby', array(
                    'menu_order' => esc_html__('Default Sorting', 'elessi-theme'),
                    'popularity' => esc_html__('Popularity', 'elessi-theme'),
                    'rating' => esc_html__('Average rating', 'elessi-theme'),
                    'date' => esc_html__('Newness', 'elessi-theme'),
                    'price' => esc_html__('Price: low to high', 'elessi-theme'),
                    'price-desc' => esc_html__('Price: high to low', 'elessi-theme')
                ));

                if (get_option('woocommerce_enable_review_rating') == 'no') :
                    unset($catalog_orderby['rating']);
                endif;

                foreach ($catalog_orderby as $id => $name) :
                    echo '<option value="' . esc_attr($id) . '" ' . selected($orderby, $id, false) . '>' . esc_attr($name) . '</option>';
                endforeach;
                ?>
            </select>
        </div>
        <?php
        if (!isset($_GET['action']) || $_GET['action'] != 'nasa_products_page') :
            // Keep query string vars intact
            foreach ($_GET as $key => $val) :
                if ('orderby' == $key) :
                    continue;
                endif;

                if (is_array($val)) :
                    foreach ($val as $innerVal) :
                        echo '<input type="hidden" name="' . esc_attr($key) . '[]" value="' . esc_attr($innerVal) . '" />';
                    endforeach;
                else :
                    echo '<input type="hidden" name="' . esc_attr($key) . '" value="' . esc_attr($val) . '" />';
                endif;
            endforeach;
        endif;
        ?>
    </form>
<?php
endif;
