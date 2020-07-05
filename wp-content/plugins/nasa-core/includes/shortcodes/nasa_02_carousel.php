<?php

function nasa_sc_carousel($atts, $content = null) {
    $dfAttr = array(
        'title' => '',
        'align' => 'left',
        'column_number' => '1',
        'column_number_tablet' => '1',
        'column_number_small' => '1',
        'navigation' => 'true',
        'nav_type' => '',
        'bullets' => 'true',
        'paginationspeed' => '800',
        'autoplay' => 'false',
        'el_class' => ''
    );
    extract(shortcode_atts($dfAttr, $atts));
    
    $text_align = $align ? 'text-' . $align : 'text-left';
    $sliderid = rand();
    
    $class_wrap = 'nasa-sc-carousel-main';
    $class_wrap .= $el_class != '' ? ' ' . $el_class : '';
    
    ob_start();
    ?>
    <div class="<?php echo esc_attr($class_wrap); ?>">
        <?php if ($title): ?>
            <div class="large-12 columns">
                <div class="title-inner <?php echo esc_attr($text_align); ?>"> 
                    <h3 class="section-title <?php echo esc_attr($text_align); ?>"><span><?php echo esc_attr($title); ?></span></h3>
                    <div class="nasa-hr medium"></div>
                </div>
            </div>
        <?php endif; ?>
        <div class="nasa-sc-carousel-warper">
            <div class="nasa-sc-carousel owl-carousel<?php echo $nav_type != '' ? ' ' . esc_attr($nav_type) : ''; ?>"
                data-contruct="<?php echo esc_attr($sliderid); ?>-<?php echo esc_attr($column_number); ?>"
                id="item-slider-<?php echo esc_attr($sliderid); ?>-<?php echo esc_attr($column_number); ?>"
                data-nav="<?php echo esc_attr($navigation); ?>"
                data-dots="<?php echo esc_attr($bullets); ?>"
                data-autoplay="<?php echo esc_attr($autoplay); ?>"
                data-speed="<?php echo esc_attr($paginationspeed); ?>"
                data-itemSmall="<?php echo esc_attr($column_number_small); ?>"
                data-itemTablet="<?php echo esc_attr($column_number_tablet); ?>"
                data-items="<?php echo esc_attr($column_number); ?>">
                <?php echo do_shortcode($content); ?>
            </div>
        </div>
    </div>
    <?php
    
    return ob_get_clean();
}

// **********************************************************************// 
// ! Register New Element: Slider
// **********************************************************************//
function nasa_register_slider(){
    $slider_params = array(
        "name" => esc_html__("Slider", 'nasa-core'),
        "base" => "nasa_slider",
        "as_parent" => array('except' => 'nasa_slider'), // Use only|except attributes to limit child shortcodes (separate multiple values with comma)
        'icon' => 'icon-wpb-nasatheme',
        'description' => esc_html__("Display slider (images, products, ...)", 'nasa-core'),
        "content_element" => true,
        'category' => 'Nasa Core',
        "params" => array(
            array(
                "type" => "textfield",
                "heading" => esc_html__("Title", 'nasa-core'),
                "param_name" => "title"
            ),
            array(
                "type" => "dropdown",
                "heading" => "Title align",
                "param_name" => "align",
                "value" => array(
                    esc_html__('Left', 'nasa-core') => 'left',
                    esc_html__('Center', 'nasa-core') => 'center',
                    esc_html__('Right', 'nasa-core') => 'right',
                )
            ),
            array(
                "type" => "dropdown",
                "heading" => esc_html__('Display Bullets', 'nasa-core'),
                "param_name" => "bullets",
                "value" => array(
                    esc_html__('Enable', 'nasa-core') => 'true',
                    esc_html__('Disable', 'nasa-core') => 'false'
                ),
                "description" => 'You only use bullets or arrows for navigation. If disable bullets. You can select arrow navigation at bellow.'
            ),
            array(
                "type" => "dropdown",
                "heading" => esc_html__('Display arrows', 'nasa-core'),
                "param_name" => "navigation",
                "value" => array(
                    esc_html__('Enable', 'nasa-core') => 'true',
                    esc_html__('Disable', 'nasa-core') => 'false'
                )
            ),
            array(
                "type" => "dropdown",
                "heading" => esc_html__('Number columns', 'nasa-core'),
                "param_name" => "column_number",
                "value" => array(
                    esc_html__('1', 'nasa-core') => '1',
                    esc_html__('2', 'nasa-core') => '2',
                    esc_html__('3', 'nasa-core') => '3',
                    esc_html__('4', 'nasa-core') => '4',
                    esc_html__('5', 'nasa-core') => '5',
                    esc_html__('6', 'nasa-core') => '6',
                )
            ),
            array(
                "type" => "dropdown",
                "heading" => esc_html__('Responsive item numbers for mobile', 'nasa-core'),
                "param_name" => "column_number_small",
                "value" => array(
                    esc_html__('1', 'nasa-core') => '1',
                    esc_html__('2', 'nasa-core') => '2',
                    esc_html__('3', 'nasa-core') => '3',
                    esc_html__('4', 'nasa-core') => '4',
                    esc_html__('5', 'nasa-core') => '5',
                    esc_html__('6', 'nasa-core') => '6',
                )
            ),
            array(
                "type" => "dropdown",
                "heading" => esc_html__('Responsive item numbers for tablet', 'nasa-core'),
                "param_name" => "column_number_tablet",
                "value" => array(
                    esc_html__('1', 'nasa-core') => '1',
                    esc_html__('2', 'nasa-core') => '2',
                    esc_html__('3', 'nasa-core') => '3',
                    esc_html__('4', 'nasa-core') => '4',
                    esc_html__('5', 'nasa-core') => '5',
                    esc_html__('6', 'nasa-core') => '6',
                )
            ),
            array(
                "type" => "dropdown",
                "heading" => esc_html__('Auto Play', 'nasa-core'),
                "param_name" => "autoplay",
                "value" => array(
                    esc_html__('Disable', 'nasa-core') => 'false',
                    esc_html__('Enable', 'nasa-core') => 'true'
                )
            ),
            array(
                "type" => "dropdown",
                "heading" => esc_html__('Pagination Speed', 'nasa-core'),
                "param_name" => "paginationspeed",
                "std" => '800',
                "value" => array(
                    esc_html__('0.4s', 'nasa-core') => '400',
                    esc_html__('0.6s', 'nasa-core') => '600',
                    esc_html__('0.8s', 'nasa-core') => '800',
                    esc_html__('1.0s', 'nasa-core') => '1000',
                    esc_html__('1.2s', 'nasa-core') => '1200',
                    esc_html__('1.4s', 'nasa-core') => '1400',
                    esc_html__('1.6s', 'nasa-core') => '1800',
                )
            ),

            array(
                "type" => "textfield",
                "heading" => esc_html__("Extra class name", 'nasa-core'),
                "param_name" => "el_class",
                "description" => esc_html__("If you wish to style particular content element differently, then use this field to add a class name and then refer to it in your css file.", 'nasa-core')
            )
        ),
        "js_view" => 'VcColumnView'
    );

    vc_map($slider_params);
}