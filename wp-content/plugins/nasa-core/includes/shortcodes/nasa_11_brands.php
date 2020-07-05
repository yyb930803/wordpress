<?php

function nasa_sc_brands($atts, $content = null) {
    global $nasa_opt;
    
    $dfAttr = array(
        'title' => '',
        'custom_links' => '',
        'images' => '',
        'columns_number' => '6',
        'columns_number_small' => '2',
        'columns_number_tablet' => '4',
        'layout' => 'carousel',
        'el_class' => ''
    );
    extract(shortcode_atts($dfAttr, $atts));
    
    $custom_links = explode(',', $custom_links);
    $images = explode(',', $images);

    if (count($images) > 0) {
        ob_start();
        $delay = 0;
        $_delay_item = (isset($nasa_opt['delay_overlay']) && (int) $nasa_opt['delay_overlay']) ? (int) $nasa_opt['delay_overlay'] : 100;
        ?>
        <div class="nasa-brands">
            <div class="row">
                <?php if ($layout == 'carousel') { ?>
                    <div class="column brands-group nasa-slider owl-carousel" data-loop="true" data-speed="1000" data-columns="<?php echo esc_attr($columns_number); ?>" data-columns-small="<?php echo esc_attr($columns_number_small); ?>" data-columns-tablet="<?php echo esc_attr($columns_number_tablet); ?>">
                        <?php foreach ($images as $key => $image) { ?>
                            <div class="brands-item wow bounceIn text-center" data-wow-duration="1s" data-wow-delay="<?php echo esc_attr($delay); ?>ms">
                                <?php
                                $img = wp_get_attachment_image($image, 'full');
                                if ($img) :
                                    $link_start = $link_end = '';
                                    if (isset($custom_links[$key]) && $custom_links[$key] != '') {
                                        $link_start = '<a href="' . $custom_links[$key] . '">';
                                        $link_end = '</a>';
                                    }
                                    echo $link_start . $img . $link_end;
                                endif;
                                ?>
                            </div>
                            <?php $delay+=$_delay_item; ?>
                        <?php } ?>
                    </div>
                
                <?php } else { ?>
                
                    <div class="large-12 columns">
                        <ul class="small-block-grid-<?php echo esc_attr($columns_number_small); ?> medium-block-grid-<?php echo esc_attr($columns_number_tablet); ?> large-block-grid-<?php echo esc_attr($columns_number); ?>">
                            <?php foreach ($images as $key => $image) { ?>
                                <li class="wow bounceIn text-center" data-wow-duration="1s" data-wow-delay="<?php echo esc_attr($delay); ?>ms">
                                    <?php
                                    $img = wpb_getImageBySize(array('attach_id' => $image, 'thumb_size' => 'full'));
                                    $link_start = $link_end = '';
                                    if (isset($custom_links[$key]) && $custom_links[$key] != '') {
                                        $link_start = '<a href="' . $custom_links[$key] . '">';
                                        $link_end = '</a>';
                                    }
                                    echo $link_start . $img['thumbnail'] . $link_end;
                                    ?>
                                </li>
                                <?php $delay+=$_delay_item; ?>
                            <?php } ?>
                        </ul>
                    </div>
                <?php } ?>
            </div>
        </div>
        <?php
        $content = ob_get_clean();
    }
    
    return $content;
}

/* ==========================================================================
! Register New Element: Nasa Brands
========================================================================== */  
function nasa_register_brands(){
    vc_map(array(
        "name" => esc_html__("Brands", 'nasa-core'),
        "base" => "nasa_brands",
        'icon' => 'icon-wpb-nasatheme',
        'description' => esc_html__("Display brands logo", 'nasa-core'),
        "class" => "",
        "category" => 'Nasa Core',
        "params" => array(
            array(
                "type" => "textfield",
                "heading" => esc_html__("Title", 'nasa-core'),
                "param_name" => "title"
            ),
            array(
                "type" => "dropdown",
                "heading" => esc_html__("Layout", 'nasa-core'),
                "param_name" => "layout",
                "value" => array(
                    esc_html__('Carousel', 'nasa-core') => 'carousel',
                    esc_html__('Grid', 'nasa-core') => 'grid',
                ),
                "admin_label" => true,
                "description" => esc_html__("Select layout.", 'nasa-core')
            ),
            array(
                'type' => 'attach_images',
                'heading' => esc_html__('Images', 'nasa-core'),
                'param_name' => 'images',
                'value' => '',
                'description' => esc_html__('Select images from media library.', 'nasa-core')
            ),
            array(
                'type' => 'exploded_textarea',
                'heading' => esc_html__('Custom links', 'nasa-core'),
                'param_name' => 'custom_links',
                'description' => esc_html__('Enter links for each slide here. Divide links with linebreaks (Enter) . ', 'nasa-core'),
            ),
            array(
                "type" => "dropdown",
                "heading" => esc_html__("Columns number", 'nasa-core'),
                "param_name" => "columns_number",
                "value" => array(6, 5, 4, 3, 2),
                "admin_label" => true,
                "description" => esc_html__("Select columns count.", 'nasa-core')
            ),
            array(
                "type" => "dropdown",
                "heading" => esc_html__("Columns number tablet", 'nasa-core'),
                "param_name" => "columns_number_tablet",
                "value" => array(4, 3, 2),
                "admin_label" => true,
                "description" => esc_html__("Select columns count in tablet.", 'nasa-core')
            ),
            array(
                "type" => "dropdown",
                "heading" => esc_html__("Columns number small", 'nasa-core'),
                "param_name" => "columns_number_small",
                "value" => array(3, 2, 1),
                "admin_label" => true,
                "description" => esc_html__("Select columns count in mobile.", 'nasa-core')
            ),

            array(
                "type" => "textfield",
                "heading" => esc_html__("Extra class name", 'nasa-core'),
                "param_name" => "el_class",
                "description" => esc_html__("If you wish to style particular content element differently, then use this field to add a class name and then refer to it in your css file.", 'nasa-core')
            )
        )
    ));
}
