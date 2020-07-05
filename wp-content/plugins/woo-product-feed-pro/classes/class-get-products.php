<?php
/**
 * Class for generating the actual feeds
 */
class WooSEA_Get_Products {

	private $feedback;
	public $feed_config;
	private $products = array();
	private $utm = array();
	public $utm_part;
	public $project_config;
	private $upload_dir;
	private $base;
	private $path;
	private $file;

        public function __construct() {
                $this->get_products = array();
        }

	/**
	 * Function to add CDATA brackets to title, short_description and description attributes
	 */
	protected function woosea_append_cdata( $string ){
		return $string;
//		return "<![CDATA[ $string ]]>"; 
	}

	/**
	 * An improved function for the strip_tags
	 * Removing tags but replacing them with spaces instead of just removing them
	 */
	public function rip_tags( $string ) { 
    		// ----- remove HTML TAGs ----- 
    		$string = preg_replace ('/<[^>]*>/', ' ', $string); 
    
    		// ----- remove control characters ----- 
    		$string = str_replace("\r", '', $string);    // --- replace with empty space
    		$string = str_replace("\n", ' ', $string);   // --- replace with space
    		$string = str_replace("\t", ' ', $string);   // --- replace with space
    
    		// ----- remove multiple spaces ----- 
    		$string = trim(preg_replace('/ {2,}/', ' ', $string));
   
    		return $string; 
	}

	/**
	 * Get all approved product review comments for Google's Product Review Feeds
	 */
	public function woosea_get_reviews ( $product_data, $product ) {
		$approved_reviews = array();
		$prod_id = $product_data['id'];

		if($product_data['product_type'] == "variation"){
			$prod_id = $product_data['item_group_id'];
		}

            	$reviews = get_comments(array(
        		'post_id'               => $prod_id,
        		'comment_type'          => 'review',
        		'comment_approved'      => 1,
			'parent'		=> 0,
		));

		// Loop through all product reviews for this specific products (ternary operators)
		foreach($reviews as $review_raw){
			$review = array();
			$review['review_reviewer_image'] = empty($product_data['reviewer_image']) ? '' : $product_data['reviewer_image'];
			$review['review_ratings'] = get_comment_meta( $review_raw->comment_ID, 'rating', true);
			$review['review_id'] = $review_raw->comment_ID;

			// Names need to be anonomyzed			
			$name_pieces = explode(" ", $review_raw->comment_author);
			$nr_name_pieces = count($name_pieces);
			$cnt = 0;
			$name = "";
			foreach($name_pieces as $n_piece){
				if($cnt > 0){
					$n_piece = substr($n_piece, 0, 1);
				}	
				$name .= $n_piece." ";
				$cnt++;
			}

			// Remove strange charachters from reviewer name
			$review['reviewer_name'] = $this->rip_tags(trim(ucfirst($name)));
			$review['reviewer_name'] = html_entity_decode((str_replace("\r", "", $review['reviewer_name'])), ENT_QUOTES | ENT_XML1, 'UTF-8');
			$review['reviewer_name'] = preg_replace( '/\[(.*?)\]/', ' ', $review['reviewer_name'] );
			$review['reviewer_name'] = str_replace("&#xa0;", "", $review['reviewer_name']);
                        $review['reviewer_name'] = $this->woosea_utf8_for_xml( $review['reviewer_name'] );

			$review['reviewer_id'] = $review_raw->user_id;
			$review['review_timestamp'] = $review_raw->comment_date;

			// Remove strange characters from review title
			$review['title'] = empty($product_data['title']) ? '' : $product_data['title'];
			$review['title'] = $this->rip_tags($review['title']);
			$review['title'] = html_entity_decode((str_replace("\r", "", $review['title'])), ENT_QUOTES | ENT_XML1, 'UTF-8');
			$review['title'] = preg_replace( '/\[(.*?)\]/', ' ', $review['title'] );
			$review['title'] = str_replace("&#xa0;", "", $review['title']);
                        $review['title'] = $this->woosea_utf8_for_xml( $review['title'] );

			// Remove strange charchters from review content
			$review['content'] = $review_raw->comment_content;
			$review['content'] = $this->rip_tags($review['content']);
			$review['content'] = html_entity_decode((str_replace("\r", "", $review['content'])), ENT_QUOTES | ENT_XML1, 'UTF-8');
			$review['content'] = preg_replace( '/\[(.*?)\]/', ' ', $review['content'] );
			$review['content'] = str_replace("&#xa0;", "", $review['content']);
                        $review['content'] = $this->woosea_utf8_for_xml( $review['content'] );

			$review['review_product_name'] = $product_data['title'];
			$review['review_url'] = $product_data['link'];
			$review['review_product_url'] = $product_data['link'];
			array_push($approved_reviews, $review);
		}
		$review_count = $product->get_review_count();
		$review_average = $product->get_average_rating();
		return $approved_reviews;
	}

	/**
	 * Strip unwanted UTF chars from string
	 */
	public function woosea_utf8_for_xml( $string ){
		$string = html_entity_decode($string);
    		return preg_replace ('/[^\x{0009}\x{000a}\x{000d}\x{0020}-\x{D7FF}\x{E000}-\x{FFFD}]+/u', ' ', $string);
	}	

	/**
         * Function that will create an append with Google Analytics UTM parameters
         * Removes UTM paramaters that are left blank
	 */
	public function woosea_append_utm_code ( $feed_config, $productId, $parentId, $link ) {
		// AdTribes conversionId
		if(array_key_exists('adtribes_conversion', $feed_config)){
			$adtribesConvId = "$feed_config[project_hash]|adtribes|$productId";
		} else {
			$adtribesConvId = "";
		}	
		
		# Create Array of Google Analytics UTM codes				
		$utm = array (
			'adTribesID' => $adtribesConvId,
			'utm_source' => $feed_config['utm_source'],
			'utm_campaign' => $feed_config['utm_campaign'],
			'utm_medium' => $feed_config['utm_medium'],
			'utm_term' => $productId,
			'utm_content' => $feed_config['utm_content']
		);

		// GA tracking is disabled, so remove from array
		if(!array_key_exists('utm_on', $feed_config)){
			unset($utm['utm_source']);
			unset($utm['utm_campaign']);
			unset($utm['utm_medium']);
			unset($utm['utm_term']);
			unset($utm['utm_content']);
		}
		$utm = array_filter($utm); // Filter out empty or NULL values from UTM array		
               
		$utm_part = "";	
		foreach ($utm as $key => $value ) {
			$value = str_replace(" ", "%20", $value);

			if($feed_config['fields'] == "google_drm"){
				$utm_part .= "&$key=$value";
			} else {
				$utm_part .= "&amp;$key=$value";
			}
		}

		/**
		 * Get the default WPML language
		 * As that does not have ?lang= behind the links
		 */
		if(isset($feed_config['WPML'])){
			if ((is_plugin_active('sitepress-multilingual-cms')) OR ( function_exists('icl_object_id') )){
		                if( !class_exists( 'Polylang' ) ) {
					global $sitepress;
					$default_lang = $sitepress->get_default_language();	

					if (preg_match("/\?/i", $link)){
						$utm_part = "&amp;".ltrim($utm_part, '&amp;');
					} else {
						$utm_part = "?".ltrim($utm_part, '&amp;');
					}
				}
			}
		} else {
			# Strip first & from utm 
			if($parentId > 0){
				# Even though variation products always have parameters in the URL we still need to check and make sure they are there
				if(strpos($link, '?') !== false){
					$utm_part = "&amp;".ltrim($utm_part, '&amp;');
				} else {
					$utm_part = "?".ltrim($utm_part, '&amp;');
				}
			} else {
				$utm_part = "?".ltrim($utm_part, '&amp;');
			}
		}
		return $utm_part;
	}

       	/**
         * Converts an ordinary xml string into a CDATA string
         */
    	public function woosea_convert_to_cdata( $string ) { 
		return "<![CDATA[ $string ]]>"; 
	}

	/**
 	 * Get number of variation sales for a product variation
	 */
	private function woosea_get_nr_orders_variation ( $variation_id ) {
    		global $wpdb;

		$nr_sales = 0;

		if(is_numeric($variation_id)){
	    		// Getting all Order Items with that variation ID
    			$nr_sales = $wpdb->get_col( $wpdb->prepare( "
        			SELECT count(*) AS nr_sales
        			FROM {$wpdb->prefix}woocommerce_order_itemmeta 
        			WHERE meta_value = %s
    			", $variation_id ) );
		}
		return $nr_sales;
	}

	/**
	 * Get custom attribute names for a product
	 */
	private function get_custom_attributes( $productId ) {
        	global $wpdb;
        	$list = array();

		$sql = "SELECT meta.meta_id, meta.meta_key as name, meta.meta_value as type FROM " . $wpdb->prefix . "postmeta" . " AS meta, " . $wpdb->prefix . "posts" . " AS posts WHERE meta.post_id=".$productId." AND meta.post_id = posts.id GROUP BY meta.meta_key ORDER BY meta.meta_key ASC";
	      	$data = $wpdb->get_results($sql);

        	if (count($data)) {

                	foreach ($data as $key => $value) {
                        	$value_display = str_replace("_", " ",$value->name);
			       	if (!preg_match("/_product_attributes/i",$value->name)){
					$list[$value->name] = ucfirst($value_display);
                        	} else {
	                                $product_attr = unserialize($value->type);
					if(!empty($product_attr)){	
                                		foreach ($product_attr as $key_inner => $arr_value) {
                                        		$value_display = @str_replace("_", " ",$arr_value['name']);
                                        		$list[$key_inner] = ucfirst($value_display);
                                		}
					}	
				}
                	}
	              	return $list;
        	}
        	return false;
	}

	/**
	 * Get orders for given time period used in filters
	 */
	public function woosea_get_orders( $project_config ){

		$allowed_products = array();

		if(isset($project_config['total_product_orders_lookback'])){

			if($project_config['total_product_orders_lookback'] > 0){

				$query_args = array(
        				'post_type'      => wc_get_order_types(),
        				'post_status'    => array_keys( wc_get_order_statuses() ),
        				'posts_per_page' => 999999999999,
   	 			);
    				$all_orders      = get_posts( $query_args );

				$today = date("Y-m-d");
				$today_limit = date('Y-m-d', strtotime('-'.$project_config['total_product_orders_lookback'].' days', strtotime($today)));

		    		foreach ( $all_orders as $orders ) {		
					$order = wc_get_order( $orders-> ID);
					$order_data = $order->get_data();
	
					$order_date_created = $order_data['date_created']->date('Y-m-d');

					if($order_date_created >= $today_limit){
						foreach ($order->get_items() as $item_key => $item_values){
							$order_product_id = $item_values->get_product_id();
							$order_variation_id = $item_values->get_variation_id();

							// When a variation was sold, add the variation	
							if($order_variation_id > 0){
								$order_product_id = $order_variation_id;
							}

							// Only for existing products
							if($order_product_id > 0){

								// Only add products that are not in the array yet
								if(!in_array($order_product_id, $allowed_products)){
									$allowed_products[] = $order_product_id;
								}
							}
						}
					}	
				}
			}
		}
		return $allowed_products;
	}

	/**
	 * Get category path (needed for Prisjakt)
	 */
	public function woosea_get_term_parents( $id, $taxonomy, $link = false, $project_taxonomy, $nicename = false, $visited = array() ) {
		// Only add Home to the beginning of the chain when we start buildin the chain
		if(empty($visited)){
			$chain = 'Home';
		} else {
			$chain = '';
		}

		$parent = get_term( $id, $taxonomy );
		$separator = ' &gt; ';

		if( $project_taxonomy == "Prisjakt" ){
			$separator = ' / ';
		}

		if ( is_wp_error( $parent ) )
			return $parent;
		
		if($parent){
			if ($nicename){
				$name = $parent->slug;
			} else {
				$name = $parent->name;
			}

			if ($parent->parent && ( $parent->parent != $parent->term_id ) && !in_array( $parent->parent, $visited, TRUE )){
				$visited[] = $parent->parent;
				$chain .= $this->woosea_get_term_parents( $parent->parent, $taxonomy, $link, $separator, $nicename, $visited );
			}
 
			if ($link){
				$chain .= $separator.$name;
			} else {
				$chain .= $separator.$name;
			}
		}
		return $chain;
	}	

	/**
	 * Get all configured shipping zones
	 */
	public function woosea_get_shipping_zones () {
		if( class_exists( 'WC_Shipping_Zones' ) ) {
			$all_zones = WC_Shipping_Zones::get_zones();
			return $all_zones;
		}
		return false;
	}

	/**
	 * Get installment for product
	 */
	public function woosea_get_installment ($project_config, $productId){
		$installment = "";
                $currency = get_woocommerce_currency();
		if(isset($project_config['WCML'])){
			$currency = $project_config['WCML'];
		}
		$installment_months = get_post_meta($productId, '_woosea_installment_months', true);
		$installment_amount = get_post_meta($productId, '_woosea_installment_amount', true);

		if(!empty($installment_amount)){
			$installment = $installment_months.":".$installment_amount." ".$currency;
		}
		return $installment;
	}
	
	/**
      	 * COnvert country name to two letter code
	 */
    	public function woosea_country_to_code( $country ){

    		$countryList = array(
        		'AF' => 'Afghanistan',
        		'AX' => 'Aland Islands',
		        'AL' => 'Albania',
		        'DZ' => 'Algeria',
		        'AS' => 'American Samoa',
		        'AD' => 'Andorra',
		        'AO' => 'Angola',
		        'AI' => 'Anguilla',
		        'AQ' => 'Antarctica',
		        'AG' => 'Antigua and Barbuda',
		        'AR' => 'Argentina',
		        'AM' => 'Armenia',
		        'AW' => 'Aruba',
		        'AU' => 'Australia',
			'AT' => 'Austria',
			'AZ' => 'Azerbaijan',
			'BS' => 'Bahamas the',
			'BH' => 'Bahrain',
			'BD' => 'Bangladesh',
			'BB' => 'Barbados',
			'BY' => 'Belarus',
			'BE' => 'Belgium',
			'BZ' => 'Belize',
			'BJ' => 'Benin',
			'BM' => 'Bermuda',
			'BT' => 'Bhutan',
			'BO' => 'Bolivia',
			'BA' => 'Bosnia and Herzegovina',
			'BW' => 'Botswana',
			'BV' => 'Bouvet Island (Bouvetoya)',
			'BR' => 'Brazil',
			'IO' => 'British Indian Ocean Territory (Chagos Archipelago)',
			'VG' => 'British Virgin Islands',
			'BN' => 'Brunei Darussalam',
			'BG' => 'Bulgaria',
			'BF' => 'Burkina Faso',
			'BI' => 'Burundi',
			'KH' => 'Cambodia',
			'CM' => 'Cameroon',
			'CA' => 'Canada',
			'CV' => 'Cape Verde',
			'KY' => 'Cayman Islands',
			'CF' => 'Central African Republic',
			'TD' => 'Chad',
			'CL' => 'Chile',
			'CN' => 'China',
			'CX' => 'Christmas Island',
			'CC' => 'Cocos (Keeling) Islands',
			'CO' => 'Colombia',
			'KM' => 'Comoros the',
			'CD' => 'Congo',
			'CG' => 'Congo the',
			'CK' => 'Cook Islands',
			'CR' => 'Costa Rica',
			'CI' => 'Cote d\'Ivoire',
			'HR' => 'Croatia',
			'CU' => 'Cuba',
			'CY' => 'Cyprus',
			'CZ' => 'Czech Republic',
			'DK' => 'Denmark',
			'DJ' => 'Djibouti',
			'DM' => 'Dominica',
			'DO' => 'Dominican Republic',
			'EC' => 'Ecuador',
			'EG' => 'Egypt',
			'SV' => 'El Salvador',
			'GQ' => 'Equatorial Guinea',
			'ER' => 'Eritrea',
			'EE' => 'Estonia',
			'ET' => 'Ethiopia',
			'FO' => 'Faroe Islands',
			'FK' => 'Falkland Islands (Malvinas)',
			'FJ' => 'Fiji the Fiji Islands',
			'FI' => 'Finland',
			'FR' => 'France, French Republic',
			'GF' => 'French Guiana',
			'PF' => 'French Polynesia',
			'TF' => 'French Southern Territories',
			'GA' => 'Gabon',
			'GM' => 'Gambia the',
			'GE' => 'Georgia',
			'DE' => 'Germany',
			'GH' => 'Ghana',
			'GI' => 'Gibraltar',
			'GR' => 'Greece',
			'GL' => 'Greenland',
			'GD' => 'Grenada',
			'GP' => 'Guadeloupe',
			'GU' => 'Guam',
			'GT' => 'Guatemala',
			'GG' => 'Guernsey',
			'GN' => 'Guinea',
			'GW' => 'Guinea-Bissau',
			'GY' => 'Guyana',
			'HT' => 'Haiti',
			'HM' => 'Heard Island and McDonald Islands',
			'VA' => 'Holy See (Vatican City State)',
			'HN' => 'Honduras',
			'HK' => 'Hong Kong',
			'HU' => 'Hungary',
			'IS' => 'Iceland',
			'IN' => 'India',
			'ID' => 'Indonesia',
			'IR' => 'Iran',
			'IQ' => 'Iraq',
			'IE' => 'Ireland',
			'IM' => 'Isle of Man',
			'IL' => 'Israel',
			'IT' => 'Italy',
			'JM' => 'Jamaica',
			'JP' => 'Japan',
			'JE' => 'Jersey',
			'JO' => 'Jordan',
			'KZ' => 'Kazakhstan',
			'KE' => 'Kenya',
			'KI' => 'Kiribati',
			'KP' => 'Korea',
			'KR' => 'Korea',
			'KW' => 'Kuwait',
			'KG' => 'Kyrgyz Republic',
			'LA' => 'Lao',
			'LV' => 'Latvia',
			'LB' => 'Lebanon',
			'LS' => 'Lesotho',
			'LR' => 'Liberia',
			'LY' => 'Libyan Arab Jamahiriya',
			'LI' => 'Liechtenstein',
			'LT' => 'Lithuania',
			'LU' => 'Luxembourg',
			'MO' => 'Macao',
			'MK' => 'Macedonia',
			'MG' => 'Madagascar',
			'MW' => 'Malawi',
			'MY' => 'Malaysia',
			'MV' => 'Maldives',
			'ML' => 'Mali',
			'MT' => 'Malta',
			'MH' => 'Marshall Islands',
			'MQ' => 'Martinique',
			'MR' => 'Mauritania',
			'MU' => 'Mauritius',
			'YT' => 'Mayotte',
			'MX' => 'Mexico',
			'FM' => 'Micronesia',
			'MD' => 'Moldova',
			'MC' => 'Monaco',
			'MN' => 'Mongolia',
			'ME' => 'Montenegro',
			'MS' => 'Montserrat',
			'MA' => 'Morocco',
			'MZ' => 'Mozambique',
			'MM' => 'Myanmar',
			'NA' => 'Namibia',
			'NR' => 'Nauru',
			'NP' => 'Nepal',
			'AN' => 'Netherlands Antilles',
			'NL' => 'Netherlands',
			'NC' => 'New Caledonia',
			'NZ' => 'New Zealand',
			'NI' => 'Nicaragua',
			'NE' => 'Niger',
			'NG' => 'Nigeria',
			'NU' => 'Niue',
			'NF' => 'Norfolk Island',
			'MP' => 'Northern Mariana Islands',
			'NO' => 'Norway',
			'OM' => 'Oman',
			'PK' => 'Pakistan',
			'PW' => 'Palau',
			'PS' => 'Palestinian Territory',
			'PA' => 'Panama',
			'PG' => 'Papua New Guinea',
			'PY' => 'Paraguay',
			'PE' => 'Peru',
			'PH' => 'Philippines',
			'PN' => 'Pitcairn Islands',
			'PL' => 'Poland',
			'PT' => 'Portugal, Portuguese Republic',
			'PR' => 'Puerto Rico',
			'QA' => 'Qatar',
			'RE' => 'Reunion',
			'RO' => 'Romania',
			'RU' => 'Russian Federation',
			'RW' => 'Rwanda',
			'BL' => 'Saint Barthelemy',
			'SH' => 'Saint Helena',
			'KN' => 'Saint Kitts and Nevis',
			'LC' => 'Saint Lucia',
			'MF' => 'Saint Martin',
			'PM' => 'Saint Pierre and Miquelon',
			'VC' => 'Saint Vincent and the Grenadines',
			'WS' => 'Samoa',
			'SM' => 'San Marino',
			'ST' => 'Sao Tome and Principe',
			'SA' => 'Saudi Arabia',
			'SN' => 'Senegal',
			'RS' => 'Serbia',
			'SC' => 'Seychelles',
			'SL' => 'Sierra Leone',
			'SG' => 'Singapore',
			'SK' => 'Slovakia (Slovak Republic)',
			'SI' => 'Slovenia',
			'SB' => 'Solomon Islands',
			'SO' => 'Somalia, Somali Republic',
			'ZA' => 'South Africa',
			'GS' => 'South Georgia and the South Sandwich Islands',
			'ES' => 'Spain',
			'LK' => 'Sri Lanka',
			'SD' => 'Sudan',
			'SR' => 'Suriname',
			'SJ' => 'Svalbard & Jan Mayen Islands',
			'SZ' => 'Swaziland',
			'SE' => 'Sweden',
			'CH' => 'Switzerland',
			'SY' => 'Syrian Arab Republic',
			'TW' => 'Taiwan',
			'TJ' => 'Tajikistan',
			'TZ' => 'Tanzania',
			'TH' => 'Thailand',
			'TL' => 'Timor-Leste',
			'TG' => 'Togo',
			'TK' => 'Tokelau',
			'TO' => 'Tonga',
			'TT' => 'Trinidad and Tobago',
			'TN' => 'Tunisia',
			'TR' => 'Turkey',
			'TM' => 'Turkmenistan',
			'TC' => 'Turks and Caicos Islands',
			'TV' => 'Tuvalu',
			'UG' => 'Uganda',
			'UA' => 'Ukraine',
			'AE' => 'United Arab Emirates',
			'GB' => 'United Kingdom',
			'US' => 'United States',
			'UM' => 'United States Minor Outlying Islands',
			'VI' => 'United States Virgin Islands',
			'UY' => 'Uruguay, Eastern Republic of',
			'UZ' => 'Uzbekistan',
			'VU' => 'Vanuatu',
			'VE' => 'Venezuela',
			'VN' => 'Vietnam',
			'WF' => 'Wallis and Futuna',
			'EH' => 'Western Sahara',
			'YE' => 'Yemen',
			'ZM' => 'Zambia',
			'ZW' => 'Zimbabwe'
		);

		return(array_search($country, $countryList));
	}	

	/**
	 * Get shipping cost for product
	 */
	public function woosea_get_shipping_cost ($class_cost_id, $project_config, $price, $tax_rates, $shipping_zones, $product_id, $item_group_id) {
        	$shipping_cost = 0;
		$shipping_arr = array();
		$zone_count = 0;
		$nr_shipping_zones = count($shipping_zones);
		$zone_details = array();
		$base_location = wc_get_base_location();
		$base_country = $base_location['country'];
		$from_currency = get_woocommerce_currency();
		$add_all_shipping = "no";
		$add_all_shipping = get_option ('add_all_shipping');

		// Normal shipping set-up
		$zone_count = count($shipping_arr)+1;

	        foreach ( $shipping_zones as $zone){
			// Start with a clean shipping zone
			$zone_details = array();
			$zone_details['country'] = "";

			// Start with a clean postal code
			$postal_code = array();

			foreach ( $zone['zone_locations'] as $zone_type ) {
				$code_from_config = $this->woosea_country_to_code($project_config['countries']);

				// Only add shipping zones to the feed for specific feed country
	                        $ship_found = strpos($zone_type->code, $code_from_config);
     
	                        if(($ship_found !== false) OR ($add_all_shipping == "yes")){	
					if ($zone_type->type == "country"){
						// This is a country shipping zone
						$zone_details['country'] = $zone_type->code;
					} elseif ($zone_type->type == "state"){
						// This is a state shipping zone, split of country
						$zone_expl = explode(":", $zone_type->code);
						$zone_details['country'] = $zone_expl[0];
		
						// Adding a region is only allowed for these countries	
						$region_countries = array ('US','JP','AU');
						if(in_array($zone_details['country'], $region_countries)){
							$zone_details['region'] = $zone_expl[1];
						}
					} elseif ($zone_type->type == "postcode"){
						// Create an array of postal codes so we can loop over it later
						if ($project_config['taxonomy'] == 'google_shopping'){
							$zone_type->code = str_replace("...", "-", $zone_type->code);	
						}	
						array_push($postal_code, $zone_type->code);
					} else {
						// Unknown shipping zone type
					}
	
					// Get the g:services and g:prices, because there could be multiple services the $shipping_arr could multiply again
					// g:service = "Method title - Shipping class costs"
					// for example, g:service = "Estimated Shipping - Heavy shipping". g:price would be 180			
 	           	   	      	$shipping_methods     = $zone['shipping_methods'];

					foreach ($shipping_methods as $k => $v){
						if($v->enabled == "yes"){
							if(empty($zone_details['country'])){
								$zone_details['service'] = $zone['zone_name'] ." ". $v->title;					
							} else {
								$zone_details['service'] = $zone['zone_name'] ." ". $v->title ." ".$zone_details['country'];					
							}
							$taxable = $v->tax_status;

							if(isset($v->instance_settings['cost'])){
								$shipping_cost = $v->instance_settings['cost'];
								if(!$shipping_cost){
									$shipping_cost = 0;
								}

								// Do we need to convert the shipping costs with the Aelia Currency Switcher
                	        				if((isset($project_config['AELIA'])) AND (!empty($GLOBALS['woocommerce-aelia-currencyswitcher'])) AND (get_option ('add_aelia_support') == "yes")){
                        	       				
									if(!array_key_exists('base_currency', $project_config)){
                               		        	 			$from_currency = get_woocommerce_currency();
                               	 					} else {
                                        					$from_currency = $project_config['base_currency'];
                                					}

                                					// Get Aelia currency conversion prices
                                					$shipping_cost = apply_filters('wc_aelia_cs_convert', $shipping_cost, $from_currency, $project_config['AELIA']);
								}

								if($taxable == "taxable"){
									foreach ($tax_rates as $k_inner => $w){
										if((isset($w['shipping'])) and ($w['shipping'] == "yes")){
											$rate = (($w['rate']+100)/100);
															
											$shipping_cost = str_replace(",", ".", $shipping_cost);
											$shipping_cost = $shipping_cost*$rate;
											$shipping_cost = round($shipping_cost, 2);
											$shipping_cost = wc_format_localized_price($shipping_cost);
										}
									}
								}
							}
	
							// WooCommerce Table Rate Bolder Elements
                                                        if(class_exists('BE_Table_Rate_WC')){
                                                                // Set shipping cost
								$shipping_cost = 0;     
                                                                if(!empty($product_id)){
                                                                         // Add product to cart
                                                                        if (isset($product_id)){
                                                                                $quantity = 1;
                                                                                
                                                                                //$customer = new WC_Customer( get_current_user_id(), true );

										if(!empty($code_from_config)){

                                                                                	WC()->customer->set_shipping_country(wc_clean( $code_from_config ));
                                                                                	if(isset($zone_details['region'])){
                                                                       
									        		WC()->customer->set_shipping_state(wc_clean( $zone_details['region'] ));
                                                                                	}
                                                                                	//$cart = new WC_Cart(); 
                                                                                	WC()->cart->add_to_cart( $product_id, $quantity );
                                                                                
                                                                                	// Read cart and get schipping costs
                                                                                	foreach ( WC()->cart->get_cart() as $cart_item_key => $cart_item ) {
                                                                                        	$total_cost = WC()->cart->get_total();
                                                                                        	$shipping_cost = WC()->cart->get_shipping_total();
												$shipping_cost = wc_format_localized_price($shipping_cost);
                                                                                	}
                                                                                	// Make sure to empty the cart again
                                                                                	WC()->cart->empty_cart();
										}
                                                                        }
                                                                }
                                                        }

							// CLASS SHIPPING COSTS
        			        	     	if(isset($v->instance_settings[$class_cost_id])){
					
								if (is_numeric($v->instance_settings[$class_cost_id])){
									$shipping_cost = $v->instance_settings[$class_cost_id];

									// Do we need to convert the shipping costswith the Aelia Currency Switcher
                        	                        		if((isset($project_config['AELIA'])) AND (!empty($GLOBALS['woocommerce-aelia-currencyswitcher'])) AND (get_option ('add_aelia_support') == "yes")){
                                						if(!array_key_exists('base_currency', $project_config)){
											// Get the WooCommerce base currency
                                        						$from_currency = get_woocommerce_currency();
                                						} else {
                                        						$from_currency = $project_config['base_currency'];
                            		    					}

                                        	             	   		// Get Aelia currency conversion prices
                                                	        		$shipping_cost = apply_filters('wc_aelia_cs_convert', $shipping_cost, $from_currency, $project_config['AELIA']);
                                                			}
	
									if($taxable == "taxable"){
										foreach ($tax_rates as $k_inner => $w){
											if((isset($w['shipping'])) and ($w['shipping'] == "yes")){
												$rate = (($w['rate']+100)/100);
												$shipping_cost = $shipping_cost*$rate;
												$shipping_cost = round($shipping_cost, 2);
												$shipping_cost = wc_format_localized_price($shipping_cost);
											}
										}
									}
								} else {
									$shipping_cost = $v->instance_settings[$class_cost_id];
									$shipping_cost = str_replace("[qty]", "1", $shipping_cost);	

									$mathString = trim($shipping_cost);     // trim white spaces
									if (preg_match("/fee percent/", $mathString)){
 										$shipcost_piece = explode("+", $mathString);
										$mathString = trim($shipcost_piece[0]);
									}
								
    									$mathString = str_replace ('..', '.', $mathString);    // remove input mistakes from users using shipping formula's
    									$mathString = str_replace (',', '.', $mathString);    // remove input mistakes from users using shipping formula's
    									$mathString = preg_replace ('[^0-9\+-\*\/\(\)]', '', $mathString);    // remove any non-numbers chars; exception for math operators
									$mathString = str_replace(array('\'', '"', ','), '', $mathString); 

									if(!empty($mathString)){
										eval("\$mathString = $mathString;");
										$shipping_cost = $mathString;
			
										if($taxable == "taxable"){
											foreach ($tax_rates as $k_inner => $w){
												if((isset($w['shipping'])) and ($w['shipping'] == "yes")){
													$rate = (($w['rate']+100)/100);
													if(is_numeric($shipping_cost)){
														$shipping_cost = $shipping_cost*$rate;
														$shipping_cost = round($shipping_cost, 2);
														$shipping_cost = wc_format_localized_price($shipping_cost);
													}
												}
											}
										}
									}

									// Do we need to convert the shipping costswith the Aelia Currency Switcher
                                          	     		 	if((isset($project_config['AELIA'])) AND (!empty($GLOBALS['woocommerce-aelia-currencyswitcher'])) AND (get_option ('add_aelia_support') == "yes")){
                                						if(!array_key_exists('base_currency', $project_config)){
                                        						$from_currency = get_woocommerce_currency();
                               		 					} else {
                                        						$from_currency = $project_config['base_currency'];
                                						}
                                                       		 		// Get Aelia currency conversion prices
                                                        			$shipping_cost = apply_filters('wc_aelia_cs_convert', $shipping_cost, $from_currency, $project_config['AELIA']);
                                                			}
								}
                            				}

							// CHECK IF WE NEED TO REMOVE LOCAL PICKUP
							if($v->id == "local_pickup"){
								$remove_local_pickup = "no";
                						$remove_local_pickup = get_option ('local_pickup_shipping');
                                                        	
								if($remove_local_pickup == "yes"){
									unset($zone_details);
                                                            		unset($shipping_cost);
								}
							}

							// FREE SHIPPING COSTS IF MINIMUM FEE REACHED
							if($v->id == "free_shipping"){
								$minimum_fee = $v->min_amount;
                
				               			if(!array_key_exists('base_currency', $project_config)){
                        	         	      	 		$currency = get_woocommerce_currency();
                                				} else {
                                        				$currency = $project_config['base_currency'];
                                				}
 
								if(isset($project_config['WCML'])){
									$currency = $project_config['WCML'];
								}
								if(isset($project_config['AELIA'])){
									$currency = $project_config['AELIA'];
									
									// convert minimum fee
                                                        		$minimum_fee = apply_filters('wc_aelia_cs_convert', $minimum_fee, $from_currency, $project_config['AELIA']);
								}

								// Set type to double otherwise the >= doesn't work
								settype($price, "double");
								settype($minimum_fee, "double");

								// Only Free Shipping when prodict price is over or equal to minimum order fee	
								if ($price >= $minimum_fee){
									$shipping_cost = 0;
                                					$zone_details['price'] = trim($currency." ".$shipping_cost);
									$zone_details['free'] = "yes";
								} else {
									// There are no free shipping requirements
									if($v->requires == ""){
										$shipping_cost = 0;
        	                        					$zone_details['price'] = trim($currency." ".$shipping_cost);
										$zone_details['free'] = "yes";
									} else {
										// No Free Shipping Allowed for this product
										unset($zone_details);
										unset($shipping_cost);
									}
								}
							}

							if(isset($zone_details)){
                       		       	  			$currency = get_woocommerce_currency();
								if(isset($project_config['WCML'])){
									$currency = $project_config['WCML'];
								} else {
									if(isset($project_config['AELIA'])){
										$currency = $project_config['AELIA'];
									} else {
	                       	    	   	  				if(!array_key_exists('base_currency', $project_config)){
        	                                					$currency = get_woocommerce_currency();
                	                					} else {
                        	                					$currency = $project_config['base_currency'];
										}
									}
								}
								
								if(strlen($shipping_cost) > 0){
									if($project_config['ship_suffix'] == "false"){
                                						$zone_details['price'] = trim($currency." ".$shipping_cost);
									} else {
                                						$zone_details['price'] = trim($shipping_cost);
									}
								} else {
									unset($zone_details);
									unset($shipping_cost);
								}
							}

							// This shipping zone has postal codes so multiply the zone details
							$nr_postals = count($postal_code);
							if ($nr_postals > 0){
								for ($x = 0; $x <= count($postal_code); ) {
									$zone_count++;
									if(!empty($postal_code[$x])){
										$zone_details['postal_code'] = $postal_code[$x];
										$shipping_arr[$zone_count] = $zone_details;
									}
									$x++;	
								}
							} else {
								if(isset($zone_details)){
									$zone_count++;
									$shipping_arr[$zone_count] = $zone_details;
								}	
							}
						}
					}	
				}
			}
		}

		// Remove other shipping classes when free shipping is relevant		
		$free_check = "no";
                $free_check = get_option ('free_shipping');

		if(in_array($free_check, array_column($shipping_arr, 'free'))) { // search value in the array
			foreach($shipping_arr as $k => $v) {
				if(!in_array($free_check, $v)){
  					unset($shipping_arr[$k]);
				}
			}
		}

		// Remove empty countries
		foreach($shipping_arr as $k => $v){
			if(empty($v['country'])){
			unset($shipping_arr[$k]);
			}
		}	
		return $shipping_arr;
	}

	/**
	 * Log queries, used for debugging errors
	 */
	public function woosea_create_query_log ( $query, $filename ) {
                $upload_dir = wp_upload_dir();

                $base = $upload_dir['basedir'];
                $path = $base . "/woo-product-feed-pro/logs";
                $file = $path . "/". $filename ."." ."log";

                // External location for downloading the file   
                $external_base = $upload_dir['baseurl'];
                $external_path = $external_base . "/woo-product-feed-pro/logs";
                $external_file = $external_path . "/" . $filename ."." ."log";

                // Check if directory in uploads exists, if not create one      
                if ( ! file_exists( $path ) ) {
                        wp_mkdir_p( $path );
                }

		// Log timestamp
		$today = "\n";
		$today .= date("F j, Y, g:i a");                 // March 10, 2001, 5:16 pm
		$today .= "\n";

                $fp = fopen($file, 'a+');
                fwrite($fp, $today);
		fwrite($fp, print_r($query, TRUE));
		fclose($fp);
	}

	/**
         * Creates XML root and header for productfeed
	 */	
	public function woosea_create_xml_feed ( $products, $feed_config, $header ) {
		$upload_dir = wp_upload_dir();
		$base = $upload_dir['basedir'];
 		$path = $base . "/woo-product-feed-pro/" . $feed_config['fileformat'];
        	$file = $path . "/" . sanitize_file_name($feed_config['filename']) . "_tmp." . $feed_config['fileformat'];
	
		// External location for downloading the file	
		$external_base = $upload_dir['baseurl'];
 		$external_path = $external_base . "/woo-product-feed-pro/" . $feed_config['fileformat'];
        	$external_file = $external_path . "/" . sanitize_file_name($feed_config['filename']) . "." . $feed_config['fileformat'];

		// Check if directory in uploads exists, if not create one	
		if ( ! file_exists( $path ) ) {
    			wp_mkdir_p( $path );
		}

		// Check if file exists, if it does: delete it first so we can create a new updated one
		if ( (file_exists( $file )) AND ($header == "true") AND ($feed_config['nr_products_processed'] == 0) || !file_exists( $file ) ) {
			unlink ( $file );
		}	

		// Check if there is a channel feed class that we need to use
		if ($feed_config['fields'] != 'standard'){
			if (!class_exists('WooSEA_'.$feed_config['fields'])){
				require plugin_dir_path(__FILE__) . '/channels/class-'.$feed_config['fields'].'.php';
				$channel_class = "WooSEA_".$feed_config['fields'];
				$channel_attributes = $channel_class::get_channel_attributes();
				update_option ('channel_attributes', $channel_attributes, 'yes');	
			} else {
				$channel_attributes = get_option('channel_attributes');
			}
		}	

		// Some channels need their own feed config and XML namespace declarations (such as Google shopping)
		if ($feed_config['taxonomy'] == 'google_shopping'){
			$namespace = array( 'g' => 'http://base.google.com/ns/1.0' );
			if ( ($header == "true") AND ($feed_config['nr_products_processed'] == 0) ) {
			   	$xml = new SimpleXMLElement('<?xml version="1.0" encoding="UTF-8"?><rss xmlns:g="http://base.google.com/ns/1.0"></rss>');
			   	$xml->addAttribute('version', '2.0');
				$xml->addChild('channel');
				$xml->channel->addChild('title', htmlspecialchars($feed_config['projectname']));
				$xml->channel->addChild('link', site_url());
				$xml->channel->addChild('description', 'WooCommerce Product Feed PRO - This product feed is created with the Product Feed PRO for WooCommerce plugin from AdTribes.io. For all your support questions check out our FAQ on https://www.adtribes.io or e-mail to: support@adtribes.io ');
				$xml->asXML($file);	
			} else {
				$xml = simplexml_load_file($file, 'SimpleXMLElement', LIBXML_NOCDATA);
				$aantal = count($products);

				if (($xml !== FALSE) AND ($aantal > 0)){
					foreach ($products as $key => $value){

						if (is_array ( $value ) ) {
							if(!empty( $value )){
								$product = $xml->channel->addChild('item');
								foreach ($value as $k => $v){
									if ($k == "g:shipping"){
										$ship = explode("||", $v);
										foreach ($ship as $kk => $vv){
											$sub_count = substr_count($vv, '##');
											$shipping = $product->addChild($k, '',htmlspecialchars($namespace['g']));
											$ship_split = explode(":", $vv);
											
											foreach($ship_split as $ship_piece){

												$piece_value = explode("##", $ship_piece);
												if (preg_match("/WOOSEA_COUNTRY/", $ship_piece)){
                                                       							$shipping_country = $shipping->addChild('g:country', $piece_value[1], $namespace['g']);
												} elseif (preg_match("/WOOSEA_REGION/", $ship_piece)){
                                                       							$shipping_region = $shipping->addChild('g:region', $piece_value[1], $namespace['g']);
												} elseif (preg_match("/WOOSEA_POSTAL_CODE/", $ship_piece)){
													$shipping_price = $shipping->addChild('g:postal_code', $piece_value[1], $namespace['g']);
												} elseif (preg_match("/WOOSEA_SERVICE/", $ship_piece)){
                                                       							$shipping_service = $shipping->addChild('g:service', $piece_value[1], $namespace['g']);
												} elseif (preg_match("/WOOSEA_PRICE/", $ship_piece)){
													$shipping_price = $shipping->addChild('g:price',trim($piece_value[1]),$namespace['g']);
												} else {
													// DO NOT ADD ANYTHING
												}
											}
										}
									// Fix issue with additional images for Google Shopping
									} elseif (preg_match("/g:additional_image_link/i",$k)){
                                       	               				$link = $product->addChild('g:additional_image_link', $v, $namespace['g']);
										//$product->$k = $v;
									} elseif (preg_match("/g:product_highlight/i",$k)){
                                       	               				$product_highlight = $product->addChild('g:product_highlight', $v, $namespace['g']);
									} elseif (preg_match("/g:product_detail/i",$k)){
										if(!empty($v)){
											$product_detail_split = explode("#", $v);
                                       	               					$product_detail = $product->addChild('g:product_detail', '', $namespace['g']);
                                                                                        $name = str_replace("_", " ", $product_detail_split[0]);

											$section_name = explode(":", $name);
											$section_name_start = ucfirst($section_name[0]);
											$name = ucfirst(trim($section_name[1]));
											
											$section_name = $product_detail->addChild('g:section_name', $section_name_start, $namespace['g']);
											$product_detail_name = $product_detail->addChild('g:attribute_name', $name, $namespace['g']);
											$product_detail_value = $product_detail->addChild('g:attribute_value', $product_detail_split[1], $namespace['g']);
										}
									} elseif ($k == "g:installment"){
										if(!empty($v)){
											$installment_split = explode(":", $v);
											$installment = $product->addChild($k, '', $namespace['g']);
                                                      					$installment_months = $installment->addChild('g:months', $installment_split[0], $namespace['g']);
                                                       					$installment_amount = $installment->addChild('g:amount', $installment_split[1], $namespace['g']);
										}
									} elseif ($k == "g:color" || $k == "g:size" || $k == "g:material"){
										if(!empty($v)){
											$attr_split = explode(",", $v);
											$nr_attr = count($attr_split)-1;
											$attr_value = "";											
	
											for ($x = 0; $x <= $nr_attr; $x++){
												$attr_value .= trim($attr_split[$x])."/";
											}	
											$attr_value = rtrim($attr_value,"/");	
											$product->$k = $attr_value;							
										}						
									} else {
										$product->$k = $v;
									}
								}
							}
						}	
					}
				}

				if(is_object($xml)){
					$xml->asXML($file);
				}
				unset($products);
			}
			unset($xml);
		} else {
			if ( ($header == "true") AND ($feed_config['nr_products_processed'] == 0) || !file_exists( $file ) ) {

				if ($feed_config['name'] == "Yandex") {
					$main_currency = get_woocommerce_currency();

					$xml = new SimpleXMLElement('<?xml version="1.0" encoding="utf-8"?><yml_catalog></yml_catalog>');	
					$xml->addAttribute('date', date('Y-m-d H:i'));
					$shop = $xml->addChild('shop');
					$shop->addChild('name', htmlspecialchars($feed_config['projectname']));
					$shop->addChild('company', get_bloginfo());
					$shop->addChild('url', site_url());
					//$shop->addChild('platform', 'WooCommerce');
					$currencies = $shop->addChild('currencies');
					$currency = $currencies->addChild('currency');
					$currency->addAttribute('id', $main_currency);
					$currency->addAttribute('rate', '1');

 					// Switch to configured WPML language
                			if(isset($feed_config['WPML'])){
                        			if ( function_exists('icl_object_id') ) {
							if( !class_exists( 'Polylang' ) ) {
								global $sitepress;
								$original_lang = ICL_LANGUAGE_CODE; // Save the current language
								$new_lang = $feed_config['WPML']; // The language in which you want to get the terms
								$sitepress->switch_lang($new_lang); // Switch to new language
							}	
						}
					}

					$args = array(
    						'taxonomy'   => "product_cat",
					);
					$product_categories = get_terms( 'product_cat', $args );

					$count = count($product_categories);
					if ($count > 0){
						$categories = $shop->addChild('categories');

        					foreach ($product_categories as $product_category){
							$category = $categories->addChild('category', htmlspecialchars($product_category->name));
							$category->addAttribute('id', $product_category->term_id);
							if ($product_category->parent > 0){
								$category->addAttribute('parentId', $product_category->parent);

							}
        					}
					}

					$shop->addChild('agency', 'AdTribes.io');
					$shop->addChild('email', 'support@adtribes.io');
					$xml->asXML($file);
				} elseif ($feed_config['name'] == "Zbozi.cz") {
					$xml = new SimpleXMLElement('<?xml version="1.0" encoding="utf-8"?><SHOP></SHOP>');	
					$xml->addAttribute('xmlns', 'http://www.zbozi.cz/ns/offer/1.0');
					$xml->asXML($file);
				} elseif ($feed_config['name'] == "Glami.gr") {
					$xml = new SimpleXMLElement('<?xml version="1.0" encoding="utf-8"?><SHOP></SHOP>');	
					$xml->asXML($file);
				} elseif ($feed_config['name'] == "Pricecheck.co.za") {
					$xml = new SimpleXMLElement('<?xml version="1.0" encoding="utf-8"?><Offers></Offers>');	
					$xml->asXML($file);
                                } elseif ($feed_config['name'] == "Pinterest RSS Board") {
                                        $xml = new SimpleXMLElement('<?xml version="1.0" encoding="utf-8"?><rss></rss>');
					$xml->addAttribute('xmlns:content', 'http://purl.org/rss/1.0/modules/content/');
					$xml->addAttribute('xmlns:wfw', 'http://wellformedweb.org/CommentAPI/');
					$xml->addAttribute('xmlns:dc', 'http://purl.org/dc/elements/1.1/');
//					$xml->addAttribute('xmlns:atom', 'http://www.w3.org/2005/Atom');
					$xml->addAttribute('xmlns:sy', 'http://purl.org/rss/1.0/modules/syndication/');
					$xml->addAttribute('xmlns:slash', 'http://purl.org/rss/1.0/modules/slash/');
					$xml->addAttribute('version', '2.0');
	                                $xml->asXML($file);
				} elseif ($feed_config['name'] == "Heureka.cz") {
					$xml = new SimpleXMLElement('<?xml version="1.0" encoding="utf-8"?><SHOP></SHOP>');	
					$xml->addAttribute('xmlns', 'http://www.heureka.cz/ns/offer/1.0');
					$xml->asXML($file);
				} elseif ($feed_config['name'] == "Zap.co.il") {
					$xml = new SimpleXMLElement('<?xml version="1.0" encoding="utf-8"?><STORE></STORE>');
					$xml->addChild('datetime', date('Y-m-d H:i:s'));
					$xml->addChild('title', htmlspecialchars($feed_config['projectname']));
					$xml->addChild('link', site_url());
					$xml->addChild('description', 'WooCommerce Product Feed PRO - This product feed is created with the free Advanced Product Feed PRO for WooCommerce plugin from AdTribes.io. For all your support questions check out our FAQ on https://www.adtribes.io or e-mail to: support@adtribes.io ');
					$xml->addChild('agency', 'AdTribes.io');
					$xml->addChild('email', 'support@adtribes.io');
					$xml->asXML($file);
				} elseif ($feed_config['name'] == "Salidzini.lv") {
					$xml = new SimpleXMLElement('<?xml version="1.0" encoding="utf-8"?><root></root>');
					$xml->addChild('datetime', date('Y-m-d H:i:s'));
					$xml->addChild('title', htmlspecialchars($feed_config['projectname']));
					$xml->addChild('link', site_url());
					$xml->addChild('description', 'WooCommerce Product Feed PRO - This product feed is created with the free Advanced Product Feed PRO for WooCommerce plugin from AdTribes.io. For all your support questions check out our FAQ on https://www.adtribes.io or e-mail to: support@adtribes.io ');
					$xml->addChild('agency', 'AdTribes.io');
					$xml->addChild('email', 'support@adtribes.io');
					$xml->asXML($file);
				} elseif ($feed_config['name'] == "Google Product Review") {
					$xml = new SimpleXMLElement('<?xml version="1.0" encoding="utf-8"?><feed></feed>');	
					$xml->addAttribute('xmlns:vc', 'http://www.w3.org/2007/XMLSchema-versioning');
					$xml->addAttribute('xmlns:xsi', 'http://www.w3.org/2001/XMLSchema-instance');
					$xml->addAttribute('xsi:noNamespaceSchemaLocation', 'http://www.google.com/shopping/reviews/schema/product/2.2/product_reviews.xsd');
					$xml->addChild('version', '2.2');
					$aggregator = $xml->addChild('aggregator');
					$aggregator->addChild('name', htmlspecialchars($feed_config['projectname']));
					$publisher = $xml->addChild('publisher');
					$publisher->addChild('name', get_bloginfo( 'name' ));
					$publisher->addChild('favicon', get_site_icon_url());
					$xml->asXML($file);
				} elseif ($feed_config['name'] == "Fruugo.nl") {
					$xml = new SimpleXMLElement('<?xml version="1.0" encoding="utf-8"?><Products></Products>');	
					$xml->asXML($file);
				} else {
					$xml = new SimpleXMLElement('<?xml version="1.0" encoding="utf-8"?><products></products>');	
					$xml->addAttribute('version', '1.0');
					$xml->addAttribute('standalone', 'yes');
					$xml->addChild('datetime', date('Y-m-d H:i:s'));
					$xml->addChild('title', htmlspecialchars($feed_config['projectname']));
					$xml->addChild('link', site_url());
					$xml->addChild('description', 'WooCommerce Product Feed PRO - This product feed is created with the free Advanced Product Feed PRO for WooCommerce plugin from AdTribes.io. For all your support questions check out our FAQ on https://www.adtribes.io or e-mail to: support@adtribes.io ');
					$xml->asXML($file);
				}
			} else {
				$xml = simplexml_load_file($file);
				$aantal = count($products);

				if ($aantal > 0){

					if (($feed_config['name'] == "Yandex") AND ($feed_config['nr_products_processed'] == 0)) {
						$shop = $xml->shop->addChild('offers');
					}

					// For ZAP template
					if (($feed_config['name'] == "Zap.co.il") AND ($feed_config['nr_products_processed'] == 0)) {
						$productz = $xml->addChild('PRODUCTS');
					}
					
					// For Pinterest RSS Board template
					if (($feed_config['name'] == "Pinterest RSS Board") AND (empty($xml->channel))) {
						$productz = $xml->addChild('channel');
						$productz = $xml->channel->addChild('title', get_bloginfo( 'name' ));
						$productz = $xml->channel->addChild('description', htmlspecialchars($feed_config['projectname']));
						$productz = $xml->channel->addChild('lastBuildDate', date('Y-m-d H:i:s'));
						$productz = $xml->channel->addChild('generator', 'Product Feed Pro for WooCommerce by AdTribes.io');
					}
	
					// For Google Product review template
					if (($feed_config['name'] == "Google Product Review") AND (empty($xml->channel))) {
						$product = $xml->addChild('reviews');
								
						foreach ($products as $key => $value){
							$expl = "||";
                                          		if(strpos($value['reviews'], $expl)) {
                                                		$review_data = explode("||", $value['reviews']);
								foreach($review_data as $rk => $rv){
				
									$review_comp = explode(":::", $rv);
									$nr_reviews = count($review_comp);

									if($nr_reviews > 1){
										$productz = $xml->reviews->addChild('review');
									
										foreach($review_comp as $rck => $rcv){
											$nodes = explode("##", $rcv);
						                        		$nodes = str_replace("::", "", $nodes);

											if($nodes[0] == "REVIEW_RATINGS"){
												$rev = $productz->addChild('ratings');
												$over = $productz->ratings->addChild('overall', $nodes[1]);
												$over->addAttribute('min', '1');
												$over->addAttribute('max', '5');
											} elseif($nodes[0] == "REVIEW_URL"){
												$rev_url = $productz->addChild(strtolower($nodes[0]), htmlspecialchars($nodes[1]));	
												$rev_url->addAttribute('type', 'singleton');	
											} elseif(($nodes[0] == "REVIEWER_NAME") OR ($nodes[0] == "REVIEWER_ID")){
        	                                        	                		if(isset($productz->reviewer)){
													if($nodes[0] == "REVIEWER_NAME"){	
														$name = $nodes[1];
														if(empty($name)){
															$reviewer->addChild('name','Anonymous');
															$reviewer->addAttribute('is_anonymous', 'true');
														} else {
															$reviewer->addChild('name',$name);
														}
													} else {
														$reviewer->addChild('reviewer_id',$nodes[1]);
													}
												} else {
													$reviewer = $productz->addChild('reviewer');
													if($nodes[0] == "REVIEWER_NAME"){	
														$name = $nodes[1];
														if(empty($name)){
															$reviewer->addChild('name','Anonymous');
															$reviewer->addAttribute('is_anonymous', 'true');
														} else {
															$reviewer->addChild('name',$name);
														}
													} else {
														$reviewer->addChild('reviewer_id',$nodes[1]);
													}
												}
											} else {
												if(isset($nodes[1])){
                                                                                                        $content = html_entity_decode($nodes[1]);
                                                                                                        $content = htmlspecialchars($content);
                                                                                                        $rev = $productz->addChild(strtolower($nodes[0]), $content);
												}
											}
									
										}
							
										$yo = $productz->addChild('products');
										$po = $yo->addChild('product');
                                                                
						        	   	     	$identifiers = array("brand","gtin","mpn","sku");

										foreach($value as $k => $v) {
	
											if(in_array($k, $identifiers)){
        	                                	                        		if(isset($po->product_ids)){
													if($k == "brand"){
														$poib = $poi->addChild('brands');
                	                                                	        			$poib->$k = $v;
													} elseif ($k == "gtin"){
														$poig = $poi->addChild('gtins');
                	                                                        				$poig->$k = $v;
													} elseif ($k == "mpn"){
														$poim = $poi->addChild('mpns');
                	                                                        				$poim->$k = $v;
													} else {
														$pois = $poi->addChild('skus');
                	                                                	        			$pois->$k = $v;
													}	
                        	                                     	        		} else {
                                	                                		       		$poi = $po->addChild('product_ids');
                                                	                        			if($k == "brand"){
														$poib = $poi->addChild('brands');
                	                                        	                			$poib->$k = $v;
													} elseif ($k == "gtin"){
														$poig = $poi->addChild('gtins');
                	                                                	        			$poig->$k = $v;
													} elseif ($k == "mpn"){
														$poim = $poi->addChild('mpns');
                	                                                     		   			$poim->$k = $v;
													} else {
														$pois = $poi->addChild('skus');
                	                                                        				$pois->$k = $v;
													}	
												}
                                                        	    			} else {
                                                                				if(($k != "reviews") AND ($k != "review_url")){
                                                                        				$poa = $po->addChild($k,htmlspecialchars($v));
                                                         	        		      	}
                                                      	 				}
										}
									}	
								}
							}
						}
					}	

					foreach ($products as $key => $value){
	
						if ((is_array ( $value )) and (!empty( $value ))) {
							if ($feed_config['name'] == "Yandex") {
								$product = $xml->shop->offers->addChild('offer');
							} elseif ($feed_config['name'] == "Heureka.cz" || $feed_config['name'] == "Zbozi.cz" || $feed_config['name'] == "Glami.gr") {
								$product = $xml->addChild('SHOPITEM');
							} elseif ($feed_config['name'] == "Zap.co.il") {
								$product = $xml->PRODUCTS->addChild('PRODUCT');
							} elseif ($feed_config['name'] == "Salidzini.lv") {
								$product = $xml->addChild('item');
							} elseif ($feed_config['name'] == "Trovaprezzi.it") {
								$product = $xml->addChild('Offer');
							} elseif ($feed_config['name'] == "Pricecheck.co.za") {
								$product = $xml->addChild('Offer');
                                                        } elseif ($feed_config['name'] == "Pinterest RSS Board") {
                                                                $product = $xml->channel->addChild('item');
							} elseif ($feed_config['name'] == "Google Product Review") {
							
							} else {
								if(count($value) > 0){
									$product = $xml->addChild('product');
								}
							}

							foreach ($value as $k => $v){

								$v = trim($v);
								$k = trim($k);	

								if(($k == "id") AND ($feed_config['name'] == "Yandex")){
									$product->addAttribute('id', trim($v));
								}
								if(($k == "available") AND ($feed_config['name'] == "Yandex")){
									if($v == "in stock"){
										$v = "true";
									} else {
										$v = "false";
									}
									$product->addAttribute('available', $v);
								}

								/**
								 * Check if a product resides in multiple categories
								 * id so, create multiple category child nodes
								 */			
								if ($k == "categories"){
									$category = $product->addChild('categories');
									$cat = explode("||",$v);							

									if (is_array ( $cat ) ) {
										foreach ($cat as $kk => $vv){
											$child = "category";
											$category->addChild("$child", htmlspecialchars($vv));
										}
									}
								} elseif (preg_match('/^additionalimage/',$k)){
                                                               		$additional_image_link = $product->addChild('additionalimage',$v);
								} elseif ($k == "shipping"){
									$expl = "||";
									if(strpos($v, $expl)) {
										$ship = explode("||", $v);
                                                                      		foreach ($ship as $kk => $vv){
											$ship_zone = $product->addChild('shipping');
                                                                            		$ship_split = explode(":", $vv);
		
                                                                           	 	foreach($ship_split as $ship_piece){
                                                                                		$piece_value = explode("##", $ship_piece);
                                                                                 	     	if (preg_match("/WOOSEA_COUNTRY/", $ship_piece)){
                                                                                			$shipping_country = $ship_zone->addChild('country', htmlspecialchars($piece_value[1]));
                                                                              	        	} elseif (preg_match("/WOOSEA_REGION/", $ship_piece)){
                                                                               		 		$shipping_region = $ship_zone->addChild('region', htmlspecialchars($piece_value[1]));
                                                                            	         	} elseif (preg_match("/WOOSEA_POSTAL_CODE/", $ship_piece)){
                                                                               		 		$postal_code = $ship_zone->addChild('postal_code', htmlspecialchars($piece_value[1]));
                                                                              	      		} elseif (preg_match("/WOOSEA_SERVICE/", $ship_piece)){
                                                                                			$shipping_service = $ship_zone->addChild('service', htmlspecialchars($piece_value[1]));
                                                                                     		} elseif (preg_match("/WOOSEA_PRICE/", $ship_piece)){
                                                                                			$shipping_price = $ship_zone->addChild('price', htmlspecialchars($piece_value[1]));
                                                                                      		} else {
                                                                                        		// DO NOT ADD ANYTHING
                                                                                      		}
                                                                             		}
										}
									} else {
										$child = "shipping";
										$product->$k = $v;	
									}
								} elseif ($k == "category_link"){
									$category = $product->addChild('category_links');
									$cat_links = explode("||",$v);							
									if (is_array ( $cat_links ) ) {
										foreach ($cat_links as $kk => $vv){
											$child = "category_link";
											$category->addChild("$child", htmlspecialchars($vv));
										}
									}
								} elseif ($k == "categoryId"){

									if($feed_config['name'] == "Yandex"){
										$args = array(
    											'taxonomy'   => "product_cat",
										);
		
										//$category = $product->addChild('categories');
										$product_categories = get_terms( 'product_cat', $args );
										$count = count($product_categories);
										$cat = explode("||",$v);							

										if (is_array ( $cat ) ) {
											foreach ($cat as $kk => $vv){
												if ($count > 0){
        												foreach ($product_categories as $product_category){
														if($vv == $product_category->name){
															$product->addChild("$k", htmlspecialchars($product_category->term_id));
														}
													}
												}
											}
										}
									}
								} elseif (($k == "id" || $k == "available") AND ($feed_config['name'] == "Yandex")){
									// Do not add these nodes to Yandex product feeds
								} elseif ($k == "CATEGORYTEXT"){
									$v = str_replace("||", " | ", $v);
									$product->addChild("$k");
									$product->$k = $v;
								} else {
									if ($feed_config['fields'] != 'standard'){
	          	                                           		$k = $this->get_alternative_key ($channel_attributes, $k);
									}
									if(!empty($k)){

										/**
                                                                 		* Some Zbozi and Heureka attributes need some extra XML nodes
                                                                 		*/
										$zbozi_nodes = "PARAM_";
										
                                                                		if((($feed_config['name'] == "Zbozi.cz") OR ($feed_config['name'] == "Glami.gr") OR ($feed_config['name'] == "Heureka.cz")) AND (preg_match("/$zbozi_nodes/i",$k))){
											$pieces = explode ("_", $k);
											$productp = $product->addChild('PARAM');
                                                                                	$productp->addChild("PARAM_NAME", $pieces[1]);
                                                                                	$productp->addChild("VAL", $v);
                                                                		} elseif(($feed_config['name'] == "Yandex") AND (preg_match("/$zbozi_nodes/i",$k))){
											$pieces = explode ("_", $k);
											$p = "param";
											$productp = $product->addChild($p,$v);
											$productp->addAttribute('name', $pieces[1]);
										} elseif ($feed_config['name'] == "Google Product Review") {
										} else {
											if(is_object($product)){
												$product->addChild("$k");
												$product->$k = $v;
											}
										}
									}
								}
							}
						}
					}	
					$xml->asXML($file);
					unset($product);
				}
				unset($products);
			}
			unset($xml);
		}
	}

	/**
         * Actual creation of CSV/TXT file
         * Returns relative and absolute file path
	 */	
	public function woosea_create_csvtxt_feed ( $products, $feed_config, $header ) {

		$upload_dir = wp_upload_dir();
		$base = $upload_dir['basedir'];
 		$path = $base . "/woo-product-feed-pro/" . $feed_config['fileformat'];
        	$file = $path . "/" . sanitize_file_name($feed_config['filename']) . "_tmp." . $feed_config['fileformat'];
	
		// External location for downloading the file	
		$external_base = $upload_dir['baseurl'];
 		$external_path = $external_base . "/woo-product-feed-pro/" . $feed_config['fileformat'];
        	$external_file = $external_path . "/" . sanitize_file_name($feed_config['filename']) . "." . $feed_config['fileformat'];

		// Check if directory in uploads exists, if not create one	
		if ( ! file_exists( $path ) ) {
    			wp_mkdir_p( $path );
		}

		// Check if file exists, if it does: delete it first so we can create a new updated one
		if ( (file_exists( $file )) AND ($feed_config['nr_products_processed'] == 0) AND ($header == "true") ) {
			@unlink ( $file );
		}	

		// Check if there is a channel feed class that we need to use
		if(empty($feed_config['fields'])){
			$feed_config['fields'] = "google_shopping";
		}

		if ($feed_config['fields'] != 'standard'){
			if (!class_exists('WooSEA_'.$feed_config['fields'])){
				require plugin_dir_path(__FILE__) . 'channels/class-'.$feed_config['fields'].'.php';
				$channel_class = "WooSEA_".$feed_config['fields'];
				$channel_attributes = $channel_class::get_channel_attributes();
				update_option ('channel_attributes', $channel_attributes, 'yes');	
			} else {
				$channel_attributes = get_option('channel_attributes');
			}
		}	
		
		// Append or write to file
		$fp = fopen($file, 'a+');
		
		// Set proper UTF encoding BOM for CSV files
		if($header == "true"){
			fputs( $fp, $bom = chr(0xEF) . chr(0xBB) . chr(0xBF) );
		}		

		// Write each row of the products array
		foreach ($products as $row) {

			foreach ($row as $k => $v){
				$pieces = explode ("','", $v);
				$pieces = str_replace("'", "", $pieces);

				foreach ($pieces as $k_inner => $v){
                                        if ($feed_config['fields'] != 'standard'){
						$v = $this->get_alternative_key ($channel_attributes, $v);
					}			            

					// For CSV fileformat the keys need to get stripped of the g:
                                      	if($feed_config['fileformat'] == "csv"){
                                        	$v = str_replace("g:", "", $v);
                                     	}	

					$pieces[$k_inner] = $v;
				}

				// Convert tab delimiter
				if($feed_config['delimiter'] == "tab"){
					$csv_delimiter = "\t";
				} else {
					$csv_delimiter = $feed_config['delimiter'];
				}

				if ($feed_config['fields'] == "google_local"){
					$tab_line = "";

					if($header == "false"){
                				// Get the store codes
						foreach ($feed_config['attributes'] as $k=>$v){
						    	if(preg_match('/\|/', $k)){ 
								$stores_local = $k;
							}
						}

						$store_ids = explode("|", $stores_local);
						if(is_array($store_ids)){
						
							foreach ($store_ids as $store_key => $store_value){
								$pieces[0] = $store_value;

								if(!empty($store_value)){
									foreach ($pieces as $t_key => $t_value){
										$tab_line .= $t_value . "$csv_delimiter";
									}
									$tab_line = rtrim($tab_line, $csv_delimiter);
									$tab_line .= PHP_EOL;
								}	
							}	
							fwrite($fp, $tab_line);
						} else {
							// Only one store code entered
							foreach ($pieces as $t_key => $t_value){
								$tab_line .= $t_value . "$csv_delimiter";
							}
							
							$tab_line = rtrim($tab_line, $csv_delimiter);
							$tab_line .= PHP_EOL;
							fwrite($fp, $tab_line);
						}
					} else {
						foreach ($pieces as $t_key => $t_value){
							$tab_line .= $t_value . "$csv_delimiter";
						}
						$tab_line = rtrim($tab_line, $csv_delimiter);
						$tab_line .= PHP_EOL;
						fwrite($fp, $tab_line);
					}
				} else {
					$tofile = fputcsv($fp, $pieces, $csv_delimiter, '"');
				}

			}
		}
		// Close the file
		fclose($fp);

		// Return external location of feed
		return $external_file;
	}

	/**
         * Get products that are eligable for adding to the file
	 */
	public function woosea_get_products ( $project_config ) {
		$nr_products_processed = $project_config['nr_products_processed'];
		$count_products = wp_count_posts('product', 'product_variation');

 		if(isset($project_config['product_variations'])){
			$count_single = wp_count_posts('product');
			$count_variation = wp_count_posts('product_variation');
			$published_single = $count_single->publish;
			$published_variation = $count_variation->publish;
			$published_products = $published_single+$published_variation;		
		} else {
			$count_single = wp_count_posts('product');
			$published_products = $count_single->publish;
		}

		$versions = array (
        		"PHP" => (float)phpversion(),
        		"Wordpress" => get_bloginfo('version'),
        		"WooCommerce" => WC()->version,
			"Plugin" => WOOCOMMERCESEA_PLUGIN_VERSION
		);

		/**
		 * Do not change these settings, they are here to prevent running into memory issues
		 */
		if ($versions['PHP'] < 5.6){
			// Old version, process a maximum of 50 products per batch
			$nr_batches = ceil($published_products/50);
		} elseif ($versions['PHP'] == 5.6){
			// Old version, process a maximum of 100 products per batch
			$nr_batches = ceil($published_products/200);
		} else {
			// Fast PHP version, process a 750 products per batch
			$nr_batches = ceil($published_products/750);

			if($published_products > 50000){
				$nr_batches = ceil($published_products/2500);
			} else {
				$nr_batches = ceil($published_products/750);
			}
		}

		/**
		 * User set his own batch size
		 */
              	$woosea_batch_size = get_option ('woosea_batch_size');
		if(!empty($woosea_batch_size)){
			if(is_numeric($woosea_batch_size)){
				$nr_batches = ceil($published_products/$woosea_batch_size);
			}
		}

		$offset_step_size = ceil($published_products/$nr_batches);

		/**
		 * Check if the [attributes] array in the project_config is of expected format.
		 * For channels that have mandatory attribute fields (such as Google shopping) we need to rebuild the [attributes] array
		 * Only add fields to the file that the user selected
		 * Construct header line for CSV ans TXT files, for XML create the XML root and header
		 */
		if($project_config['fileformat'] != 'xml'){
			if($project_config['fields'] != 'standard'){
				foreach ($project_config['attributes'] as $key => $value){
					foreach($value as $k => $v){
						if(($k == "attribute") AND (strlen($v) > 0)){
                     	       				if(!isset($attr)){
								$attr = "'$v'";
							} else {
								$attr .= ",'$v'";
							}
						}
					}
				}
			} else {
				foreach( array_keys($project_config['attributes']) as $attribute_key ){
					if (!isset($attr)){
						if(strlen($attribute_key) > 0){
							$attr = "'$attribute_key'";
						}
					} else {
						if(strlen($attribute_key) > 0){
							$attr .= ",'$attribute_key'";
						}
					}			
				}
			}
			$attr = trim($attr, "'");
			$products[] = array ( $attr );
			if($nr_products_processed == 0){
				$file = $this->woosea_create_csvtxt_feed ( $products, $project_config, 'true' );
			}
		} else {
			$products[] = array ();
			$file = $this->woosea_create_xml_feed ( $products, $project_config, 'true' );
		}
		$xml_piece = "";

		// Get taxonomies
              	$no_taxonomies = array("element_category","template_category","portfolio_category","portfolio_skills","portfolio_tags","faq_category","slide-page","yst_prominent_words","category","post_tag","nav_menu","link_category","post_format","product_type","product_visibility","product_cat","product_shipping_class","product_tag");
             	$taxonomies = get_taxonomies();
          	$diff_taxonomies = array_diff($taxonomies, $no_taxonomies);

		// Check if we need to get just products or also product variations
		if(isset($project_config['product_variations'])){
			$post_type = array('product', 'product_variation');
		} else {
			$post_type = array('product');
		}

		// Check shipping currency location
		$project_config['ship_suffix'] = "false";
        	foreach ($project_config['attributes'] as $attr_key => $attr_value) {

			if($attr_value['mapfrom'] == "shipping"){
				
				if(!empty($attr_value['suffix'])){
					$project_config['ship_suffix'] = "true";
				}
			}
		}

		// Pinteres RSS feeds need different sorting
		if($project_config['fields'] == "pinterest_rss"){
			$orderby = "ASC";
		} else {
			$orderby = "DESC";
		}

		// Switch to configured WPML language
        	if(isset($project_config['WPML'])){
                	if ( function_exists('icl_object_id') ) {
				if( !class_exists( 'Polylang' ) ) {
					global $sitepress;
					$lang = $project_config['WPML'];
					$sitepress->switch_lang($lang);
				}
			}
		}

           	// Get Orders
		if(isset($project_config['total_product_orders_lookback'])){
			if($project_config['total_product_orders_lookback'] > 0){
             			$allowed_product_orders = $this->woosea_get_orders ( $project_config );
			}
		}

		// Construct WP query
		$wp_query = array(
				'posts_per_page' => $offset_step_size,
                                'offset' => $nr_products_processed,
				'post_type' => $post_type,
				'post_status' => 'publish',
                                'fields' => 'ids',
                                'no_found_rows' => true,
                );
		$prods = new WP_Query($wp_query);
		$shipping_zones = $this->woosea_get_shipping_zones();

           	// Log some information to the WooCommerce logs
             	$add_woosea_logging = get_option ('add_woosea_logging');
              	if($add_woosea_logging == "yes"){
                	$logger = new WC_Logger();
                     	$logger->add('Product Feed Pro by AdTribes.io','<!-- Start new QUERY -->');
                    	$logger->add('Product Feed Pro by AdTribes.io',print_r($wp_query, TRUE));
                   	$logger->add('Product Feed Pro by AdTribes.io','<!-- START new QUERY -->');
             	}

	        while ($prods->have_posts()) : $prods->the_post(); 
			global $product;
			$attr_line = "";
			$catname = "";	
			$catlink = "";
			$xml_product = array();

			$this->childID = get_the_ID();
            		$this->parentID = wp_get_post_parent_id($this->childID);
			$post = get_post($this->parentID);

			// When Wordpress user is an admin and runs the process of creating product feeds also products are put in the feed
			// with a status other than published. This is unwanted behaviour so we skip all products that are not on publish.
			$status = get_post_status($this->parentID);
			if($status != "publish") { continue; }

			$product_data['id'] = get_the_ID();
			
			// Only products that have been sold are allowed to go through
                	if(isset($project_config['total_product_orders_lookback'])){
                        	if($project_config['total_product_orders_lookback'] > 0){
					if(!in_array($product_data['id'], $allowed_product_orders)){ continue; }
				}
			}

			$product_data['title'] = $product->get_title();
                        $product_data['title'] = $this->woosea_utf8_for_xml( $product_data['title'] );
			$product_data['mother_title'] = $product->get_title();
                        $product_data['mother_title'] = $this->woosea_utf8_for_xml( $product_data['mother_title'] );
			$product_data['sku'] = $product->get_sku();
			$product_data['sku_id'] = $product_data['id'];
			$product_data['wc_post_id_product_id'] = "wc_post_id_".$product_data['id'];
			$product_data['publication_date'] = get_the_date('d-m-y G:i:s');
			$product_data['add_to_cart_link'] = get_site_url()."/shop/?add-to-cart=".$product_data['id'];

			// Get product creation date
			if(!empty( $product->get_date_created() )){
				$product_data['product_creation_date'] = $product->get_date_created()->format('Y-m-d'); 
				//$product_data['product_creation_date'] = "2019-10-27";
				$today_date = date('Y-m-d');
				$diff = abs(strtotime($today_date) - strtotime($product_data['product_creation_date']));
				$diff_days = floor($diff/86400);
				$product_data['days_back_created'] = $diff_days;
			}

			// Start product visibility logic
			$product_data['exclude_from_catalog'] = "no";
			$product_data['exclude_from_search'] = "no";
			$product_data['exclude_from_all'] = "no";
			$product_data['featured'] = "no";

			// Get product tax details
                        $product_data['tax_status'] = $product->get_tax_status();
                        $product_data['tax_class'] = $product->get_tax_class();

			// End product visibility logic
                       	$product_data['item_group_id'] = $this->parentID;

			// Get number of orders for this product
			$product_data['total_product_orders'] = 0;
			$product_data['total_product_orders'] = get_post_meta($product_data['id'], 'total_sales', true);

			if($product_data['item_group_id'] > 0){
				$visibility_list = wp_get_post_terms($product_data['item_group_id'], 'product_visibility', array("fields" => "all"));
			} else {
				$visibility_list = wp_get_post_terms(get_the_ID(), 'product_visibility', array("fields" => "all"));
			}

			foreach($visibility_list as $visibility_single){
				if($visibility_single->slug == "exclude-from-catalog"){
					$product_data['exclude_from_catalog'] = "yes";
				}
				if($visibility_single->slug == "exclude-from-search"){
					$product_data['exclude_from_search'] = "yes";
				}
				if($visibility_single->slug == "featured"){
					$product_data['featured'] = "yes";
				}
			}				

			if(($product_data['exclude_from_search'] == "yes") AND ($product_data['exclude_from_catalog'] == "yes")){
				$product_data['exclude_from_all'] = "yes";
			}
	
			if (!empty($product_data['sku'])){
				$product_data['sku_id'] = $product_data['sku']."_".$product_data['id'];

				if ($project_config['fields'] == "facebook_drm"){
					if($product_data['item_group_id'] > 0){
						$product_data['sku_item_group_id'] = $product_data['sku']."_".$product_data['item_group_id'];
					} else {
						$product_data['sku_item_group_id'] = $product_data['sku']."_".$product_data['id'];
					}
				}
			}
			
			$cat_alt = array();
			$cat_term = "";
			$categories = array();

			if($product_data['item_group_id'] > 0){
                        	$cat_obj = get_the_terms( $product_data['item_group_id'], 'product_cat' );
			} else {
                        	$cat_obj = get_the_terms( $product_data['id'], 'product_cat' );
			}     


	          	if($cat_obj){
				foreach($cat_obj as $cat_term){
        	               		$cat_alt[] = $cat_term->term_id;
                	      	}
			}
			$categories = $cat_alt;

			// Determine real category hierarchy
			$cat_order = array();
			foreach ($categories as $key => $value){
	                	$product_cat = get_term($value, 'product_cat');

				// Not in array so we can add it
				if(!in_array($value, $cat_order)){
		
					$parent_cat = $product_cat->parent;
					// Check if parent is in array
					if(in_array($parent_cat, $cat_order)){
						// Parent is in array, now determine position
						$position = array_search($parent_cat, $cat_order);					

						// Use array splice to add it in the right position in array
						$new_position = $position+1;

						// Insert on new position in array
						array_splice( $cat_order, $new_position, 0, $value );  
					} else {
						// Parent is not in array
						if($parent_cat > 0){
							if(in_array($parent_cat, $categories)){
								$cat_order[] = $parent_cat;
							}
							$cat_order[] = $value;
						} else {
							// This is the MAIN cat so should be in front
							array_unshift($cat_order, $value);
						}
					}
				}
			}

			$categories = $cat_order;

			// This is a category fix for Yandex, probably needed for all channels
			// When Yoast is not installed and a product is linked to multiple categories
			// The ancestor categoryId does not need to be in the feed
                        $double_categories = array(
                        	0 => "Yandex",
                              	1 => "Prisjakt.se",
                              	2 => "Pricerunner.se",
                              	3 => "Pricerunner.dk",
                     	);
                                
                     	if (in_array($project_config['name'], $double_categories, TRUE)){
				$cat_alt = array();
				$cat_terms = get_the_terms( $product_data['id'], 'product_cat' );

                       		if($cat_terms){
					foreach($cat_terms as $cat_term){
        	                		$cat_alt[] = $cat_term->term_id;
                	      		}
				}
				$categories = $cat_alt;
			}

			$product_data['category_path'] = "";

			foreach ($categories as $key => $value){
	               		$product_cat = get_term($value, 'product_cat');

				// Check if there are mother categories
				if(!empty($product_cat)){	
					$category_path = $this->woosea_get_term_parents( $product_cat->term_id, 'product_cat', $link = false, $project_taxonomy = $project_config['taxonomy'], $nicename = false, $visited = array() );
					$category_path_skroutz = str_replace("&gt;",">",$category_path);
			
		                	if(!is_object($category_path)){
						$product_data['category_path'] = $category_path;
						$product_data['category_path_skroutz'] = $category_path_skroutz;
						$product_data['category_path_skroutz'] = str_replace("Home >","",$product_data['category_path_skroutz']);
						$product_data['category_path_skroutz'] = str_replace("&amp;","&",$product_data['category_path_skroutz']);
					}

					$parent_categories = get_ancestors($product_cat->term_id, 'product_cat');
					foreach ($parent_categories as $category_id){
						$parent = get_term_by('id', $category_id, 'product_cat');
						$parent_name = $parent->name;
					}

                         	     	if(isset($product_cat->name)) {
						$catname .= "||".$product_cat->name;
						$catlink .= "||".get_term_link($value,'product_cat');
					}
				}
			}
	
			// Get the Yoast primary category (if exists)
			if ( class_exists('WPSEO_Primary_Term') ) {

     				// Show the post's 'Primary' category, if this Yoast feature is available, & one is set
				$item_id = $product_data['id'];
				if($product_data['item_group_id'] > 0){
					$item_id = $product_data['item_group_id'];	
				}
				$wpseo_primary_term = new WPSEO_Primary_Term( 'product_cat', $item_id );
				$prm_term = $wpseo_primary_term->get_primary_term();
	               		$prm_cat = get_term($prm_term, 'product_cat');
				if(!is_wp_error($prm_cat)){
					if(!empty($prm_cat->name)){
						$product_data['category_path'] = $this->woosea_get_term_parents( $prm_cat->term_id, 'product_cat', $link = false, $project_taxonomy = $project_config['taxonomy'], $nicename = false, $visited = array() );
						$product_data['one_category'] = $prm_cat->name;
					}				
				}
			}

			$product_data['category_path_short'] = str_replace("Home &gt;","",$product_data['category_path']);
			$product_data['category_path_short'] = str_replace("&gt;",">",$product_data['category_path_short']);
			$product_data['category_link'] = $catlink;
			$product_data['raw_categories'] = ltrim($catname,"||");
			$product_data['categories'] = $catname;
			$product_data['description'] = html_entity_decode((str_replace("\r", "", $post->post_content)), ENT_QUOTES | ENT_XML1, 'UTF-8');
			$product_data['short_description'] = html_entity_decode((str_replace("\r", "", $post->post_excerpt)), ENT_QUOTES | ENT_XML1, 'UTF-8');

			// Strip HTML from (short) description
			$product_data['description'] = $this->rip_tags($product_data['description']);
			$product_data['short_description'] = $this->rip_tags($product_data['short_description']);

			// Strip out Visual Composer short codes, including the Visual Composer Raw HTML
    			$product_data['description'] = preg_replace('/\[vc_raw_html.*\[\/vc_raw_html\]/', '', $product_data['description']);
			$product_data['description'] = preg_replace( '/\[(.*?)\]/', ' ', $product_data['description'] );
    			$product_data['short_description'] = preg_replace('/\[vc_raw_html.*\[\/vc_raw_html\]/', '', $product_data['short_description']);
			$product_data['short_description'] = preg_replace( '/\[(.*?)\]/', ' ', $product_data['short_description'] );

			// Strip out the non-line-brake character
			$product_data['description'] = str_replace("&#xa0;", "", $product_data['description']);
			$product_data['short_description'] = str_replace("&#xa0;", "", $product_data['short_description']);

			// Strip strange UTF chars
			$product_data['description'] = trim($this->woosea_utf8_for_xml($product_data['description']));
			$product_data['short_description'] = trim($this->woosea_utf8_for_xml($product_data['short_description']));

			/**
		 	* Check of we need to add Google Analytics UTM parameters
		 	*/
			if(isset($project_config['utm_on'])){
				$utm_part = $this->woosea_append_utm_code ( $project_config, get_the_ID(), $this->parentID, get_permalink( $product_data['id'] ));
			} else {
				$utm_part = "";
			}

			$product_data['link'] = get_permalink( $product_data['id'])."$utm_part";
			$variable_link = get_permalink( $product_data['id'] );
			$vlink_piece = explode("?", $variable_link);
			$qutm_part = ltrim($utm_part, "&amp;");
			$qutm_part = ltrim($qutm_part, "amp;");
			$qutm_part = ltrim($qutm_part, "?");
			if($qutm_part){
				$product_data['variable_link'] = $vlink_piece[0]."?".$qutm_part;
			} else {
				$product_data['variable_link'] = $vlink_piece[0];
			}

			$product_data['condition'] = ucfirst( get_post_meta( $product_data['id'], '_woosea_condition', true ) );
			if(empty($product_data['condition']) || $product_data['condition'] == "Array"){
				$product_data['condition'] = "New";
			}
			$product_data['availability'] = $this->get_stock( $this->childID );
	
			/**
			* When 'Enable stock management at product level is active
			* availability will always return out of stock, even when the stock quantity > 0
			* Therefor, we need to check the stock_status and overwrite te availability value
			*/
			$stock_status = $product->get_stock_status();
			if ($stock_status == "outofstock"){
				$product_data['availability'] = "out of stock";
			} elseif ($stock_status == "onbackorder") {
				$product_data['availability'] = "on backorder";
			} else {
				$product_data['availability'] = "in stock";
			}

			$product_data['author'] = get_the_author();
			$product_data['quantity'] = $this->clean_quantity( $this->childID, "_stock" );
			$product_data['visibility'] = $this->get_attribute_value( $this->childID,"_visibility" );
			$product_data['menu_order'] =  get_post_field( 'menu_order', $product_data['id'] );
			$product_data['currency'] = get_woocommerce_currency();
			if(isset($project_config['WCML'])){
				$product_data['currency'] = $project_config['WCML'];
			}
                        $product_data['sale_price_start_date'] = $this->get_sale_date($this->childID, "_sale_price_dates_from");
                        $product_data['sale_price_end_date'] = $this->get_sale_date($this->childID, "_sale_price_dates_to");
			$product_data['sale_price_effective_date'] = $product_data['sale_price_start_date'] ."/".$product_data['sale_price_end_date'];
			if($product_data['sale_price_effective_date'] == "/"){
				$product_data['sale_price_effective_date'] = "";
			}
			$product_data['image'] = wp_get_attachment_url($product->get_image_id());
			$product_data['image_all'] = $product_data['image'];
			$product_data['all_images'] = $product_data['image'];
			$product_data['all_gallery_images'] = "";
	
			// For variable products I need to get the product gallery images of the simple mother product	
			if($product_data['item_group_id'] > 0){
				$parent_product = wc_get_product( $product_data['item_group_id'] );
				if(is_object($parent_product)){
					$gallery_ids = $parent_product->get_gallery_image_ids();
					$product_data['image_all'] = wp_get_attachment_url($parent_product->get_image_id());
					$gal_id=1;
					foreach ($gallery_ids as $gallery_key => $gallery_value){
						$product_data["image_" . $gal_id] = wp_get_attachment_url($gallery_value);
						$product_data['all_images'] .= ",".wp_get_attachment_url($gallery_value);
						$product_data['all_gallery_images'] .= ",".wp_get_attachment_url($gallery_value);
						$gal_id++;
					}
				}
			} else {
				$gallery_ids = $product->get_gallery_image_ids();
				$gal_id=1;
				foreach ($gallery_ids as $gallery_key => $gallery_value){
					$product_data["image_" . $gal_id] = wp_get_attachment_url($gallery_value);
					$product_data['all_images'] .= ",".wp_get_attachment_url($gallery_value);
					$product_data['all_gallery_images'] .= ",".wp_get_attachment_url($gallery_value);
					$gal_id++;
				}
			}

                      	$product_data['all_images'] = ltrim($product_data['all_images'],',');
                      	$product_data['all_gallery_images'] = ltrim($product_data['all_gallery_images'],',');
			$product_data['product_type'] = $product->get_type();
			$product_data['content_type'] = "product";
			if($product_data['product_type'] == "variation"){
				$product_data['content_type'] = "product_group";
			}
                        $product_data['rating_total'] = $product->get_rating_count();
                        $product_data['rating_average'] = $product->get_average_rating();

			// When a product has no reviews than remove the 0 rating	
			if ($product_data['rating_average'] == 0){
				unset($product_data['rating_average']);
			}

	                $product_data['shipping'] = 0;
			$tax_rates = WC_Tax::get_base_tax_rates( $product->get_tax_class() );
			$shipping_class_id = $product->get_shipping_class_id();
			
                	$shipping_class= $product->get_shipping_class();
			$class_cost_id = "class_cost_".$shipping_class_id;
			if($class_cost_id == "class_cost_0"){
				$class_cost_id = "no_class_cost";
			}

			$product_data['shipping_label'] = $product->get_shipping_class();

			// Get product prices
			$product_data['price'] = wc_get_price_including_tax($product, array('price'=> $product->get_price()));
			$product_data['price'] = wc_format_decimal($product_data['price'],2);
			$product_data['sale_price'] = wc_get_price_including_tax($product, array('price'=> $product->get_sale_price()));
			$product_data['sale_price'] = wc_format_decimal($product_data['sale_price'],2);
			$product_data['regular_price'] = wc_get_price_including_tax($product, array('price'=> $product->get_regular_price()));
			$product_data['regular_price'] = wc_format_decimal($product_data['regular_price'],2);

			// Untouched raw system pricing - DO NOT CHANGE THESE
			$product_data['system_net_price'] = round(wc_get_price_excluding_tax( $product ), 2);
			$product_data['system_net_price'] = wc_format_decimal($product_data['system_net_price'],2);
                        $product_data['system_regular_price'] = round($product->get_regular_price(),2);
			$product_data['system_regular_price'] = wc_format_decimal($product_data['system_regular_price'],2);

			$product_data['system_price'] = wc_get_price_including_tax($product, array('price'=> $product->get_price()));
            		$product_data['system_price'] = ($product->get_regular_price()) ? $this->get_product_price($product, $product->get_regular_price()) : '';
			$product_data['system_price'] = wc_format_decimal($product_data['system_price'],2);

			$product_data['system_sale_price'] = wc_get_price_including_tax($product, array('price'=> $product->get_sale_price()));
            		$sale_price = $product_data['system_sale_price'];
            		$product_data['system_sale_price'] = ($product->get_regular_price() != $sale_price) ? $this->get_product_price($product, $sale_price ) : '';
			$product_data['system_sale_price'] = wc_format_decimal($product_data['system_sale_price'],2);

			// Override price when WCML price is different than the non-translated price	
			if(isset($project_config['WCML'])){
				$product_data['price'] = apply_filters('wcml_raw_price_amount', $product_data['price'], $project_config['WCML']);
				$product_data['regular_price'] = apply_filters('wcml_raw_price_amount', $product_data['regular_price'], $project_config['WCML']);
				$product_data['sale_price'] = apply_filters('wcml_raw_price_amount', $product_data['sale_price'], $project_config['WCML']);

				// When WCML manual prices have been entered
				// Make sure the product ID is not NULL either
				if(!is_null($product_data['id'])){				
					global $woocommerce_wpml;  
					$custom_prices = $woocommerce_wpml->get_multi_currency()->custom_prices->get_product_custom_prices( $product_data['id'], $project_config['WCML'] );

					if($custom_prices['_price'] > 0){
						$product_data['price'] = $custom_prices['_price'];
					}

					if($custom_prices['_regular_price'] > 0){
						$product_data['regular_price'] = $custom_prices['_regular_price'];
					}

					if($custom_prices['_sale_price'] > 0){
						$product_data['sale_price'] = $custom_prices['_sale_price'];
					}
				}
			}
	
			if($product_data['regular_price'] == $product_data['sale_price']){
				$product_data['sale_price'] = "";
			}

			// Override price when bundled product
			if(($product->get_type() == "bundle") OR ($product->get_type() == "composite")){
				$meta = get_post_meta($product_data['id']);
                        	$product_data['price'] = get_post_meta($product_data['id'], '_price', true);
                        	$product_data['regular_price'] = get_post_meta($product_data['id'], '_regular_price', true);
				if($product_data['price'] != $product_data['regular_price']){
                        		$product_data['sale_price'] = get_post_meta($product_data['id'], '_price', true);
				}	
			}
	
			if(!empty($tax_rates)){	
				foreach ($tax_rates as $tk => $tv){
					if($tv['rate'] > 0){
						$tax_rates[1]['rate'] = $tv['rate'];
					} else {
						$tax_rates[1]['rate'] = 0;
					}
				}
			} else {
				$tax_rates[1]['rate'] = 0;
			}

			$fullrate = 100+$tax_rates[1]['rate'];
			
			// Determine the gross prices of products
			if($product->get_price()){
				$product_data['price_forced'] = round(wc_get_price_excluding_tax($product,array('price'=> $product->get_price())) * (100+$tax_rates[1]['rate'])/100,2);
			}
			if($product->get_regular_price()){
				$product_data['regular_price_forced'] = round(wc_get_price_excluding_tax($product, array('price'=> $product->get_regular_price())) * (100+$tax_rates[1]['rate'])/100,2);
				$product_data['net_regular_price'] = ($product->get_regular_price()/$fullrate)*100;
				$product_data['net_regular_price'] = round($product_data['net_regular_price'],2);
			}
			if($product->get_sale_price()){
				$product_data['sale_price_forced'] = round(wc_get_price_excluding_tax($product, array('price'=> $product->get_sale_price())) * (100+$tax_rates[1]['rate'])/100,2);
				$product_data['net_sale_price'] = ($product->get_sale_price()/$fullrate)*100;
				$product_data['net_sale_price'] = round($product_data['net_sale_price'],2); 

				// We do not want to have 0 sale price values in the feed	
				if($product_data['net_sale_price'] == 0){
					$product_data['net_sale_price'] = "";
				}
			}
			$product_data['net_price'] = round(wc_get_price_excluding_tax( $product ), 2);
 
			$price = wc_get_price_including_tax($product,array('price'=> $product->get_price()));
			if($product_data['sale_price'] > 0){
				$price = $product_data['sale_price'];
			}
			
			// Do we need to convert all of the above prices with the Aelia Currency Switcher
			if((isset($project_config['AELIA'])) AND (!empty($GLOBALS['woocommerce-aelia-currencyswitcher'])) AND (get_option ('add_aelia_support') == "yes")){
				if(!array_key_exists('base_currency', $project_config)){
					$from_currency = get_woocommerce_currency();
				} else {
					$from_currency = $project_config['base_currency'];
				}		
				//$set_country_base = add_filter('wc_aelia_cs_selected_currency', 'SEK', 0);
				//$product_data['price'] = apply_filters('wc_aelia_cs_convert', $product_data['price'], $from_currency, $project_config['AELIA']);
				//$product_data['regular_price'] = apply_filters('wc_aelia_cs_convert', $product_data['regular_price'], $from_currency, $project_config['AELIA']);
				//$product_data['sale_price'] = apply_filters('wc_aelia_cs_convert', $product_data['sale_price'], $from_currency, $project_config['AELIA']);

				//$product_data['price'] = do_shortcode('[aelia_cs_product_price product_id="'.$product_data['id'].'" formatted="0" currency="'.$project_config['AELIA'].'"]');
				
				$product_data['price'] = apply_filters('wc_aelia_cs_convert', $product_data['price'], $from_currency, $project_config['AELIA']);
				$product_data['regular_price'] = apply_filters('wc_aelia_cs_convert', $product_data['regular_price'], $from_currency, $project_config['AELIA']);
				$product_data['sale_price'] = apply_filters('wc_aelia_cs_convert', $product_data['sale_price'], $from_currency, $project_config['AELIA']);

				if(isset($product_data['price_forced'])){
					$product_data['price_forced'] = apply_filters('wc_aelia_cs_convert', $product_data['price_forced'], $from_currency, $project_config['AELIA']);
				}

				if(isset($product_data['regular_price_forced'])){
					$product_data['regular_price_forced'] = apply_filters('wc_aelia_cs_convert', $product_data['regular_price_forced'], $from_currency, $project_config['AELIA']);
				}
				if($product->get_sale_price()){
					$product_data['sale_price_forced'] = apply_filters('wc_aelia_cs_convert', $product_data['sale_price_forced'], $from_currency, $project_config['AELIA']);
				}

				$product_data['net_price'] = apply_filters('wc_aelia_cs_convert', $product_data['net_price'], $from_currency, $project_config['AELIA']);
				if(isset($product_data['net_regular_price'])){	
					$product_data['net_regular_price'] = apply_filters('wc_aelia_cs_convert', $product_data['net_regular_price'], $from_currency, $project_config['AELIA']);
				}	
				if(isset($product_data['net_sale_price'])){	
					$product_data['net_sale_price'] = apply_filters('wc_aelia_cs_convert', $product_data['net_sale_price'], $from_currency, $project_config['AELIA']);
				}

				// Get Aelia manually inserted currency prices
				if($product->is_type('simple')){
					$regular_aelia_prices = get_post_meta($product_data['id'], '_regular_currency_prices', true);
				} else {
					$regular_aelia_prices = get_post_meta($product_data['id'], 'variable_regular_currency_prices', true);
				}

				$regular_aelia_prices = trim($regular_aelia_prices, "}");
				$regular_aelia_prices = trim($regular_aelia_prices, "{");

				if(strlen($regular_aelia_prices) > 2){
					$regular_aelia_pieces = explode(",", $regular_aelia_prices);
					foreach ($regular_aelia_pieces as $rap_k => $rap_v){
						$regulars = explode(":", $rap_v);
						$reg_cur = trim($regulars[0], "\"");
						$reg_val = trim($regulars[1], "\"");
			
						if($reg_cur == $project_config['AELIA']){
							$product_data['price'] = $reg_val;
							$product_data['regular_price'] = $reg_val;
						}
					}
				}

				// Is the Aelia rounding plugin active
				if(class_exists('WC_Aelia_CS_Custom_Rounding')){

                                        // Do not round the base currency
                                        if($from_currency != $project_config['AELIA']){
						$product_data['price'] = round($product_data['price'], 0) - 0.05;
						$product_data['regular_price'] = round($product_data['regular_price'], 0) - 0.05;
						$product_data['sale_price'] = round($product_data['sale_price'], 0) - 0.05;
						$product_data['price_forced'] = round($product_data['price_forced'], 0) - 0.05;
					}
				}

				if($product->is_type('simple')){
					$sale_aelia_prices = get_post_meta($product_data['id'], '_sale_currency_prices', true);
				} else {
					$sale_aelia_prices = get_post_meta($product_data['id'], 'variable_sale_currency_prices', true);
				}
				$sale_aelia_prices = trim($sale_aelia_prices, "}");
				$sale_aelia_prices = trim($sale_aelia_prices, "{");

				if(strlen($sale_aelia_prices) > 2){
					$sale_aelia_pieces = explode(",", $sale_aelia_prices);
					foreach ($sale_aelia_pieces as $sap_k => $sap_v){
						$sales = explode(":", $sap_v);
						$sale_cur = trim($sales[0], "\"");
						$sale_val = trim($sales[1], "\"");
						if($sale_cur == $project_config['AELIA']){
							$product_data['sale_price'] = $sale_val;
						}
					}
				}
			}

			// Localize the price attributes
			$decimal_separator = wc_get_price_decimal_separator();
			$product_data['price'] = wc_format_localized_price($product_data['price']);
			$product_data['regular_price'] = wc_format_localized_price($product_data['regular_price']);
			$product_data['sale_price'] = wc_format_localized_price($product_data['sale_price']);
                        if($product->get_price()){
				$product_data['price_forced'] = wc_format_localized_price($product_data['price_forced']);
			}
                        if($product->get_regular_price()){
				$product_data['regular_price_forced'] = wc_format_localized_price($product_data['regular_price_forced']);
			}
			if($product->get_sale_price()){
				$product_data['sale_price_forced'] = wc_format_localized_price($product_data['sale_price_forced']);
			}	
			$product_data['net_price'] = wc_format_localized_price($product_data['net_price']);
	
			if(isset($product_data['net_regular_price'])){
				$product_data['net_regular_price'] = wc_format_localized_price($product_data['net_regular_price']);
			}

			if(isset($product_data['net_sale_price'])){
				$product_data['net_sale_price'] = wc_format_localized_price($product_data['net_sale_price']);
				$product_data['net_sale_price'] = wc_format_localized_price($product_data['net_sale_price']);
				$product_data['net_sale_price'] = wc_format_localized_price($product_data['net_sale_price']);
			}

                        $product_data['system_price'] = wc_format_localized_price($product_data['system_price']);
                        $product_data['system_net_price'] = wc_format_localized_price($product_data['system_net_price']);
                        $product_data['system_regular_price'] = wc_format_localized_price($product_data['system_regular_price']);
                        $product_data['system_sale_price'] = wc_format_localized_price($product_data['system_sale_price']);

			// Add rounded price options
			$product_data['rounded_price'] = round($product_data['price']);
			$product_data['rounded_regular_price'] = round($product_data['regular_price']);
			$product_data['rounded_sale_price'] = round($product_data['sale_price']);

			foreach($project_config['attributes'] as $attr_key => $attr_arr){
				if(is_array($attr_arr)){
					if($attr_arr['attribute'] == "g:shipping"){
						$product_data['shipping'] =  $this->woosea_get_shipping_cost($class_cost_id, $project_config, $product_data['price'], $tax_rates, $shipping_zones, $product_data['id'], $product_data['item_group_id']);
						$shipping_str = $product_data['shipping'];
					}
				}
			}

			if ((array_key_exists('shipping', $project_config['attributes'])) OR (array_key_exists('shipping_price', $project_config['attributes'])) OR ($project_config['fields'] == "trovaprezzi")){
				$product_data['shipping'] =  $this->woosea_get_shipping_cost($class_cost_id, $project_config, $product_data['price'], $tax_rates, $shipping_zones, $product_data['id'], $product_data['item_group_id']);
				$shipping_str = $product_data['shipping'];
			}

			// Get only shipping costs
			$product_data['shipping_price'] = 0;
			$shipping_arr = $product_data['shipping'];			
			if(is_array($shipping_arr)){
				foreach($shipping_arr as $akey => $arr){
					//$product_data['shipping_price'] = $arr['price'];
                                        $pieces_ship = explode (" ", $arr['price']);
					if(isset($pieces_ship['1'])){
						$product_data['shipping_price'] = $pieces_ship['1'];
					}
				}		

			}

			// Google Dynamic Remarketing feeds require the English price notation
			if ($project_config['name'] == "Google Remarketing - DRM"){
				$thousand_separator = wc_get_price_thousand_separator();

				if($thousand_separator != ','){
					$product_data['price'] = floatval(str_replace(',', '.', str_replace('.', '', $product_data['price'])));
					$product_data['regular_price'] = floatval(str_replace(',', '.', str_replace('.', '', $product_data['regular_price'])));
					if($product_data['sale_price'] > 0){
						$product_data['sale_price'] = floatval(str_replace(',', '.', str_replace('.', '', $product_data['sale_price'])));
					}
					if(isset($product_data['regular_price_forced'])){
						$product_data['regular_price_forced'] = floatval(str_replace(',', '.', str_replace('.', '', $product_data['regular_price_forced'])));
					}
					if($product->get_sale_price()){
						$product_data['sale_price_forced'] = floatval(str_replace(',', '.', str_replace('.', '', $product_data['sale_price_forced'])));
					}
					if($product_data['net_price'] > 0){
						$product_data['net_price'] = floatval(str_replace(',', '.', str_replace('.', '', $product_data['net_price'])));
					}
					$product_data['net_regular_price'] = floatval(str_replace(',', '.', str_replace('.', '', $product_data['net_regular_price'])));
					$product_data['net_sale_price'] = floatval(str_replace(',', '.', str_replace('.', '', $product_data['net_sale_price'])));
				}
			}

			$product_data['installment'] = $this->woosea_get_installment($project_config, $product_data['id']);
			$product_data['weight'] = ($product->get_weight()) ? $product->get_weight() : false;
                        $product_data['height'] = ($product->get_height()) ? $product->get_height() : false;
                        $product_data['length'] = ($product->get_length()) ? $product->get_length() : false;
			$product_data['width'] = ($product->get_width()) ? $product->get_width() : false;

                        // Featured Image
                        if (has_post_thumbnail($post->ID)){
                         	$image = wp_get_attachment_image_src(get_post_thumbnail_id($post->ID), 'single-post-thumbnail');
                            	$product_data['feature_image'] = $this->get_image_url($image[0]);
                        } else {
                           	$product_data['feature_image'] = $this->get_image_url($product_data['image']);
                        }

			/**
			 * Do we need to add Dynamic Attributes?
			 */
			$project_config['attributes_original'] = $project_config['attributes'];

			if($project_config['fields'] != 'standard'){
				//$project_config['attributes_original'] = array();
				foreach($project_config['attributes'] as $stand_key => $stand_val){
					if((isset($stand_val['mapfrom'])) AND (strlen($stand_val['mapfrom']) > 0)){
						$project_config['attributes_original'][$stand_val['mapfrom']] = "true";
					}
				}				
			}

			foreach($diff_taxonomies as $taxo){
				$term_value = get_the_terms($product_data['id'], $taxo);
				$product_data[$taxo] = "";

				if(is_array($term_value)){
					foreach($term_value as $term){
						$product_data[$taxo] .= ",". $term->name;
					}
					$product_data[$taxo] = ltrim($product_data[$taxo],',');
					$product_data[$taxo] = rtrim($product_data[$taxo],',');
				}
			}

			/*r
			 * Add product tags to the product data array
			 */
			$product_tags = get_the_terms($product_data['id'], "product_tag");
			if(is_array($product_tags)){

				foreach($product_tags as $term){

					if(!array_key_exists("product_tag", $product_data)){
						$product_data["product_tag"] = array($term->name);
						$product_data["product_tag_space"] = array($term->name);
					} else {
			               		array_push ($product_data["product_tag"], $term->name);
			               		array_push ($product_data["product_tag_space"], $term->name);
					}
				}
			} else {
				$product_data["product_tag"] = array();
				$product_data["product_tag_space"] = array();
			}
			
			/**
			 * Get Custom Attributes for Single, Bundled and Composite products
			 */
			if (($product->is_type('simple')) OR ($product->is_type('external')) OR ($product->is_type('bundle')) OR ($product->is_type('composite')) OR ($product_data['product_type'] == "variable")){
				$custom_attributes = $this->get_custom_attributes( $product_data['id'] );

				if(!in_array("woosea optimized title", $custom_attributes)){
					$woosea_opt = array (
						"_woosea_optimized_title" =>  "woosea optimized title",
					);
					$custom_attributes = array_merge($custom_attributes, $woosea_opt);
				}

                		if ( class_exists( 'All_in_One_SEO_Pack' ) ) {
                        		$custom_attributes['_aioseop_title'] = "All in one seo pack title";
                        		$custom_attributes['_aioseop_description'] = "All in one seo pack description";
                		}


				foreach($custom_attributes as $custom_kk => $custom_vv){
    					$custom_value = get_post_meta( $product_data['id'], $custom_kk, true );
					$new_key ="custom_attributes_" . $custom_kk;
			
					// Just to make sure the title is never empty
					if(($custom_kk == "_aioseop_title") && ($custom_value == "")){
						$custom_value = $product_data['title'];
					}
	
					// Just to make sure the description is never empty
					if(($custom_kk == "_aioseop_description") && ($custom_value == "")){
						$custom_value = $product_data['description'];
					}

					// Just to make sure product names are never empty
					if(($custom_kk == "_woosea_optimized_title") && ($custom_value == "")){
						$custom_value = $product_data['title'];
					}
					
					// Just to make sure the condition field is never empty
					if(($custom_kk == "_woosea_condition") && ($custom_value == "")){
						$custom_value = $product_data['condition'];
					}

                                 	// Need to clean up the strange price rightpress is returning
                                      	if($custom_kk == "rp_wcdpd_price_cache"){

		  	              		if((isset($project_config['AELIA'])) AND (!empty($GLOBALS['woocommerce-aelia-currencyswitcher'])) AND (get_option ('add_aelia_support') == "yes")){
							$product_data['price'] = do_shortcode('[aelia_cs_product_price product_id="'.$product_data['id'].'" formatted="0" currency="'.$project_config['AELIA'].'"]');
                                			$product_data['sale_price'] = apply_filters('wc_aelia_cs_convert', $custom_value['sale_price']['p'], $from_currency, $project_config['AELIA']);
						} else {
							if(array_key_exists("price", $custom_value)){
								$product_data['price'] = $custom_value['price']['p'];
                                          		}

							if(array_key_exists("sale_price", $custom_value)){
								$product_data['sale_price'] = $custom_value['sale_price']['p'];
                	                  		}
						}
					}
					$product_data[$new_key] = $custom_value;
				}

				/**
				 * We need to check if this product has individual custom product attributes
				 */
				global $wpdb;
                		$sql = "SELECT meta.meta_id, meta.meta_key as name, meta.meta_value as type FROM " . $wpdb->prefix . "postmeta" . " AS meta, " . $wpdb->prefix . "posts" . " AS posts WHERE meta.post_id=".$product_data['id']." AND meta.post_id = posts.id GROUP BY meta.meta_key ORDER BY meta.meta_key ASC";              
				$data = $wpdb->get_results($sql);
                		if (count($data)) {
                        		foreach ($data as $key => $value) {
                                		$value_display = str_replace("_", " ",$value->name);
                                		if (preg_match("/_product_attributes/i",$value->name)){
                                        		$product_attr = unserialize($value->type);
							if(!empty($product_attr)){
			                                	foreach ($product_attr as $key => $arr_value) {
									$new_key ="custom_attributes_" . $key;
									if(!empty($arr_value['value'])){
										$product_data[$new_key] = $arr_value['value'];
									}
                                        			}
							}
						}
					}
				}	
			}

			/**
			 * Get Product Attributes for Single products 
			 */
			if (($product->is_type('simple')) OR ($product->is_type('external')) OR ($product->is_type('bundle')) OR ($product->is_type('composite'))){
				$single_attributes = $product->get_attributes();

				foreach ($single_attributes as $attribute){
					$attr_name = strtolower($attribute->get_name());
					$attr_value = $product->get_attribute($attr_name);
					$product_data[$attr_name] = $attr_value;
				}
			}

                     	// Check if user would like to use the mother main image for all variation products
                      	$add_mother_image = get_option ('add_mother_image');
                      	if(($add_mother_image == "yes") AND ($product_data['item_group_id'] > 0)){
				$mother_image = wp_get_attachment_image_src( get_post_thumbnail_id( $product_data['item_group_id'] ), 'full' );
				$product_data['image'] = $mother_image[0];
                       	}

                        // Get product reviews for Google Product Review Feeds
                        // $product_data['reviews'] = $this->woosea_get_reviews( $product_data, $product );

			/**
			 * Versioned products need a seperate approach
			 * Get data for these products based on the mother products item group id 
			 */
			$variation_pass = "true";

			if( ($product_data['item_group_id'] > 0) AND (is_object(wc_get_product( $product_data['item_group_id']))) AND ($product_data['product_type'] == "variation")){
				$product_variations = new WC_Product_Variation( $product_data['id'] );
				$variations = $product_variations->get_variation_attributes();
		
				// Determine the default variation product
			      	$mother_product = wc_get_product($product_data['item_group_id']);
				$def_attributes = $mother_product->get_default_attributes();

				// Get review rating and count for parent product
	                        $product_data['rating_total'] = $mother_product->get_rating_count();
        	                $product_data['rating_average'] = $mother_product->get_average_rating();

				$diff_result = array_diff($variations, $def_attributes);

				if(isset($project_config['default_variations']) AND (!empty($diff_result))){	
					// Only when a variant has no attributes selected we will let it pass
					if(count(array_filter($variations)) == 0){
						$variation_pass = "true";
					} else {
						$variation_pass = "false";
					}
				}

				$append = "";

        			$variable_description = get_post_meta( $product_data['id'], '_variation_description', true );
				$product_data['parent_sku'] = get_post_meta( $product_data['item_group_id'], '_sku', true);

				/**
				 * When there is a specific description for a variation product than override the description of the mother product
				 */
				if(!empty($variable_description)){	
                        		$product_data['description'] = html_entity_decode((str_replace("\r", "", $variable_description)), ENT_QUOTES | ENT_XML1, 'UTF-8');
                        		//$product_data['short_description'] = html_entity_decode((str_replace("\r", "", $variable_description)), ENT_QUOTES | ENT_XML1, 'UTF-8');

                        		// Strip HTML from (short) description
                        		$product_data['description'] = $this->rip_tags($product_data['description']);
                        		//$product_data['short_description'] = $this->rip_tags($product_data['short_description']);
                        		
					// Strip out Visual Composer short codes
                        		$product_data['description'] = preg_replace( '/\[(.*?)\]/', ' ', $product_data['description'] );
                        		//$product_data['short_description'] = preg_replace( '/\[(.*?)\]/', ' ', $product_data['short_description'] );

                        		// Strip out the non-line-brake character
                        		$product_data['description'] = str_replace("&#xa0;", "", $product_data['description']);
                        		//$product_data['short_description'] = str_replace("&#xa0;", "", $product_data['short_description']);
			
					// Strip unwanted UTF8 chars
					$product_data['description'] = $this->woosea_utf8_for_xml( $product_data['description'] );
					//$product_data['short_description'] = $this->woosea_utf8_for_xml( $product_data['short_description'] );
				}

				/**
				 * Add the product visibility values for variations based on the simple mother product
				 */
				$product_data['exclude_from_catalog'] = "no";
				$product_data['exclude_from_search'] = "no";
				$product_data['exclude_from_all'] = "no";

                        	// Get number of orders for this product
				// First check if user added this field or created a rule or filter on it
				$ruleset = "false";
				if(array_key_exists('rules', $project_config)){
					foreach($project_config['rules'] as $rkey => $rvalue){
						if(in_array('total_product_orders', $rvalue)){
							$ruleset = "true";
						}
					}
				}
				
				if(array_key_exists('rules2', $project_config)){
					foreach($project_config['rules2'] as $rkey => $rvalue){
						if(in_array('total_product_orders', $rvalue)){
							$ruleset = "true";
						}
					}
				}
	
				if((array_key_exists('total_product_orders', $project_config['attributes'])) OR ($ruleset == "true")){
					$product_data['total_product_orders'] = 0;
                        		$sales_array = $this->woosea_get_nr_orders_variation ( $product_data['id'] );
                        		$product_data['total_product_orders'] = $sales_array[0];
				}

				$visibility_list = wp_get_post_terms($product_data['item_group_id'], 'product_visibility', array("fields" => "all"));

				if(!empty($visibility_list)){
					foreach($visibility_list as $visibility_single){
						if($visibility_single->slug == "exclude-from-catalog"){
							$product_data['exclude_from_catalog'] = "yes";
						}
						if($visibility_single->slug == "exclude-from-search"){
							$product_data['exclude_from_search'] = "yes";
						} 
					}	
				}			
		
				if(($product_data['exclude_from_search'] == "yes") AND ($product_data['exclude_from_catalog'] == "yes")){
					$product_data['exclude_from_all'] = "yes";
				}

				/**
				 * Although this is a product variation we also need to grap the Product attributes belonging to the simple mother product
				 */
				$mother_attributes = get_post_meta($product_data['item_group_id'], '_product_attributes');

				if(!empty($mother_attributes)){

	                      		foreach ($mother_attributes as $attribute){
						foreach($attribute as $key => $attr){
							$attr_name = $attr['name'];
						
							if(!empty($attr_name)){
								$terms = get_the_terms($product_data['item_group_id'], $attr_name);
				
								if(is_array($terms)){
									foreach($terms as $term){
										$attr_value = $term->name;
									}
									$product_data[$attr_name] = $attr_value;		
								} else {
									// Add the variable parent attributes	
									// When the attribute was not set for variations
									if($attr['is_variation'] == 0){	
										$new_key ="custom_attributes_" . $key;
										$product_data[$new_key] = $attr['value'];
									}
								}
							}
						}
					}
				}

				/**
				 * Although this is a product variation we also need to grap the Dynamic attributes belonging to the simple mother prodict
				 */
                        	foreach($diff_taxonomies as $taxo){
					$term_value = get_the_terms($product_data['item_group_id'], $taxo);

                                	if(is_array($term_value)){
                                        	foreach($term_value as $term){
							$product_data[$taxo] = $term->name;
                                       		}
                                	}
                        	}

                        	/**
                         	 * Add product tags to the product data array
                         	 */
                        	$product_tags = get_the_terms($product_data['item_group_id'], "product_tag");
                        	if(is_array($product_tags)){

                                	foreach($product_tags as $term){

                                        	if(!array_key_exists("product_tag", $product_data)){
                                    	           	$product_data["product_tag"] = array($term->name);
                                        	} else {
                                                	array_push ($product_data["product_tag"], $term->name);
                                        	}
                                	}
                        	}

				// Add attribute values to the variation product names to make them unique
				foreach($variations as $kk => $vv){
					$custom_key = $kk; 

					if (isset($project_config['product_variations']) AND ($project_config['product_variations'] == "on")){
						$taxonomy = str_replace("attribute_","",$kk);

						$term = get_term_by('slug', $vv, $taxonomy); 

						if($term && $term->name){
							$vv = $term->name;
						}

						if($vv){
							$append = ucfirst($vv);
							$append = rawurldecode($append);
							// Prevent duplicate attribute values from being added to the product name
							if(!preg_match("/" . preg_quote($product_data['title'], '/') . "/", $append)){
								$product_data['title'] = $product_data['title']." ".$append;
							}
						}
					}
					
					$custom_key = str_replace("attribute_","",$custom_key);
					$product_data[$custom_key] = $vv;
					$append = "";
				}

        	                /**
                	         * Get Custom Attributes for this variable product
                       	  	 */
                        	$custom_attributes = $this->get_custom_attributes( $product_data['id'] );

                                if(!in_array("woosea optimized title", $custom_attributes)){
                                        $woosea_opt = array (
                                                "_woosea_optimized_title" =>  "woosea optimized title",
                                        );
                                        $custom_attributes = array_merge($custom_attributes, $woosea_opt);
                                }

                                if ( class_exists( 'All_in_One_SEO_Pack' ) ) {
                                        $custom_attributes['_aioseop_title'] = "All in one seo pack title";
                                        $custom_attributes['_aioseop_description'] = "All in one seo pack description";
                                }
                  	      	
				foreach($custom_attributes as $custom_kk => $custom_vv){
                               		$custom_value = get_post_meta( $product_data['id'], $custom_kk, true );

					// Product variant brand is empty, grap that of the mother product
					if(($custom_kk == "_woosea_brand") && ($custom_value == "")){
                                		$custom_value = get_post_meta( $product_data['item_group_id'], $custom_kk, true );
					}

                                        // Just to make sure the title is never empty
                                        if(($custom_kk == "_aioseop_title") && ($custom_value == "")){
                                                $custom_value = $product_data['title'];
                                        }

                                        // Just to make sure the description is never empty
                                        if(($custom_kk == "_aioseop_description") && ($custom_value == "")){
                                                $custom_value = $product_data['description'];
                                        }

					// Product variant optimized title is empty, grap the mother product title
					if(($custom_kk == "_woosea_optimized_title") && ($custom_value == "")){
						$custom_value = $product_data['title'];
					}

					if(!is_array($custom_value)){
                                        	$custom_kk = str_replace("attribute_","",$custom_kk);
						$new_key ="custom_attributes_" . $custom_kk;
					
						// In order to make the mapping work again, replace var by product
                                        	$new_key = str_replace("var","product",$new_key);
						if(!empty( $custom_value )){
							$product_data[$new_key] = $custom_value;
                       				}
					}
				}

                                /**
                                 * We need to check if this product has individual custom product attributes
                                 */
                                global $wpdb;
                                $sql = "SELECT meta.meta_id, meta.meta_key as name, meta.meta_value as type FROM " . $wpdb->prefix . "postmeta" . " AS meta, " . $wpdb->prefix . "posts" . " AS posts WHERE meta.post_id=".$product_data['id']." AND meta.post_id = posts.id GROUP BY meta.meta_key ORDER BY meta.meta_key ASC";
                                $data = $wpdb->get_results($sql);
                                if (count($data)) {
                                        foreach ($data as $key => $value) {
                                                $value_display = str_replace("_", " ",$value->name);
                                                if (preg_match("/_product_attributes/i",$value->name)){
                                                        $product_attr = unserialize($value->type);
                                                        foreach ($product_attr as $key => $arr_value) {
                                                                $new_key ="custom_attributes_" . $key;
                                                                $product_data[$new_key] = $arr_value['value'];
							 }
                                                }
                                        }
                                }

				/**
				 * We also need to make sure that we get the custom attributes belonging to the simple mother product
				 */
	                       	$custom_attributes_mother = $this->get_custom_attributes( $product_data['item_group_id'] );

                        	foreach($custom_attributes_mother as $custom_kk_m => $custom_value_m){

					if(!array_key_exists($custom_kk_m, $product_data)){
						$custom_value_m = get_post_meta( $product_data['item_group_id'], $custom_kk_m, true );
						$new_key_m ="custom_attributes_" . $custom_kk_m;
						// In order to make the mapping work again, replace var by product
			                      	$new_key_m = str_replace("var","product",$new_key_m);
						if(!key_exists($new_key_m, $product_data) AND (!empty($custom_value_m))){
							if(is_array($custom_value_m)){
								// determine what to do with this later	
							} else {
								$product_data[$new_key_m] = $custom_value_m;
							}
						}
					}
                        	}
			} 
			// END VARIABLE PRODUCT CODE

			/**
			 * In order to prevent XML formatting errors in Google's Merchant center
			 * we will add CDATA brackets to the title and description attributes
			 */
                        $product_data['title'] = $this->woosea_append_cdata ( $product_data['title'] );
                        $product_data['description'] = $this->woosea_append_cdata ( $product_data['description'] );
                        $product_data['short_description'] = $this->woosea_append_cdata ( $product_data['short_description'] );

			/**
			 * Check if individual products need to be excluded
			 */
			$product_data = $this->woosea_exclude_individual( $product_data );	

			/**
			 * Field manipulation
			 */
			if (array_key_exists('field_manipulation', $project_config)){
				if(is_array($product_data)){
					$product_data = $this->woocommerce_field_manipulation( $project_config['field_manipulation'], $product_data ); 
				}
			}			

			/**
                        * Get product reviews for Google Product Review Feeds
			*/
			$product_data['reviews'] = $this->woosea_get_reviews( $product_data, $product );

			/**
			 * Filter execution
			 */
//			if (array_key_exists('rules', $project_config)){
//				if(is_array($product_data)){
//					$product_data = $this->woocommerce_sea_filters( $project_config['rules'], $product_data ); 
//				}
//			}

			/**
			 * Check if we need to add category taxonomy mappings (Google Shopping)
			 */
			if ((array_key_exists('mappings', $project_config)) AND ($project_config['taxonomy'] == 'google_shopping')){
				if(isset($product_data['id'])){
					$product_data = $this->woocommerce_sea_mappings( $project_config['mappings'], $product_data ); 
				}
			} elseif ((!array_key_exists('mappings', $project_config)) AND ($project_config['taxonomy'] == 'google_shopping')){
				if(isset($product_data['id'])){
					$product_data['categories'] = "";	
				}
			}

			/**
			 * Rules execution
			 */
			if (array_key_exists('rules2', $project_config)){
				if(is_array($product_data)){
					$product_data = $this->woocommerce_sea_rules( $project_config['rules2'], $product_data ); 
				}
			}

			/**
			 * Filter execution
			 */
			if (array_key_exists('rules', $project_config)){
				if(is_array($product_data)){
					$product_data = $this->woocommerce_sea_filters( $project_config['rules'], $product_data ); 
				}
			}

			/**
			 * When a product is a variable product we need to delete the original product from the feed, only the originals are allowed
			 */
			// For these channels parent products are allowed
			$allowed_channel_parents = array(
				"skroutz",
				"google_dsa",
			);	

			if (!in_array($project_config['fields'], $allowed_channel_parents)){
				if(($product->is_type('variable')) AND ($product_data['item_group_id'] == 0)){
					$product_data = array();
                      			$product_data = null;	
				}
			}	

			/**
			 * Remove variation products that are not THE default variation product
			 */
			if((isset($variation_pass)) AND ($variation_pass == "false")){
				$product_data = array();
                        	$product_data = null;	
			}

			/**
			 * And item_group_id is not allowed for simple products, prevent users from adding this to the feedd
			 */
			if($product->is_type('simple')){
				unset($product_data['item_group_id']);
			}

			/**
			 * Truncate length of product title when it is over 150 characters (requirement for Google Shopping, Pinterest and Facebook
			 */
			if(isset($product_data['title'])){
				$length_title = strlen($product_data['title']);
				if($length_title > 149){
					$product_data['title'] = mb_substr($product_data['title'],0,150);
				}	
			}

			/**
			 * When product has passed the filter rules it can continue with the rest
			 */
			if(!empty($product_data)){
				/**
				 * Determine what fields are allowed to make it to the csv and txt productfeed
				 */
			        if (($project_config['fields'] != "standard") AND (!isset($tmp_attributes))){
					$old_attributes_config = $project_config['attributes'];
                      			$tmp_attributes = array();
					foreach ($project_config['attributes'] as $key => $value){
						if(strlen($value['mapfrom']) > 0){
							$tmp_attributes[$value['mapfrom']] = "true";
						}
					}
	                      		$project_config['attributes'] = $tmp_attributes;
				}

				if(isset($old_attributes_config)){
					$identifier_positions = array();
					$loop_count = 0;

					foreach($old_attributes_config as $attr_key => $attr_value){
				
						if(!$attr_line){
							if(array_key_exists('static_value', $attr_value)){
								if(strlen($attr_value['mapfrom'])){
									$attr_line = "'".$attr_value['prefix']. "".$attr_value['mapfrom']."" .$attr_value['suffix']."'";
								} else {
									$attr_line = "''";
								}
							} else {
								if((strlen($attr_value['mapfrom'])) AND (array_key_exists($attr_value['mapfrom'], $product_data))){
									if(($attr_value['attribute'] == "g:link") OR ($attr_value['attribute'] == "g:link_template") OR ($attr_value['attribute'] == "g:image_link") OR ($attr_value['attribute'] == "link") OR ($attr_value['attribute'] == "Final URL") OR ($attr_value['attribute'] == "SKU")){
										$attr_line = "'".$attr_value['prefix']."".$product_data[$attr_value['mapfrom']]."".$attr_value['suffix']."'";
									} else {
										$attr_line = "'".$attr_value['prefix']. "".$product_data[$attr_value['mapfrom']]."" .$attr_value['suffix']."'";
									}
								} else {
									$attr_line = "''";
								}
							}
						} else {
							if(array_key_exists('static_value', $attr_value)){
								$attr_line .= ",'".$attr_value['prefix']. "".$attr_value['mapfrom']."" .$attr_value['suffix']."'";
							} else {
								// Determine position of identifiers in CSV row
								if($attr_value['attribute'] == "g:brand" || $attr_value['attribute'] == "g:gtin" || $attr_value['attribute'] == "g:mpn" || $attr_value['attribute'] == "g:identifier_exists"){
									$arr_pos = array($attr_value['attribute'] => $loop_count);
									$identifier_positions = array_merge($identifier_positions, $arr_pos);	
								}

 								if (array_key_exists($attr_value['mapfrom'], $product_data)){
								
									if(is_array($product_data[$attr_value['mapfrom']])){

										if($attr_value['mapfrom'] == "product_tag"){
											$product_tag_str = "";

                                                                               		foreach ($product_data['product_tag'] as $key => $value){
                                                       	                        		$product_tag_str .= ",";
                                                                                        	$product_tag_str .= "$value";
                                                                       	     		}
                                                                        		$product_tag_str = rtrim($product_tag_str, ",");
                                                                     	      		$product_tag_str = ltrim($product_tag_str, ",");

											$attr_line .= ",'".$product_tag_str."'";
										} elseif ($attr_value['mapfrom'] == "reviews"){
											$review_str = "";
   											foreach ($product_data[$attr_value['mapfrom']] as $key => $value){
                                                       	                        		$review_str .= "||";
                                                               	                     		foreach($value as $k => $v){
                                                                                        		$review_str .= ":$v";
												}
                                                                       	     		}
                                                                  	      	  	$review_str = ltrim($review_str, "||");
                                                                        		$review_str = rtrim($review_str, ":");
                                                                     	      		$review_str = ltrim($review_str, ":");
                                                                             		$review_str = str_replace("||:", "||", $review_str);
											$review_str .= "||";
											$attr_line .= ",'".$review_str."'";
										} else {
                                        	                               		$shipping_str = "";
                                                                               		foreach ($product_data[$attr_value['mapfrom']] as $key => $value){
                                                       	                        		$shipping_str .= "||";
												if(is_array($value)){
                                                               	                     			foreach($value as $k => $v){
														if(preg_match('/[0-9]/', $v)){
															$shipping_str .= ":$attr_value[prefix]".$v."$attr_value[suffix]";
														//	$shipping_str .= ":$attr_value[prefix]".$v."$attr_value[suffix]";
                                                                                  	         		} else {
                                                                                        				$shipping_str .= ":$v";
                                                                     	                 			}
													}
												}
                                                                       	     		}
                                                                  	      	  	$shipping_str = ltrim($shipping_str, "||");
                                                                        		$shipping_str = rtrim($shipping_str, ":");
                                                                     	      		$shipping_str = ltrim($shipping_str, ":");
                                                                             		$shipping_str = str_replace("||:", "||", $shipping_str);

											$attr_line .= ",'".$shipping_str."'";
                                                            			}	
								 	 } else {
										if(strlen($product_data[$attr_value['mapfrom']])){
											if(($attr_value['attribute'] == "g:link") OR ($attr_value['attribute'] == "g:link_template") OR ($attr_value['attribute'] == "g:image_link") OR ($attr_value['attribute'] == "link") OR ($attr_value['attribute'] == "Final URL") OR ($attr_value['attribute'] == "SKU")){
												$attr_line .= ",'".$attr_value['prefix']."".$product_data[$attr_value['mapfrom']]."".$attr_value['suffix']."'";
											} else {
												$attr_line .= ",'".$attr_value['prefix']. " ".$product_data[$attr_value['mapfrom']]." " .$attr_value['suffix']."'";
											}
										} else {
											$attr_line .= ",''";
										}
									}
								} else {
									$attr_line .= ",''";
								}
							}
						}
						$loop_count++;
					}
					$pieces_row = explode ("','", $attr_line);
					$pieces_row = array_map('trim', $pieces_row);

					if($project_config['fields'] == "google_shopping"){
						foreach($identifier_positions as $id_key => $id_value){
							if($id_key != "g:identifier_exists"){
								if ($pieces_row[$id_value]){
									$identifier_exists = "yes";
								}
							} else {
								$identifier_position = $id_value;
							}
						}
	
						if((isset($identifier_exists)) AND ($identifier_exists == "yes")){
							$pieces_row[$id_value] = $identifier_exists;
						} else {
							if(isset($id_value)){
								$pieces_row[$id_value] = "no";
							}
						}
					}
					$attr_line = implode("','", $pieces_row);
					$products[] = array ( $attr_line );
				} else {
					foreach( array_keys($project_config['attributes']) as $attribute_key ){
                                        	if (array_key_exists($attribute_key, $product_data)){
                                                        if(!$attr_line){
                                                                $attr_line = "'".$product_data[$attribute_key]."'";
                                                        } else {
                                                                $attr_line .= ",'".$product_data[$attribute_key]."'";
                                                        }
						}
					}
					$attr_line = trim($attr_line, "'");
					$products[] = array ( $attr_line );
				}

				/**
				 * Build an array needed for the adding Childs in the XML productfeed
				 */
				foreach( array_keys($project_config['attributes']) as $attribute_key ){
			
					if(!is_numeric($attribute_key)){
						if(!isset($old_attributes_config)){
							if(!$xml_product){
								$xml_product = array (
									$attribute_key => $product_data[$attribute_key]
								);
							} else {
								if(isset($product_data[$attribute_key])){
									$xml_product = array_merge($xml_product, array($attribute_key => $product_data[$attribute_key]));
								}
							}
						} else {
							foreach($old_attributes_config as $attr_key => $attr_value){

								$ca = 0;
								$ga = 0;
								// Static attribute value was set by user
								if(array_key_exists('static_value', $attr_value)){
									if(!isset($xml_product)){
										$xml_product = array (
											$attr_value['attribute'] => "$attr_value[prefix] ". $attr_value['mapfrom'] ." $attr_value[suffix]"
										);
									} else {
										$xml_product[$attr_value['attribute']] = "$attr_value[prefix] ". $attr_value['mapfrom'] ." $attr_value[suffix]";	
									}
								} elseif ($attr_value['mapfrom'] == $attribute_key){
									if(!isset($xml_product)){
										$xml_product = array (
											$attr_value['attribute'] => "$attr_value[prefix] ". $product_data[$attr_value['mapfrom']] ." $attr_value[suffix]"
										);
									} else {
										if(key_exists($attr_value['mapfrom'],$product_data)){

											if(is_array($product_data[$attr_value['mapfrom']])){
											
												if($attr_value['mapfrom'] == "product_tag"){
													$product_tag_str = "";

                                                        		                       		foreach ($product_data['product_tag'] as $key => $value){
                                                       	        		                		$product_tag_str .= ",";
                                                                        		                	$product_tag_str .= "$value";
                                                                       	     				}
                                                                  	      	  			$product_tag_str = ltrim($product_tag_str, ",");
                                                                        				$product_tag_str = rtrim($product_tag_str, ",");

													$xml_product[$attr_value['attribute']] = "$product_tag_str";
												} elseif($attr_value['mapfrom'] == "product_tag_space"){
													$product_tag_str_space = "";

                                                        		                       		foreach ($product_data['product_tag'] as $key => $value){
                                                       	        		                		$product_tag_str_space .= ", ";
                                                                        		                	$product_tag_str_space .= "$value";
                                                                       	     				}
                                                                  	      	  			$product_tag_str_space = ltrim($product_tag_str_space, " ,");
                                                                        				$product_tag_str_space = rtrim($product_tag_str_space, ", ");
													$xml_product[$attr_value['attribute']] = "$product_tag_str_space";
												} elseif($attr_value['mapfrom'] == "reviews"){
                                                                                                	$review_str = "";
													
                                                                                                        foreach ($product_data[$attr_value['mapfrom']] as $key => $value){
                                                                                                                $review_str .= "||";

                                                                                                                foreach($value as $k => $v){
                                                                                                                        if($k == "review_product_id"){
                                                                                                                                $review_str .= ":::REVIEW_PRODUCT_ID##$v";
                                                                                                                        } elseif ($k == "reviewer_image"){
                                                                                                                                $review_str .= ":::REVIEWER_IMAGE##$v";
                                                                                                                        } elseif ($k == "review_ratings"){
                                                                                                                                $review_str .= ":::REVIEW_RATINGS##$v";
                                                                                                                        } elseif ($k == "review_id"){
                                                                                                                                $review_str .= ":::REVIEW_ID##$v";
															} elseif ($k == "reviewer_name"){
                                                                                                                                $review_str .= ":::REVIEWER_NAME##$v";
 															} elseif ($k == "reviewer_id"){
                                                                                                                                $review_str .= ":::REVIEWER_ID##$v";
 															} elseif ($k == "review_timestamp"){
																$v = str_replace(" ", "T", $v);
																$v .= "Z";
                                                                                                                                $review_str .= ":::REVIEW_TIMESTAMP##$v";
 															} elseif ($k == "review_url"){
                                                                                                                                $review_str .= ":::REVIEW_URL##$v";
															} elseif ($k == "title"){
                                                                                                                                $review_str .= ":::TITLE##$v";
 															} elseif ($k == "content"){
                                                                                                                                $review_str .= ":::CONTENT##$v";
 															} elseif ($k == "pros"){
                                                                                                                                $review_str .= ":::PROS##$v";
															} elseif ($k == "cons"){
                                                                                                                                $review_str .= ":::CONS##$v";
                                                                                                                        } else {
                                                                                                                                // UNKNOWN, DO NOT ADD
                                                                                                                        }
                                                                                                                }
                                                                                                        }
                                                                                                        $review_str = ltrim($review_str, "||");
                                                                                                        $review_str = rtrim($review_str, ":");
                                                                                                        $review_str = ltrim($review_str, ":");
                                                                                                        $review_str = str_replace("||:", "||", $review_str);

													$review_str .= "||";

                                                                                                        $xml_product[$attr_value['attribute']] = "$review_str";	
 												} else {
													$shipping_str = "";
                        										foreach ($product_data[$attr_value['mapfrom']] as $key => $value){
														$shipping_str .= "||";
													
														foreach($value as $k => $v){

															if($k == "country"){
																$shipping_str .= ":WOOSEA_COUNTRY##$v";
															} elseif ($k == "region"){
																$shipping_str .= ":WOOSEA_REGION##$v";	
															} elseif ($k == "service"){
																$shipping_str .= ":WOOSEA_SERVICE##$v";
															} elseif ($k == "postal_code"){
																$shipping_str .= ":WOOSEA_POSTAL_CODE##$v";
															} elseif ($k == "price"){
																$shipping_str .= ":WOOSEA_PRICE##$attr_value[prefix] $v $attr_value[suffix]";
															} else {
																// UNKNOWN, DO NOT ADD
															}
														}
                        										}
                        										$shipping_str = ltrim($shipping_str, "||");
                        										$shipping_str = rtrim($shipping_str, ":");
                        										$shipping_str = ltrim($shipping_str, ":");
													$shipping_str = str_replace("||:", "||", $shipping_str);

													$xml_product[$attr_value['attribute']] = "$shipping_str";
												}
											} else {
												if(array_key_exists($attr_value['attribute'], $xml_product)){
													$ca = explode("_", $attr_value['mapfrom']);
													$ga++;
													// Google Shopping Actions, allow multiple product highlights in feed
													if($attr_value['attribute'] == "g:product_highlight"){
														$xml_product[$attr_value['attribute']."_$ga"] = "$attr_value[prefix] ". $product_data[$attr_value['mapfrom']] ." $attr_value[suffix]";	
													} elseif($attr_value['attribute'] == "g:product_detail"){
														$xml_product[$attr_value['attribute']."_$ga"] = "$attr_value[prefix] ". $attr_value['mapfrom']."#".$product_data[$attr_value['mapfrom']] ." $attr_value[suffix]";	
													} else {
														$xml_product[$attr_value['attribute']."_$ca[1]"] = "$attr_value[prefix] ". $product_data[$attr_value['mapfrom']] ." $attr_value[suffix]";	
													}
												} else {
													if(strlen($product_data[$attr_value['mapfrom']])){
														
														if(($attr_value['attribute'] == "g:link") OR ($attr_value['attribute'] == "link") OR ($attr_value['attribute'] == "g:link_template")){
															$xml_product[$attr_value['attribute']] = "$attr_value[prefix]". $product_data[$attr_value['mapfrom']] ."$attr_value[suffix]";	
														} elseif(($attr_value['attribute'] == "g:image_link") OR ($attr_value['attribute'] == "image_link")){
															$xml_product[$attr_value['attribute']] = "$attr_value[prefix]".$product_data[$attr_value['mapfrom']]."$attr_value[suffix]";	
														} elseif(($attr_value['attribute'] == "g:id") OR ($attr_value['attribute'] == "id") OR ($attr_value['attribute'] == "g:item_group_id")){
															$xml_product[$attr_value['attribute']] = "$attr_value[prefix]". $product_data[$attr_value['mapfrom']] ."$attr_value[suffix]";	
														} elseif($attr_value['attribute'] == "g:product_detail"){
															$xml_product[$attr_value['attribute']] = "$attr_value[prefix] ". $attr_value['mapfrom']."#".$product_data[$attr_value['mapfrom']] ." $attr_value[suffix]";	
														} else {
															$xml_product[$attr_value['attribute']] = "$attr_value[prefix] ". $product_data[$attr_value['mapfrom']] ." $attr_value[suffix]";	
														}
													}
												}
											}
										}
									}
								}
							}
						}
					}
				}

				// Do we need to do some calculation on attributes for Google Shopping
				$xml_product = $this->woosea_calculate_value ( $project_config, $xml_product ); 

				foreach($xml_product as $key_product => $value_product){
					if (preg_match("/custom_attributes_attribute_/", $key_product)){
						$pieces = explode("custom_attributes_attribute_",$key_product);
						unset($xml_product[$key_product]);
						$xml_product[$pieces[1]] = $value_product;
					} elseif (preg_match("/product_attributes_/", $key_product)){
						$pieces = explode("product_attributes_",$key_product);
						unset($xml_product[$key_product]);
						$xml_product[$pieces[1]] = $value_product;
					}
				}

				if(!$xml_piece){
					$xml_piece = array ($xml_product);
					unset($xml_product);
				} else {
					array_push ($xml_piece, $xml_product);
					unset($xml_product);
				}
				unset($product_data);	
			}
		endwhile;
		wp_reset_query();

		/**
		 * Update processing status of project
		 */
		//$project_updated = $this->woosea_project_update($project_config['project_hash'], $offset_step_size, $xml_piece);

		/**
		 * Write row to CSV/TXT or XML file
		 */
		if($project_config['fileformat'] != 'xml'){
			unset($products[0]);
			$file = $this->woosea_create_csvtxt_feed ( array_filter($products), $project_config, 'false' );
		} else {
			if(is_array($xml_piece)){
				$file = $this->woosea_create_xml_feed ( array_filter($xml_piece), $project_config, 'false' );
				unset($xml_piece);
			}
			unset($products);
		}

		/**
		 * Update processing status of project
		 */
		$project_updated = $this->woosea_project_update($project_config['project_hash'], $offset_step_size);

		/**
	  	 * Ready creating file, clean up our feed configuration mess now
		 */
		 delete_option('attributes_dropdown');
		 delete_option('channel_attributes');
	}

	/**
 	 * Update processing statistics of batched projects 
 	 */
	public function woosea_project_update($project_hash, $offset_step_size){
        	$feed_config = get_option( 'cron_projects' );
		$nr_projects = count ($feed_config);

		// Information for debug log
		$count_variation = wp_count_posts('product_variation');
		$count_single = wp_count_posts('product');
		$published_single = $count_single->publish;
		$published_variation = $count_variation->publish;
		$published_products = $published_single+$published_variation;

		$product_numbers = array (
      			"Single products" => $published_single,
        		"Variation products" => $published_variation,
        		"Total products" => $published_products,
			"Number projects" => count($feed_config)
		);

                $versions = array (
                        "PHP" => (float)phpversion(),
                        "Wordpress" => get_bloginfo('version'),
                        "WooCommerce" => WC()->version,
                        "Plugin" => WOOCOMMERCESEA_PLUGIN_VERSION
                );

                // Get the sales from created product feeds
		global $wpdb;
                $charset_collate = $wpdb->get_charset_collate();
                $table_name = $wpdb->prefix . 'adtribes_my_conversions';
                $order_rows = $wpdb->get_results("SELECT * FROM $table_name", ARRAY_A);

        //     	$notifications_obj = new WooSEA_Get_Admin_Notifications;
        //      $external_debug_file = $notifications_obj->woosea_debug_informations ($versions, $product_numbers, $order_rows, $feed_config);
		// End information for debug log

		foreach ( $feed_config as $key => $val ) {
                
			if(isset($val['product_variations'])){
		                $count_variation = wp_count_posts('product_variation');
                		$count_single = wp_count_posts('product');
				$published_single = $count_single->publish;
                		$published_variation = $count_variation->publish;
                		$published_products = $published_single+$published_variation; 
			} else {
                		$count_single = wp_count_posts('product');
				$published_products = $count_single->publish;
			}

			if ($val['project_hash'] == $project_hash){
				$nrpr = $feed_config[$key]['nr_products_processed'];
				$nr_prods_processed = $nrpr+$offset_step_size;

//				if(is_array($xml_piece)){
					// End of processing batched feed
					if($nrpr >= $published_products){

						// Set counters back to 0
						$feed_config[$key]['nr_products_processed'] = 0;
						$feed_config[$key]['nr_products'] = $published_products;

						// Set processing status on ready
						$feed_config[$key]['running'] = "ready";
						$project_data['last_updated'] = date("d M Y H:i");
                				$upload_dir = wp_upload_dir();
                				$base = $upload_dir['basedir'];
                				$path = $base . "/woo-product-feed-pro/" . $feed_config[$key]['fileformat'];
                				$tmp_file = $path . "/" . sanitize_file_name($feed_config[$key]['filename']) . "_tmp." . $feed_config[$key]['fileformat'];
                				$new_file = $path . "/" . sanitize_file_name($feed_config[$key]['filename']) . "." . $feed_config[$key]['fileformat'];

						if (!copy($tmp_file, $new_file)) {
							error_log("Copy of file failed");
						} else {
							unlink($tmp_file);
						}
						// END
						
						$batch_project = "batch_project_".$feed_config[$key]['project_hash'];
						delete_option( $batch_project );
        					delete_option('woosea_allow_update');

						// In 2 minutes from now check the amount of products in the feed and update the history count
						wp_schedule_single_event( time() + 120, 'woosea_update_project_stats', array($val['project_hash']) );
					} else {
						$feed_config[$key]['nr_products_processed'] = $nr_prods_processed;
						$feed_config[$key]['running'] = "processing";
						$feed_config[$key]['nr_products'] = $published_products;

						// Set new scheduled event for next batch in 2 seconds
						if($offset_step_size < $published_products){
        						if (! wp_next_scheduled ( 'woosea_create_batch_event', array($feed_config[$key]['project_hash']) ) ) {
								wp_schedule_single_event( time() + 2, 'woosea_create_batch_event', array($feed_config[$key]['project_hash']) );
								$batch_project = "batch_project_".$feed_config[$key]['project_hash'];
								update_option( $batch_project, $val);
							}
						} else {
							// No batch is needed, already done processing all products
							// Set counters back to 0
							$feed_config[$key]['nr_products_processed'] = 0;
							$feed_config[$key]['nr_products'] = $published_products;
             		 				
							$upload_dir = wp_upload_dir();
                					$base = $upload_dir['basedir'];
                					$path = $base . "/woo-product-feed-pro/" . $feed_config[$key]['fileformat'];
                					$tmp_file = $path . "/" . sanitize_file_name($feed_config[$key]['filename']) . "_tmp." . $feed_config[$key]['fileformat'];
                					$new_file = $path . "/" . sanitize_file_name($feed_config[$key]['filename']) . "." . $feed_config[$key]['fileformat'];

							if (!copy($tmp_file, $new_file)) {
								error_log("Copy of file failed - small file");
							} else {
								unlink($tmp_file);
							}
							// END
	
							// Set processing status on ready
							$feed_config[$key]['running'] = "ready";
							$project_data['last_updated'] = date("d M Y H:i");

							$batch_project = "batch_project_".$feed_config[$key]['project_hash'];
							delete_option( $batch_project );
        						delete_option('woosea_allow_update');
							
							// In 2 minutes from now check the amount of products in the feed and update the history count
							wp_schedule_single_event( time() + 120, 'woosea_update_project_stats', array($val['project_hash']) );
						}
					}
                	}
        	}
		$nr_projects_cron = count ( get_option ( 'cron_projects' ) );

		/**
		 * Only update the cron_project when no new project was created during the batched run otherwise the new project will be overwritten and deleted
		 */
		if ($nr_projects == $nr_projects_cron){
        		update_option( 'cron_projects', $feed_config);
		}
	}

	/**
	 * Calculate the value of an attribute
	 */
	public function woosea_calculate_value ( $project_config, $xml_product ) {
		// trim whitespaces from attribute values
		$xml_product = array_map('trim', $xml_product);

		// Check for new products in the Google Shopping feed if we need to 'calculate' the identifier_exists attribute value
	    	if(($project_config['taxonomy'] == "google_shopping") AND (isset($xml_product['g:condition'])) AND (!isset($xml_product['g:identifier_exists']))){
			$identifier_exists = "no"; // default value is no

			if (array_key_exists("g:brand", $xml_product) AND ($xml_product['g:brand'] != "")){
				// g:gtin exists and has a value
				if ((array_key_exists("g:gtin", $xml_product)) AND ($xml_product['g:gtin'] != "")){
					$identifier_exists = "yes";
				// g:mpn exists and has a value
				} elseif ((array_key_exists("g:mpn", $xml_product)) AND ($xml_product['g:mpn'] != "")){
					$identifier_exists = "yes";
				// g:brand is empty and so are g:gtin and g:mpn, so no identifier exists
				} else {
					$identifier_exists = "no";
				}
			} else {
				// g:gtin exists and has a value but brand is empty
				if ((array_key_exists("g:gtin", $xml_product)) AND ($xml_product['g:gtin'] != "")){
					$identifier_exists = "no";
				// g:mpn exists and has a value but brand is empty
				} elseif ((array_key_exists("g:mpn", $xml_product)) AND ($xml_product['g:mpn'] != "")){
					$identifier_exists = "no";
				// g:brand is empty and so are g:gtin and g:mpn, so no identifier exists
				} else {
					$identifier_exists = "no";
				}
			}
			// New policy of Google, only when the value is yes add it to the feed
			// 28 October 2019
			// if($identifier_exists == "yes"){
				$xml_product['g:identifier_exists'] = $identifier_exists;
			//}
		}
		return $xml_product;
	}

	/**
	 * Check if the channel requires unique key/field names and change when needed
	 */
	private function get_alternative_key ($channel_attributes, $original_key) {
		$alternative_key = $original_key;

		if(!empty($channel_attributes)){
			foreach ($channel_attributes as $k => $v){
				foreach ($v as $key => $value){
					if(array_key_exists("woo_suggest", $value)){				
						if ($original_key == $value['woo_suggest']){
							$alternative_key = $value['feed_name'];
						}
					}
				} 
			}
		}
		return $alternative_key;
	}	

	/**
	 * Make product quantity readable
	 */
    	public function clean_quantity( $id, $name ) {
        	$quantity = $this->get_attribute_value( $id, $name );
        	if ($quantity) {
            		return $quantity + 0;
        	}
        	return "0";
    	}

        /**
         * Return product price
         *
         * @author Carlos Rodríguez <carlos.rodriguez@yourinspiration.it>
         * @since 1.0.3
         */
       	public function get_product_price($product, $price) {
     		$product_price = $this->woosea_get_price_including_tax($product, 1, $price);
            	return $product_price;
        }


    	/**
     	* @param WC_Product $product
     	* @param int        $qty
     	* @param string     $price
     	*
     	* @return float|string
     	*/
    	public function woosea_get_price_excluding_tax( $product, $qty = 1, $price = '' ) {
        	if ( version_compare( WC()->version, '2.7.0', '>=' ) ) {
            		$price = wc_get_price_excluding_tax( $product, array( 'qty' => $qty, 'price' => $price ) );
        	} else {
            		$price = $product->get_price_excluding_tax( $qty, $price );
        	}
        	return $price;
    	}

    	/**
     	* @param WC_Product $product
     	* @param int        $qty
     	* @param string     $price
     	*
     	* @return float|string
     	*/
    	public function woosea_get_price_including_tax( $product, $qty = 1, $price = '' ) {
        	if ( version_compare( WC()->version, '2.7.0', '>=' ) ) {
            		$price = wc_get_price_including_tax( $product, array( 'qty' => $qty, 'price' => $price ) );
        	} else {
            		$price = $product->get_price_including_tax( $qty, $price );
        	}
        	return $price;
    	}

	/**
	 * Make start and end sale date readable
	 */
    	public function get_sale_date($id, $name) {
        	$date = $this->get_attribute_value($id, $name);
        	if ($date) {
            		return date("Y-m-d", $date);
        	}
        	return false;
    	}

	/**
	 * Get product stock
	 */
    	public function get_stock( $id ){
        	$status=$this->get_attribute_value($id,"_stock_status");
        	if ($status) {
            		if ($status == 'instock') {
                		return "in stock";
            		} elseif ($status == 'outofstock') {
                		return "out of stock";
            		}
        	}
        	return "out of stock";
    	}

	/**
	 * Create proper format image URL's
	 */
	public function get_image_url($image_url = ""){
        	if (!empty($image_url)) {
            		if (substr(trim($image_url), 0, 4) === "http" || substr(trim($image_url), 0,5) === "https" || substr(trim($image_url), 0, 3) === "ftp" || substr(trim($image_url), 0, 4) === "sftp") {
                		return rtrim($image_url, "/");
            		} else {
                		$base = get_site_url();
                		$image_url = $base . $image_url;
                		return rtrim($image_url, "/");
            		}
		}
        	return $image_url;
	}

	/**
     	 * Get attribute value
     	 */
    	public function get_attribute_value( $id, $name ){
        	if (strpos($name, 'attribute_pa') !== false) {
        		$taxonomy = str_replace("attribute_","",$name);
            		$meta = get_post_meta($id,$name, true);
            		$term = get_term_by('slug', $meta, $taxonomy);
            		return $term->name;
        	} else {
            		return get_post_meta($id, $name, true);
        	}
    	}
	/**
	 * Execute category taxonomy mappings
	 */
        private function woocommerce_sea_mappings( $project_mappings, $product_data ){
		$original_cat = $product_data['categories'];
		$original_cat = preg_replace('/&amp;/','&',$original_cat);
		$original_cat = preg_replace('/&gt;/','>',$original_cat);
              	$original_cat = ltrim($original_cat,"||");

		$tmp_cat = "";
		$match = "false";

		foreach ($project_mappings as $pm_key => $pm_array){
			// Strip slashes
			$pm_array['criteria'] = str_replace("\\","",$pm_array['criteria']);
			$pm_array['criteria'] = str_replace("/","",$pm_array['criteria']);
			$pm_array['criteria'] = trim($pm_array['criteria']);
			$original_cat = str_replace("\\","",$original_cat);
			$original_cat = str_replace("/","",$original_cat);
			$original_cat = trim($original_cat);

			// First check if there is a category mapping for this specific product
			if ((preg_match('/'.$pm_array['criteria'].'/', $original_cat))){
				if(!empty($pm_array['map_to_category'])){
					$category_pieces = explode("-", $pm_array['map_to_category']);
					$tmp_cat = $category_pieces[0];
					$match = "true";
				}
			} elseif($pm_array['criteria'] == $original_cat){
				$category_pieces = explode("-", $pm_array['map_to_category']);
				$tmp_cat = $category_pieces[0];
				$match = "true";
			} else {
				// Do nothing
			}
		}

		if($match == "true"){
			if(array_key_exists('id', $product_data)){
				$product_data['categories'] = $tmp_cat;
			}
		} else {
			// No mapping found so make google_product_category empty
			$product_data['categories'] = "";
		}

		return $product_data;
	}

	/**
	 * Execute field manipulations
	 */
	private function woocommerce_field_manipulation( $field_manipulation, $product_data ){
		$aantal_prods = count($product_data);
		$product_type_data = $product_data['product_type'];

		if($aantal_prods > 0){
			foreach ($field_manipulation as $manipulation_key => $manipulation_array){
				foreach ($manipulation_array as $ma_k => $ma_v){

					if($ma_k == "attribute"){
						$alter_field = $ma_v;
					} elseif ($ma_k == "rowCount"){
						$rowCount = $ma_v;
					} elseif ($ma_k == "product_type"){
						$product_type = $ma_v;
					} else {
						$becomes = $ma_v;
						$value = "";
			
						if($product_type == "variable"){
							$product_type = "variation";
						}

						// Field manipulation only for the product_types that were determined
						if(($product_type == $product_type_data) OR ($product_type == "all")){
							foreach ($becomes as $bk => $bv){
								foreach ($bv as $bkk => $bvv){
									if($bkk == "attribute"){
										if(isset($product_data[$bvv])){
											// product tags and categories are arrays
											if(is_array($product_data[$bvv])){
												foreach($product_data[$bvv] as $ka => $va){
													$value .= $va." ";
												}
											} else {
												// These are numeric values, user probably want to add those together
												if($bvv == "price" || $bvv == "shipping_price" || $bvv == "sale_price" || $bvv == "regular_price"){
													$old_format_price = $product_data[$bvv];
													$product_data[$bvv] = wc_format_decimal($product_data[$bvv]);
													settype($product_data[$bvv], "double");
													settype($value, "double");
													$value = ($value+$product_data[$bvv]);
													$product_data[$bvv] = $old_format_price;
                                                       					                $value = wc_format_decimal($value,2);
                                                                        				$value = wc_format_localized_price($value);
												} else {
													$value .= $product_data[$bvv]." ";
												}
											}
										}
									}
								}
							}
							$product_data[$alter_field] = $value;
						}
					}
				}
			}	
		}		
		return $product_data;
	}

	/**
	 * Execute project rules 
	 */
        private function woocommerce_sea_rules( $project_rules2, $product_data ){
		$aantal_prods = count($product_data);
		if($aantal_prods > 0){

			foreach ($project_rules2 as $pr_key => $pr_array){

				foreach ($product_data as $pd_key => $pd_value){

					// Check is there is a rule on specific attributes
					if($pd_key == $pr_array['attribute']){

						// This is because for data manipulation the than attribute is empty
						if(!array_key_exists('than_attribute', $pr_array)){
							$pr_array['than_attribute'] = $pd_key;
						}

                                                // Check if a rule has been set for Google categories
                                                if (!empty($product_data['categories']) AND ($pr_array['than_attribute'] == "google_category") AND ($product_data[$pr_array['attribute']] == $pr_array['criteria'])){
                                                    
							$pr_array['than_attribute'] = "categories";
                                                        $category_id = explode("-", $pr_array['newvalue']);
                                                        $pr_array['newvalue'] = $category_id[0];
							$product_data['categories'] = $pr_array['newvalue'];
						}

						// Make sure that rules on numerics are on true numerics
						if (!is_array($pd_value) AND (!preg_match('/[A-Za-z]/', $pd_value))){
							$pd_value = strtr($pd_value, ',', '.');
						}


						// Make sure the price or sale price is numeric
						if(($pr_array['attribute'] == "sale_price") OR ($pr_array['attribute'] == "price")){
							settype($pd_value, "double");
						}

						if (((is_numeric($pd_value)) AND ($pr_array['than_attribute'] != "shipping"))){

							// Rules for numeric values
							switch ($pr_array['condition']) {
								case($pr_array['condition'] = "contains"):
									if ((preg_match('/'.$pr_array['criteria'].'/', $pd_value))){
										$product_data[$pr_array['than_attribute']] = str_replace($pr_array['criteria'], $pr_array['newvalue'], $pd_value);
									}
									break;
								case($pr_array['condition'] = "containsnot"):
									if ((!preg_match('/'.$pr_array['criteria'].'/', $pd_value))){
										$product_data[$pr_array['than_attribute']] = $pr_array['newvalue'];
									}
									break;
								case($pr_array['condition'] = "="):
									if (($pd_value == $pr_array['criteria'])){
										$product_data[$pr_array['than_attribute']] = $pr_array['newvalue'];
									}
									break;
								case($pr_array['condition'] = "!="):
									if (($pd_value != $pr_array['criteria'])){
										$product_data[$pr_array['than_attribute']] = $pr_array['newvalue'];
									}
									break;
								case($pr_array['condition'] = ">"):
									if (($pd_value > $pr_array['criteria'])){
										$product_data[$pr_array['than_attribute']] = $pr_array['newvalue'];
									}
    									break;
								case($pr_array['condition'] = ">="):
									if (($pd_value >= $pr_array['criteria'])){
										$product_data[$pr_array['than_attribute']] = $pr_array['newvalue'];
									}
									break;
								case($pr_array['condition'] = "<"):
									if (($pd_value < $pr_array['criteria'])){
										$product_data[$pr_array['than_attribute']] = $pr_array['newvalue'];
									}
									break;
								case($pr_array['condition'] = "=<"):
									if (($pd_value <= $pr_array['criteria'])){
										$product_data[$pr_array['than_attribute']] = $pr_array['newvalue'];
									}
									break;
								case($pr_array['condition'] = "empty"):
									if(empty($product_data[$pr_array['attribute']])){
										if ((strlen($pd_value) < 1)){
											$product_data[$pr_array['than_attribute']] = $pr_array['newvalue'];
										} else {
											$product_data[$pr_array['attribute']] = $product_data[$pr_array['than_attribute']];
										}
									}
									break;
								case($pr_array['condition'] = "multiply"):
									$pr_array['criteria'] = strtr($pr_array['criteria'], ',', '.');
									$convert_back = "false";
									$pos = strpos($pd_value, ',');
									if($pos !== false){
										$convert_back = "true";	
									}
									$pd_value = strtr($pd_value, ',', '.');
									$newvalue = $pd_value*$pr_array['criteria'];
									$newvalue = round($newvalue, 2);
									if($convert_back == "true"){
										$newvalue = strtr($newvalue, '.',',');		
									}	
									$product_data[$pr_array['attribute']] = $newvalue;
									break;
								case($pr_array['condition'] = "divide"):
									$newvalue = ($pd_value / $pr_array['criteria']);
									$product_data[$pr_array['attribute']] = $newvalue;
									break;
								case($pr_array['condition'] = "plus"):
									$newvalue = ($pd_value + $pr_array['criteria']);
									$product_data[$pr_array['attribute']] = $newvalue;
									break;
								case($pr_array['condition'] = "minus"):
									$newvalue = ($pd_value - $pr_array['criteria']);
									$product_data[$pr_array['attribute']] = $newvalue;
									break;
								case($pr_array['condition'] = "findreplace"):
									if (strpos($pd_value, $pr_array['criteria']) !== false){
                                                                                // Make sure that a new value has been set
										if(!empty($pr_array['newvalue'])){              
									          	// Find and replace only work on same attribute field, otherwise create a contains rule 
                                                                                        if($pr_array['attribute'] == $pr_array['than_attribute']){
                                                                                                $newvalue = str_replace($pr_array['criteria'],$pr_array['newvalue'], $pd_value);
                                                                                                $product_data[$pr_array['than_attribute']] = ucfirst($newvalue);
                                                                                        }
                                                                                
										}
									}
									break;
								default:
									break;
							}
						} elseif (is_array($pd_value)) {
			
							// For now only shipping details are in an array
							foreach ($pd_value as $k => $v){
								if(is_array($v)){
									foreach ($v as $kk => $vv){
										// Only shipping detail rule can be on price for now
										if($kk == "price"){
											switch ($pr_array['condition']) {
												case($pr_array['condition'] = "contains"):
													if ((preg_match('/'.$pr_array['criteria'].'/', $vv))){
														$pd_value[$k]['price'] = str_replace($pr_array['criteria'], $pr_array['newvalue'], $vv);
														$product_data[$pr_array['than_attribute']] = $pd_value;
													}
													break;
												case($pr_array['condition'] = "containsnot"):
													if ((!preg_match('/'.$pr_array['criteria'].'/', $vv))){
														$pd_value[$k]['price'] = $pr_array['newvalue'];
														$product_data[$pr_array['than_attribute']] = $pd_value;
													}
													break;
												case($pr_array['condition'] = "="):
													if (($vv == $pr_array['criteria'])){
														$pd_value[$k]['price'] = $pr_array['newvalue'];
														$product_data[$pr_array['than_attribute']] = $pd_value;
													}
													break;
												case($pr_array['condition'] = "!="):
													if (($vv != $pr_array['criteria'])){
														$pd_value[$k]['price'] = $pr_array['newvalue'];
														$product_data[$pr_array['than_attribute']] = $pd_value;
													}
													break;
												case($pr_array['condition'] = ">"):
													if (($vv > $pr_array['criteria'])){
														$pd_value[$k]['price'] = $pr_array['newvalue'];
														$product_data[$pr_array['than_attribute']] = $pd_value;
													}
    													break;
												case($pr_array['condition'] = ">="):
													if (($vv >= $pr_array['criteria'])){
														$pd_value[$k]['price'] = $pr_array['newvalue'];
														$product_data[$pr_array['than_attribute']] = $pd_value;
													}
													break;
												case($pr_array['condition'] = "<"):
													if (($vv < $pr_array['criteria'])){
														$pd_value[$k]['price'] = $pr_array['newvalue'];
														$product_data[$pr_array['than_attribute']] = $pd_value;
													}
													break;
												case($pr_array['condition'] = "=<"):
													if (($vv <= $pr_array['criteria'])){
														$pd_value[$k]['price'] = $pr_array['newvalue'];
														$product_data[$pr_array['than_attribute']] = $pd_value;
													}
													break;
												case($pr_array['condition'] = "empty"):
													if ((strlen($vv) < 1)){
														$pd_value[$k]['price'] = $pr_array['newvalue'];
														$product_data[$pr_array['than_attribute']] = $pd_value;
													}
													break;
												case($pr_array['condition'] = "multiply"):
													// Only shipping array
													if(is_array($pd_value)){
														$pr_array['criteria'] = strtr($pr_array['criteria'], ',', '.');
														foreach ($pd_value as $ship_a_key => $shipping_arr){
															foreach($shipping_arr as $ship_key => $ship_value){
                                                                        							if($ship_key == "price"){
																	$ship_pieces = explode(" ", $ship_value);
																	$pd_value = strtr($ship_pieces[1], ',', '.');
                                                           		             							$newvalue = $pd_value*$pr_array['criteria'];
                                                                        								$newvalue = round($newvalue, 2);
                                                                        								$newvalue = strtr($newvalue, '.',',');
																	$newvalue = $ship_pieces[0]." ".$newvalue;
                                                                        								$product_data[$pr_array['than_attribute']][$ship_a_key]['price'] = $newvalue;	
																}
															}
														}
													}
													break;
												default:
													break;
											}
										}
									}
								} else {
									// Rules on product tags
									foreach ($pd_value as $k => $v){
									
										// Rules for string values
										if (!array_key_exists('cs', $pr_array)){
											$v = strtolower($v);
											$pr_array['criteria'] = strtolower($pr_array['criteria']);
										}			

										switch ($pr_array['condition']) {
											case($pr_array['condition'] = "contains"):
												if ((preg_match('/'.$pr_array['criteria'].'/', $v))){
													$product_data[$pr_array['than_attribute']] = $pr_array['newvalue'];
												}
												break;
											case($pr_array['condition'] = "containsnot"):
												if ((!preg_match('/'.$pr_array['criteria'].'/', $v))){
													$product_data[$pr_array['than_attribute']] = $pr_array['newvalue'];
												}
												break;
											case($pr_array['condition'] = "="):
												if (($v == $pr_array['criteria'])){
													$product_data[$pr_array['than_attribute']] = $pr_array['newvalue'];
												}
												break;
											case($pr_array['condition'] = "!="):
												if (($v != $pr_array['criteria'])){
													$product_data[$pr_array['than_attribute']] = $pr_array['newvalue'];
												}
												break;
											case($pr_array['condition'] = ">"):
												if (($v > $pr_array['criteria'])){
													$product_data[$pr_array['than_attribute']] = $pr_array['newvalue'];
												}
    												break;
											case($pr_array['condition'] = ">="):
												if (($v >= $pr_array['criteria'])){
													$product_data[$pr_array['than_attribute']] = $pr_array['newvalue'];
												}
												break;
											case($pr_array['condition'] = "<"):
												if (($v < $pr_array['criteria'])){
													$product_data[$pr_array['than_attribute']] = $pr_array['newvalue'];
												}
												break;
											case($pr_array['condition'] = "=<"):
												if (($v <= $pr_array['criteria'])){
													$product_data[$pr_array['than_attribute']] = $pr_array['newvalue'];
												}
												break;
											case($pr_array['condition'] = "empty"):
												if ((strlen($v) < 1)){
													$product_data[$pr_array['than_attribute']] = $pr_array['newvalue'];
												}
												break;
											case($pr_array['condition'] = "multiply"):
												// Only shipping array
												if(is_array($v)){
													$pr_array['criteria'] = strtr($pr_array['criteria'], ',', '.');
													foreach ($v as $ship_a_key => $shipping_arr){
														foreach($shipping_arr as $ship_key => $ship_value){
                                                                        						if($ship_key == "price"){
																$ship_pieces = explode(" ", $ship_value);
																$pd_value = strtr($ship_pieces[1], ',', '.');
                                                           		             						$newvalue = $pd_value*$pr_array['criteria'];
                                                                        							$newvalue = round($newvalue, 2);
                                                                   	     							$newvalue = strtr($newvalue, '.',',');
																$newvalue = $ship_pieces[0]." ".$newvalue;
                                                                        							$product_data[$pr_array['than_attribute']][$ship_a_key]['price'] = $newvalue;	
															}
														}
													}
												}
												break;
											default:
												break;
										}
									}
								}
							}
						} else {
							// Rules for string values
							if (!array_key_exists('cs', $pr_array)){
								$pd_value = strtolower($pd_value);
								$pr_array['criteria'] = strtolower($pr_array['criteria']);
							}			

							switch ($pr_array['condition']) {
								case($pr_array['condition'] = "contains"):
									if ((preg_match('/'.$pr_array['criteria'].'/', $pd_value))){
										// Specifically for shipping price rules
										if(!empty($product_data[$pr_array['than_attribute']])){
											if(is_array($product_data[$pr_array['than_attribute']])){
												$arr_size = (count($product_data[$pr_array['than_attribute']])-1);
												for ($x = 0; $x <= $arr_size; $x++) {
													$product_data[$pr_array['than_attribute']][$x]['price'] = $pr_array['newvalue'];	
												}	
											} else {
												$product_data[$pr_array['than_attribute']] = $pr_array['newvalue'];
											}
										} else {
											// This attribute value is empty for this product
											$product_data[$pr_array['than_attribute']] = $pr_array['newvalue'];
										}
									}
									break;
								case($pr_array['condition'] = "containsnot"):
									if ((!preg_match('/'.$pr_array['criteria'].'/', $pd_value))){
										// Specifically for shipping price rules
										if(is_array($product_data[$pr_array['than_attribute']])){
											$arr_size = (count($product_data[$pr_array['than_attribute']])-1);
											for ($x = 0; $x <= $arr_size; $x++) {
												$product_data[$pr_array['than_attribute']][$x]['price'] = $pr_array['newvalue'];	
											}	
										} else {
											$product_data[$pr_array['than_attribute']] = $pr_array['newvalue'];
										}
									}
									break;
								case($pr_array['condition'] = "="):
									if (($pr_array['criteria'] == "$pd_value")){
										// Specifically for shipping price rules
										if(is_array($product_data[$pr_array['than_attribute']])){
											$arr_size = (count($product_data[$pr_array['than_attribute']])-1);
											for ($x = 0; $x <= $arr_size; $x++) {
												$product_data[$pr_array['than_attribute']][$x]['price'] = $pr_array['newvalue'];	
											}	
										} else {
											$product_data[$pr_array['than_attribute']] = $pr_array['newvalue'];
										}
									}
									$ship = $product_data['shipping'];
									break;
								case($pr_array['condition'] = "!="):
									if (($pr_array['criteria'] != "$pd_value")){
										// Specifically for shipping price rules
										if(is_array($product_data[$pr_array['than_attribute']])){
											$arr_size = (count($product_data[$pr_array['than_attribute']])-1);
											for ($x = 0; $x <= $arr_size; $x++) {
												$product_data[$pr_array['than_attribute']][$x]['price'] = $pr_array['newvalue'];	
											}	
										} else {
											$product_data[$pr_array['than_attribute']] = $pr_array['newvalue'];
										}
									}
									break;
								case($pr_array['condition'] = ">"):
									// Use a lexical order on relational string operators
									if (($pd_value > $pr_array['criteria'])){
										// Specifically for shipping price rules
										if(is_array($product_data[$pr_array['than_attribute']])){
											$arr_size = (count($product_data[$pr_array['than_attribute']])-1);
											for ($x = 0; $x <= $arr_size; $x++) {
												$product_data[$pr_array['than_attribute']][$x]['price'] = $pr_array['newvalue'];	
											}	
										} else {
											$product_data[$pr_array['than_attribute']] = $pr_array['newvalue'];
										}
									}
									break;
								case($pr_array['condition'] = ">="):
									// Use a lexical order on relational string operators
									if (($pd_value >= $pr_array['criteria'])){
										// Specifically for shipping price rules
										if(is_array($product_data[$pr_array['than_attribute']])){
											$arr_size = (count($product_data[$pr_array['than_attribute']])-1);
											for ($x = 0; $x <= $arr_size; $x++) {
												$product_data[$pr_array['than_attribute']][$x]['price'] = $pr_array['newvalue'];	
											}	
										} else {
											$product_data[$pr_array['than_attribute']] = $pr_array['newvalue'];
										}
									}
									break;
								case($pr_array['condition'] = "<"):
									// Use a lexical order on relational string operators
									if (($pd_value < $pr_array['criteria'])){
										// Specifically for shipping price rules
										if(isset($product_data[$pr_array['than_attribute']]) AND (is_array($product_data[$pr_array['than_attribute']]))){
											$arr_size = (count($product_data[$pr_array['than_attribute']])-1);
											for ($x = 0; $x <= $arr_size; $x++) {
												$product_data[$pr_array['than_attribute']][$x]['price'] = $pr_array['newvalue'];	
											}	
										} else {
											$product_data[$pr_array['than_attribute']] = $pr_array['newvalue'];
										}
									}
									break;
								case($pr_array['condition'] = "=<"):
									// Use a lexical order on relational string operators
									if (($pd_value <= $pr_array['criteria'])){
										// Specifically for shipping price rules
										if(is_array($product_data[$pr_array['than_attribute']])){
											$arr_size = (count($product_data[$pr_array['than_attribute']])-1);
											for ($x = 0; $x <= $arr_size; $x++) {
												$product_data[$pr_array['than_attribute']][$x]['price'] = $pr_array['newvalue'];	
											}	
										} else {
											$product_data[$pr_array['than_attribute']] = $pr_array['newvalue'];
										}
									}
									break;

								case($pr_array['condition'] = "empty"):
									if(empty($product_data[$pr_array['attribute']])){
										if(empty($product_data[$pr_array['than_attribute']])){
											$product_data[$pr_array['than_attribute']] = $pr_array['newvalue'];
										} else {
											$product_data[$pr_array['attribute']] = $product_data[$pr_array['than_attribute']];
										}
									}
									break;
								case($pr_array['condition'] = "replace"):
									$product_data[$pr_array['than_attribute']] = str_replace($pr_array['criteria'], $pr_array['newvalue'], $product_data[$pr_array['than_attribute']]);
									break;
                                                                case($pr_array['condition'] = "findreplace"):
                                                                        if (strpos($pd_value, $pr_array['criteria']) !== false){
										// Make sure that a new value has been set
										if(!empty($pr_array['newvalue'])){
											// Find and replace only work on same attribute field, otherwise create a contains rule	
											if($pr_array['attribute'] == $pr_array['than_attribute']){
				                                             			$newvalue = str_replace($pr_array['criteria'],$pr_array['newvalue'], $pd_value);
                                                                                		$product_data[$pr_array['than_attribute']] = ucfirst($newvalue);
                                                                        		}
										}
									}
                                                                        break;
								default:
									break;
							}
						}
					} else {
						// When a rule has been set on an attribute that is not in product_data
						// Add the newvalue to product_data
						if (!array_key_exists($pr_array['attribute'], $product_data)){
							if(!empty($pr_array['newvalue'])){
								$product_data[$pr_array['than_attribute']] = $pr_array['newvalue'];
							} else {
								if(array_key_exists($pr_array['than_attribute'], $product_data)){
									$product_data[$pr_array['attribute']] = $product_data[$pr_array['than_attribute']];
								}
							}
						}
					}
				}
			}
		}
		return $product_data;
	}

	/**
	 * Function to exclude products based on individual product exclusions
	 */
	private function woosea_exclude_individual( $product_data ){
		$allowed = 1;

		// Check if product was already excluded from the feed
		$product_excluded = ucfirst( get_post_meta( $product_data['id'], '_woosea_exclude_product', true ) );

		if( $product_excluded == "Yes"){
			$allowed = 0;
		}

		if ($allowed < 1){
			$product_data = array();
			$product_data = null;
		} else {
			return $product_data;
		}
	}

	/**
	 * Execute project filters (include / exclude) 
	 */
        private function woocommerce_sea_filters( $project_rules, $product_data ){
		$allowed = 1;

		// Check if product was already excluded from the feed
		$product_excluded = ucfirst( get_post_meta( $product_data['id'], '_woosea_exclude_product', true ) );

		if( $product_excluded == "Yes"){
			$allowed = 0;
		}

		foreach ($project_rules as $pr_key => $pr_array){

			if($pr_array['attribute'] == "categories"){
				$pr_array['attribute'] = "raw_categories";
			}

			if(array_key_exists($pr_array['attribute'], $product_data)){

				foreach ($product_data as $pd_key => $pd_value){
					// Check is there is a rule on specific attributes

					if(in_array($pd_key, $pr_array, TRUE)){

						if($pd_key == "price"){
							//$pd_value = @number_format($pd_value,2);
							$pd_value = wc_format_decimal($pd_value);
						}
				
						if (is_numeric($pd_value)){
							$old_value = $pd_value;
							if($pd_key == "price"){
								$pd_value = @number_format($pd_value,2);
							}
	
							// Rules for numeric values	
							switch ($pr_array['condition']) {
								case($pr_array['condition'] = "contains"):
									if ((preg_match('/'.$pr_array['criteria'].'/', $pd_value)) && ($pr_array['than'] == "exclude")){
										$allowed = 0;
									} elseif ((!preg_match('/'.$pr_array['criteria'].'/', $pd_value)) && ($pr_array['than'] == "include_only")){
										$allowed = 0;
									} 
									break;
								case($pr_array['condition'] = "containsnot"):
									if ((!preg_match('/'.$pr_array['criteria'].'/', $pd_value)) && ($pr_array['than'] == "exclude")){
										$allowed = 0;
									} elseif ((preg_match('/'.$pr_array['criteria'].'/', $pd_value)) && ($pr_array['than'] == "include_only")){
										$allowed = 0;
									}
									break;
								case($pr_array['condition'] = "="):
									if (($old_value == $pr_array['criteria']) && ($pr_array['than'] == "exclude")){
										$allowed = 0;
									} elseif (($old_value != $pr_array['criteria']) && ($pr_array['than'] == "include_only")){
										$allowed = 0;
									}
									break;
								case($pr_array['condition'] = "!="):
									if (($old_value == $pr_array['criteria']) && ($pr_array['than'] == "exclude")){
										if($allowed <> 0){
											$allowed = 1;
										}
									} elseif (($old_value == $pr_array['criteria']) && ($pr_array['than'] == "include_only")){
										$allowed = 0;
									}
									break;
								case($pr_array['condition'] = ">"):
									if (($old_value > $pr_array['criteria']) && ($pr_array['than'] == "exclude")){
										$allowed = 0;
									} elseif (($old_value <= $pr_array['criteria']) && ($pr_array['than'] == "include_only")){
										$allowed = 0;
									}
    									break;
								case($pr_array['condition'] = ">="):
									if (($old_value >= $pr_array['criteria']) && ($pr_array['than'] == "exclude")){
										$allowed = 0;
									} elseif (($old_value < $pr_array['criteria']) && ($pr_array['than'] == "include_only")){
										$allowed = 0;
									}
									break;
								case($pr_array['condition'] = "<"):
									if (($old_value < $pr_array['criteria']) && ($pr_array['than'] == "exclude")){
										$allowed = 0;
									} elseif (($old_value > $pr_array['criteria']) && ($pr_array['than'] == "include_only")){
										$allowed = 0;
									}
									break;
								case($pr_array['condition'] = "=<"):
									if (($old_value <= $pr_array['criteria']) && ($pr_array['than'] == "exclude")){
										$allowed = 0;
									} elseif (($old_value > $pr_array['criteria']) && ($pr_array['than'] == "include_only")){
										$allowed = 0;
									}
									break;
								case($pr_array['condition'] = "empty"):
									if ((strlen($pd_value) < 1) && ($pr_array['than'] == "exclude")){
										$allowed = 0;
									} elseif ((strlen($pd_value > 0)) && ($pr_array['than'] == "include_only")){
										$allowed = 0;
									}
									break;
								default:
									break;
							}
						} elseif (is_array($pd_value)){
							// Tis can either be a shipping or product_tag array
							if($pr_array['attribute'] == "product_tag"){
								$in_tag_array = "not";

								foreach($pd_value as $pt_key => $pt_value){
		                                                        // Rules for string values
                                                        		if (!array_key_exists('cs', $pr_array)){
                                                                		$pt_value = strtolower($pt_value);
                                                                		$pr_array['criteria'] = strtolower($pr_array['criteria']);
                                                        		}	

									if(preg_match('/'.$pr_array['criteria'].'/', $pt_value)){
										$in_tag_array = "yes";
									}
								}
	
								if($in_tag_array == "yes"){
								//if(in_array($pr_array['criteria'], $pd_value, TRUE)) {
									$v = $pr_array['criteria'];

									switch ($pr_array['condition']) {
										case($pr_array['condition'] = "contains"):
											if ((preg_match('/'.$pr_array['criteria'].'/', $v))){
												if($pr_array['than'] == "include_only"){
													if($allowed <> 0){
														$allowed = 1;
													}
												} else {
													$allowed = 0;
												}
											} else {
												$allowed = 0;
											}
											break;
										case($pr_array['condition'] = "containsnot"):
											if ((!preg_match('/'.$pr_array['criteria'].'/', $v))){
												if($pr_array['than'] == "include_only"){
													if($allowed <> 0){
														$allowed = 1;
													}
												} else {
													$allowed = 0;
												}
											} else {
												$allowed = 0;
											}
											break;
										case($pr_array['condition'] = "="):
											if (($v == $pr_array['criteria'])){
												if($pr_array['than'] == "include_only"){
													if($allowed <> 0){
														$allowed = 1;
													}
												} else {
													$allowed = 0;
												}
											} else {
												$allowed = 0;
											}
											break;
										case($pr_array['condition'] = "!="):
											if (($v != $pr_array['criteria'])){
												if($pr_array['than'] == "include_only"){
													if($allowed <> 0){
														$allowed = 1;
													}
												} else {	
													$allowed = 0;
												}
											}
											break;
										case($pr_array['condition'] = ">"):
											if (($v > $pr_array['criteria'])){
												if($pr_array['than'] == "include_only"){
													if($allowed <> 0){
														$allowed = 1;
													}
												} else {	
													$allowed = 0;
												}
											}
    											break;
										case($pr_array['condition'] = ">="):
											if (($v >= $pr_array['criteria'])){
												if($pr_array['than'] == "include_only"){
													if($allowed <> 0){
														$allowed = 1;
													}
												} else {	
													$allowed = 0;
												}
											}
											break;
										case($pr_array['condition'] = "<"):
											if (($v < $pr_array['criteria'])){
												if($pr_array['than'] == "include_only"){
													if($allowed <> 0){
														$allowed = 1;
													}
												} else {	
													$allowed = 0;
												}
											}
											break;
										case($pr_array['condition'] = "=<"):
											if (($v <= $pr_array['criteria'])){
												if($pr_array['than'] == "include_only"){
													if($allowed <> 0){
														$allowed = 1;
													}
												} else {	
													$allowed = 0;
												}
											}
											break;
										case($pr_array['condition'] = "empty"):
											if (strlen($v) < 1){
												if($pr_array['than'] == "include_only"){
													if($allowed <> 0){
														$allowed = 1;
													}
												} else {	
													$allowed = 0;
												}
											}
											break;
										default:
											break;
									}
								} else {
									switch ($pr_array['condition']) {
										case($pr_array['condition'] = "contains"):
											if($pr_array['than'] == "include_only"){
												$allowed = 0;
											} else {
												if($allowed <> 0){
													$allowed = 1;
												}
											}
											break;
										case($pr_array['condition'] = "containsnot"):
											if($pr_array['than'] == "include_only"){
												if($allowed <> 0){
													$allowed = 1;
												}
											} else {
												$allowed = 0;
											}
											break;
										case($pr_array['condition'] = "="):
											if($pr_array['than'] == "include_only"){
												$allowed = 0;
											} else {
												if($allowed <> 0){
													$allowed = 1;
												}
											}
											break;
										case($pr_array['condition'] = "!="):
											if($pr_array['than'] == "include_only"){
												if($allowed <> 0){
													$allowed = 1;
												}
											} else {	
												$allowed = 0;
											}
											break;
										case($pr_array['condition'] = ">"):
											if($pr_array['than'] == "include_only"){
												$allowed = 0;
											} else {	
												$allowed = 0;
											}
    											break;
										case($pr_array['condition'] = ">="):
											if($pr_array['than'] == "include_only"){
												$allowed = 0;
											} else {	
												$allowed = 0;
											}
											break;
										case($pr_array['condition'] = "<"):
											if($pr_array['than'] == "include_only"){
												$allowed = 0;
											} else {	
												$allowed = 0;
											}
											break;
										case($pr_array['condition'] = "=<"):
											if($pr_array['than'] == "include_only"){
												$allowed = 0;
											} else {	
												$allowed = 0;
											}
											break;
										case($pr_array['condition'] = "empty"):
											if($pr_array['than'] == "include_only"){
												if($allowed <> 0){	
													$allowed = 1;
												}
											} else {	
												$allowed = 0;
											}
											break;
										default:
											break;
									}
								}
							} else {
								// For now only shipping details are in an array
								foreach ($pd_value as $k => $v){
									foreach ($v as $kk => $vv){
										// Only shipping detail rule can be on price for now
										if($kk == "price"){
											switch ($pr_array['condition']) {
												case($pr_array['condition'] = "contains"):
													if ((preg_match('/'.$pr_array['criteria'].'/', $vv))){
														$allowed = 0;	
													}
													break;
												case($pr_array['condition'] = "containsnot"):
													if ((!preg_match('/'.$pr_array['criteria'].'/', $vv))){
														$allowed = 0;
													}
													break;
												case($pr_array['condition'] = "="):
													if (($vv == $pr_array['criteria'])){
														$allowed = 0;
													}
													break;
												case($pr_array['condition'] = "!="):
													if (($vv != $pr_array['criteria'])){
														$allowed = 0;
													}
													break;
												case($pr_array['condition'] = ">"):
													if (($vv > $pr_array['criteria'])){
														$allowed = 0;
													}
    													break;
												case($pr_array['condition'] = ">="):
													if (($vv >= $pr_array['criteria'])){
														$allowed = 0;
													}
													break;
												case($pr_array['condition'] = "<"):
													if (($vv < $pr_array['criteria'])){
														$allowed = 0;
													}
													break;
												case($pr_array['condition'] = "=<"):
													if (($vv <= $pr_array['criteria'])){
														$allowed = 0;
													}
													break;
												case($pr_array['condition'] = "empty"):
													if (strlen($vv) < 1){
														$allowed = 0;
													}
													break;
												default:
													break;
											}
										}
									}
								}
							}
						} else {
							// Filters for string values

							// If case-sensitve is off than lowercase both the criteria and attribute value
							if (array_key_exists('cs', $pr_array)){
								if ($pr_array['cs'] != "on"){
									$pd_value = strtolower($pd_value);
									$pr_array['criteria'] = strtolower($pr_array['criteria']);
								}
							}				
							$pos = strpos($pd_value, '&amp;');
							$pos_slash = strpos($pr_array['criteria'], '\\');
							if($pos !== false){
								$pd_value = str_replace("&amp;","&",$pd_value);
							}
							if($pos_slash !== false){
								$pr_array['criteria'] = str_replace("\\","",$pr_array['criteria']);
							}

							switch ($pr_array['condition']) {
								case($pr_array['condition'] = "contains"):
									if ((preg_match('/'.$pr_array['criteria'].'/', $pd_value)) && ($pr_array['than'] == "exclude")){
										$allowed = 0;
									} elseif ((!preg_match('/'.$pr_array['criteria'].'/', $pd_value)) && ($pr_array['than'] == "include_only")){
										$allowed = 0;
									} elseif ((preg_match('/'.$pr_array['criteria'].'/', $pd_value)) && ($pr_array['than'] == "include_only")){
										if($allowed <> 0){
											$allowed = 1;
										}
									}
									break;
								case($pr_array['condition'] = "containsnot"):
									if ((!preg_match('/'.$pr_array['criteria'].'/', $pd_value)) && ($pr_array['than'] == "exclude")){
										$allowed = 0;
									} elseif ((preg_match('/'.$pr_array['criteria'].'/', $pd_value)) && ($pr_array['than'] == "include_only")){
										$allowed = 0;
									}
									break;
								case($pr_array['condition'] = "="):
									if (($pr_array['criteria'] == "$pd_value") && ($pr_array['than'] == "exclude")){
										$allowed = 0;
									} elseif (($pr_array['criteria'] != "$pd_value") && ($pr_array['than'] == "include_only")){
										$found = strpos($pd_value,$pr_array['criteria']);
										if ($found !== false) {
											if($allowed <> 0){
												$allowed = 1;
											}
										} else {
											$allowed = 0;
										}
									} elseif (($pr_array['criteria'] == "$pd_value") && ($pr_array['than'] == "include_only")){
										if($allowed <> 0){			
											$allowed = 1;
										}
								      	} elseif ((preg_match('/'.$pr_array['criteria'].'/', $pd_value)) && ($pr_array['than'] == "exclude")){
                                                                                $allowed = 0;
								      	} elseif ((preg_match('/'.$pr_array['criteria'].'/', $pd_value)) && ($pr_array['than'] == "include_only")){
										$allowed = 1;
							                }
									break;
								case($pr_array['condition'] = "!="):
									if (($pr_array['criteria'] == "$pd_value") && ($pr_array['than'] == "exclude")){
										if($allowed <> 0){
											$allowed = 1;
										}
									} elseif (($pr_array['criteria'] == "$pd_value") && ($pr_array['than'] == "include_only")){
										$allowed = 0; 
									} elseif (($pr_array['criteria'] != "$pd_value") && ($pr_array['than'] == "exclude")){
										$allowed = 0;
									}
									break;
								case($pr_array['condition'] = ">"):
									// Use a lexical order on relational string operators
									if (($pd_value > $pr_array['criteria']) && ($pr_array['than'] == "exclude")){
										$allowed = 0;
									} elseif (($pd_value < $pr_array['criteria']) && ($pr_array['than'] == "include_only")){
										$allowed = 0;
									}
									break;
								case($pr_array['condition'] = ">="):
									// Use a lexical order on relational string operators
									if (($pd_value >= $pr_array['criteria']) && ($pr_array['than'] == "exclude")){
										$allowed = 0;
									} elseif (($pd_value < $pr_array['criteria']) && ($pr_array['than'] == "include_only")){
										$allowed = 0;
									}
									break;
								case($pr_array['condition'] = "<"):
									// Use a lexical order on relational string operators
									if (($pd_value < $pr_array['criteria']) && ($pr_array['than'] == "exclude")){
										$allowed = 0;
									} elseif (($pd_value > $pr_array['criteria']) && ($pr_array['than'] == "include_only")){
										$allowed = 0;
									}
									break;
								case($pr_array['condition'] = "=<"):
									// Use a lexical order on relational string operators
									if (($pd_value <= $pr_array['criteria']) && ($pr_array['than'] == "exclude")){
										$allowed = 0;
									} elseif (($pd_value > $pr_array['criteria']) && ($pr_array['than'] == "include_only")){
										$allowed = 0;
									}
									break;
								case($pr_array['condition'] = "empty"):
									if ((strlen($pd_value) < 1) && ($pr_array['than'] == "exclude")){
										$allowed = 0;
									} elseif ((strlen($pd_value) > 0) && ($pr_array['than'] == "exclude")){
										if($allowed <> 0){
											$allowed = 1;
										}
									} elseif ((strlen($pd_value) > 0) && ($pr_array['than'] == "include_only")){
										$allowed = 0;
									}
									break;
								default:
									break;
							}
						}
					}
				}
			} else {
				// A empty rule has been set on an attribute that is not in a product anyhow. Still, remove this product from the feed
				if($pr_array['condition'] == "empty"){
					if($pr_array['than'] == "exclude"){
						$allowed = 0;
					} else {
						$allowed = 1;
					}
				} elseif($pr_array['condition'] == "="){
					if($pr_array['than'] == "exclude"){
						$allowed = 1;
					} else {
						$allowed = 0;
					}
				} elseif($pr_array['condition'] == "contains"){
					if($pr_array['than'] == "exclude"){
						$allowed = 1;
					} else {
						$allowed = 0;
					}
				} else {
					if($pr_array['than'] == "exclude"){
						$allowed = 0;
					} else {
						$allowed = 1;
					}
				}
			}
		}

		if ($allowed < 1){
			$product_data = array();
			$product_data = null;
		} else {
			return $product_data;
		}
	}
}
