<?php
if ( ! defined('ABSPATH') ) exit;

class RBVB_CPT {

  public static function init() {
    add_action('init', array(__CLASS__, 'register'));
  }

  public static function register() {

    $labels = array(
      'name'               => __( 'Videos', 'video' ),
      'singular_name'      => __( 'Video', 'video' ),
      'add_new'            => __( 'Add New', 'video' ),
      'add_new_item'       => __( 'Add New Video', 'video' ),
      'edit_item'          => __( 'Edit Video', 'video' ),
      'new_item'           => __( 'New Video', 'video' ),
      'view_item'          => __( 'View Video', 'video' ),
      'search_items'       => __( 'Search Videos', 'video' ),
      'not_found'          => __( 'No videos found', 'video' ),
      'not_found_in_trash' => __( 'No videos found in Trash', 'video' ),
      'menu_name'          => __( 'Videos', 'video' ),
    );

    $args = array(
      'labels' => $labels,
      'hierarchical' => false,
      'description' => 'A place to base all your videos for the website.',
      'supports' => array( 'title', 'editor', 'thumbnail'),
      //'supports' => array( 'title', 'editor', 'thumbnail', 'custom-fields' ),
      'taxonomies' => array( 'category' ),
      //'taxonomies' => array('video_category'), // instead of category/post_tag
      'public' => true,
      'show_ui' => true,
      'show_in_menu' => true,
      'show_in_rest' => true,
      'menu_position' => 5,
      'show_in_nav_menus' => true,
      'publicly_queryable' => true,
      'exclude_from_search' => false,

      // videos archive
      'has_archive' => 'videos',
      'rewrite' => array(
        'slug' => 'videos',
        'with_front' => false,
      ),

      'query_var' => true,
      'can_export' => true,
      'capability_type' => 'post',
      
    );

    register_post_type('video', $args);
  }
}
