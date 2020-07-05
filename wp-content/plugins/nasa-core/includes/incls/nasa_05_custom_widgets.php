<?php
add_action('widgets_init', 'nasa_cat_sidebar_override', 999);
function nasa_cat_sidebar_override() {
    $sidebar_cats = get_option('nasa_sidebars_cats');
    
    if(!empty($sidebar_cats)) {
        foreach ($sidebar_cats as $sidebar) {
            if(isset($sidebar['slug'])) {
                $name = esc_html__('Products Category: ', 'nasa-core') . (isset($sidebar['name']) ? ($sidebar['name'] . ' (' . $sidebar['slug'] . ')') : $sidebar['slug']);
                register_sidebar(array(
                    'name' => $name,
                    'id' => $sidebar['slug'],
                    'before_widget' => '<div id="%1$s" class="widget %2$s"><a href="javascript:void(0);" class="nasa-toggle-widget"></a><div class="nasa-open-toggle">',
                    'before_title'  => '<h2 class="widgettitle">',
                    'after_title'   => '</h2>',
                    'after_widget'  => '</div></div>'
                ));
            }
        }
    }
}
