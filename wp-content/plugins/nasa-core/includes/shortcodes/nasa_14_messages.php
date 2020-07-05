<?php

function nasa_sc_message_box($atts, $content = null) {
    extract(shortcode_atts(array(
        'bg' => '#333',
        'text_color' => 'light',
    ), $atts));

    $color = ($text_color == 'light') ? "dark" : "light";
    $background = "";
    $background_color = "";
    if (strpos($bg, 'http://') !== false || strpos($bg, 'https://') !== false) {
        $background = $bg;
    } elseif (strpos($bg, '#') !== false) {
        $background_color = 'background-color:' . $bg . '!important';
    } else {
        $bg = wp_get_attachment_image_src($bg, 'large');
        $background = $bg[0];
    }

    $content = do_shortcode($content);

    return 
        '<div class="message-box ' . $color . '" style="background-image:url(' . $background . ');' . $background_color . '">' .
            '<div class="row">' .
                '<div class="large-12 columns">' .
                    '<div class="inner">' . $content . '</div>' .
                '</div>' .
            '</div>' .
        '</div><!-- .message-box -->';
}
