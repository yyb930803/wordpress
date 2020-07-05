<?php
/**
 * Render Time sale countdown
 * 
 * @param type $time_sale
 * @return type
 */
function nasa_time_sale($time_sale = false, $gmt = true) {
    if($time_sale) {
        return $gmt ?
        '<span class="countdown pause" data-countdown="' . esc_attr(get_date_from_gmt(date('Y-m-d H:i:s', $time_sale), 'M j Y H:i:s O')) . '"></span>' : 
        '<span class="countdown" data-countdown="' . esc_attr(date('M j Y H:i:s O', $time_sale)) . '"></span>';
    }
    
    return '';
}

/**
 * Link Instagram get access token
 */
function nasa_url_access_token_intagram() {
    $client_id = '8c2753f7dee94b8b8b0b261b91332338';
    $redirect_uri = 'https://nasatheme.com/api-instagram.html';
    $url = 'https://instagram.com/oauth/authorize/?client_id=' . $client_id . '&scope=basic&redirect_uri=' . $redirect_uri . '&response_type=token';
    
    return $url;
}

// **********************************************************************// 
// ! Fix shortcode content
// **********************************************************************//
if (!function_exists('nasa_fixShortcode')) {

    function nasa_fixShortcode($content) {
        $fix = array(
            '&nbsp;' => '',
            '<p>' => '',
            '</p>' => '',
            '<p></p>' => '',
        );
        $content = strtr($content, $fix);
        $content = wpautop(preg_replace('/<\/?p\>/', "\n", $content) . "\n");

        return do_shortcode(shortcode_unautop($content));
    }

}

add_action('wp_ajax_get_shortcode', 'nasa_get_shortcode');
add_action('wp_ajax_nopriv_get_shortcode', 'nasa_get_shortcode');
function nasa_get_shortcode() {
    die(do_shortcode($_POST["content"]));
}

/* ==========================================================================
  WooCommerce - Function get Query
  ========================================================================== */
function nasa_woocommerce_query($type, $post_per_page = -1, $cat = '', $paged = '', $not = array(), $deal_time = null) {
    global $woocommerce;
    if (!$woocommerce) {
        return array();
    }
    
    $page = $paged == '' ? ($paged = get_query_var('paged') ? (int) get_query_var('paged') : 1) : (int) $paged;

    $data = new WP_Query(nasa_woocommerce_query_args($type, $post_per_page, $cat, $page, $not, $deal_time));
    remove_filter('posts_clauses', 'nasa_order_by_rating_post_clauses');
    remove_filter('posts_clauses', 'nasa_order_by_recent_review_post_clauses');
    
    return $data;
}

/**
 * Order by rating review
 * @global type $wpdb
 * @param type $args
 * @return array
 */
function nasa_order_by_rating_post_clauses($args) {
    global $wpdb;

    $args['fields'] .= ', AVG(' . $wpdb->commentmeta . '.meta_value) as average_rating';
    $args['where'] .= ' AND (' . $wpdb->commentmeta . '.meta_key = "rating" OR ' . $wpdb->commentmeta . '.meta_key IS null) AND ' . $wpdb->comments . '.comment_approved=1 ';
    $args['join'] .= ' LEFT OUTER JOIN ' . $wpdb->comments . ' ON(' . $wpdb->posts . '.ID = ' . $wpdb->comments . '.comment_post_ID) LEFT JOIN ' . $wpdb->commentmeta . ' ON(' . $wpdb->comments . '.comment_ID = ' . $wpdb->commentmeta . '.comment_id) ';
    $args['orderby'] = 'average_rating DESC, ' . $wpdb->posts . '.post_date DESC';
    $args['groupby'] = $wpdb->posts . '.ID';

    return $args;
}

/**
 * Order by recent review
 * @global type $wpdb
 * @param type $args
 * @return array
 */
function nasa_order_by_recent_review_post_clauses($args) {
    global $wpdb;

    $args['where'] .= ' AND ' . $wpdb->comments . '.comment_approved=1 ';
    $args['join'] .= ' LEFT JOIN ' . $wpdb->comments . ' ON(' . $wpdb->posts . '.ID = ' . $wpdb->comments . '.comment_post_ID)';
    $args['orderby'] = $wpdb->comments . '.comment_date DESC, ' . $wpdb->comments . '.comment_post_ID DESC';
    $args['groupby'] = $wpdb->posts . '.ID';

    return $args;
}

/**
 * Build query for Nasa WooCommerce Products
 * 
 * @global type $woocommerce
 * @param type $type
 * @param type $post_per_page
 * @param type $cat
 * @param type $paged
 * @param type $not
 * @param type $deal_time
 * @return type
 */
function nasa_woocommerce_query_args($type, $post_per_page = -1, $cat = '', $paged = 1, $not = array(), $deal_time = null) {
    global $woocommerce;
    if (!$woocommerce) {
        return array();
    }

    $args = array(
        'post_type' => 'product',
        'posts_per_page' => $post_per_page,
        'post_status' => 'publish',
        'paged' => $paged
    );

    $args['meta_query'] = array();
    $args['meta_query'][] = $woocommerce->query->stock_status_meta_query();
    $args['tax_query'] = array('relation' => 'AND');
    
    switch ($type) {
        case 'best_selling':
            $args['meta_key']   = 'total_sales';
            $args['order']      = 'DESC';
            $args['orderby']    = 'meta_value_num';
            $args['ignore_sticky_posts'] = 1;
            $args['meta_query'][] = $woocommerce->query->visibility_meta_query();
            break;
        case 'featured_product':
            $args['ignore_sticky_posts'] = 1;

            $args['tax_query'][] = array(
                'taxonomy' => 'product_visibility',
                'field' => 'name',
                'terms' => 'featured'
            );
            $args['meta_query'][] = $woocommerce->query->visibility_meta_query();
            break;
        case 'top_rate':
            add_filter('posts_clauses', 'nasa_order_by_rating_post_clauses');
            $args['meta_query'][] = $woocommerce->query->visibility_meta_query();
            break;
        case 'on_sale':
            $args['meta_query'][] = $woocommerce->query->visibility_meta_query();
            $args['post__in'] = array_merge(array(0), wc_get_product_ids_on_sale());
            break;
        case 'recent_review':
            // nasa_order_by_recent_review_post_clauses
            add_filter('posts_clauses', 'nasa_order_by_recent_review_post_clauses');
            $args['meta_query'][] = $woocommerce->query->visibility_meta_query();

            break;
        case 'deals':
            $args['meta_query'][] = $woocommerce->query->visibility_meta_query();
            $args['meta_query'][] = array(
                'key' => '_sale_price_dates_from',
                'value' => NASA_TIME_NOW,
                'compare' => '<=',
                'type' => 'numeric'
            );
            $args['meta_query'][] = array(
                'key' => '_sale_price_dates_to',
                'value' => NASA_TIME_NOW,
                'compare' => '>',
                'type' => 'numeric'
            );
            $args['post_type'] = array('product', 'product_variation');

            if ($deal_time > 0) {
                $args['meta_query'][] = array(
                    'key' => '_sale_price_dates_to',
                    'value' => $deal_time,
                    'compare' => '>=',
                    'type' => 'numeric'
                );
            }

            break;

        case 'recent_product':
        default:
            $args['orderby'] = 'date ID';
            $args['order']   = 'DESC';
            break;
    }

    if (!empty($not)) {
        $args['post__not_in'] = $not;
        if(!empty($args['post__in'])) {
            $args['post__in'] = array_diff($args['post__in'], $args['post__not_in']);
        }
    }

    if (is_numeric($cat) && $cat) {
        $args['tax_query'][] = array(
            'taxonomy' => 'product_cat',
            'field' => 'id',
            'terms' => array($cat)
        );
    } elseif (is_array($cat) && $cat) {
        $args['tax_query'][] = array(
            'taxonomy' => 'product_cat',
            'field' => 'id',
            'terms' => $cat
        );
    }

    // Find by slug
    elseif (is_string($cat) && $cat != '') {
        $args['tax_query'][] = array(
            'taxonomy' => 'product_cat',
            'field' => 'slug',
            'terms' => $cat
        );
    }

    $product_visibility_terms = wc_get_product_visibility_term_ids();
    $arr_not_in = array($product_visibility_terms['exclude-from-catalog']);

    // Hide out of stock products.
    if ('yes' === get_option('woocommerce_hide_out_of_stock_items')) {
        $arr_not_in[] = $product_visibility_terms['outofstock'];
    }

    if (!empty($arr_not_in)) {
        $args['tax_query'][] = array(
            'taxonomy' => 'product_visibility',
            'field' => 'term_taxonomy_id',
            'terms' => $arr_not_in,
            'operator' => 'NOT IN',
        );
    }
    
    if(empty($args['orderby']) || empty($args['order'])) {
        $ordering_args      = WC()->query->get_catalog_ordering_args();
        $args['orderby']    = empty($args['orderby']) ? $ordering_args['orderby'] : $args['orderby'];
        $args['order']      = empty($args['order']) ? $ordering_args['order'] : $args['order'];
    }

    return apply_filters('nasa_woocommerce_query_args', $args);
}

/**
 * 
 * @global type $woocommerce
 * @param type $ids
 * @return \WP_Query
 */
function nasa_get_products_by_ids($ids = array()) {
    global $woocommerce;
    if (!$woocommerce || empty($ids)) {
        return null;
    }
    
    $args = array(
        'post_type' => 'product',
        'post__in' => $ids,
        'posts_per_page' => count($ids),
        'post_status' => 'publish',
        'paged' => 1
    );
    
    return new WP_Query($args);
}

/**
 * Get product ids deals
 * return []
 */
function nasa_get_pids_deal() {
    $key_cache = 'nasa_deal_product_ids';
    // MINUTE_IN_SECONDS * 15 => cache 15 minutes
    
    // Load from cache.
    $deals_product_ids = get_transient($key_cache);

    // Valid cache found.
    if (false !== $deals_product_ids) {
        return $deals_product_ids;
    }
    
    global $wpdb;
    
    $deals = $wpdb->get_results(
        $wpdb->prepare(
            "SELECT post.ID as id FROM `$wpdb->posts` AS post
            LEFT JOIN `$wpdb->postmeta` AS meta ON post.ID = meta.post_id
            LEFT JOIN `$wpdb->postmeta` AS meta2 ON post.ID = meta2.post_id
            WHERE post.post_type IN ('product', 'product_variation')
                AND post.post_status = 'publish'
                AND meta.meta_key = '_sale_price_dates_to'
                AND meta2.meta_key = '_sale_price_dates_from'
                AND CAST(meta.meta_value AS UNSIGNED) >= %d
                AND CAST(meta.meta_value AS CHAR) != ''
                AND CAST(meta2.meta_value AS UNSIGNED) <= %d
                AND CAST(meta2.meta_value AS CHAR) != ''
            GROUP BY post.ID",
            NASA_TIME_NOW,
            NASA_TIME_NOW
        )
    );
    
    if(empty($deals)) {
        return array();
        // set_transient($key_cache, array(), MINUTE_IN_SECONDS * 15);
    }
    
    $pids_sale = wc_get_product_ids_on_sale();
    $pids = array();
    foreach ($deals as $deal) {
        if(in_array($deal->id, $pids_sale)) {
            $pids[] = $deal->id;
        }
    }
    
    set_transient($key_cache, $pids, MINUTE_IN_SECONDS * 30);
    
    return $pids;
}

// **********************************************************************// 
// ! Twitter API functions
// **********************************************************************// 
function nasa_capture_tweets($consumer_key, $consumer_secret, $user_token, $user_secret, $user, $count) {
    if (!class_exists('TwitterOAuth')) {
        return;
    }

    $connection = new TwitterOAuth($consumer_key, $consumer_secret, $user_token, $user_secret);
    $content = $connection->get("statuses/user_timeline", array(
        'screen_name' => $user,
        'count' => $count
    ));

    return json_encode($content);
}

function nasa_tweet_linkify($tweet) {
    $tweet = preg_replace("#(^|[\n ])([\w]+?://[\w]+[^ \"\n\r\t< ]*)#", "\\1<a href=\"\\2\" target=\"_blank\">\\2</a>", $tweet);
    $tweet = preg_replace("#(^|[\n ])((www|ftp)\.[^ \"\t\n\r< ]*)#", "\\1<a href=\"http://\\2\" target=\"_blank\">\\2</a>", $tweet);
    $tweet = preg_replace("/@(\w+)/", "<a href=\"http://www.twitter.com/\\1\" target=\"_blank\">@\\1</a>", $tweet);
    $tweet = preg_replace("/#(\w+)/", "<a href=\"http://search.twitter.com/search?q=\\1\" target=\"_blank\">#\\1</a>", $tweet);
    return $tweet;
}

function nasa_store_tweets($file, $tweets) {
    ob_start(); // turn on the output buffering 
    $fo = fopen($file, 'w'); // opens for writing only or will create if it's not there
    if (!$fo) {
        return nasa_print_tweet_error(error_get_last());
    }
    $fr = fwrite($fo, $tweets); // writes to the file what was grabbed from the previous function
    if (!$fr) {
        return nasa_print_tweet_error(error_get_last());
    }
    fclose($fo); // closes
    ob_end_flush(); // finishes and flushes the output buffer; 
}

function nasa_pick_tweets($file) {
    ob_start(); // turn on the output buffering 
    $fo = fopen($file, 'r'); // opens for reading only 
    if (!$fo) {
        return nasa_print_tweet_error(error_get_last());
    }
    $fr = fread($fo, filesize($file));
    if (!$fr) {
        return nasa_print_tweet_error(error_get_last());
    }
    fclose($fo);
    ob_end_flush();
    return $fr;
}

function nasa_print_tweet_error($errorArray) {
    return '<p class="eth-error">Error: ' . $errorArray['message'] . 'in ' . $errorArray['file'] . 'on line ' . $errorArray['line'] . '</p>';
}

function nasa_twitter_cache_enabled() {
    return true;
}

function nasa_print_tweets($consumer_key, $consumer_secret, $user_token, $user_secret, $user, $count, $cachetime = 50) {
    if (nasa_twitter_cache_enabled()) {
        //setting the location to cache file
        $cachefile = get_template_directory() . '/includes/cache/twitterCache.json';

        // the file exitsts but is outdated, update the cache file
        if (file_exists($cachefile) && (NASA_TIME_NOW - $cachetime > filemtime($cachefile)) && filesize($cachefile) > 0) {
            //capturing fresh tweets
            $tweets = nasa_capture_tweets($consumer_key, $consumer_secret, $user_token, $user_secret, $user, $count);
            $tweets_decoded = json_decode($tweets, true);
            //if get error while loading fresh tweets - load outdated file
            if (isset($tweets_decoded['error'])) {
                $tweets = nasa_pick_tweets($cachefile);
            }
            //else store fresh tweets to cache
            else {
                nasa_store_tweets($cachefile, $tweets);
            }
        }
        //file doesn't exist or is empty, create new cache file
        elseif (!file_exists($cachefile) || filesize($cachefile) == 0) {
            $tweets = nasa_capture_tweets($consumer_key, $consumer_secret, $user_token, $user_secret, $user, $count);
            $tweets_decoded = json_decode($tweets, true);
            //if request fails, and there is no old cache file - print error
            if (isset($tweets_decoded['error'])) {
                return 'Error: ' . $tweets_decoded['error'];
            }
            //make new cache file with request results
            else {
                nasa_store_tweets($cachefile, $tweets);
            }
        }
        //file exists and is fresh
        //load the cache file
        else {
            $tweets = nasa_pick_tweets($cachefile);
        }
    } else {
        $tweets = nasa_capture_tweets($consumer_key, $consumer_secret, $user_token, $user_secret, $user, $count);
    }

    $tweets = json_decode($tweets, true);
    $html = '<ul class="twitter-list">';

    foreach ($tweets as $tweet) {
        $html .= '<li class="lastItem firstItem"><div class="media"><i class="pull-left fa fa-twitter"></i><div class="media-body">' . $tweet['text'] . '</div></div></li>';
    }
    $html .= '</ul>';

    return nasa_tweet_linkify($html);
}

//convert dates to readable format  
if (!function_exists('nasa_relative_time')) {

    function nasa_relative_time($a) {
        //get current timestampt
        $b = strtotime('now');
        //get timestamp when tweet created
        $c = strtotime($a);
        //get difference
        $d = $b - $c;
        //calculate different time values
        $minute = 60;
        $hour = $minute * 60;
        $day = $hour * 24;
        $week = $day * 7;

        if (is_numeric($d) && $d > 0) {
            //if less then 3 seconds
            if ($d < 3) {
                return esc_html__('right now', 'nasa-core');
            }
            //if less then minute
            if ($d < $minute) {
                return floor($d) . esc_html__(' seconds ago', 'nasa-core');
            }
            //if less then 2 minutes
            if ($d < $minute * 2) {
                return esc_html__('about 1 minute ago', 'nasa-core');
            }
            //if less then hour
            if ($d < $hour) {
                return floor($d / $minute) . esc_html__(' minutes ago', 'nasa-core');
            }
            //if less then 2 hours
            if ($d < $hour * 2) {
                return esc_html__('about 1 hour ago', 'nasa-core');
            }
            //if less then day
            if ($d < $day) {
                return floor($d / $hour) . esc_html__(' hours ago', 'nasa-core');
            }
            //if more then day, but less then 2 days
            if ($d > $day && $d < $day * 2) {
                return esc_html__('yesterday', 'nasa-core');
            }
            //if less then year
            if ($d < $day * 365) {
                return floor($d / $day) . esc_html__(' days ago', 'nasa-core');
            }
            //else return more than a year
            return esc_html__('over a year ago', 'nasa-core');
        }
    }

}

// Do shortcode anything more ...
add_action('init', 'nasa_custom_do_sc');
function nasa_custom_do_sc() {
    add_filter('widget_text', 'do_shortcode');
    add_filter('the_excerpt', 'do_shortcode');
}

// Recommend product
add_action('nasa_recommend_product', 'nasa_get_recommend_product', 10, 1);
function nasa_get_recommend_product($cat = null) {
    global $nasa_opt, $woocommerce;

    if (!$woocommerce || (isset($nasa_opt['enable_recommend_product']) && $nasa_opt['enable_recommend_product'] != '1')) {
        return '';
    }

    $columns_number = isset($nasa_opt['recommend_columns_desk']) ? (int) $nasa_opt['recommend_columns_desk'] : 5;

    $columns_number_small = isset($nasa_opt['recommend_columns_small']) ? (int) $nasa_opt['recommend_columns_small'] : 1;
    $columns_number_tablet = isset($nasa_opt['recommend_columns_tablet']) ? (int) $nasa_opt['recommend_columns_tablet'] : 3;

    $number = (isset($nasa_opt['recommend_product_limit']) && ((int) $nasa_opt['recommend_product_limit'] >= $columns_number)) ? (int) $nasa_opt['recommend_product_limit'] : 9;

    $loop = nasa_woocommerce_query('featured_product', $number, (int) $cat ? (int) $cat : null, 1);
    if ($loop->found_posts) {
        ?>
        <div class="row margin-bottom-50 nasa-recommend-product">
            <div class="large-12 columns">
                <div class="woocommerce">
                    <?php
                    $type = null;
                    $data_margin = 10;
                    $height_auto = 'false';
                    $arrows = 1;
                    $title_shortcode = !isset($nasa_opt['recommend_product_title']) ? esc_html__('Recommend Products', 'nasa-core') : $nasa_opt['recommend_product_title'];
                    include NASA_CORE_PRODUCT_LAYOUTS . 'nasa_products/carousel.php';
                    ?>
                </div>
                <?php
                if (isset($nasa_opt['recommend_product_position']) && $nasa_opt['recommend_product_position'] == 'top') :
                    echo '<hr class="nasa-separator" />';
                endif;
                ?>
            </div>
        </div>
        <?php
    }
}

/* ============================================= */

function nasa_getProductDeals($id = null) {
    if (!(int) $id || !function_exists('wc_get_product')) {
        return null;
    }

    if ($product = wc_get_product((int) $id)) {
        $time_sale = $time_from = false;

        if ($product->get_type() == 'variable') {
            $args = array(
                'fields' => 'ids',
                'post_type' => 'product_variation',
                'post_parent' => (int) $id,
                'posts_per_page' => 100,
                'post_status' => 'publish',
                'orderby' => 'ID',
                'order' => 'ASC',
                'paged' => 1
            );

            $children = new WP_Query($args);
            if (!empty($children->posts)) {
                foreach ($children->posts as $variable) {
                    $time_sale = get_post_meta($variable, '_sale_price_dates_to', true);
                    $time_from = get_post_meta($variable, '_sale_price_dates_from', true);
                    if ($time_sale && $time_sale > NASA_TIME_NOW) {
                        break;
                    }
                }
            }
        }

        if (!$time_sale) {
            $time_sale = !$time_sale ? get_post_meta((int) $id, '_sale_price_dates_to', true) : $time_sale;
            $time_from = get_post_meta((int) $id, '_sale_price_dates_from', true);
        }

        if ($time_sale > NASA_TIME_NOW && $time_from && $time_from < NASA_TIME_NOW) {
            $product->time_sale = $time_sale;
            return $product;
        }
    }

    return null;
}

function nasa_getProductGrid($notid = null, $catIds = null, $type = 'best_selling', $limit = 6) {
    $notIn = $notid ? array($notid) : array();
    return nasa_woocommerce_query($type, $limit, $catIds, 1, $notIn);
}

function nasa_getThumbs($_id, $image_pri, $count_imgs, $img_thumbs) {
    $thumbs = '<div class="nasa-sc-p-thumbs">';
    $thumbs .= '<div class="product-thumbnails-' . $_id . ' owl-carousel">';

    if ($image_pri) {
        $thumbs .= '<a href="javascript:void(0);" class="active-thumbnail nasa-thumb-a">';
        $thumbs .= '<img class="nasa-thumb-img" src="' . esc_attr($image_pri['thumb'][0]) . '" />';
        $thumbs .= '</a>';
    }

    if ($count_imgs) {
        foreach ($img_thumbs as $thumb) {
            $thumbs .= '<a href="javascript:void(0);" class="nasa-thumb-a">';
            $thumbs .= '<img class="nasa-thumb-img" src="' . esc_attr($thumb['src'][0]) . '" />';
            $thumbs .= '</a>';
        }
    } else {
        $thumbs .= sprintf('<a href="%s" class="active-thumbnail"><img src="%s" /></a>', wc_placeholder_img_src(), wc_placeholder_img_src());
    }

    $thumbs .= '</div>';
    $thumbs .= '</div>';
    
    return $thumbs;
}

function nasa_getThumbsVertical($_id, $image_pri, $count_imgs, $img_thumbs) {
    $thumbs = '';
    $show = 3;
    $k = 0;
    
    if ($image_pri) {
        $thumbs .= '<a href="javascript:void(0);" class="nasa-thumb-a"><div class="row nasa-pos-relative">';
        $thumbs .= '<div class="large-4 medium-4 small-2 columns nasa-icon-current"><i class="pe-7s-angle-left"></i></div>';
        $thumbs .= '<div class="large-8 medium-8 small-10 columns"><img class="nasa-thumb-img" src="' . esc_attr($image_pri['thumb'][0]) . '" /></div>';
        $thumbs .= '</div></a>';
        $k++;
    }

    if ($count_imgs) {
        foreach ($img_thumbs as $thumb) {
            $k++;
            $thumbs .= '<a href="javascript:void(0);" class="nasa-thumb-a"><div class="row nasa-pos-relative">';
            $thumbs .= '<div class="large-4 medium-4 small-2 columns nasa-icon-current"><i class="pe-7s-angle-left"></i></div>';
            $thumbs .= '<div class="large-8 medium-8 small-10 columns"><img class="nasa-thumb-img" src="' . esc_attr($thumb['src'][0]) . '" /></div>';
            $thumbs .= '</div></a>';
        }
    } else {
        $k++;
        $imgSrc = wc_placeholder_img_src();
        $thumbs .=
            '<a href="' . $imgSrc . '" class="nasa-thumb-a">' .
                '<div class="nasa-pos-relative">' .
                    '<div class="large-4 medium-4 small-2 columns nasa-icon-current">' .
                        '<i class="pe-7s-angle-left"></i>' .
                    '</div>' .
                    '<div class="large-8 medium-8 small-10 columns">' .
                        '<img src="' . $imgSrc . '" />' .
                    '</div>' .
                '</div>' .
            '</a>';
    }

    $thumbs_begin = '<div class="nasa-sc-p-thumbs">';
    $attr_top = ($k <= $show) ? ' data-top="1"' : '';

    $thumbs_begin .= '<div class="y-thumb-images-' . $_id . ' images-popups-gallery" data-show="' . $show . '" data-autoplay="1"' . $attr_top . '>';

    $thumbs .= '</div>';
    $thumbs .= '</div>';

    return $thumbs_begin . $thumbs;
}

function nasa_category_thumbnail($category, $type = '280x150') {
    $small_thumbnail_size = apply_filters('subcategory_archive_thumbnail_size', $type);
    $thumbnail_id = get_term_meta($category->term_id, 'thumbnail_id', true);
    
    if ($thumbnail_id) {
        $image = wp_get_attachment_image_src($thumbnail_id, $small_thumbnail_size);
        if (isset($image[0])) {
            $image_src = $image[0];
            $image_width = $image[1];
            $image_height = $image[2];
        }
    } else {
        $image_src = wc_placeholder_img_src();
        $image_width = 100;
        $image_height = 100;
    }

    if ($image_src) {
        echo '<img src="' . esc_url($image_src) . '" alt="' . esc_attr($category->name) . '" width="' . $image_width . '" height="' . $image_height . '" />';
    }
}

/**
 * Deprecated
 * 
 * @param type $atts
 * @return string
 */
function nasa_shortcode_vars($atts) {
    $variables = array();
    
    if (!empty($atts)) {
        $old_key = '';
        foreach ($atts as $value) {
            $value = explode('=', $value);
            $count = count($value);
            
            if ($count == 2) {
                $old_key = $value[0];
                $variables[$old_key] = str_replace('"', '', $value[1]);
            } 
            
            if ($count == 1) {
                $variables[$old_key] .= ' ' . str_replace('"', '', $value[0]);
            }
        }
    }

    return $variables;
}

/**
 * Deprecated
 * 
 * @param type $atts
 * @return string
 */
function nasa_shortcode_text($name = '', $atts = array(), $content = '') {
    global $id_shortcode;
    $GLOBALS['id_shortcode'] = (!isset($id_shortcode) || !$id_shortcode) ? 1 : $id_shortcode + 1;
    $height = (isset($atts['min_height']) && (int) $atts['min_height']) ? (int) $atts['min_height'] . 'px;' : '200px;';
    $height .= (isset($atts['height']) && (int) $atts['height']) ? 'height:' . (int) $atts['height'] . 'px;' : '';
    $attsSC = array();
    if (!empty($atts)) {
        foreach ($atts as $key => $value) {
            $attsSC[] = $key . '="' . $value . '"';
        }
    }

    $result = '<div class="nasa_load_ajax" data-id="' . $id_shortcode . '" id="nasa_sc_' . $id_shortcode . '" data-shortcode="' . $name . '" style="min-height: ' . $height . '">';

    $result .= '<div class="nasa-loader"></div>';
    $result .= '<div class="nasa-shortcode-content hidden-tag">[' . $name;
    $result .= !empty($attsSC) ? ' ' . implode(' ', $attsSC) : '';
    $result .= trim($content) != '' ? ']' . esc_html($content) . '[/' . $name : '';
    $result .= ']</div></div>';

    return $result;
}

/* ============================================= */
/**
 * Set cookie products viewed
 */
remove_action('template_redirect', 'wc_track_product_view', 25);
add_action('template_redirect', 'nasa_set_products_viewed', 20);
function nasa_set_products_viewed() {
    global $nasa_opt;

    if (!class_exists('WooCommerce') || !is_singular('product') || (isset($nasa_opt['disable-viewed']) && $nasa_opt['disable-viewed'])) {
        return;
    }

    global $post;

    $product_id = isset($post->ID) ? (int) $post->ID : 0;

    if ($product_id) {

        $limit = !isset($nasa_opt['limit_product_viewed']) || !(int) $nasa_opt['limit_product_viewed'] ?
            12 : (int) $nasa_opt['limit_product_viewed'];

        $list_viewed = !empty($_COOKIE[NASA_COOKIE_VIEWED]) ? explode('|', $_COOKIE[NASA_COOKIE_VIEWED]) : array();
        if (!in_array((int) $product_id, $list_viewed)) {
            if (count($list_viewed) > $limit) {
                array_shift($list_viewed);
            }
            $list_viewed[] = $product_id;

            setcookie(NASA_COOKIE_VIEWED, implode('|', $list_viewed), 0, COOKIEPATH, COOKIE_DOMAIN, false, false);
        }
    }
}

/**
 * Get cookie products viewed
 */
function nasa_get_products_viewed() {
    global $nasa_opt;
    $query = null;

    if (!class_exists('WooCommerce') || (isset($nasa_opt['disable-viewed']) && $nasa_opt['disable-viewed'])) {
        return $query;
    }

    $viewed_products = !empty($_COOKIE[NASA_COOKIE_VIEWED]) ? explode('|', $_COOKIE[NASA_COOKIE_VIEWED]) : array();
    if (!empty($viewed_products)) {

        $limit = !isset($nasa_opt['limit_product_viewed']) || !(int) $nasa_opt['limit_product_viewed'] ? 12 : (int) $nasa_opt['limit_product_viewed'];

        $query_args = array(
            'posts_per_page' => $limit,
            'no_found_rows' => 1,
            'post_status' => 'publish',
            'post_type' => 'product',
            'post__in' => $viewed_products,
            'orderby' => 'post__in',
        );

        if ('yes' === get_option('woocommerce_hide_out_of_stock_items')) {
            $query_args['tax_query'] = array(
                array(
                    'taxonomy' => 'product_visibility',
                    'field' => 'name',
                    'terms' => 'outofstock',
                    'operator' => 'NOT IN',
                ),
            );
        }

        $query = new WP_Query($query_args);
    }

    return $query;
}

function nasa_get_product_meta_value($post_id, $field_id = null) {
    $meta_value = get_post_meta($post_id, 'wc_productdata_options', true);
    if (isset($meta_value[0]) && $field_id) {
        return isset($meta_value[0][$field_id]) ? $meta_value[0][$field_id] : '';
    }

    return isset($meta_value[0]) ? $meta_value[0] : $meta_value;
}

/**
 * variation gallery images
 */
add_filter('woocommerce_available_variation', 'nasa_variation_gallery_images');
function nasa_variation_gallery_images($variation) {
    global $nasa_opt;
    if(!isset($nasa_opt['gallery_images_variation']) || $nasa_opt['gallery_images_variation']) {
        if(!isset($variation['nasa_gallery_variation'])) {
            $variation['nasa_gallery_variation'] = array();
            $variation['nasa_variation_back_img'] = '';
            $gallery = get_post_meta($variation['variation_id'], 'nasa_variation_gallery_images', true);

            if($gallery) {
                $variation['nasa_gallery_variation'] = $gallery;
                $galleryIds = explode(',', $gallery);
                $back_id = isset($galleryIds[0]) && (int) $galleryIds[0] ? (int) $galleryIds[0] : false;
                $image_size = apply_filters('single_product_archive_thumbnail_size', 'shop_catalog');
                $image_back = $back_id ? wp_get_attachment_image_src($back_id, $image_size) : null;
                $variation['nasa_variation_back_img'] = isset($image_back[0]) ? $image_back[0] : '';
            }
        }
    }
    
    return $variation;
}

/**
 * Enable Gallery images variation in front-end
 */
add_action('woocommerce_after_add_to_cart_button', 'nasa_enable_variation_gallery_images', 30);
function nasa_enable_variation_gallery_images() {
    global $product, $nasa_opt;
    
    if(!isset($nasa_opt['gallery_images_variation']) || $nasa_opt['gallery_images_variation']) {
        $productType = $product->get_type();
        if($productType == 'variable' || $productType == 'variation') {
            $mainProduct = ($productType == 'variation') ?
                wc_get_product(wp_get_post_parent_id($product->get_id())) : $product;
            
            if (!$mainProduct) {
                return;
            }

            $variations = $mainProduct->get_available_variations();
            foreach ($variations as $vari) {
                if(isset($vari['nasa_gallery_variation']) && !empty($vari['nasa_gallery_variation'])) {
                    echo '<input type="hidden" name="nasa-gallery-variation-supported" class="nasa-gallery-variation-supported" value="1" />';
                    return;
                }
            }
        }
    }
}

/**
 * Size Guide Product
 */
add_action('woocommerce_single_product_summary', 'nasa_size_guide', 35);
function nasa_size_guide() {
    global $nasa_opt, $product;
    
    if(isset($nasa_opt['enable_size_guide']) && !$nasa_opt['enable_size_guide']) {
        return;
    }
    
    $size_guide_src = isset($nasa_opt['size_guide']) && $nasa_opt['size_guide'] ? $nasa_opt['size_guide'] : '';
    $term_id = false;
    $size_guide_id = false;
    $product_cats = get_the_terms($product->get_id(), 'product_cat');
    if($product_cats) {
        foreach ($product_cats as $cat) {
            $term_id = $cat->term_id;
            break;
        }
    }
    
    if($term_id) {
        $size_guide_id = get_term_meta($term_id, 'cat_size_guide', true);
        
        if(!$size_guide_id) {
            global $nasa_root_term_id;
            
            if($nasa_root_term_id) {
                $term_id = $nasa_root_term_id;
            } else {
                $ancestors = get_ancestors($term_id, 'product_cat');
                $term_id = $ancestors ? end($ancestors) : 0;
                $GLOBALS['nasa_root_term_id'] = $term_id;
            }

            if($term_id) {
                $size_guide_id = get_term_meta($term_id, 'cat_size_guide', true);
            }
        }
        
        if ($size_guide_id) {
            $size_guide_src_overr = wp_get_attachment_image_url($size_guide_id, 'full');
            $size_guide_src = $size_guide_src_overr ? $size_guide_src_overr : $size_guide_src;
        }
    }
    
    if($size_guide_src) {
        echo '<div class="nasa-size-guide"><a class="nasa-size-guide-popup" href="javascript:void(0);" data-close="' . esc_html__('Close', 'nasa-core') . '" data-src="' . esc_url($size_guide_src) . '">' . esc_html__('Size Guide', 'nasa-core') . '</a></div>';
    }
}

/**
 * Get Pins
 * 
 * @param type $type
 * @return type
 */
function nasa_get_pin_arrays($type = 'nasa_pin_pb') {
    $pins = get_posts(array(
        'posts_per_page'    => -1,
        'post_status'       => 'publish',
        'post_type'         => $type
    ));
    
    $pin_items = array(esc_html__('Select Item', 'nasa-core') => '');
    if($pins) {
        foreach ($pins as $pin) {
            $pin_items[$pin->post_title] = $pin->post_name;
        }
    }
    
    return $pin_items;
}

/**
 * Build key short-code
 * 
 * @param type $shortcode
 * @param type $dfAtts
 * @param type $atts
 * @return type
 */
function nasa_key_shortcode($shortcode, $dfAtts, $atts) {
    global $nasa_opt;
    
    $string = $shortcode;
    
    foreach ($dfAtts as $key => $value) {
        /**
         * For Atts
         */
        if (isset($atts[$key])) {
            $string .= '_' . $atts[$key];
        }
        
        /**
         * For Default Atts
         */
        elseif ($value) {
            $string .= '_' . $value;
        }
    }
    
    /**
     * Support for WPML
     */
    $lang = defined('ICL_LANGUAGE_CODE') ? ICL_LANGUAGE_CODE : get_option('WPLANG');
    
    $string .= $lang ? '_' . $lang : '';
    
    $string .= isset($nasa_opt['nasa_in_mobile']) && $nasa_opt['nasa_in_mobile'] ? '_mobile' : '';
    
    return $string ? $string : '';
}

/**
 * Set Cache for short-code
 * 
 * @param type $key
 * @param type $content
 * @return type
 */
function nasa_set_cache_shortcode($key = false, $content = '') {
    if (!$key) {
        return false;
    }
    
    return Nasa_Caching::set_content($key, $content, 'shortcodes');
}

/**
 * Set Cache for short-code
 * 
 * @param type $key
 * @param type $content
 * @return type
 */
function nasa_get_cache_shortcode($key = false) {
    if (!$key) {
        return false;
    }
    
    return Nasa_Caching::get_content($key, 'shortcodes');
}
