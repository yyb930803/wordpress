<?php

function nasa_sc_menu($atts, $content = null) {
    global $nasa_opt;
    
    $dfAttr = array(
        'title' => '',
        'menu' => '',
        'el_class' => ''
    );
    extract(shortcode_atts($dfAttr, $atts));
    
    if ($menu) {
        ob_start();
        ?>
        <div class="nasa-nav-sc-menu<?php echo $el_class != '' ? ' ' . esc_attr($el_class) : ''; ?>">
            <?php if ($title) : ?>
                <div class="nasa-nav-sc-menu-title">
                    <h5 class="section-title">
                        <span><?php echo esc_attr($title); ?></span>
                    </h5>
                </div>
            <?php endif; ?>
            <div class="nasa-nav-sc-menu-container">
                <ul class="nasa-menu-wrapper">
                    <?php
                    wp_nav_menu(array(
                        'menu' => $menu,
                        'container' => false,
                        'items_wrap' => '%3$s',
                        'depth' => 3,
                        'walker' => new Nasa_Nav_Menu()
                    ));
                    ?>
                </ul>
            </div>
        </div>
        <?php $content = ob_get_clean();
    }
    
    return $content;
}

// **********************************************************************// 
// ! Register New Element: Menu vertical
// **********************************************************************//   
function nasa_register_menu_shortcode() {
    $menus = wp_get_nav_menus(array('orderby' => 'name'));
    $option_menu = array(esc_html__("Select menu", 'nasa-core') => '');
    foreach ($menus as $menu_option) {
        $option_menu[$menu_option->name] = $menu_option->slug;
    }

    $params = array(
        "name" => esc_html__("Menu 1 level", 'nasa-core'),
        "base" => "nasa_menu",
        'icon' => 'icon-wpb-nasatheme',
        'description' => esc_html__("Display shortcode menu.", 'nasa-core'),
        "category" => 'Nasa Core',
        "params" => array(
            array(
                "type" => "textfield",
                "heading" => esc_html__("Title", 'nasa-core'),
                "param_name" => "title"
            ),
            array(
                'type' => 'dropdown',
                'heading' => esc_html__('Menu', 'nasa-core'),
                'param_name' => 'menu',
                "value" => $option_menu,
                "description" => esc_html__("Select Menu.", 'nasa-core'),
                "admin_label" => true
            ),

            array(
                "type" => "textfield",
                "heading" => esc_html__("Extra Class", 'nasa-core'),
                "param_name" => "el_class",
                "description" => esc_html__('If you wish to style particular content element differently, then use this field to add a class name and then refer to it in your css file.', 'nasa-core')
            )
        )
    );

    vc_map($params);
}
