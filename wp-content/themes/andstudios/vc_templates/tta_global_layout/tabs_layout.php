<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

$output = $this->getTemplateVariable('title');

$alignment = $alignment ? ' text-' . $alignment : '';
$el_class = (trim($el_class) != '') ? ' ' . $el_class : '';
$tabs_slide = (isset($tabs_display_type) && $tabs_display_type == 'slide') ? true : false;
$class_tabable = ' margin-bottom-15';
$class_a_click = 'nasa-a-tab';
$tab_bg = array();
$tab_color = $tab_color_h5 = array();
if (WPBakeryShortCode_VC_Tta_Section::$section_info):
    if($tabs_slide) :
        $el_class .= ' nasa-slide-style';
    else :
        $tabs_type = !isset($tabs_display_type) ? '2d-no-border' : $tabs_display_type;
        switch ($tabs_type) :
            
            case '2d':
                $tabs_type_class = ' nasa-classic-2d';
                break;
            
            case '3d':
                $tabs_type_class = ' nasa-classic-3d';
                break;
            
            case '2d-has-bg':
                $tabs_type_class = ' nasa-classic-2d nasa-tabs-no-border nasa-tabs-has-bg';
                $class_tabable = '';
                $tabs_bg_color = !isset($tabs_bg_color) ? '#efefef' : $tabs_bg_color;
                $tab_bg[] = 'background-color: ' . $tabs_bg_color;
                if(isset($tabs_text_color) && $tabs_text_color != '') {
                    $tab_color[] = 'color: ' . $tabs_text_color;
                    $tab_color_h5[] = 'border-color: ' . $tabs_text_color;
                    $class_a_click .= ' nasa-custom-text-color';
                }
                break;
                
            case '2d-radius':
                $tabs_type_class = ' nasa-classic-2d nasa-tabs-no-border nasa-tabs-radius';
                break;
            
            case '2d-no-border':
            default:
                $tabs_type_class = ' nasa-classic-2d nasa-tabs-no-border';
                break;
            
        endswitch;
        $el_class .= ' nasa-classic-style' . $tabs_type_class;
    endif;
    
    $class_tabable .= $alignment ? $alignment : '';
    
    $output .= '<div class="nasa-tabs-content' . esc_attr($el_class) . '">';
    $output .= '<div class="nasa-tabs-wrap' . esc_attr($class_tabable) . '">';
    $output .= '<ul class="nasa-tabs"' . (!empty($tab_bg) ? ' style="' . implode(';', $tab_bg) . '"' : '') . '>';
    $output .= $tabs_slide ? '<li class="nasa-slide-tab"></li>' : '';
    
    foreach (WPBakeryShortCode_VC_Tta_Section::$section_info as $k => $v):
        $custom_icon = false;
        if(trim($v["section_nasa_icon"]) !== '') {
            $v['add_icon'] = 'true';
            $custom_icon = true;
        }
        $title = esc_html($v['title']);
        $icon = '';
        if ($v['add_icon'] == 'true') {
            $icon = 'nasa-tab-icon ';
            $icon .= !$custom_icon ?
                $v['i_icon_' . $v['i_type']] : 'padding-bottom-15 ' . $v["section_nasa_icon"];
            
            switch ($v['i_position']) {
                case 'right':
                    $title = $title . '<i class="' . $icon . '"></i>';
                    break;
                case 'left':
                default :
                    $title = '<i class="' . $icon . '"></i>' . $title;
                    break;
            }
        }
        
        $class_item = 'nasa-tab';
        $class_item .= $k == 0 ? ' active first' : '';
        $class_item .= ($k + 1) == WPBakeryShortCode_VC_Tta_Section::$self_count ? ' last' : '';
        $nasa_attr = ' class="' . $class_item . '"';
        $nasa_attr .= $k == 0 ? ' data-show="1"' : ' data-show="0"';
        $nasa_attr .= !empty($tab_color) ? ' style="' . implode(';', $tab_color) . '"' : '';
        $output .= '<li' . $nasa_attr . '>';
        $output .= '<a href="javascript:void(0);" data-id="#nasa-secion-' . esc_attr($v['tab_id']) . '" class="' . esc_attr($class_a_click) . '"><h5' . (!empty($tab_color_h5) ? ' style="' . implode(';', $tab_color_h5) . '"' : '') . '>' . $title . '</h5></a></li>';
    endforeach;
    
    $output .= '</ul>';
    $output .= '</div>';
    $output .= '<div class="nasa-panels">';
    $output .= $prepareContent; // Content
    $output .= '</div>';
    $output .= '</div>';
endif;

echo ($output);
