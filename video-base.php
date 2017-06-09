<?php
/*
Plugin Name: RB Video
Plugin URI: http://redballoon.io
Description: A plugin for responsively displaying video iframes. The videos can be displayed inline or using a modal or a gallery. Designed to work with youtube videos.
Version: 1.2
Author: Red Balloon Design Ltd
Author URI: http://redballoon.io
License: GPLv2
*/

// Custom Post Type
add_action( 'init', 'register_rbd_video' );
function register_rbd_video() {

        $labels = array(
            'name' => __( 'Videos', 'video' ),
            'singular_name' => __( 'Video', 'video' ),
            'add_new' => __( 'Add New', 'video' ),
            'add_new_item' => __( 'Add New Video', 'video' ),
            'edit_item' => __( 'Edit Video', 'video' ),
            'new_item' => __( 'New Video', 'video' ),
            'view_item' => __( 'View Video', 'video' ),
            'search_items' => __( 'Search Videos', 'video' ),
            'not_found' => __( 'No videos found', 'video' ),
            'not_found_in_trash' => __( 'No videos found in Trash', 'video' ),
            'parent_item_colon' => __( 'Parent Video:', 'video' ),
            'menu_name' => __( 'Videos', 'video' ),
        );

        $args = array(
            'labels' => $labels,
            'hierarchical' => false,
            'description' => 'Add videos here that require a custom description or thumbnail.',
            'supports' => array( 'title', 'excerpt', 'thumbnail', 'custom-fields' ),
            'taxonomies' => array( 'category', 'post_tag', 'video_categories' ),
            'public' => true,
            'show_ui' => true,
            'show_in_menu' => true,
            'menu_position' => 5,
            'show_in_nav_menus' => true,
            'publicly_queryable' => true,
            'exclude_from_search' => false,
            'has_archive' => false,
            'query_var' => true,
            'can_export' => true,
            'rewrite' => true,
            'capability_type' => 'post'
        );

        register_post_type( 'video', $args );
};


// Custom Meta Boxes
add_action( 'add_meta_boxes', 'add_video_metaboxes' );
function add_video_metaboxes() {

    add_meta_box('video_attributes', 'Video Attributes', 'video_attributes', 'video', 'normal', 'high');
    
};

// Add the meta box to WP Admin
function video_attributes() {
	global $post;
	$blog_id = get_current_blog_id();

	// Noncename needed to verify where the data originated
	echo '<input type="hidden" name="videometa_noncename" id="videometa_noncename" value="' .

	wp_create_nonce( plugin_basename(__FILE__) ) . '" />';

	echo '<input type="hidden" name="blog_id" value="'. $blog_id .'">';

	// Get the location data if its already been entered
    $video_url_id = get_post_meta($post->ID, '_video_url_id', true);

	// Echo out the field
    echo '<p>Youtube ID</p>';

    echo '<input type="text" name="_video_url_id" value="' . $video_url_id  . '" class="widefat" />';

}


// Save the Metabox Data
function rbd_save_video_meta($post_id, $post) {

	$custom_meta = '';

	// verify this came from the our screen and with proper authorization,
	// because save_post can be triggered at other times
	if ( isset($_POST['videometa_noncename']) && !wp_verify_nonce( $_POST['videometa_noncename'], plugin_basename(__FILE__) )) {
		return $post->ID;
	}

	// Is the user allowed to edit the post or page?
	if ( !current_user_can( 'edit_post', $post->ID )){
        return $post->ID;
    }
    
    // Network compatibility. Our plugin should not be synchronized.
    if ( empty ( $_POST[ 'blog_id' ] ) ){
        return FALSE;
    }

    if ( (int) $_POST[ 'blog_id' ] !== get_current_blog_id() ){
        return FALSE;
    }

	if( isset($_POST['videometa_noncename'])){
		$custom_meta['_video_url_id'] = $_POST['_video_url_id'];

		foreach ($custom_meta as $key => $value) { // Cycle through the $events_meta array!
			if( $post->post_type == 'revision' ) return; // Don't store custom data twice
			$value = implode(',', (array)$value); // If $value is an array, make it a CSV (unlikely)
			if(get_post_meta($post->ID, $key, FALSE)) { // If the custom field already has a value
				update_post_meta($post->ID, $key, $value);
			} else { // If the custom field doesn't have a value
				add_post_meta($post->ID, $key, $value);
			}
			if(!$value) delete_post_meta($post->ID, $key); // Delete if blank
		}
	}

  update_post_meta( $post_id, 'video_attributes', $custom_meta );

};

add_action('save_post', 'rbd_save_video_meta', 1, 2); // save the custom fields



// Scripts
function add_video_base_files(){

    if ( shortcode_exists('video') ) {
        wp_register_script( 'video-script', plugins_url( 'public/js/video-base.min.js', __FILE__ ), array('jquery') ,'1.1', true);
        wp_register_style( 'video-styles',  plugins_url( 'public/css/video-base.min.css', __FILE__ ));
    };
    
}
add_action( 'wp_enqueue_scripts', 'add_video_base_files' );

// Shortcodes
add_shortcode('video', 'rbd_video_shortcode');

include('public/shortcode.php');

?>
