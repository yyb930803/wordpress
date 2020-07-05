<?php

function nasa_sc_google_maps($atts = array(), $content = null) {
    return '<p class="nasa-error nasa-bold">Please change Nasa Google Maps by VC Google Maps at Add Element > Content > Google Maps</p>';
}

// **********************************************************************// 
// ! Register New Element: Google Map
// **********************************************************************// 
function nasa_register_google_maps(){
    $map_params = array(
        "name" => esc_html__("Google Map - Deprecated", 'nasa-core'),
        "base" => "nasa_google_map",
        'icon' => 'icon-wpb-nasatheme',
        'description' => esc_html__("Deprecated.", 'nasa-core'),
        "content_element" => true,
        "category" => 'Nasa Deprecated',
        "params" => array(
            array(
                "type" => "textfield",
                "heading" => esc_html__("Extra class name", 'nasa-core'),
                "param_name" => "el_class",
                "description" => esc_html__("If you wish to style particular content element differently, then use this field to add a class name and then refer to it in your css file.", 'nasa-core')
            )
        )
    );
    vc_map($map_params);
}
