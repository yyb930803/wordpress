<?php
if (class_exists('WC_Widget')) {

    add_action('widgets_init', 'elessi_product_categories_widget');

    function elessi_product_categories_widget() {
        register_widget('Elessi_Product_Categories_Widget');
    }

    class Elessi_Product_Categories_Widget extends WC_Widget {

        /**
         * Category ancestors
         *
         * @var array
         */
        public $cat_ancestors;

        /**
         * Current Category
         *
         * @var bool
         */
        public $current_cat;

        /**
         * Constructor
         */
        public function __construct() {
            $this->widget_cssclass = 'woocommerce widget_product_categories';
            $this->widget_description = esc_html__('Display product categories with Accordion.', 'elessi-theme');
            $this->widget_id = 'nasa_product_categories';
            $this->widget_name = esc_html__('Nasa Product Categories', 'elessi-theme');
            $this->settings = array(
                'title' => array(
                    'type' => 'text',
                    'std' => esc_html__('Product Categories', 'elessi-theme'),
                    'label' => esc_html__('Title', 'elessi-theme')
                ),
                'orderby' => array(
                    'type' => 'select',
                    'std' => 'name',
                    'label' => esc_html__('Order by', 'elessi-theme'),
                    'options' => array(
                        'order' => esc_html__('Category Order', 'elessi-theme'),
                        'name' => esc_html__('Name', 'elessi-theme')
                    )
                ),
                'count' => array(
                    'type' => 'checkbox',
                    'std' => 0,
                    'label' => esc_html__('Show product counts', 'elessi-theme')
                ),
                'hierarchical' => array(
                    'type' => 'checkbox',
                    'std' => 1,
                    'label' => esc_html__('Show hierarchy', 'elessi-theme')
                ),
                'show_children_only' => array(
                    'type' => 'checkbox',
                    'std' => 0,
                    'label' => esc_html__('Only show children of the current category', 'elessi-theme')
                ),
                'hide_empty' => array(
                    'type'  => 'checkbox',
                    'std'   => 0,
                    'label' => esc_html__('Hide empty categories', 'elessi-theme'),
                ),
                'accordion' => array(
                    'type' => 'checkbox',
                    'std' => 1,
                    'label' => esc_html__('Show as Accordion', 'elessi-theme')
                ),
                'show_items' => array(
                    'type' => 'text',
                    'std' => 'All',
                    'label' => esc_html__('Show default numbers items', 'elessi-theme')
                )
            );
            
            parent::__construct();
        }

        /**
         * Updates a particular instance of a widget.
         *
         * @see WP_Widget->update
         *
         * @param array $new_instance
         * @param array $old_instance
         *
         * @return array
         */
        public function update($new_instance, $old_instance) {
            $this->nasa_settings($new_instance);

            return parent::update($new_instance, $old_instance);
        }

        /**
         * form function.
         *
         * @see WP_Widget->form
         * @param array $instance
         */
        public function form($instance) {
            $this->nasa_settings($instance);

            if (empty($this->settings)) {
                return;
            }

            foreach ($this->settings as $key => $setting) {
                $value = isset($instance[$key]) ? $instance[$key] : (isset($setting['std']) ? $setting['std'] : '');
                $_id = $this->get_field_id($key);
                $_name = $this->get_field_name($key);

                switch ($setting['type']) {

                    case 'text' :
                        ?>
                        <p>
                            <label for="<?php echo esc_attr($_id); ?>"><?php echo ($setting['label']); ?></label>
                            <input class="widefat" id="<?php echo esc_attr($_id); ?>" name="<?php echo esc_attr($_name); ?>" type="text" value="<?php echo esc_attr($value); ?>" />
                        </p>
                        <?php
                        break;

                    case 'number' :
                        ?>
                        <p>
                            <label for="<?php echo esc_attr($_id); ?>"><?php echo ($setting['label']); ?></label>
                            <input class="widefat" id="<?php echo esc_attr($_id); ?>" name="<?php echo esc_attr($_name); ?>" type="number" step="<?php echo esc_attr($setting['step']); ?>" min="<?php echo esc_attr($setting['min']); ?>" max="<?php echo esc_attr($setting['max']); ?>" value="<?php echo esc_attr($value); ?>" />
                        </p>
                        <?php
                        break;

                    case 'select' :
                        ?>
                        <p>
                            <label for="<?php echo esc_attr($_id); ?>"><?php echo ($setting['label']); ?></label>
                            <select class="widefat" id="<?php echo esc_attr($_id); ?>" name="<?php echo esc_attr($_name); ?>">
                                <?php foreach ($setting['options'] as $o_key => $o_value): ?>
                                    <option value="<?php echo esc_attr($o_key); ?>" <?php selected($o_key, $value); ?>><?php echo esc_html($o_value); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </p>
                        <?php
                        break;

                    case 'checkbox' :
                        ?>
                        <p>
                            <input id="<?php echo esc_attr($_id); ?>" name="<?php echo esc_attr($_name); ?>" type="checkbox" value="1" <?php checked($value, 1); ?> />
                            <label for="<?php echo esc_attr($_id); ?>"><?php echo ($setting['label']); ?></label>
                        </p>
                        <?php
                        break;
                    
                    case 'toggle-show-icon' :
                        ?>
                        <p>
                            <a class="toggle-choose-icon-btn" href="javascript:void(0);"><?php echo esc_html__('Add icons for categories.', 'elessi-theme'); ?></a>
                        </p>
                        <?php
                        break;
                    
                    // Button chosen icon font
                    case 'icons':
                        echo ($this->getTemplateAdminIcon($_name, $_id, $setting['label'], $value));
                        break;
                }
            }
        }
        
        public function getTemplateAdminIcon($_name, $_id, $label, $value) {
            $content = '<p class="toggle-choose-icon hidden-tag">';
            $content .= '<a class="nasa-chosen-icon" data-fill="' . esc_attr($_id) . '">' . esc_html__('Click select icon for ', 'elessi-theme') . '</a>';
            $content .= '<span id="ico-' . esc_attr($_id) . '">';
            if ($value):
                $content .= '<i class="' . esc_attr($value) . '"></i>';
                $content .= '<a href="javascript:void(0);" class="nasa-remove-icon" data-id="' . esc_attr($_id) . '">';
                $content .= '<i class="fa fa-remove"></i>';
                $content .= '</a>';
            endif;
            $content .= '</span>';
            $content .= '<label for="' . $_id . '">' . $label . '</label><br />';
            $content .= '<input class="widefat" id="' . esc_attr($_id) . '" name="' . $_name . '" type="hidden" readonly="true" value="' . esc_attr($value) . '" />';
            $content .= '</p>';

            return $content;
        }


        /**
         * Init settings after post types are registered.
         */
        public function nasa_settings($instance) {
            // Default setting color
            if (empty($instance)) {
                if ($default = get_option('widget_' . $this->widget_id, true)) {
                    foreach ($default as $v) {
                        $instance = $v;
                        break;
                    }
                }
            }

            $top_level = get_terms(apply_filters('woocommerce_product_attribute_terms', array(
                'taxonomy' => 'product_cat',
                'hierarchical' => true,
                'hide_empty' => false
            )));
            
            if ($top_level) {
                $this->settings['toggle'] = array(
                    'type' => 'toggle-show-icon'
                );
                
                foreach ($top_level as $v) {
                    // Change settings
                    $this->settings['cat_' . $v->slug] = array(
                        'type' => 'icons',
                        'std' => isset($instance['cat_' . $v->slug]) ? $instance['cat_' . $v->slug] : '',
                        'label' => '<b>' . $v->name . '</b>'
                    );
                }
            }
        }

        /**
         * widget function.
         *
         * @see WP_Widget
         *
         * @param array $args
         * @param array $instance
         *
         * @return void
         */
        public function widget($args, $instance) {
            global $wp_query, $post, $nasa_opt;
            
            $a = isset($instance['accordion']) ? $instance['accordion'] : $this->settings['accordion']['std'];
            $c = isset($instance['count']) ? $instance['count'] : $this->settings['count']['std'];
            $h = isset($instance['hierarchical']) ? $instance['hierarchical'] : $this->settings['hierarchical']['std'];
            
            $o = isset($instance['orderby']) ? $instance['orderby'] : $this->settings['orderby']['std'];
            $hide_empty = isset($instance['hide_empty']) ? $instance['hide_empty'] : $this->settings['hide_empty']['std'];
            $show_items = isset($instance['show_items']) ? (int) $instance['show_items'] : 0;
            
            $only_show_child = isset($instance['show_children_only']) && $instance['show_children_only'] == '1' ? true : false;
            
            $getSide = false;
            $is_product = is_singular('product');
            if(!$is_product) {
                $getSide = isset($nasa_opt['category_sidebar']) ? $nasa_opt['category_sidebar'] : 'top';
                $getSide = isset($_GET['sidebar']) ? $_GET['sidebar'] : $getSide;
                $getSide = in_array($getSide, array('left', 'right', 'left-classic', 'right-classic', 'top-2')) ? $getSide : 'top';
            }
            
            /**
             * Show all items
             */
            $list_args = array(
                'show_count' => $c,
                'hierarchical' => $h,
                'taxonomy' => 'product_cat',
                'hide_empty' => $hide_empty
            );

            // Menu Order
            $list_args['menu_order'] = false;
            if ($o == 'order') {
                $list_args['menu_order'] = 'asc';
            } else {
                $list_args['orderby'] = 'title';
            }

            // Setup Current Category
            $this->current_cat = false;
            $this->cat_ancestors = array();
            $rootId = 0;
            
            if (is_tax('product_cat')) {
                $this->current_cat = $wp_query->queried_object;
                $this->cat_ancestors = get_ancestors($this->current_cat->term_id, 'product_cat');
            } elseif ($is_product) {
                $productId = isset($wp_query->queried_object->ID) ? $wp_query->queried_object->ID : $post->ID;
                
                $terms = wc_get_product_terms($productId, 'product_cat', array(
                    'orderby' => 'parent',
                    'order'   => 'DESC'
                ));
                
                if ($terms) {
                    $main_term           = apply_filters('woocommerce_product_categories_widget_main_term', $terms[0], $terms);
                    $this->current_cat   = $main_term;
                    $this->cat_ancestors = get_ancestors($main_term->term_id, 'product_cat');
                }
            }
            
            /**
             * Only Show Children
             */
            if($only_show_child) {
                
                if($this->current_cat && $this->current_cat->term_id) {
                    $terms_chilren = get_terms(apply_filters('woocommerce_product_attribute_terms', array(
                        'taxonomy' => 'product_cat',
                        'parent' => $this->current_cat->term_id,
                        'hierarchical' => $h,
                        'hide_empty' => $hide_empty
                    )));
                    
                    if(! $terms_chilren) {
                        $term_root = get_ancestors($this->current_cat->term_id, 'product_cat');
                        $rootId = isset($term_root[0]) ? $term_root[0] : $rootId;
                    } else {
                        $rootId = $this->current_cat->term_id;
                    }
                }
            }
            
            elseif((isset($nasa_opt['disable_top_level_cat']) && $nasa_opt['disable_top_level_cat'])) {
                $rootId = $this->cat_ancestors ? end($this->cat_ancestors) :
                    ($this->current_cat ? $this->current_cat->term_id : $rootId);
            }
            
            $this->widget_start($args, $instance);
            $menu_cat = new Elessi_Product_Cat_List_Walker();
            $menu_cat->setIcons($instance);
            $menu_cat->setShowDefault($show_items);
            $list_args['walker'] = $menu_cat;
            $list_args['title_li'] = '';
            $list_args['pad_counts'] = 1;
            $list_args['show_option_none'] = esc_html__('No product categories exist.', 'elessi-theme');
            $list_args['current_category'] = $this->current_cat ? $this->current_cat->term_id : '';
            $list_args['current_category_ancestors'] = $this->cat_ancestors;
            $list_args['child_of'] = $rootId;
            if(version_compare(WC()->version, '3.3.0', ">=") && (!isset($nasa_opt['show_uncategorized']) || !$nasa_opt['show_uncategorized'])) {
                $list_args['exclude'] = get_option('default_product_cat');
            }
            
            $accordion = $a ? ' nasa-accordion' : '';

            if($getSide == 'top') {
                $class_wrap_cat_top = 'nasa-widget-filter-cats-topbar';
                if(isset($nasa_opt['top_bar_cat_pos']) && $nasa_opt['top_bar_cat_pos'] == 'top') {
                    $accordion .= ' nasa-top-cat-filter';
                    $class_wrap_cat_top .= ' nasa-top-cat-filter-wrap';
                }
                echo '<div class="' . esc_attr($class_wrap_cat_top) . '">';
            }
            
            echo '<ul class="nasa-root-cat product-categories' . $accordion . '">';
            wp_list_categories(apply_filters('woocommerce_product_categories_widget_args', $list_args));

            if ($show_items && ($menu_cat->getTotalRoot() > $show_items)) {
                echo '<li class="nasa_show_manual"><a data-show="1" class="nasa-show" href="javascript:void(0);">' . esc_html__('+ Show more', 'elessi-theme') . '</a><a data-show="0" class="nasa-hidden" href="javascript:void(0);">' . esc_html__('- Show less', 'elessi-theme') . '</a></li>';
            }
            
            echo '<li class="nasa-current-note"></li>';

            echo '</ul>';
            
            if($getSide == 'top') {
                echo '</div>';
            }
            
            $this->widget_end($args);
        }

    }

    if (!class_exists('WC_Product_Cat_List_Walker')) {
        include_once WC()->plugin_path() . '/includes/walkers/class-product-cat-list-walker.php';
    }

    class Elessi_Product_Cat_List_Walker extends WC_Product_Cat_List_Walker {

        protected $_icons = array();
        protected $_k = 0;
        protected $_show_default = 0;

        public function setIcons($instance) {
            $this->_icons = $instance;
        }

        public function setShowDefault($show) {
            $this->_show_default = (int) $show;
        }

        public function getTotalRoot() {
            return $this->_k;
        }

        /**
         * @see Walker::start_el()
         * @since 2.1.0
         *
         * @param string $output Passed by reference. Used to append additional content.
         * @param int $depth Depth of category in reference to parents.
         * @param integer $current_object_id
         */
        public function start_el(&$output, $cat, $depth = 0, $args = array(), $current_object_id = 0) {
            $output .= '<li class="cat-item cat-item-' . $cat->term_id . ' cat-item-' . $cat->slug;
            $nasa_active = $accodion = $icon = '';
            if ($depth == 0) {
                $output .= ' root-item';
                if ($this->_show_default && ($this->_k >= $this->_show_default)) {
                    $output .= ' nasa-show-less';
                }
                $this->_k++;
            }
            if (isset($this->_icons['cat_' . $cat->slug]) && trim($this->_icons['cat_' . $cat->slug]) != '') {
                $icon = '<i class="' . $this->_icons['cat_' . $cat->slug] . '"></i>';
                $icon .= '&nbsp;&nbsp;';
            }

            if ($args['current_category'] == $cat->term_id) {
                $output .= ' current-cat active';
                $nasa_active = ' nasa-active';
            }

            if ($args['has_children'] && $args['hierarchical']) {
                $output .= ' cat-parent li_accordion';
                $accodion = $args['current_category'] == $cat->term_id ? 
                    '<a href="javascript:void(0);" class="accordion" data-class_show="pe-7s-plus" data-class_hide="pe-7s-less"><span class="icon pe-7s-less"></span></a>':
                    '<a href="javascript:void(0);" class="accordion" data-class_show="pe-7s-plus" data-class_hide="pe-7s-less"><span class="icon pe-7s-plus"></span></a>';
            }

            if ($args['current_category_ancestors'] && $args['current_category'] && in_array($cat->term_id, $args['current_category_ancestors'])) {
                $output .= ' current-cat-parent active';
                $accodion = '<a href="javascript:void(0);" class="accordion" data-class_show="pe-7s-plus" data-class_hide="pe-7s-less"><span class="icon pe-7s-less"></span></a>';
            }
            
            $output .= '">' . $accodion;

            $output .= '<a href="' . get_term_link($cat, $this->tree_type) . '" data-id="' . esc_attr((int) $cat->term_id) . '" class="nasa-filter-by-cat' . $nasa_active . '">' . $icon . $cat->name;
            $output .= $args['show_count'] ? ' <span class="count">(' . $cat->count . ')</span>' : '';
            $output .= '</a>';
        }
    }
}
