<?php

defined('ABSPATH') or exit; // Exit if accessed directly

$file = ELESSI_CHILD_PATH . '/portfolio.php';
include_once is_file($file) ? $file : ELESSI_THEME_PATH . '/portfolio.php';
