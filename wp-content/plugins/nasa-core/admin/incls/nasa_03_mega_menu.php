<?php

if (!class_exists('Walker_Nav_Menu_Edit')) {
    require_once ABSPATH . 'wp-admin/includes/nav-menu.php';
}

function nasa_megamenu_admin_script() {
    wp_enqueue_script('nasa_media_uploader', NASA_CORE_PLUGIN_URL . 'admin/assets/nasa-mega-menu.js');
}

function nasa_walker_nav_menu_edit() {
    return 'Nasa_Walker_Nav_Menu_Edit';
}

class Nasa_Nav_Menu_Item_Custom_Fields {

    public static $options = array(
        'item_tpl' =>
        '<p class="additional-menu-field-{name} description description-{type_show}">
            <label for="edit-menu-item-{name}-{id}">
                {label}<br />
                <input
                    type="{input_type}"
                    id="edit-menu-item-{name}-{id}"
                    class="widefat code edit-menu-item-{name}"
                    name="menu-item-{name}[{id}]"
                    value="{value}" />
            </label>
        </p>',
        'checkbox' =>
        '<p class="additional-menu-field-{name} description description-{type_show}">
            <label for="edit-menu-item-{name}-{id}"><br />
                <input
                    type="checkbox"
                    id="edit-menu-item-{name}-{id}"
                    class="widefat code edit-menu-item-{name}"
                    name="menu-item-{name}[{id}]"
                    data-id="{id}"
                    value="1"{checked} />{label}
            </label>
        </p>'
    );

    public static function setup() {
        add_action('admin_enqueue_scripts', 'nasa_megamenu_admin_script');
        $new_fields = apply_filters('nasa_nav_menu_item_fields', array());
        if (empty($new_fields)) {
            return;
        }
        
        self::$options['fields'] = self::get_fields_schema($new_fields);
        add_filter('wp_edit_nav_menu_walker', 'nasa_walker_nav_menu_edit');
        add_action('save_post', array(__CLASS__, '_save_post'), 10, 2);
    }

    public static function get_fields_schema($new_fields) {
        $schema = array();
        foreach ($new_fields as $name => $field) {
            $field['name'] = empty($field['name']) ? $name : $field['name'];
            $schema[] = $field;
        }

        return $schema;
    }

    public static function get_menu_item_postmeta_key($name) {
        return '_menu_item_nasa_' . $name;
    }

    /**
     * Inject the 
     * @hook {action} save_post
     */
    public static function get_field($item, $depth, $args) {
        $new_fields = '';
        $hidden = true;
        foreach (self::$options['fields'] as $field) {
            $field['value'] = get_post_meta($item->ID, self::get_menu_item_postmeta_key($field['name']), true);
            $field['id'] = $item->ID;
            if ($field['name'] == 'image_mega_enable' && $field['value'] == 1) {
                $hidden = false;
            }

            switch ($field['input_type']) {
                case 'select-widget':
                    $new_fields .= self::getWidgets($field);
                    break;

                case 'select':
                    $new_fields .= self::getSelect($field);
                    break;

                case 'select_position':
                    $new_fields .= self::getSelectPosition($field, $hidden);
                    break;

                case 'image':
                    $new_fields .= self::getMedia($field, $hidden);
                    break;

                case 'checkbox':
                    $field['checked'] = ($field['value'] == 1) ? ' checked' : '';
                    $default = self::$options['checkbox'];
                    foreach ($field as $key => $value) {
                        $default = str_replace('{' . $key . '}', $value, $default);
                    }
                    $new_fields .= $default;

                    break;

                case 'icons':
                    $new_fields .= self::getIcons($field);
                    break;

                default:
                    $default = self::$options['item_tpl'];
                    foreach ($field as $key => $value) {
                        $default = str_replace('{' . $key . '}', $value, $default);
                    }
                    $new_fields .= $default;
                    break;
            }
        }

        return $new_fields;
    }

    public static function getIcons($field) {
        $field['icon'] = (trim($field['value']) != '') ?
            '<span id="ico-edit-menu-item-' . $field['name'] . '-' . $field['id'] . '">' .
                '<i class="' . $field['value'] . '"></i>' .
                '<a href="javascript:void(0);" class="nasa-remove-icon" data-id="edit-menu-item-' . $field['name'] . '-' . $field['id'] . '">' .
                    '<i class="fa fa-remove"></i>' .
                '</a>' .
            '</span>' : '<span id="ico-edit-menu-item-' . $field['name'] . '-' . $field['id'] . '"></span>';

        return
            '<p class="additional-menu-field-' . $field['name'] . ' description description-' . $field['type_show'] . '">' .
                '<label for="edit-menu-item-' . $field['name'] . '-' . $field['id'] . '">' .
                    '<a class="nasa-chosen-icon" data-fill="edit-menu-item-' . $field['name'] . '-' . $field['id'] . '">' . $field['label'] . '</a>' . $field['icon'] .
                    '<input
                        type="hidden"
                        id="edit-menu-item-' . $field['name'] . '-' . $field['id'] . '"
                        class="widefat code edit-menu-item-' . $field['name'] . '"
                        name="menu-item-' . $field['name'] . '[' . $field['id'] . ']"
                        value="' . $field['value'] . '" />' .
                '</label>' .
            '</p>';
    }

    public static function getSelect($field) {
        $select = '<p class="additional-menu-field-' . $field['name'] . ' description description-' . $field['type_show'] . ' select-field-' . $field['id'] . '">' .
                '<label for="edit-menu-item-' . $field['name'] . '-' . $field['id'] . '">' .
                $field['label'] . '<br />' .
                '<select id="edit-menu-item-' . $field['name'] . '-' . $field['id'] . '" class="widefat code edit-menu-item-' . $field['name'] . '" name="menu-item-' . $field['name'] . '[' . $field['id'] . ']">';

        $select .= (!isset($field['default']) || $field['default'] == true) ? '<option value="0">' . $field['label'] . '</option>' : '';
        if (!empty($field['values']) && is_array($field['values'])) {
            foreach ($field['values'] as $k => $v) {
                $select .= '<option value="' . esc_attr($k) . '" ' . selected($field['value'], $k, false) . '>' . esc_html($v) . '</option>';
            }
        }
        $select .= '</select>' .
                '</lable>' .
                '</p>';

        return $select;
    }

    public static function getSelectPosition($field, $hidden = false) {
        $hidden = ($hidden) ? 'hidden-tag ' : '';

        $select = '<p class="' . $hidden . 'additional-menu-field-' . $field['name'] . ' description description-' . $field['type_show'] . ' select-field-' . $field['id'] . '">' .
                '<label for="edit-menu-item-' . $field['name'] . '-' . $field['id'] . '">' .
                $field['label'] . '<br />' .
                '<select id="edit-menu-item-' . $field['name'] . '-' . $field['id'] . '" class="widefat code edit-menu-item-' . $field['name'] . '" name="menu-item-' . $field['name'] . '[' . $field['id'] . ']">';
        $select .= (!isset($field['default']) || $field['default'] == true) ? '<option value="0">' . $field['label'] . '</option>' : '';
        if (!empty($field['values']) && is_array($field['values'])) {
            foreach ($field['values'] as $k => $v) {
                $select .= '<option value="' . esc_attr($k) . '" ' . selected($field['value'], $k, false) . '>' . esc_html($v) . '</option>';
            }
        }
        $select .= '</select>' .
                '</lable>' .
                '</p>';

        return $select;
    }

    public static function getWidgets($field) {
        global $wp_registered_sidebars;

        $select = '<p class="additional-menu-field-' . $field['name'] . ' description description-' . $field['type_show'] . '">' .
                '<label for="edit-menu-item-' . $field['name'] . '-' . $field['id'] . '">' .
                $field['label'] . '<br />' .
                '<select id="edit-menu-item-' . $field['name'] . '-' . $field['id'] . '" class="widefat code edit-menu-item-' . $field['name'] . '" name="menu-item-' . $field['name'] . '[' . $field['id'] . ']">' .
                '<option value="0">' . esc_html__('Select Widget Area', 'nasa-core') . '</option>';
        if (!empty($wp_registered_sidebars) && is_array($wp_registered_sidebars)) {
            foreach ($wp_registered_sidebars as $sidebar) {
                $select .= '<option value="' . esc_attr($sidebar['id']) . '" ' . selected($field['value'], $sidebar['id'], false) . '>' . esc_html($sidebar['name']) . '</option>';
            }
        }
        $select .= '</select>' .
                '</lable>' .
                '</p>';

        return $select;
    }

    public static function getMedia($field, $hidden = false) {
        $img = '';
        if (isset($field['value']) && $field['value']) {
            if (is_numeric($field['value'])) {
                $image = wp_get_attachment_image_src($field['value'], 'full');
                if (isset($image[0])) {
                    $img .= '<img src="' . esc_url($image[0]) . '" />';
                }
            } else {
                $img .= '<img src="' . $field['value'] . '" />';
            }
        }
        
        $hidden = $hidden ? 'hidden-tag ' : '';
        $media = '<p class="' . $hidden . 'additional-menu-field-' . $field['name'] . ' description description-' . $field['type_show'] . ' menu-field-media-' . $field['id'] . '">' .
                $field['label'] .
                '<input type="hidden" id="edit-menu-item-' . $field['name'] . '-' . $field['id'] . '" name="menu-item-' . $field['name'] . '[' . $field['id'] . ']" value="' . $field['value'] . '" />' .
                '<a href="javascript:void(0);" class="button nasa-media-upload-button menu_upload_button" data-id="edit-menu-item-' . $field['name'] . '-' . $field['id'] . '">' . esc_html__('Upload', 'nasa-core') . '</a>' .
                '<a href="javascript:void(0);" class="button nasa-media-remove-button media_remove_button" data-id="edit-menu-item-' . $field['name'] . '-' . $field['id'] . '">' . esc_html__('Remove', 'nasa-core') . '</a>' .
                '<span class="imgmega edit-menu-item-' . $field['name'] . '-' . $field['id'] . '">' . $img . '</span>' .
                '</p>';

        return $media;
    }

    /**
     * Save the newly submitted fields
     * @hook {action} save_post
     */
    public static function _save_post($post_id, $post) {
        if ($post->post_type !== 'nav_menu_item') {
            return $post_id; // prevent weird things from happening
        }

        foreach (self::$options['fields'] as $field_schema) {
            $form_field_name = 'menu-item-' . $field_schema['name'];

            // @todo FALSE should always be used as the default $value, otherwise we wouldn't be able to clear checkboxes
            if ($field_schema['input_type'] == 'checkbox' && !isset($_POST[$form_field_name][$post_id])) {
                $_POST[$form_field_name][$post_id] = false;
            }

            if (isset($_POST[$form_field_name][$post_id])) {
                $key = self::get_menu_item_postmeta_key($field_schema['name']);
                $value = stripslashes($_POST[$form_field_name][$post_id]);
                update_post_meta($post_id, $key, $value);
            }
        }
    }

}

class Nasa_Walker_Nav_Menu_Edit extends Walker_Nav_Menu_Edit {

    public function start_el(&$output, $item, $depth = 0, $args = array(), $id = 0) {
        $item_output = '';
        parent::start_el($item_output, $item, $depth, $args, $id);

        $new_fields = Nasa_Nav_Menu_Item_Custom_Fields::get_field($item, $depth, $args);
        if ($new_fields) :
            $item_output = preg_replace('/(?=<div[^>]+class="[^"]*submitbox)/', $new_fields, $item_output);
        endif;
        $output .= $item_output;
    }

}

// Config more custom fields 
add_filter('nasa_nav_menu_item_fields', 'nasa_menu_item_additional_fields');
function nasa_menu_item_additional_fields() {
    return array(
        'nasa_megamenu' => array(
            'name' => 'enable_mega',
            'label' => esc_html__('Mega Menu', 'nasa-core'),
            'container_class' => 'enable-widget',
            'input_type' => 'checkbox',
            'type_show' => 'thin'
        ),
        'nasa_fullwidth' => array(
            'name' => 'enable_fullwidth',
            'label' => esc_html__('Full Width', 'nasa-core'),
            'container_class' => 'enable-fullwidth',
            'input_type' => 'checkbox',
            'type_show' => 'thin'
        ),
        'nasa_icon' => array(
            'name' => 'icon_menu',
            'label' => esc_html__('Icon Menu ', 'nasa-core'),
            'container_class' => 'icon-menu',
            'input_type' => 'icons',
            'type_show' => 'wide'
        ),
        'nasa_select_width' => array(
            'name' => 'columns_mega',
            'label' => esc_html__('Number Columns Mega Menu', 'nasa-core'),
            'container_class' => 'select-columns',
            'input_type' => 'select',
            'values' => array(
                '2' => '2 Columns',
                '3' => '3 Columns',
                '4' => '4 Columns',
                '5' => '5 Columns',
            ),
            'default' => false,
            'type_show' => 'wide'
        ),
        'nasa_megamenu_image' => array(
            'name' => 'image_mega_enable',
            'label' => esc_html__('Image Megamenu', 'nasa-core'),
            'container_class' => 'enable-widget',
            'input_type' => 'checkbox',
            'type_show' => 'wide'
        ),
        'nasa_megamenu_image_btn' => array(
            'name' => 'image_mega',
            'label' => esc_html__('', 'nasa-core'),
            'container_class' => 'enable-widget',
            'input_type' => 'image',
            'type_show' => 'wide'
        ),
        'nasa_select_position_image' => array(
            'name' => 'position_image_mega',
            'label' => esc_html__('Position', 'nasa-core'),
            'container_class' => 'select-position',
            'input_type' => 'select_position',
            'values' => array(
                'before' => 'Before title',
                'after' => 'After title',
                'bg' => 'Background menu',
            ),
            'default' => false,
            'type_show' => 'wide'
        ),
        'nasa_select_disable_title' => array(
            'name' => 'disable_title_image_mega',
            'label' => esc_html__('Show Title', 'nasa-core'),
            'container_class' => 'select-position',
            'input_type' => 'select_position',
            'values' => array(
                '0' => 'Enable',
                '1' => 'Disable',
            ),
            'default' => false,
            'type_show' => 'wide'
        ),
        'nasa_el_class' => array(
            'name' => 'el_class',
            'label' => esc_html__('Custom Class', 'nasa-core'),
            'container_class' => 'enable-widget',
            'input_type' => 'text',
            'values' => '',
            'default' => '',
            'type_show' => 'wide'
        )
    );
}

add_action('init', array('Nasa_Nav_Menu_Item_Custom_Fields', 'setup'));
