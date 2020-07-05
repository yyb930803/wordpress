<?php
/**
 * Single Product tabs / and sections
 *
 * @author 		WooThemes
 * @package 	WooCommerce/Templates
 * @version     2.4.0
 */
if (!defined('ABSPATH')) :
    exit; // Exit if accessed directly
endif;

/**
 * Filter tabs and allow third parties to add their own
 *
 * Each tab is an array containing title, callback and priority.
 * @see woocommerce_default_product_tabs()
 */
$tabs = apply_filters('woocommerce_product_tabs', array());
global $nasa_opt, $product;

$specifications = (!isset($nasa_opt['enable_specifications']) || $nasa_opt['enable_specifications'] == '1') ?
    elessi_get_product_meta_value($product->get_id(), 'nasa_specifications') : '';
$specifi_desc = (!isset($nasa_opt['merge_specifi_to_desc']) || $nasa_opt['merge_specifi_to_desc'] == '1') ? true : false;

$comboContent = elessi_combo_tab(false);

$tab_style = isset($nasa_opt['tab_style_info']) ? $nasa_opt['tab_style_info'] : '3d';
$class_tab = 'nasa-tabs-content woocommerce-tabs';
switch ($tab_style) :
    case 'accordion':
        $class_tab = 'nasa-accordions-content woocommerce-tabs nasa-no-global';
        break;
    case '2d':
        $class_tab .= ' nasa-classic-style nasa-classic-2d';
        break;
    case '2d-radius':
        $class_tab .= ' nasa-classic-style nasa-classic-2d nasa-tabs-no-border nasa-tabs-radius';
        break;
    case '2d-no-border':
        $class_tab .= ' nasa-classic-style nasa-classic-2d nasa-tabs-no-border';
        break;
    case 'slide':
        $class_tab .= ' nasa-slide-style';
        break;
    case '3d':
    default:
        $class_tab .= ' nasa-classic-style';
        break;
endswitch;

?>
<div class="product-details" id="nasa-single-product-tabs">
    <div class="<?php echo esc_attr($class_tab); ?>">
        <?php
        /**
         * Accordion layout style
         */
        if ($tab_style === 'accordion') :
            $file = ELESSI_CHILD_PATH . '/includes/nasa-single-product-tabs_accordion_layout.php';
            include is_file($file) ?
                $file : ELESSI_THEME_PATH . '/includes/nasa-single-product-tabs_accordion_layout.php';

        /**
         * Tabs layout style
         */
        else:
            $file = ELESSI_CHILD_PATH . '/includes/nasa-single-product-tabs_tab_layout.php';
            include is_file($file) ?
                $file : ELESSI_THEME_PATH . '/includes/nasa-single-product-tabs_tab_layout.php';
        endif;
        ?>
    </div>
</div>
