<?php
if (!defined('ABSPATH')) :
    exit; // Exit if accessed directly
endif;

$align_tab = isset($nasa_opt['tab_align_info']) ? 'text-' . $nasa_opt['tab_align_info'] : 'text-center';
?>
<div class="nasa-tab-wrap <?php echo esc_attr($align_tab); ?>">
    <ul class="nasa-tabs">
        <?php if($comboContent) : ?>
            <li class="nasa-single-product-tab nasa_combo_tab nasa-tab active first">
                <a href="javascript:void(0);" data-id="#nasa-tab-combo-gift">
                    <h5>
                        <?php echo esc_html__('Bundle product', 'elessi-theme'); ?>
                    </h5>
                </a>
            </li>
        <?php endif; ?>
        <?php if (!empty($tabs)) :
            
            $k_title = $comboContent ? 1 : 0;
            $countTabs = $comboContent ? (count($tabs) - 1) : count($tabs);
            
            foreach ($tabs as $key => $tab) :
                $class_item = $k_title == 0 ? ' active first' : '';
                $class_item .= $k_title == $countTabs ? ' last' : '';
                ?>
                <li class="nasa-single-product-tab <?php echo esc_attr($key); ?>_tab nasa-tab<?php echo esc_attr($class_item); ?>">
                    <a href="javascript:void(0);" data-id="#nasa-tab-<?php echo esc_attr($key); ?>">
                        <h5>
                            <?php echo apply_filters('woocommerce_product_' . $key . '_tab_title', $tab['title'], $key); ?>
                        </h5>
                    </a>
                </li>
                <?php if ($key == 'description' && (trim($specifications) != '' && !$specifi_desc)) : ?>
                    <li class="nasa-single-product-tab specifications_tab nasa-tab">
                        <a href="javascript:void(0);" data-id="#nasa-tab-specifications">
                            <h5>
                                <?php echo esc_html__('Specifications', 'elessi-theme'); ?>
                            </h5>
                        </a>
                    </li>
                    <?php
                endif;
                $k_title++;
            endforeach;
        endif; ?>
        <li class="nasa-slide-tab"></li>
    </ul>
</div>
<div class="nasa-panels">
    <?php if ($comboContent) : ?>
        <div class="nasa-panel entry-content nasa-content-combo-gift active" id="nasa-tab-combo-gift">
            <div class="row nasa-combo-row no-border">
                <?php echo $comboContent; ?>
            </div>
        </div>
    <?php endif; ?>
    <?php
    if (!empty($tabs)) :
        $k_tab = $comboContent ? 1 : 0;
        foreach ($tabs as $key => $tab) :
            ?>
            <div class="nasa-panel nasa-content-<?php echo esc_attr($key); ?> <?php echo ($k_tab == 0) ? ' active' : ''; ?>" id="nasa-tab-<?php echo esc_attr($key); ?>">
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
            <?php 
            if ($key == 'description' && (trim($specifications) != '') && !$specifi_desc) : ?>
                <div class="nasa-panel nasa-content-specifications" id="nasa-tab-specifications">
                    <?php echo $specifications; ?>
                </div>
            <?php
            endif;
            $k_tab++;
        endforeach;
    endif; ?>
</div>
