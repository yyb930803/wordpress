<?php

function nasa_sc_search_post($atts, $content = null) {
    $dfAttr = array(
        "label_search" => 'Search Posts',
        "btn_text" => 'Search',
        "post_type" => 'post',
        'el_class' => ''
    );
    extract(shortcode_atts($dfAttr, $atts));
    
    $el_class = $el_class != '' ? 'nasa-search-form-warp ' . $el_class : 'nasa-search-form-warp';
    
    $_id = rand();
    ob_start();
    ?>
    <div class="<?php echo esc_attr($el_class); ?>">
        <form method="get" action="<?php echo esc_url(home_url('/')); ?>" class="nasa-search-post-form">
            <div class="nasa-search-post-wrap">
                <label class="nasa-search-post-label">
                    <?php echo $label_search; ?>
                </label>
                <input id="nasa-input-<?php echo esc_attr($_id); ?>" type="text" class="nasa-search-input" value="<?php echo get_search_query(); ?>" name="s" placeholder="<?php esc_attr_e("Search ...", 'nasa-core'); ?>" />
                <input type="hidden" name="post_type" value="<?php echo esc_attr($post_type); ?>" />
                <span class="nasa-icon-submit-page"><input type="submit" name="page" value="<?php echo esc_attr($btn_text); ?>" /></span>
            </div>
        </form>
    </div>

    <?php
    $content = ob_get_clean();
    
    return $content;
}
 
function nasa_register_search_posts(){
    $params = array(
        "name" => esc_html__("Search", 'nasa-core'),
        "base" => "nasa_search_posts",
        'icon' => 'icon-wpb-nasatheme',
        'description' => esc_html__("Display form search.", 'nasa-core'),
        "content_element" => true,
        "category" => 'Nasa Core',
        "params" => array(
            array(
                "type" => "textfield",
                "heading" => esc_html__('Label', 'nasa-core'),
                "param_name" => "label_search",
                "std" => 'Search',
                "description" => esc_html__('Label search', 'nasa-core')
            ),

            array(
                "type" => "textfield",
                "heading" => esc_html__('Button text', 'nasa-core'),
                "param_name" => "btn_text",
                "std" => 'Search',
                "description" => esc_html__('Button text.', 'nasa-core')
            ),

            array(
                "type" => "dropdown",
                "heading" => esc_html__("Post Type", 'nasa-core'),
                "param_name" => "post_type",
                "value" => array(
                    esc_html__('Product - WooCommerce', 'nasa-core') => 'product',
                    esc_html__('Post - Blog', 'nasa-core') => 'post'
                ),
                "std" => 'post'
            ),
        )
    );

    vc_map($params);
}