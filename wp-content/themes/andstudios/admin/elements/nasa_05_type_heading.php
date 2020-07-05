<?php
add_action('init', 'elessi_type_heading');
if (!function_exists('elessi_type_heading')) {
    function elessi_type_heading() {
        /* --------------------------------------------------------------------- */
        /* The Options Array */
        /* --------------------------------------------------------------------- */
        // Set the Options Array
        global $of_options;
        if(empty($of_options)) {
            $of_options = array();
        }
        
        $google_fonts = elessi_get_google_fonts();
        $custom_fonts = elessi_get_custom_fonts();
        
        $of_options[] = array(
            "name" => esc_html__("Fonts", 'elessi-theme'),
            "target" => 'fonts',
            "type" => "heading"
        );
        
        $of_options[] = array(
            "name" => esc_html__("Type Font", 'elessi-theme'),
            "id" => "type_font_select",
            "std" => "google",
            "type" => "select",
            "options" => array(
                "" => esc_html__("Default font", 'elessi-theme'),
                "custom" => esc_html__("Custom font", 'elessi-theme'),
                "google" => esc_html__("Google font", 'elessi-theme')
            ),
            'class' => 'nasa-type-font'
        );

        $of_options[] = array(
            "name" => esc_html__("Heading fonts (H1, H2, H3, H4, H5, H6)", 'elessi-theme'),
            "id" => "type_headings",
            "std" => "Nunito Sans",
            "type" => "select_google_font",
            "preview" => array(
                "text" => '<strong>' . esc_html__("NasaTheme", 'elessi-theme') . '</strong><br /><span style="font-size:60%!important">' . esc_html__("UPPERCASE TEXT", 'elessi-theme') . '</span>',
                "size" => "30px"
            ),
            "options" => $google_fonts,
            'class' => 'hidden-tag nasa-type-font-glb nasa-type-font-google'
        );

        $of_options[] = array(
            "name" => esc_html__("Text fonts (paragraphs, etc..)", 'elessi-theme'),
            "id" => "type_texts",
            "std" => "Nunito Sans",
            "type" => "select_google_font",
            "preview" => array(
                "text" => esc_html__("Lorem ipsum dosectetur adipisicing elit, sed do.Lorem ipsum dolor sit amet, consectetur Nulla fringilla purus at leo dignissim congue. Mauris elementum accumsan leo vel tempor. Sit amet cursus nisl aliquam. Aliquam et elit eu nunc rhoncus viverra quis at felis..", 'elessi-theme'),
                "size" => "14px"
            ),
            "options" => $google_fonts,
            'class' => 'hidden-tag nasa-type-font-glb nasa-type-font-google'
        );

        $of_options[] = array(
            "name" => esc_html__("Main navigation", 'elessi-theme'),
            "id" => "type_nav",
            "std" => "Nunito Sans",
            "type" => "select_google_font",
            "preview" => array(
                "text" => "<span style='font-size:45%'>" . esc_html__("THIS IS THE TEXT.", 'elessi-theme') . "</span>",
                "size" => "30px"
            ),
            "options" => $google_fonts,
            'class' => 'hidden-tag nasa-type-font-glb nasa-type-font-google'
        );

        $of_options[] = array(
            "name" => esc_html__("Banner font", 'elessi-theme'),
            "id" => "type_banner",
            "std" => "Nunito Sans",
            "type" => "select_google_font",
            "preview" => array(
                "text" => esc_html__("This is the text.", 'elessi-theme'),
                "size" => "30px"
            ),
            "options" => $google_fonts,
            'class' => 'hidden-tag nasa-type-font-glb nasa-type-font-google'
        );
        
        $of_options[] = array(
            "name" => esc_html__("Price font", 'elessi-theme'),
            "id" => "type_price",
            "std" => "Nunito Sans",
            "type" => "select_google_font",
            "preview" => array(
                "text" => esc_html__("$999.", 'elessi-theme'),
                "size" => "30px"
            ),
            "options" => $google_fonts,
            'class' => 'hidden-tag nasa-type-font-glb nasa-type-font-google'
        );

        $of_options[] = array(
            "name" => esc_html__("Character Sub-sets", 'elessi-theme'),
            "id" => "type_subset",
            "std" => array("latin"),
            "type" => "multicheck",
            "options" => array(
                "latin"         => esc_html__("Latin", 'elessi-theme'),
                "arabic"        => esc_html__("Arabic", 'elessi-theme'),
                "cyrillic"      => esc_html__("Cyrillic", 'elessi-theme'),
                "cyrillic-ext"  => esc_html__("Cyrillic Extended", 'elessi-theme'),
                "greek"         => esc_html__("Greek", 'elessi-theme'),
                "greek-ext"     => esc_html__("Greek Extended", 'elessi-theme'),
                "vietnamese"    => esc_html__("Vietnamese", 'elessi-theme'),
                "latin-ext"     => esc_html__("Latin Extended", 'elessi-theme')
            ),
            'class' => 'hidden-tag nasa-type-font-glb nasa-type-font-google'
        );
        
        $of_options[] = array(
            "name" => esc_html__("Upload Custom Font", 'elessi-theme'),
            "std" => "",
            "type" => "nasa_upload_custom_font",
            'class' => 'hidden-tag nasa-type-font-glb nasa-type-font-custom'
        );
        
        $of_options[] = array(
            "name" => esc_html__("Select Custom Font", 'elessi-theme'),
            "id" => "custom_font",
            "std" => "",
            "type" => "select",
            "options" => $custom_fonts,
            'class' => 'hidden-tag nasa-type-font-glb nasa-type-font-custom'
        );
        
        $of_options[] = array(
            "name" => esc_html__("Include FontAwesome 5.0.13 (Note: You only can use Free icons)", 'elessi-theme'),
            "id" => "include_font_awesome_new",
            "std" => 0,
            "type" => "switch"
        );
    }
}
