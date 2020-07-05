<?php

if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) { // If uninstall not called from WordPress exit
	exit();
}

/**
 * Manages WooCommerce Gift Wrapper uninstallation
 * The goal is to remove ALL WooCommerce Gift Wrapper related data in db
 *
 * @since 2.2
 */
class WCGWP_Unwrap {

	/**
	 * Constructor: manages uninstall for multisite
	 *
	 * @since 0.5
	 */
	function __construct() {
		global $wpdb;

		// Check if it is a multisite uninstall - if so, run the uninstall function for each blog id
		if ( is_multisite() ) {
			foreach ( $wpdb->get_col( "SELECT blog_id FROM $wpdb->blogs" ) as $blog_id ) {
				switch_to_blog( $blog_id );
				$this->uninstall();
			}
			restore_current_blog();
		}
		else {
			$this->uninstall();
		}
	}

	/**
	 * Removes ALL plugin data
	 * only when the relevant option is active
	 *
	 * @since 0.5
	 */
	function uninstall() {
	
		if ( get_option( 'giftwrap_delete_all' ) !== 'yes' ) {
			return;
		}

		global $wpdb;
        // remove product meta data
		$wpdb->query( "DELETE FROM $wpdb->postmeta WHERE meta_key LIKE '%wcgwp_%'" );

        foreach ( array(
            'giftwrap_all_products',
            'giftwrap_bootstrap_off',
            'giftwrap_button',
            'giftwrap_category_id',
            'giftwrap_details',
            'giftwrap_display',
            'giftwrap_exclude_cats',
            'giftwrap_number',
            'giftwrap_header',
            'giftwrap_line_item',
            'giftwrap_line_item_modal',
            'giftwrap_link',
            'giftwrap_modal',
            'giftwrap_product_link',
            'giftwrap_product_show_thumb',
            'giftwrap_show_thumb',
            'giftwrap_simple_product',
            'giftwrap_text_label',
            'giftwrap_textarea_limit',
            'wcgwp_license_key', // EDD SL
            'wcgwp_license_status', // EDD SL
            'wcgwp_data',
            'wcgwp_instance',
            'wcgwp_deactivate_checkbox',
            'wcgwp_activated',
            'giftwrap_delete_all', // BYE BYE
        ) as $option) {
                delete_option( $option );
        }
	}
}		
new WCGWP_Unwrap();