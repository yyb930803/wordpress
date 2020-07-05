<?php
defined('ABSPATH') or die(); // Exit if accessed directly

/**
 * @class 		Nasa_Abstract_WC_Variation_UX
 * @version		1.0
 * @author 		NasaTheme
 */

abstract class Nasa_Abstract_WC_Attr_UX {
    
    const _NASA_COLOR = 'nasa_color';
    const _NASA_LABEL = 'nasa_label';
    const _NASA_IMAGE = 'nasa_image';
    const _NASA_SELECT = 'select';
    
    public static $no_image = '';
    
    protected $types = array();

    public function __construct() {
        self::$no_image = NASA_CORE_PLUGIN_URL . 'assets/images/no_image.jpg';
        
        $this->types = array(
            self::_NASA_COLOR => esc_html__('Color', 'nasa-core'),
            self::_NASA_LABEL => esc_html__('Label', 'nasa-core'),
            self::_NASA_IMAGE => esc_html__('Image', 'nasa-core')
        );
    }

    /**
     * Get Attribute 's properties
     *
     * @param string $taxonomy
     *
     * @return object
     */
    public static function get_tax_attribute($taxonomy) {
        global $wpdb;
        return $wpdb->get_row('SELECT * FROM ' . $wpdb->prefix . 'woocommerce_attribute_taxonomies WHERE attribute_name="' . str_replace('pa_', '', $taxonomy) . '"');
    }
    
    /**
     * Get Attribute 's properties selects
     * @return array
     */
    public static function get_tax_selects() {
        global $wpdb, $nasa_selects;
        
        if(!isset($nasa_selects)) {
            $nasa_selects = array();
            $results = $wpdb->get_results('SELECT `attribute_name` FROM ' . $wpdb->prefix . 'woocommerce_attribute_taxonomies WHERE attribute_type="' . self::_NASA_SELECT . '"');
            if($results) {
                foreach ($results as $value) {
                    $nasa_selects[] = $value->attribute_name;
                }
            }
            
            $GLOBALS['nasa_selects'] = $nasa_selects;
        }
        
        return $nasa_selects;
    }
    
    /**
     * Get Attribute 's properties colors
     * @return array
     */
    public static function get_tax_color() {
        global $wpdb, $nasa_colors;
        
        if(!isset($nasa_colors)) {
            $nasa_colors = array();
            $results = $wpdb->get_results('SELECT `attribute_name` FROM ' . $wpdb->prefix . 'woocommerce_attribute_taxonomies WHERE attribute_type="' . self::_NASA_COLOR . '"');
            if($results) {
                foreach ($results as $value) {
                    $nasa_colors[] = $value->attribute_name;
                }
            }
            
            $GLOBALS['nasa_colors'] = $nasa_colors;
        }
        
        return $nasa_colors;
    }
    
    /**
     * Get Attribute 's properties labels
     * @return array
     */
    public static function get_tax_labels() {
        global $wpdb, $nasa_labels;
        
        if(!isset($nasa_labels)) {
            $nasa_labels = array();
            $results = $wpdb->get_results('SELECT `attribute_name` FROM ' . $wpdb->prefix . 'woocommerce_attribute_taxonomies WHERE attribute_type="' . self::_NASA_LABEL . '"');
            if($results) {
                foreach ($results as $value) {
                    $nasa_labels[] = $value->attribute_name;
                }
            }
            
            $GLOBALS['nasa_labels'] = $nasa_labels;
        }
        
        return $nasa_labels;
    }
    
    /**
     * Get Attribute 's properties images
     * @return array
     */
    public static function get_tax_images() {
        global $wpdb, $nasa_images;
        
        if(!isset($nasa_images)) {
            $nasa_images = array();
            $results = $wpdb->get_results('SELECT `attribute_name` FROM ' . $wpdb->prefix . 'woocommerce_attribute_taxonomies WHERE attribute_type="' . self::_NASA_IMAGE . '"');
            if($results) {
                foreach ($results as $value) {
                    $nasa_images[] = $value->attribute_name;
                }
            }
            
            $GLOBALS['nasa_images'] = $nasa_images;
        }
        
        return $nasa_images;
    }
    
    /**
     * 
     * @param type $value
     * @param type $id
     * @return string image
     */
    public static function get_image_preview($value = false, $id = false, $width = 60, $height= 60, $name = '') {
        $image_src = $value ? wp_get_attachment_thumb_url($value) : false;
        if (!$image_src) {
            $image_src = self::$no_image;
        }
        
        return '<img' . ($id ? ' id="' . esc_attr($id) . '"' : '') . ' class="attr-image-preview" src="' . esc_url($image_src) . '" width="' . (int) $width . '" height="' . (int) $height . '" alt="' . esc_attr($name) . '" />';
    }
}
