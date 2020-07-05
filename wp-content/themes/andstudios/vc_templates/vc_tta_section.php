<?php

if (!defined('ABSPATH')) {
    die('-1');
}

/**
 * Shortcode attributes
 * @var $atts
 * @var $content - shortcode content
 * @var $this WPBakeryShortCode_VC_Tta_Section
 */

$atts = vc_map_get_attributes($this->getShortcode(), $atts);
$class_tab = array('vc_tta-panel nasa-panel hidden-tag');
$class_acc = array('nasa-accordion hidden-tag');
if ((WPBakeryShortCode_VC_Tta_Section::$self_count == 0)) {
    $class_tab[] = 'active first';
    $class_acc[] = 'active first';
}

if($atts['el_class'] != '') {
    $class_tab[] = $atts['el_class'];
    $class_acc[] = $atts['el_class'];
}

$class_tab_str = implode(' ', $class_tab);
$class_acc_str = implode(' ', $class_acc);

$this->resetVariables($atts, $content);
WPBakeryShortCode_VC_Tta_Section::$self_count++;
WPBakeryShortCode_VC_Tta_Section::$section_info[] = $atts;
$isPageEditable = vc_is_page_editable();
$tabId = $this->getTemplateVariable('tab_id');
$output = '';

$output .= '<div class="nasa-accordion-title">';
$output .= '<a class="' . $class_acc_str . '" data-id="' . esc_attr($tabId) . '" href="javascript:void(0);">' . $this->getTemplateVariable('title') . '</a>';
$output .= '</div>';

$output .= '<div class="' . $class_tab_str . '" id="nasa-secion-' . esc_attr($tabId) . '">';
$output .= $this->getTemplateVariable('content');
$output .= '</div>';

echo $output;
