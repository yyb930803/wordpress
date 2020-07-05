<?php

defined('ABSPATH') or die(); // Exit if accessed directly

/**
 * @class 		Nasa_WC_Product_Data_Fields
 * @version		1.0
 * @author 		nasaTheme
 */
if (!class_exists('Nasa_WC_Product_Data_Fields')) {

    class Nasa_WC_Product_Data_Fields {

        protected static $_instance = null;
        public static $plugin_prefix = 'wc_productdata_options_';
        
        protected $_custom_fields = array();
        
        public static function getInstance() {
            if(!class_exists('WooCommerce')) {
                return null;
            }
            
            if (is_null(self::$_instance)) {
                self::$_instance = new self();
            }
            
            return self::$_instance;
        }

        /**
         * Gets things started by adding an action to initialize this plugin once
         * WooCommerce is known to be active and initialized
         */
        public function __construct() {
            $custom_fields = array();
            
            /* ============= Casa Vinicola ================================= */
            $custom_fields['key'][0] = array(
                'tab_name'    => esc_html__('   Casa Vitinicola', 'nasa-core'),
                'tab_id'      => 'casavinicola'
            );
            $custom_fields['value'][0][] = array(
                'id'          => 'imgcasavinicola',
                'type'        => 'nasa_media',
                'label'       => esc_html__('Immagine casa vitinicola', 'nasa-core'),
                'class'       => 'large',
                'style'       => 'width:100%;',
                'description' => esc_html__('Foto principale della casa vinicola', 'nasa-core')
            );
            $custom_fields['value'][0][] = array(
                'id'          => 'titolocasavinicola',
                'type'        => 'text',
                'label'       => esc_html__('Casa Vitinicola', 'nasa-core'),
                'class'       => 'large',
                'style'       => 'width: 100%;',
                'description' => esc_html__('Inserisci il nome della casa vinicola', 'nasa-core')
            );

            $custom_fields['value'][0][] = array(
                'id'          => 'descrizionecasavinicola',
                'type'        => 'editor',
                'label'       => esc_html__('Descrizione', 'nasa-core'),
                'class'       => 'large',
                'style'       => 'width: 100%;',
                'description' => esc_html__('Inserisci una descrizione per casa vitinicola', 'nasa-core')
            );

            /* ============= Specifications ================================= */
            $custom_fields['key'][1] = array(
                'tab_name'    => esc_html__('   Rewards', 'nasa-core'),
                'tab_id'      => 'formatobottiglia'
            );

	    $custom_fields['value'][1][] = array(
                'id'          => 'titoloreward',
                'type'        => 'text',
                'label'       => esc_html__('Reward', 'nasa-core'),
                'class'       => 'large',
                'style'       => 'width: 100%;',
                'description' => esc_html__('Inserisci reward', 'nasa-core')
            );
		
	    $custom_fields['value'][1][] = array(
                'id'          => 'siglatitoloreward',
                'type'        => 'text',
                'label'       => esc_html__('Sigla Reward', 'nasa-core'),
                'class'       => 'large',
                'style'       => 'width: 100%;',
                'description' => esc_html__('Inserisci sigla reward', 'nasa-core')
            );

	    $custom_fields['value'][1][] = array(
                'id'          => 'titoloreward2',
                'type'        => 'text',
                'label'       => esc_html__('Reward2', 'nasa-core'),
                'class'       => 'large',
                'style'       => 'width: 100%;',
                'description' => esc_html__('Inserisci reward', 'nasa-core')
            );
		
	    $custom_fields['value'][1][] = array(
                'id'          => 'siglatitoloreward2',
                'type'        => 'text',
                'label'       => esc_html__('Sigla Reward2', 'nasa-core'),
                'class'       => 'large',
                'style'       => 'width: 100%;',
                'description' => esc_html__('Inserisci sigla reward', 'nasa-core')
            );

	    $custom_fields['value'][1][] = array(
                'id'          => 'titoloreward3',
                'type'        => 'text',
                'label'       => esc_html__('Reward3', 'nasa-core'),
                'class'       => 'large',
                'style'       => 'width: 100%;',
                'description' => esc_html__('Inserisci reward', 'nasa-core')
            );
		
	    $custom_fields['value'][1][] = array(
                'id'          => 'siglatitoloreward3',
                'type'        => 'text',
                'label'       => esc_html__('Sigla Reward3', 'nasa-core'),
                'class'       => 'large',
                'style'       => 'width: 100%;',
                'description' => esc_html__('Inserisci sigla reward', 'nasa-core')
            );
	    
            $custom_fields['value'][1][] = array(
                'id'          => 'titoloreward4',
                'type'        => 'text',
                'label'       => esc_html__('Reward4', 'nasa-core'),
                'class'       => 'large',
                'style'       => 'width: 100%;',
                'description' => esc_html__('Inserisci reward', 'nasa-core')
            );
		
	    $custom_fields['value'][1][] = array(
                'id'          => 'siglatitoloreward4',
                'type'        => 'text',
                'label'       => esc_html__('Sigla Reward4', 'nasa-core'),
                'class'       => 'large',
                'style'       => 'width: 100%;',
                'description' => esc_html__('Inserisci sigla reward', 'nasa-core')
            );

            $custom_fields['value'][1][] = array(
                'id'          => 'titoloreward5',
                'type'        => 'text',
                'label'       => esc_html__('Reward5', 'nasa-core'),
                'class'       => 'large',
                'style'       => 'width: 100%;',
                'description' => esc_html__('Inserisci reward', 'nasa-core')
            );
		
	    $custom_fields['value'][1][] = array(
                'id'          => 'siglatitoloreward5',
                'type'        => 'text',
                'label'       => esc_html__('Sigla Reward5', 'nasa-core'),
                'class'       => 'large',
                'style'       => 'width: 100%;',
                'description' => esc_html__('Inserisci sigla reward', 'nasa-core')
            );
            
	    $custom_fields['value'][1][] = array(
                'id'          => 'titoloreward6',
                'type'        => 'text',
                'label'       => esc_html__('Reward6', 'nasa-core'),
                'class'       => 'large',
                'style'       => 'width: 100%;',
                'description' => esc_html__('Inserisci reward', 'nasa-core')
            );
		
	    $custom_fields['value'][1][] = array(
                'id'          => 'siglatitoloreward6',
                'type'        => 'text',
                'label'       => esc_html__('Sigla Reward6', 'nasa-core'),
                'class'       => 'large',
                'style'       => 'width: 100%;',
                'description' => esc_html__('Inserisci sigla reward', 'nasa-core')
            );

	     $custom_fields['value'][1][] = array(
                'id'          => 'titoloreward7',
                'type'        => 'text',
                'label'       => esc_html__('Reward7', 'nasa-core'),
                'class'       => 'large',
                'style'       => 'width: 100%;',
                'description' => esc_html__('Inserisci reward', 'nasa-core')
            );
		
	    $custom_fields['value'][1][] = array(
                'id'          => 'siglatitoloreward7',
                'type'        => 'text',
                'label'       => esc_html__('Sigla Reward7', 'nasa-core'),
                'class'       => 'large',
                'style'       => 'width: 100%;',
                'description' => esc_html__('Inserisci sigla reward', 'nasa-core')
            );

            $this->_custom_fields = $custom_fields;
            
            add_action('woocommerce_init', array(&$this, 'init'));
        }
        
        /**
         * Init WooCommerce Custom Product Data Fields extension once we know WooCommerce is active
         */
        public function init() {
            global $nasa_opt;
            
            add_action('woocommerce_product_write_panel_tabs', array($this, 'product_write_panel_tab'));
            add_action('woocommerce_product_data_panels', array($this, 'product_write_panel'));
            add_action('woocommerce_process_product_meta', array($this, 'product_save_data'), 10, 2);
            
            /**
             * For variable product
             */
            if(!isset($nasa_opt['gallery_images_variation']) || $nasa_opt['gallery_images_variation']) {
                add_action('woocommerce_save_product_variation', array($this, 'nasa_save_variation_gallery'), 10, 1);
                add_action('woocommerce_product_after_variable_attributes', array($this, 'nasa_variation_gallery_admin_html'), 10, 3);
            }
            
            add_action('woocommerce_product_options_related', array($this, 'nasa_accessories_product'));
        }
        
        /**
         * Save variation gallery
         * 
         * @param type $variation_id
         * return void
         */
        public function nasa_save_variation_gallery($variation_id) {
            if (isset($_POST['nasa_variation_gallery_images'])) {
                global $nasa_product_parent;
                
                /**
                 * Delete cache by post id
                 */
                if(!$nasa_product_parent) {
                    $parent_id = wp_get_post_parent_id($variation_id);
                    $nasa_product_parent = $parent_id ? wc_get_product($parent_id) : null;
                    $GLOBALS['nasa_product_parent'] = $nasa_product_parent;
                }
                
                if($nasa_product_parent) {
                    $productId = $nasa_product_parent->get_id();
                    nasa_del_cache_by_product_id($productId);
                }
                
                /**
                 * Save gallery for variation
                 */
                if (isset($_POST['nasa_variation_gallery_images'][$variation_id])) {
                    $galery = trim($_POST['nasa_variation_gallery_images'][$variation_id], ',');
                    update_post_meta($variation_id, 'nasa_variation_gallery_images', $galery);
                    
                    return;
                }
            }
            
            delete_post_meta($variation_id, 'nasa_variation_gallery_images');
        }
        
        /**
         * Variation gallery images
         * 
         * @param type $loop
         * @param type $variation_data
         * @param type $variation
         */
        public function nasa_variation_gallery_admin_html($loop, $variation_data, $variation) {
            $variation_id   = absint($variation->ID);
            $gallery_images = get_post_meta($variation_id, 'nasa_variation_gallery_images', true);
            $gallery_images = $gallery_images && is_string($gallery_images) ?
                explode(',', $gallery_images) : $gallery_images;
            ?>
            <div class="form-row form-row-full nasa-variation-gallery-wrapper">
                <h4><?php esc_html_e('Variation Image Gallery', 'nasa-core') ?></h4>
                <div class="nasa-variation-gallery-image-container">
                    <input type="hidden"
                        id="nasa_variation_gallery_images-<?php echo absint($variation->ID); ?>" 
                        name="nasa_variation_gallery_images[<?php echo $variation_id ?>]" 
                        value="<?php echo $gallery_images ? esc_attr(implode(',', $gallery_images)) : ''; ?>" />
                    <ul class="nasa-variation-gallery-images" id="nasa-variation_gallery-<?php echo absint($variation->ID); ?>" data-variation_id="<?php echo absint($variation->ID); ?>">
                        <?php
                            if (is_array($gallery_images) && !empty($gallery_images)) {
                                include NASA_CORE_PLUGIN_PATH . 'admin/views/variation-admin-template.php';
                            }
                        ?>
                    </ul>
                </div>
                <p class="nasa-add-variation-gallery-image-wrapper hide-if-no-js">
                    <a href="javascript:void(0);" 
                       data-product_variation_id="<?php echo absint($variation->ID); ?>" 
                       class="button nasa-add-variation-gallery-image" 
                       data-choose="<?php echo esc_attr__('Add images to variation gallery', 'nasa-core'); ?>" 
                       data-update="<?php echo esc_attr__('Add to gallery', 'nasa-core'); ?>" 
                       data-delete="<?php echo esc_attr__('Delete image', 'nasa-core'); ?>" 
                       data-text="<?php echo esc_attr__('Delete', 'nasa-core'); ?>">
                        <?php esc_html_e('Add Gallery Images', 'nasa-core'); ?>
                    </a>
                </p>
            </div>
            <?php
        }

        /**
         * Adds a new tab to the Product Data postbox in the admin product interface
         */
        public function product_write_panel_tab() {
            $fields = $this->_custom_fields;
            foreach ($fields['key'] as $field) {
                echo '<li class="wc_productdata_options_tab"><a href="#wc_tab_' . $field['tab_id'] . '">' . $field['tab_name'] . '</a></li>';
            }
        }

        /**
         * Adds the panel to the Product Data postbox in the product interface
         */
        public function product_write_panel() {
            global $post;
            // Pull the field data out of the database
            $available_fields = array();
            $available_fields[] = maybe_unserialize(get_post_meta($post->ID, 'wc_productdata_options', true));
            if ($available_fields) {
                $fields = $this->_custom_fields;
                // Display fields panel
                foreach ($available_fields as $available_field) {
                    foreach ($fields['value'] as $key => $values) {
                        echo '<div id="wc_tab_' . $fields['key'][$key]['tab_id'] . '" class="panel woocommerce_options_panel">';
                        foreach ($values as $v) {
                            $this->wc_product_data_options_fields($v);
                        }
                        echo '</div>';
                    }
                }
            }
        }

        /**
         * Create Fields
         */
        public function wc_product_data_options_fields($field) {
            global $thepostid, $post;
            
            $fieldtype = isset($field['type']) ? $field['type'] : 'text';
            $field_id = isset($field['id']) ? $field['id'] : '';
            $thepostid = empty($thepostid) ? $post->ID : $thepostid;
            $options_data = maybe_unserialize(get_post_meta($thepostid, 'wc_productdata_options', true));
            $inputval = isset($options_data[0][$field_id]) ? $options_data[0][$field_id] : '';
            
            $field['name'] = isset($field['name']) ? $field['name'] : $field['id'];
            $field['placeholder'] = isset($field['placeholder']) ? $field['placeholder'] : '';
            $field['class'] = isset($field['class']) ? $field['class'] : 'short';
            $field['wrapper_class'] = isset($field['wrapper_class']) ? $field['wrapper_class'] : '';

            switch ($fieldtype) {
                case 'number':
                    echo
                    '<p class="form-field ' . esc_attr($field['id']) . '_field ' . esc_attr($field['wrapper_class']) . '">' .
                        '<label for="' . esc_attr($field['id']) . '">' . 
                            wp_kses_post($field['label']) . 
                        '</label>' .
                        '<input ' .
                            'type="' . esc_attr($field['type']) . '" ' .
                            'class="' . esc_attr($field['class']) . '" ' .
                            'name="' . esc_attr($field['name']) . '" ' .
                            'id="' . esc_attr($field['id']) . '" ' .
                            'value="' . esc_attr($inputval) . '" ' .
                            'placeholder="' . esc_attr($field['placeholder']) . '"' . 
                            (isset($field['style']) ? ' style="' . $field['style'] . '"' : '') .
                        ' /> ';

                    if (!empty($field['description'])) {
                        echo (isset($field['desc_tip']) && false !== $field['desc_tip']) ?
                            '<img class="help_tip" data-tip="' . esc_attr($field['description']) . '" src="' . esc_url(WC()->plugin_url()) . '/assets/images/help.png" height="16" width="16" />' :
                            '<span class="description">' . wp_kses_post($field['description']) . '</span>';
                    }
                    
                    echo '</p>';
                    break;

                case 'textarea':
                    
                    echo '<p class="form-field ' . $field['id'] . '_field"><label for="' . $field['id'] . '">' . $field['label'] . '</label><textarea class="' . $field['class'] . '" name="' . $field['id'] . '" id="' . $field['id'] . '" placeholder="' . $field['placeholder'] . '" rows="2" cols="20"' . (isset($field['style']) ? ' style="' . $field['style'] . '"' : '') . '">' . esc_textarea($inputval) . '</textarea>';

                    if (!empty($field['description'])) {
                        echo (isset($field['desc_tip']) && false !== $field['desc_tip']) ?
                            '<img class="help_tip" data-tip="' . esc_attr($field['description']) . '" src="' . esc_url(WC()->plugin_url()) . '/assets/images/help.png" height="16" width="16" />' :
                            '<span class="description">' . wp_kses_post($field['description']) . '</span>';
                    }
                    echo '</p>';
                    break;

                case 'editor' :
                    $height     = isset($field['height']) && (int) $field['height'] ? (int) $field['height'] : 200;
                    wp_editor($inputval, $field['id'], array('editor_height' => $height));
                    break;

                case 'checkbox':
                    $field['cbvalue']       = isset($field['cbvalue']) ? $field['cbvalue'] : '"yes"';
                    echo '<p class="form-field ' . esc_attr($field['id']) . '_field ' . esc_attr($field['wrapper_class']) . '"><label for="' . esc_attr($field['id']) . '">' . wp_kses_post($field['label']) . '</label><input type="checkbox" class="' . esc_attr($field['class']) . '" name="' . esc_attr($field['name']) . '" id="' . esc_attr($field['id']) . '" value="' . esc_attr($field['cbvalue']) . '" ' . checked($inputval, $field['cbvalue'], false) . ' /> ';

                    if (!empty($field['description'])) {
                        echo (isset($field['desc_tip']) && false !== $field['desc_tip']) ?
                            '<img class="help_tip" data-tip="' . esc_attr($field['description']) . '" src="' . esc_url(WC()->plugin_url()) . '/assets/images/help.png" height="16" width="16" />' :
                            '<span class="description">' . wp_kses_post($field['description']) . '</span>';
                    }
                    echo '</p>';
                    break;

                case 'select':
                    $field['class'] = isset($field['class']) ? $field['class'] : 'select short';

                    echo '<p class="form-field ' . esc_attr($field['id']) . '_field ' . esc_attr($field['wrapper_class']) . '"><label for="' . esc_attr($field['id']) . '">' . wp_kses_post($field['label']) . '</label><select id="' . esc_attr($field['id']) . '" name="' . esc_attr($field['id']) . '" class="' . esc_attr($field['class']) . '">';

                    foreach ($field['options'] as $key => $value) {
                        echo '<option value="' . esc_attr($key) . '" ' . selected(esc_attr($inputval), esc_attr($key), false) . '>' . esc_html($value) . '</option>';
                    }

                    echo '</select> ';

                    if (!empty($field['description'])) {
                        echo (isset($field['desc_tip']) && false !== $field['desc_tip']) ?
                            '<img class="help_tip" data-tip="' . esc_attr($field['description']) . '" src="' . esc_url(WC()->plugin_url()) . '/assets/images/help.png" height="16" width="16" />' :
                            '<span class="description">' . wp_kses_post($field['description']) . '</span>';
                    }
                    echo '</p>';
                    break;

                case 'radio':
                    $field['class'] = isset($field['class']) ? $field['class'] : 'select short';

                    echo '<fieldset class="form-field ' . esc_attr($field['id']) . '_field ' . esc_attr($field['wrapper_class']) . '"><legend style="float:left; width:150px;">' . wp_kses_post($field['label']) . '</legend><ul class="wc-radios" style="width: 25%; float:left;">';
                    foreach ($field['options'] as $key => $value) {
                        echo '<li style="padding-bottom: 3px; margin-bottom: 0;"><label style="float:none; width: auto; margin-left: 0;"><input name="' . esc_attr($field['name']) . '" value="' . esc_attr($key) . '" type="radio" class="' . esc_attr($field['class']) . '" ' . checked(esc_attr($inputval), esc_attr($key), false) . ' /> ' . esc_html($value) . '</label></li>';
                    }
                    echo '</ul>';

                    if (!empty($field['description'])) {
                        echo (isset($field['desc_tip']) && false !== $field['desc_tip']) ?
                            '<img class="help_tip" data-tip="' . esc_attr($field['description']) . '" src="' . esc_url(WC()->plugin_url()) . '/assets/images/help.png" height="16" width="16" />' :
                            '<span class="description">' . wp_kses_post($field['description']) . '</span>';
                    }

                    echo '</fieldset>';
                    break;

                case 'hidden':
                    $field['class'] = isset($field['class']) ? $field['class'] : '';

                    echo '<input type="hidden" class="' . esc_attr($field['class']) . '" name="' . esc_attr($field['id']) . '" id="' . esc_attr($field['id']) . '" value="' . esc_attr($inputval) . '" /> ';

                    break;
                
                /**
                 * Image
                 */
                case 'nasa_media':
                    $no_img_src = wc_placeholder_img_src();
                    $image_src = $inputval ? wp_get_attachment_image_src($inputval, 'thumbnail') : false;
                    $image_src = isset($image_src[0]) ? $image_src[0] : $no_img_src;
                    
                    echo 
                    '<p class="form-field ' . esc_attr($field['id']) . '_field ' . esc_attr($field['wrapper_class']) . '">' .
                        '<label for="' . esc_attr($field['id']) . '">' . 
                            wp_kses_post($field['label']) . 
                        '</label>' .
                        '<a href="javascript:void(0);" class="nasa-custom-upload' . ($inputval ? ' nasa-remove' : '') . '" data-confirm_remove="' . esc_attr__('Are you sure to delete image ?', 'nasa-core') . '" data-no_img="' . esc_url($no_img_src) . '">' .
                            '<img src="' . esc_url($image_src) . '" height="100" />' .
                            '<input type="hidden" name="' . esc_attr($field['name']) . '" value="' . esc_attr($inputval) . '" />' .
                        '</a>';
                    
                    if (!empty($field['description'])) {
                        echo (isset($field['desc_tip']) && false !== $field['desc_tip']) ?
                            '<img class="help_tip" data-tip="' . esc_attr($field['description']) . '" src="' . esc_url(WC()->plugin_url()) . '/assets/images/help.png" height="16" width="16" />' :
                            '<span class="description nasa-block">' . wp_kses_post($field['description']) . '</span>';
                    }
                    
                    echo '</p>';
                    
                    break;

                case 'text':
                default :
                    echo '<p class="form-field ' . esc_attr($field['id']) . '_field ' . esc_attr($field['wrapper_class']) . '"><label for="' . esc_attr($field['id']) . '">' . wp_kses_post($field['label']) . '</label><input type="' . esc_attr($field['type']) . '" class="' . esc_attr($field['class']) . '" name="' . esc_attr($field['name']) . '" id="' . esc_attr($field['id']) . '" value="' . esc_attr($inputval) . '" placeholder="' . esc_attr($field['placeholder']) . '"' . (isset($field['style']) ? ' style="' . $field['style'] . '"' : '') . ' /> ';

                    if (!empty($field['description'])) {
                        echo (isset($field['desc_tip']) && false !== $field['desc_tip']) ?
                            '<img class="help_tip" data-tip="' . esc_attr($field['description']) . '" src="' . esc_url(WC()->plugin_url()) . '/assets/images/help.png" height="16" width="16" />' :
                            '<span class="description">' . wp_kses_post($field['description']) . '</span>';
                    }
                    echo '</p>';
                    break;
            }
        }

        /**
         * Saves the data inputed into the product boxes, as post meta data
         * identified by the name 'wc_productdata_options'
         *
         * @param int $post_id the post (product) identifier
         * @param stdClass $post the post (product)
         */
        public function product_save_data($post_id, $post) {
            /** field name in pairs array * */
            $data_args = array();
            $fields = $this->_custom_fields;

            foreach ($fields['value'] as $key => $datas) {
                foreach ($datas as $k => $data) {
                    if (isset($data['id'])) {
                        $data_args[$data['id']] = stripslashes($_POST[$data['id']]);
                    }
                }
            }

            $options_value = array($data_args);

            // save the data to the database
            update_post_meta($post_id, 'wc_productdata_options', $options_value);
            
            /**
             * Accessories for product
             */
            if (isset($_POST['accessories_ids'])) {
                update_post_meta($post_id, '_accessories_ids', $_POST['accessories_ids']);
            } else {
                update_post_meta($post_id, '_accessories_ids', null);
            }
            
            /**
             * Delete cache by post id
             */
            nasa_del_cache_by_product_id($post_id);
        }
        
        /**
         * HTML Accessories of Product
         */
        public function nasa_accessories_product() {
            global $post, $thepostid, $product_object;
            $product_ids = $this->get_accessories_ids($thepostid);
            include NASA_CORE_PLUGIN_PATH . 'admin/views/html-accessories-product.php';
        }
        
        protected function get_accessories_ids($post_id) {
            $ids = get_post_meta($post_id, '_accessories_ids', true);
            
            return $ids;
        }

    }
    
    /**
     * Instantiate Class
     */
    add_action('init', array('Nasa_WC_Product_Data_Fields', 'getInstance'), 0);
}
