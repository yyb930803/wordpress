<?php
$ulclass = 'instagram-pics instagram-size-large';
$liclass = 'instagram-li';
$aclass = 'instagram-a';
$imgclass = 'instagram-img';

$class = 'nasa-instagram';
$class .= ' items-' . $photos;
$class .= ' items-tablet-' . $photos_tablet;
$class .= ' items-mobile-' . $photos_mobile;
$class .= $el_class ? ' ' . $el_class : '';

echo '<div class="nasa-intagram-wrap' . ($el_class != '' ? ' ' . $el_class : '') . '">';
echo '<div class="' . $class . '">';

if ($username_show || $instagram_link) :
    echo $instagram_link ? '<a href="' . esc_url($instagram_link) . '" rel="me" target="_blank" title="' . esc_attr__('Follow us on Instagram', 'nasa-core') . '">' : '';

    echo '<div class="username-text text-center"><i class="fa fa-instagram"></i><span class="hide-for-small">' . $username_show . '</span></div>';

    echo $instagram_link ? '</a>' : '';
endif;

echo '<ul class="' . esc_attr($ulclass) . '">';

foreach ($jsonData as $value) {
    echo '<li class="' . esc_attr($liclass) . '">' .
        '<a href="' . esc_url($value->link) . '" target="_blank"  class="' . esc_attr($aclass) . '">' .
            '<img src="' . esc_url($value->images->{$img_size}->url) . '" alt="' . esc_attr($value->caption->text) . '" class="' . esc_attr($imgclass) . '" width="' . esc_attr($value->images->{$img_size}->width) . '" height="' . esc_attr($value->images->{$img_size}->height) . '" />' .
        '</a>' .
    '</li>';
}

echo '</ul>';

echo '</div></div>';
