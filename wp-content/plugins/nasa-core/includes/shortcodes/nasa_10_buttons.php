<?php

function nasa_sc_buttons($atts, $content = null) {
    extract(shortcode_atts(array(
        'text' => '',
        'style' => '',
        'color' => '',
        'size' => '',
        'link' => '',
        'target' => ''
    ), $atts));

    $target = $target ? ' target="' . $target . '"' : '';
    $color = $color ? ' style="background-color: ' . $color . ' !important"' : '';
    $content = '<a href="' . ($link != '' ? $link : 'javascript:void(0);') . '" class="button ' . $size . ' ' . $style . '"' . $color . $target . '>' . $text . '</a>';
    
    return $content;
}
