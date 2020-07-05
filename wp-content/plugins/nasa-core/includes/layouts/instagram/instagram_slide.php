<?php
$ulclass = 'instagram-pics instagram-size-large slider nasa-slider owl-carousel';
// $liclass = 'instagram-li';
$aclass = 'instagram-a';
$imgclass = 'instagram-img';

$class = 'nasa-instagram';
$class .= $el_class ? ' ' . $el_class : '';

echo '<div class="nasa-intagram-wrap' . ($el_class != '' ? ' ' . $el_class : '') . '">';
echo '<div class="' . $class . '">';

if ($username_show || $instagram_link) :
    echo $instagram_link ? '<a href="' . esc_url($instagram_link) . '" rel="me" target="_blank" title="' . esc_attr__('Follow us on Instagram', 'nasa-core') . '">' : '';

    echo '<div class="username-text text-center"><i class="fa fa-instagram"></i><span class="hide-for-small">' . $username_show . '</span></div>';

    echo $instagram_link ? '</a>' : '';
endif;

echo '<div class="' . esc_attr($ulclass) . '" data-margin="0" data-margin-small="0" data-margin-medium="0" data-columns="' . $photos . '" data-columns-small="' . $photos_mobile . '" data-columns-tablet="' . $photos_tablet . '" data-autoplay="false" data-loop="false" data-height-auto="false" data-dot="false" data-disable-nav="false">';

foreach ($jsonData as $value) {
    echo '<a href="' . esc_url($value->link) . '" target="_blank"  class="' . esc_attr($aclass) . '">' .
            '<img src="' . esc_url($value->images->{$img_size}->url) . '" alt="' . esc_attr($value->caption->text) . '" class="' . esc_attr($imgclass) . '" width="' . esc_attr($value->images->{$img_size}->width) . '" height="' . esc_attr($value->images->{$img_size}->height) . '" />' .
        '</a>';
}

echo '</div>';

echo '</div></div>';
