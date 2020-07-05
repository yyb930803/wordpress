<?php
/**
 * Shortcode attributes
 * @var $atts
 * @var $content - shortcode content
 */

if (!defined('ABSPATH')) :
    die('-1');
endif;

$atts = vc_map_get_attributes($this->getShortcode(), $atts);
$this->resetVariables($atts, $content);
extract($atts);

$this->setGlobalTtaInfo();
$prepareContent = $this->getTemplateVariable('content');

$file = ELESSI_CHILD_PATH . '/vc_templates/tta_global_layout/' . $this->layout . '_layout.php';
require is_file($file) ? $file : ELESSI_THEME_PATH . '/vc_templates/tta_global_layout/' . $this->layout . '_layout.php';
