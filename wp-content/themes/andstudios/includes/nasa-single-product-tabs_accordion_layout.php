<?php
if (!defined('ABSPATH')) :
    exit; // Exit if accessed directly
endif;
?>

<?php if($comboContent) : ?>
    <div class="nasa-accordion-title">
        <a class="nasa_combo_tab nasa-accordion hidden-tag active first" href="javascript:void(0);" data-id="accordion-combo-gift">
            <?php echo esc_html__('Bundle product', 'elessi-theme'); ?>
        </a>
    </div>
    <div class="nasa-panel hidden-tag nasa-content-combo-gift active first" id="nasa-secion-accordion-combo-gift">
        <div class="row nasa-combo-row no-border">
            <?php echo $comboContent; ?>
        </div>
    </div>
<?php endif; ?>

<?php if (!empty($tabs)) :
    $k_acc = $comboContent ? 1 : 0;
    $countTabs = $comboContent ? (count($tabs) - 1) : count($tabs);

    foreach ($tabs as $key => $tab) :
        $class_item = $k_acc == 0 ? ' active first' : '';
        $class_item .= $k_acc == $countTabs ? ' last' : '';
        $class_item .= ' nasa-accordion-' . $key;
        ?>
            <div class="nasa-accordion-title">
                <a class="nasa-single-product-tab nasa-accordion hidden-tag<?php echo esc_attr($class_item); ?>" href="javascript:void(0);" data-id="accordion-<?php echo esc_attr($key); ?>">
                    <?php echo apply_filters('woocommerce_product_' . $key . '_tab_title', $tab['title'], $key); ?>
                </a>
            </div>

            <div class="nasa-content-<?php echo esc_attr($key); ?> nasa-panel hidden-tag<?php echo ($k_acc == 0) ? ' active first' : ''; ?>" id="nasa-secion-accordion-<?php echo esc_attr($key); ?>">
                <?php if ($key == 'description' && $specifi_desc): ?>
                    <div class="nasa-panel-block">
                        <?php call_user_func($tab['callback'], $key, $tab); ?>
                    </div>
                    <?php if (trim($specifications) != '') : ?>
                        <div class="nasa-panel-block nasa-content-specifications">
                            <?php echo $specifications; ?>
                        </div>
                    <?php endif; ?>
                <?php
                else:
                    call_user_func($tab['callback'], $key, $tab);
                endif;
                ?>
            </div>

            <?php if ($key == 'description' && (trim($specifications) != '' && !$specifi_desc)) : ?>
                <div class="nasa-accordion-title">
                    <a class="nasa-single-product-accordion specifications_accordion nasa-accordion hidden-tag<?php echo esc_attr($class_item); ?>" href="javascript:void(0);" data-id="accordion-<?php echo esc_attr($key); ?>">
                        <?php echo esc_html__('Specifications', 'elessi-theme'); ?>
                    </a>
                </div>

                <div class="nasa-panel hidden-tag nasa-content-specifications" id="nasa-secion-accordion-specifications">
                    <?php echo $specifications; ?>
                </div>
                <?php
            endif;

        $k_acc++;
    endforeach;
endif;
