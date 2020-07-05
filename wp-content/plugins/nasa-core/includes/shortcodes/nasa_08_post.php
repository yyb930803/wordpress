<?php

function nasa_sc_posts($atts, $content = null) {
    global $nasa_opt;
    
    $dfAttr = array(
        "title" => '',
        "align" => '',
        'show_type' => 'slide',
        'auto_slide' => 'false',
        'dots' => 'false',
        'arrows' => 1,
        'posts' => '8',
        'category' => '',
        'columns_number' => '3',
        'columns_number_small' => '1',
        'columns_number_tablet' => '2',
        'cats_enable' => 'yes',
        'date_author' => 'bot',
        'date_enable' => 'yes',
        'author_enable' => 'yes',
        'readmore' => 'yes',
        'page_blogs' => 'yes',
        'des_enable' => 'no',
        'el_class' => ''
    );
    extract(shortcode_atts($dfAttr, $atts));
    
    /**
     * Cache shortcode
     */
    $key = false;
    if (isset($nasa_opt['nasa_cache_shortcodes']) && $nasa_opt['nasa_cache_shortcodes']) {
        $key = nasa_key_shortcode('nasa_post', $dfAttr, $atts);
        $content = nasa_get_cache_shortcode($key);
    }
    
    if (!$content) {
        ob_start();
        $align = ($align == 'center') ? ' text-center' : '';
        ?>
        <div class="nasa-sc-posts-warp<?php echo $el_class != '' ? ' ' . $el_class : ''; ?>">
            <?php if ($title != '') : ?> 
                <div class="row">
                    <div class="large-12 columns<?php echo esc_attr($align); ?>">
                        <?php /* div class="nasa-hr medium"></div */?>
                        <div class="nasa-title nasa_type_2">
                            <h3 class="nasa-title-heading">
                                <span><?php echo esc_attr($title); ?></span>
                            </h3>
                            <hr class="nasa-separator" />
                        </div>
                    </div>
                </div>
            <?php endif;
            $args = array(
                'post_status' => 'publish',
                'post_type' => 'post',
                'category_name' => $category != '' ? $category : '',
                'posts_per_page' => (int) $posts ? (int) $posts : 8
            );

            $recentPosts = new WP_Query($args);
            if ($recentPosts->have_posts()) :
                switch ($show_type) :
                    case 'grid':
                        include NASA_CORE_BLOG_LAYOUTS . 'latestblog_grid.php';
                        break;
                    case 'grid_2':
                        include NASA_CORE_BLOG_LAYOUTS . 'latestblog_grid_2.php';
                        break;
                    case 'list':
                        include NASA_CORE_BLOG_LAYOUTS . 'latestblog_list.php';
                        break;
                    case 'slide':
                    default:
                        include NASA_CORE_BLOG_LAYOUTS . 'latestblog_carousel.php';
                        break;
                endswitch;
            endif;
        ?>
        </div>
        <?php
        $content = ob_get_clean();
        
        if ($content) {
            nasa_set_cache_shortcode($key, $content);
        }
    }
    
    return $content;
}

// **********************************************************************// 
// ! Register New Element: Recent Posts
// **********************************************************************//   
function nasa_register_latest_post(){
    $params = array(
        "name" => esc_html__("Post blogs", 'nasa-core'),
        "base" => "nasa_post",
        'icon' => 'icon-wpb-nasatheme',
        'description' => esc_html__("Display posts as many format.", 'nasa-core'),
        "content_element" => true,
        "category" => 'Nasa Core',
        "params" => array(
            array(
                "type" => "textfield",
                "heading" => esc_html__('Title', 'nasa-core'),
                "param_name" => "title",
                "std" => '',
                "description" => esc_html__('Title', 'nasa-core')
            ),
            array(
                "type" => "dropdown",
                "heading" => esc_html__("Show Type", 'nasa-core'),
                "param_name" => "show_type",
                "value" => array(
                    esc_html__('Carousel style', 'nasa-core') => 'slide',
                    esc_html__('Grid style 1', 'nasa-core') => 'grid',
                    esc_html__('Grid style 2', 'nasa-core') => 'grid_2',
                    esc_html__('List', 'nasa-core') => 'list'
                ),
                "std" => 'slide'
            ),
            array(
                "type" => "dropdown",
                "heading" => esc_html__('Slide auto', 'nasa-core'),
                "param_name" => 'auto_slide',
                "value" => array(
                    esc_html__('Yes, please', 'nasa-core') => 'true',
                    esc_html__('No, thank', 'nasa-core') => 'false'
                ),
                "std" => 'false',
                "dependency" => array(
                    "element" => "show_type",
                    "value" => array(
                        "slide"
                    )
                ),
                "description" => esc_html__("Only using for Show type is Carousel.", 'nasa-core')
            ),

            array(
                "type" => "dropdown",
                "heading" => esc_html__('Show dots', 'nasa-core'),
                "param_name" => 'dots',
                "value" => array(
                    esc_html__('Yes, please', 'nasa-core') => 'true',
                    esc_html__('No, thank', 'nasa-core') => 'false'
                ),
                "std" => 'false',
                "dependency" => array(
                    "element" => "show_type",
                    "value" => array(
                        "slide"
                    )
                ),
                "description" => esc_html__("Only using for Show type is Carousel.", 'nasa-core')
            ),

            array(
                "type" => "dropdown",
                "heading" => esc_html__('Show arrows', 'nasa-core'),
                "param_name" => 'arrows',
                "value" => array(
                    esc_html__('Yes, please', 'nasa-core') => 1,
                    esc_html__('No, thank', 'nasa-core') => 0
                ),
                "std" => 1,
                "dependency" => array(
                    "element" => "show_type",
                    "value" => array(
                        "slide"
                    )
                ),
                "description" => esc_html__("Only using for Show type is Carousel.", 'nasa-core')
            ),

            array(
                "type" => "textfield",
                "heading" => esc_html__("Post number", 'nasa-core'),
                "param_name" => "posts",
                "value" => "8"
            ),

            array(
                "type" => "dropdown",
                "heading" => esc_html__("Columns number", 'nasa-core'),
                "param_name" => "columns_number",
                "value" => array(5, 4, 3, 2, 1),
                "std" => 3,
                "dependency" => array(
                    "element" => "show_type",
                    "value" => array(
                        "slide", "grid", "grid_2"
                    )
                ),
                "admin_label" => true,
                "description" => esc_html__("Select columns count.", 'nasa-core')
            ),

            array(
                "type" => "dropdown",
                "heading" => esc_html__("Columns number small", 'nasa-core'),
                "param_name" => "columns_number_small",
                "value" => array(2, 1),
                "std" => 1,
                "dependency" => array(
                    "element" => "show_type",
                    "value" => array(
                        "slide", "grid", "grid_2"
                    )
                ),
                "admin_label" => true,
                "description" => esc_html__("Select columns count small display.", 'nasa-core')
            ),

            array(
                "type" => "dropdown",
                "heading" => esc_html__("Columns number tablet", 'nasa-core'),
                "param_name" => "columns_number_tablet",
                "value" => array(3, 2, 1),
                "std" => 2,
                "dependency" => array(
                    "element" => "show_type",
                    "value" => array(
                        "slide", "grid", "grid_2"
                    )
                ),
                "admin_label" => true,
                "description" => esc_html__("Select columns count in tablet.", 'nasa-core')
            ),

            array(
                "type" => "textfield",
                "heading" => esc_html__("Categories", 'nasa-core'),
                "param_name" => "category",
                "value" => '',
                "description" => esc_html__('Input categories slug Divide links with ","', 'nasa-core')
            ),

            // Date
            array(
                "type" => "dropdown",
                "heading" => esc_html__("Show Categories of post", 'nasa-core'),
                "param_name" => "cats_enable",
                "value" => array(
                    esc_html__('Yes', 'nasa-core') => 'yes',
                    esc_html__('No', 'nasa-core') => 'no'
                ),
                'std' => 'yes'
            ),

            // date_author
            array(
                "type" => "dropdown",
                "heading" => esc_html__("Date/Author/Readmore position with description", 'nasa-core'),
                "param_name" => "date_author",
                "value" => array(
                    esc_html__('Top', 'nasa-core') => 'top',
                    esc_html__('Bottom', 'nasa-core') => 'bot'
                ),
                'std' => 'bot',
                "dependency" => array(
                    "element" => "show_type",
                    "value" => array(
                        "slide", "grid", "list"
                    )
                )
            ),

            // Date
            array(
                "type" => "dropdown",
                "heading" => esc_html__("Show date post", 'nasa-core'),
                "param_name" => "date_enable",
                "value" => array(
                    esc_html__('Yes', 'nasa-core') => 'yes',
                    esc_html__('No', 'nasa-core') => 'no'
                ),
                'std' => 'yes'
            ),

            // Author
            array(
                "type" => "dropdown",
                "heading" => esc_html__("Show author post", 'nasa-core'),
                "param_name" => "author_enable",
                "value" => array(
                    esc_html__('Yes', 'nasa-core') => 'yes',
                    esc_html__('No', 'nasa-core') => 'no'
                ),
                'std' => 'yes'
            ),

            // Read more
            array(
                "type" => "dropdown",
                "heading" => esc_html__("Show read more", 'nasa-core'),
                "param_name" => "readmore",
                "value" => array(
                    esc_html__('Yes', 'nasa-core') => 'yes',
                    esc_html__('No', 'nasa-core') => 'no'
                ),
                'std' => 'yes'
            ),

            // Page blogs
            array(
                "type" => "dropdown",
                "heading" => esc_html__("Show button page blogs", 'nasa-core'),
                "param_name" => "page_blogs",
                "value" => array(
                    esc_html__('Yes', 'nasa-core') => 'yes',
                    esc_html__('No', 'nasa-core') => 'no'
                ),
                'std' => 'yes'
            ),

            array(
                "type" => "dropdown",
                "heading" => esc_html__("Show description", 'nasa-core'),
                "param_name" => "des_enable",
                "value" => array(
                    esc_html__('Yes', 'nasa-core') => 'yes',
                    esc_html__('No', 'nasa-core') => 'no'
                ),
                "std" => 'no',
                "dependency" => array(
                    "element" => "show_type",
                    "value" => array(
                        "slide", "grid", "list"
                    )
                )
            ),

            array(
                "type" => "textfield",
                "heading" => esc_html__("Extra class name", 'nasa-core'),
                "param_name" => "el_class",
                "description" => esc_html__("If you wish to style particular content element differently, then use this field to add a class name and then refer to it in your css file.", 'nasa-core')
            )
        )
    );

    vc_map($params);
}
