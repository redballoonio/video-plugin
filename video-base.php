<?php
/*
Plugin Name: RB Video Base
Plugin URI: http://redballoon.io
Description: A base for all your videos
Version: 1.1.1
Author: Red Balloon Design Ltd
Author URI: http://redballoon.io
License: GPLv2
*/

/*
View the readme here:
https://docs.google.com/spreadsheets/d/1apC0th0X_rq8ybvTDsp40lWcHfthdna8WMbAywX7DBU/pubhtml?gid=1345923635&single=true
*/

if ( ! defined('ABSPATH') ) exit;

define('RBVB_VERSION', '1.2.0');
define('RBVB_PATH', plugin_dir_path(__FILE__));
define('RBVB_URL', plugin_dir_url(__FILE__));

require_once RBVB_PATH . 'includes/Plugin.php';

register_activation_hook(__FILE__, array('RBVB_Plugin', 'activate'));
register_deactivation_hook(__FILE__, array('RBVB_Plugin', 'deactivate'));

RBVB_Plugin::instance();

// Shortcodes (keep your existing)
add_shortcode('video', 'video_shortcode');
require_once RBVB_PATH . 'includes/Shortcode.php';

// Footer content (keep your existing)
require_once RBVB_PATH . 'includes/Footer.php';
add_action('wp_footer', 'outputModalHTML');