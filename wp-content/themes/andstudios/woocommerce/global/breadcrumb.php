<?php

/**
 * Shop breadcrumb
 *
 * @author 	WooThemes
 * @package 	WooCommerce/Templates
 * @version     2.3.0
 */
if (!defined('ABSPATH')) :
    exit; // Exit if accessed directly
endif;

if (!empty($breadcrumb)) {
    global $post, $nasa_opt, $wp_query;
    
    /**
     * Breadcrumb single row
     */
    if (isset($nasa_opt['breadcrumb_row']) && $nasa_opt['breadcrumb_row'] == 'single') {
        /**
         * Single Portfolio
         */
        if (is_singular('portfolio')) {
            $breadcrumb = elessi_rebuilt_breadcrumb_portfolio($breadcrumb);
        }

        /**
         * Archive Portfolio
         */
        else {
            $queried_object = $wp_query->get_queried_object();

            if(isset($queried_object->taxonomy) && $queried_object->taxonomy == 'portfolio_category') {
                $breadcrumb = elessi_rebuilt_breadcrumb_portfolio($breadcrumb, false);
            }
        }

        echo $wrap_before;

        $key = 0;
        $sizeof = sizeof($breadcrumb);
        foreach ($breadcrumb as $crumb) {
            echo $before;

            echo (!empty($crumb[1]) && $sizeof !== $key + 1) ?
                '<a href="' . esc_url($crumb[1]) . '" title="' . esc_attr($crumb[0]) . '">' .
                    esc_html($crumb[0]) .
                '</a>' :
                esc_html($crumb[0]);

            echo $after;

            if ($sizeof !== $key + 1) {
                echo $delimiter;
            }

            $key++;
        }

        echo $wrap_after;
    }
    
    /**
     * Breadcrumb double row
     */
    else {
        $queried_object = $wp_query->get_queried_object();

        $title = '';
        $count = count($breadcrumb);

        /**
         * Single product
         */
        if(is_product()) {
            if(isset($breadcrumb[$count-1][1])) {
                unset($breadcrumb[$count-1][1]);
            }

            $shop_page_id = wc_get_page_id('shop');
            if ($shop_page_id > 0) {
                $shop_page_title = get_the_title($shop_page_id);
                $shop_page_url = get_permalink($shop_page_id);


                $h2 = $breadcrumb[1];
                unset($breadcrumb[1]);

                $title = !empty($h2[1]) ?
                    '<a href="' . esc_url($h2[1]) . '" title="' . esc_attr($h2[0]) . '">' . esc_html($h2[0]) . '</a>' :
                    esc_html($h2[0]);
            }
        }

        /**
         * Single post
         */
        elseif(is_singular('post')) {
            $blogs_page_id = get_option('page_for_posts');
            if($blogs_page_id) {
                $blogs_page = get_page($blogs_page_id);
                if($blogs_page) {
                    $blogs_page_title = $blogs_page->post_title;
                    $blogs_page_url = get_permalink($blogs_page_id);
                    $title = '<a href="' . esc_url($blogs_page_url) . '" title="' . esc_attr($blogs_page_title) . '">' . $blogs_page_title . '</a>';
                }
            }

            if(isset($breadcrumb[$count-1][1])) {
                unset($breadcrumb[$count-1][1]);
            }
        }

        /**
         * Single Portfolio
         */
        elseif (is_singular('portfolio')) {
            $title = get_the_title();
            $breadcrumb = elessi_rebuilt_breadcrumb_portfolio($breadcrumb);
        }

        /**
         * Archive Portfolio
         */
        elseif(isset($queried_object->taxonomy) && $queried_object->taxonomy == 'portfolio_category') {
            $title = $queried_object->name;
            $breadcrumb = elessi_rebuilt_breadcrumb_portfolio($breadcrumb, false);
        }

        /**
         * page Other
         */
        else {
            if($count > 1) {
                $endBreadcrumb = $breadcrumb[$count - 1];
                unset($breadcrumb[$count - 1]);
                $title = esc_html($endBreadcrumb[0]);

                /**
                 * Page search
                 */
                if(is_search() && $count > 2 && isset($breadcrumb[$count-2][1])) {
                    unset($breadcrumb[$count-2][1]);
                }
            }
        }

        echo $title ? '<h2>' . $before . $title . $after . '</h2>' : '';

        echo $wrap_before;

        $key = 0;
        $sizeof = sizeof($breadcrumb);
        foreach ($breadcrumb as $crumb) {
            echo $before;
            echo (!empty($crumb[1])) ? '<a href="' . esc_url($crumb[1]) . '" title="' . esc_attr($crumb[0]) . '">' . esc_html($crumb[0]) . '</a>' : esc_html($crumb[0]);
            echo $after;
            echo ($sizeof !== $key + 1) ? $delimiter : '';

            $key++;
        }

        echo $wrap_after;
    }
}
