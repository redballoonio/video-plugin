<?php
if ( ! defined('ABSPATH') ) exit;

class RBVB_Templates {

  public static function init() {
    add_filter('template_include', array(__CLASS__, 'template_include'));
    add_action('pre_get_posts', array(__CLASS__, 'modify_archive_query'));
  }

  public static function modify_archive_query($query) {
    if ( is_admin() || ! $query->is_main_query() ) return;

    if ( $query->is_post_type_archive('video') ) {
      //$query->set('posts_per_page', 12);
      $query->set('posts_per_page', (int) RBVB_Settings::get('posts_per_page', 12));
      $query->set('orderby', 'date');
      $query->set('order', 'DESC');
    }
  }

  public static function template_include($template) {

    // Archive: /videos
    if ( is_post_type_archive('video') ) {

      $theme_template = locate_template(array(
        'rb-video-base/archive-video.php',
        'archive-video.php'
      ));
      if ( $theme_template ) return $theme_template;

      $plugin_template = RBVB_PATH . 'templates/archive-video.php';
      if ( file_exists($plugin_template) ) return $plugin_template;
    }

    // Single: /videos/{slug}/
    if ( is_singular('video') ) {

      $theme_template = locate_template(array(
        'rb-video-base/single-video.php',
        'single-video.php'
      ));
      if ( $theme_template ) return $theme_template;

      $plugin_template = RBVB_PATH . 'templates/single-video.php';
      if ( file_exists($plugin_template) ) return $plugin_template;
    }

    // Video Category Template: /video/category/{slug}/
    // if ( is_tax('video_category') ) {

    //   $theme_template = locate_template(array(
    //     'rb-video-base/taxonomy-video_category.php',
    //     'taxonomy-video_category.php'
    //   ));
    //   if ( $theme_template ) return $theme_template;

    //   $plugin_template = RBVB_PATH . 'templates/taxonomy-video_category.php';
    //   if ( file_exists($plugin_template) ) return $plugin_template;
    // }

    return $template;
  }
  
}
