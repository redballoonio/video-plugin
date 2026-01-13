<?php // includes/Enqueue.php
if ( ! defined('ABSPATH') ) exit;

class RBVB_Enqueue {

  public static function init() {
    add_action('wp_enqueue_scripts', array(__CLASS__, 'frontend'));
    add_action('admin_enqueue_scripts', array(__CLASS__, 'admin'));
  }

  public static function frontend() {
    // Register - shortcode will enqueue when it runs
    wp_register_script('video-script', RBVB_URL . 'js/video-base.js', array('jquery'), RBVB_VERSION, true);
    wp_register_style('video-styles', RBVB_URL . 'css/video-base.css', array(), RBVB_VERSION);
    //wp_register_style('video-styles', RBVB_URL . 'css/video-base.min.css', array(), RBVB_VERSION);

    // Enqueue Video Styles
    wp_enqueue_style('video-styles');
  }

  public static function admin($hook) {
    if ( $hook !== 'post.php' && $hook !== 'post-new.php' ) return;

    $screen = function_exists('get_current_screen') ? get_current_screen() : null;
    if ( empty($screen->post_type) || $screen->post_type !== 'video' ) return;

    wp_enqueue_media();

    // Admin JavaScript
    wp_enqueue_script(
      'rbvb-admin',
      RBVB_URL . 'js/admin.js',
      array('jquery'),
      RBVB_VERSION,
      true
    );
  }
}
