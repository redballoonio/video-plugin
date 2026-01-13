<?php // includes/Taxonomy.php
if ( ! defined('ABSPATH') ) exit;

class RBVB_Taxonomy {

  public static function init() {
    add_action('init', array(__CLASS__, 'register'));
  }

  public static function register() {
    $labels = array(
      'name'              => __('Video Categories', 'rbvb'),
      'singular_name'     => __('Video Category', 'rbvb'),
      'search_items'      => __('Search Video Categories', 'rbvb'),
      'all_items'         => __('All Video Categories', 'rbvb'),
      'parent_item'       => __('Parent Video Category', 'rbvb'),
      'parent_item_colon' => __('Parent Video Category:', 'rbvb'),
      'edit_item'         => __('Edit Video Category', 'rbvb'),
      'update_item'       => __('Update Video Category', 'rbvb'),
      'add_new_item'      => __('Add New Video Category', 'rbvb'),
      'new_item_name'     => __('New Video Category Name', 'rbvb'),
      'menu_name'         => __('Video Categories', 'rbvb'),
    );

    register_taxonomy('video_category', array('video'), array(
      'hierarchical'      => true,
      'labels'            => $labels,
      'show_ui'           => true,
      'show_admin_column' => true,
      'show_in_rest'      => true,
      'query_var'         => true,

      // Put taxonomy under /videos/category/{term}
      'rewrite' => array(
        'slug'       => 'videos/category',
        'with_front' => false,
      ),
    ));
  }
}
