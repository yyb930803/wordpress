<?php
// **********************************************************************// 
// ! Flickr Photos widget
// **********************************************************************// 

// add_action('widgets_init', 'nasa_flickr_widget');
function nasa_flickr_widget() {
    register_widget('Nasa_Flickr_Widget');
}

class Nasa_Flickr_Widget extends WP_Widget {

    const DF_SCREEN_NAME = '107945286@N06';
    
    function __construct() {
        $widget_ops = array('classname' => 'flickr', 'description' => esc_html__('Photos from flickr.', 'nasa-core'));
        $control_ops = array('id_base' => 'nasa_flickr-widget');
        parent::__construct('nasa_flickr-widget', esc_html__('Nasa Flickr Photos', 'nasa-core'), $widget_ops, $control_ops);
    }

    function widget($args, $instance) {
        extract($args);

        $title = apply_filters('widget_title', $instance['title'], $instance, $this->id_base);
        $screen_name = (isset($instance['screen_name']) && $instance['screen_name'] != '') ? $instance['screen_name'] : self::DF_SCREEN_NAME;
        $number = isset($instance['number']) ? $instance['number'] : 0;
        $show_button = isset($instance['show_button']) ? $instance['show_button'] : 1;

        echo $before_widget;
        echo $title ? $before_title . $title . $after_title : '';
        echo ($screen_name && $number) ? '<script src="//www.flickr.com/badge_code_v2.gne?count=' . $number . '&display=latest&size=s&layout=x&source=user&user=' . $screen_name . '"></script>' : '';
        echo $after_widget;
    }

    function update($new_instance, $old_instance) {
        $instance = $old_instance;

        $instance['title'] = strip_tags($new_instance['title']);
        $instance['screen_name'] = $new_instance['screen_name'];
        $instance['number'] = $new_instance['number'];

        return $instance;
    }

    function form($instance) {
        $defaults = array('title' => 'Photos from Flickr', 'screen_name' => '', 'number' => 6, 'show_button' => 1);
        $instance = wp_parse_args((array) $instance, $defaults);
        ?>

        <p>
            <label for="<?php echo esc_attr($this->get_field_id('title')); ?>"><?php esc_html_e('Title:', 'nasa-core'); ?></label>
            <input class="widefat" style="width: 216px;" id="<?php echo esc_attr($this->get_field_id('title')); ?>" name="<?php echo esc_attr($this->get_field_name('title')); ?>" value="<?php echo esc_attr($instance['title']); ?>" />
        </p>

        <p>
            <label for="<?php echo esc_attr($this->get_field_id('screen_name')); ?>"><?php esc_html_e('Flickr ID', 'nasa-core'); ?></label>
            <input class="widefat" style="width: 216px;" id="<?php echo esc_attr($this->get_field_id('screen_name')); ?>" name="<?php echo esc_attr($this->get_field_name('screen_name')); ?>" value="<?php echo esc_attr($instance['screen_name']); ?>" />
            <br/>
            <?php esc_html_e('To find your flickID visit ', 'nasa-core'); ?><strong>http://idgettr.com</strong>
        </p>

        <p>
            <label for="<?php echo esc_attr($this->get_field_id('number')); ?>"><?php esc_html_e('Number of photos to show:', 'nasa-core'); ?></label>
            <input class="widefat" style="width: 30px;" id="<?php echo esc_attr($this->get_field_id('number')); ?>" name="<?php echo esc_attr($this->get_field_name('number')); ?>" value="<?php echo esc_attr($instance['number']); ?>" />
        </p>

        <?php
    }

}
