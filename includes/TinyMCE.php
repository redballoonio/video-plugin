<?php
if ( ! defined('ABSPATH') ) exit;

class RBVB_TinyMCE {

  public static function init() {
    add_action('admin_init', array(__CLASS__, 'register_button'));
    add_action('admin_enqueue_scripts', array(__CLASS__, 'enqueue_media'));
  }

  public static function register_button() {
    if ( ! current_user_can('edit_posts') && ! current_user_can('edit_pages') ) return;
    if ( 'true' !== get_user_option('rich_editing') ) return;

    add_filter('mce_external_plugins', array(__CLASS__, 'mce_plugin'));
    add_filter('mce_buttons', array(__CLASS__, 'mce_buttons'));
  }

  public static function mce_plugin($plugins) {
    // IMPORTANT: use RBVB_URL so the path is correct from any include file
    $plugins['rb_video_button'] = RBVB_URL . 'js/admin.js';
    return $plugins;
  }

  public static function mce_buttons($buttons) {
    $buttons[] = 'rb_video_button';
    return $buttons;
  }

  public static function enqueue_media($hook) {
    // Only need media on editor screens
    if ( in_array($hook, array('post.php','post-new.php','page.php','page-new.php'), true) ) {
      wp_enqueue_media();
    }
  }
}
