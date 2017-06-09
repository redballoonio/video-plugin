<?php

global $modal_number;
global $modals_html;
$modals_html = 0;
$modals_html = '';


/**
 * Outputs the video shortcode onto the page:
 */
function rbd_video_shortcode( $atts, $content = null)  {
    global $modal_number;
    global $modals_html;

    wp_enqueue_script( 'video-script' );
    wp_enqueue_style( 'video-styles' );

    extract( shortcode_atts( array(
                'id'                => '',                // id of the video - required to the hook into the video post
                'youtube_id'        => '',                // url of the video - if no id and want to link to a youtube video embed
                'title'             => 'show',            // SHOW/HIDE the title - default is show.
                'title_style'       => '',                // How to display the title of the page. Default is blank. "overlayed" absolutly positions the title over the video in the bottom left hand corner.
                'thumbnail'         => 'show',            // whether or not to SHOW/HIDE the thumbnail. default to show and only available on embedded video.
                'excerpt'           => 'hide',            // whether to SHOW, HIDE or replace the excerpt.
                'type'              => 'embed',           // As a MODAL or as an in post EMBED? Default is embed.
                'iframe_url'        => '',                 // URL to use if id and youtube_id haven't been set.
                'modal_options'   => '',                 // comma seperated list of options for the modal
                'gallery_options'   => ''                 // comma seperated list of options for the gallery
            ), $atts
        )
    );

    // Count for the video to add unique IDs to each video.
    static $video_count = 0;

    $video_output = ''; // output variable

    $YT_id_array = array(); // intialise $YT_id_array with correct scope

    // If user selects a gallery:
    $id_array = explode(',', $id);
    $youtube_id_array = explode(',', preg_replace('/\s+/', '', $youtube_id) );

    if ((count($id_array) > 1 OR count($youtube_id_array) > 1) AND $type !== 'gallery' ) {
        return "<p><strong>Please only enter a single ID, or set the type to gallery.</strong></p>";
    }

    if ($type === 'gallery') {
        $excerpt_html = '';
        $title_out = '';
        if (count($id_array) > 1 ){
            $id_count = 0;
            foreach ($id_array as $id_n) {
                $YT_id_array[$id_count] = get_post_meta( intval($id_n), '_video_url_id', true);;
                $id_count++;
            }
        } elseif (count($youtube_id_array) > 1) {
            $YT_id_array = $youtube_id_array;
        } else {
            return 'Error';
        }
    } else {
        //if we have an id, get the video post and populate based on users options
        if ( !empty($youtube_id) OR !empty($id) ){
            if ( !empty($id) ) {
                $video                  = get_post( $id );
                $video_url_id		    = get_post_meta($video->ID, '_video_url_id', true);
                $video_excerpt           = apply_filters('the_excerpt', get_post_field('post_excerpt', $video));

        		if ( !empty($youtube_id) ) {
                    $video_YT_ID         = $youtube_id;
                } else {
                    $video_YT_ID         = $video_url_id;
                }

                // decide to show/hide excerpt
                if ( $excerpt === 'show') {
                    $excerpt_html       = '<div class="excerpt">'.$video_excerpt.'</div>';
                } else {
                    $excerpt_html       = '';
                }
                // decide to replace the title
                if ( !empty($video->post_title) ) {
                    $title_out           = $video->post_title;
                }
                // we can also only show video thumbs videos with id's, but tat code is currently below.
            } else {
                $video_YT_ID             = $youtube_id;
                $title_out               = $content;
                $excerpt_html            = '';
            }

            $video_ar= get_ar_from_youtube_id($video_YT_ID);
            $video_ar_padding             = 'padding-bottom:'.strval($video_ar).'%;';


            // Creates the HTML for the thumbnail.
            if ( $thumbnail === 'show' && !empty($id) && has_post_thumbnail($id) ) {

                $thumb_code_src           =  wp_get_attachment_image_src(get_post_thumbnail_id($id), 'full');

                // Fix for multi post thumbnails
                if (gettype($thumb_code_src) === 'array') {
                    $thumb_code_src = $thumb_code_src[0];
                }
            } else {
                $thumb_code_src           = 'http://img.youtube.com/vi/'.$video_YT_ID.'/hqdefault.jpg';
            }

            $thumbnailHTML              = '<div class="rbd-video-thumbnail" style="background-image:url('.$thumb_code_src.');"><div class="play-icon"></div></div>';

            $iframeHTML                 = '<div class="rbd-iframe-element" data-id="'.$video_YT_ID.'" id="video_element_'.$video_count.'"></div>';

            $host                       = 'youtube';

        } else {
            $title_out                   = '';
            $video_ar_padding             = 'padding-bottom:56.25%;';
            $thumbnailHTML              = '';
            $excerpt_html                = '';
            $iframeHTML                 = '<iframe src="'.$iframe_url.'" frameborder="0" allowfullscreen="" width="660" height="340"></iframe>';
            $host                       = '';
        }

        // Creates html for video title.
        $titleHTML                      = '';
        if ( $title != 'hide' ) {
            if (strlen($content) > 0) {
                $titleHTML              = '<div class="rbd-video-title '.$title_style.'"><h4>' . $content . '</h4></div>';
            } else if (strlen($title_out) > 0 ) {
                $titleHTML              = '<div class="rbd-video-title '.$title_style.'"><h4>' . $title_out . '</h4></div>';
            }
        }
    }

    $embed = ''; // Gets added in the main content area where the shortcode is.
    $modal = ''; // Gets added at the bottom of the page.
    if ($type === 'gallery'){

        static $video_gallery_count = 0;

        $video_output .= '<div id="video-base-carousel_'.$video_gallery_count.'" class="rbd-carousel slide rbd-video-base-carousel" data-ride="carousel" data-interval="false"><div class="rbd-carousel-inner">';

        $output_n = 0;

        $iterations = count($YT_id_array) - 1;

        $host                       = 'youtube';
        $indicators_html             = ''; // circluar buttons that control the
        $thumbnails_html             = '';
        $arrows_html                 = '';
        if ( strpos($gallery_options, 'arrows') !== false) {
            $arrows_html .= '<a class="rbd-carousel-control left" href="#video-base-carousel_'.$video_gallery_count.'" data-slide="prev"><span class="glyphicon glyphicon-chevron-left"></span></a>';
            $arrows_html .= '<a class="rbd-carousel-control right" href="#video-base-carousel_'.$video_gallery_count.'" data-slide="next"><span class="glyphicon glyphicon-chevron-right"></span></a>';
        }

        if (strpos($gallery_options, 'indicators') !== false) {
            $indicators_html .= '<ol class="rbd-carousel-indicators">';
        }

        if (strpos($gallery_options, 'thumbnails') !== false) {
            $thumbnails_html .= '<ul class="rbd-video-thumbnail-controls">';
        }
        while ($output_n <= $iterations) {
            // Loop through all of the videos and output them here:
            $titleHTML = '';
            if (count($id_array)>1) {
                $title_out = get_the_title($id_array[$output_n]);
            } else {
                $title_out = '';
            }
            if ( $title != 'hide' ) {
                if (strlen($content) > 0) {
                    $titleHTML              = '<div class="video-title '.$title_style.'"><h4>' . $content . '</h4></div>';
                } else if (strlen($title_out) > 0 ) {
                    $titleHTML              = '<div class="video-title '.$title_style.'"><h4>' . $title_out . '</h4></div>';
                }
            }
            $video_ar = get_ar_from_youtube_id($YT_id_array[$output_n]);
            $video_ar_padding             = 'padding-bottom:'.strval($video_ar).'%;';

            $iframeHTML                 = '<div class="rbd-iframe-element" data-id="'.$YT_id_array[$output_n].'" id="video_element_'.$video_count.'"></div>';
            if ( count($id_array) > 1 && has_post_thumbnail( $id_array[$output_n] ) ) {
                $thumb_code_src           =  wp_get_attachment_image_src(get_post_thumbnail_id($id_array[$output_n]), 'full');
                if (gettype($thumb_code_src) === 'array') {
                    $thumb_code_src = $thumb_code_src[0];
                }
            } else {
                $thumb_code_src           = 'http://img.youtube.com/vi/'.$YT_id_array[$output_n].'/hqdefault.jpg';
            }
            $thumbnailHTML              = '<div class="rbd-video-thumbnail" style="background-image:url('.$thumb_code_src.');"><div class="play-icon"></div></div>';
            $active_item = '';

            if ($output_n === 0){
                $active_item = 'active';
            }
            $embed                  = '';
            $embed                 .= '<div class="rbd-item '.$active_item.' ">';
            $embed                 .= '<div class="rbd-video-base type--gallery '.$host.'" id="video_base_'.$video_count.'">';
            $embed                 .= '<div class="rbd-video-content">';
            $embed                 .= $titleHTML;
            $embed                 .= '<div class="rbd-iframe-wrap" style="'.$video_ar_padding.'">';
            $embed                 .= $iframeHTML;
            $embed                 .= '</div><!--iframe-wrap outer--></div><!--video-content-->';
            $embed                 .= '</div><!-- video_base_'.$video_count.' -->';
            $embed                 .= '</div><!--/item-->';

            if (strpos($gallery_options, 'indicators') !== false) {
                $indicators_html .= '<li data-target="#video-base-carousel_'.$video_gallery_count.'" data-slide-to="'.$output_n.'" class="'.$active_item.'"></li>';
            }
            if (strpos($gallery_options, 'thumbnails') !== false) {
                if (strpos($gallery_options, 'thumbnails-title') !== false) {
                    $thumbnails_html .= '<li style="width:'.(100/($iterations+1)).'%"><div data-target="#video-base-carousel_'.$video_gallery_count.'" data-slide-to="'.$output_n.'" class="rbd-thumbnail-control"><div class="video-title">'.$title_out.'</div><div class="rbd-thumbnail-controls-image" style="background-image:url('.$thumb_code_src.'); '.$video_ar_padding.'"></div></div></li>';
                } else {
                    $thumbnails_html .= '<li style="width:'.(100/($iterations+1)).'%"><div data-target="#video-base-carousel_'.$video_gallery_count.'" data-slide-to="'.$output_n.'" class="rbd-thumbnail-control"><div class="rbd-thumbnail-controls-image" style="background-image:url('.$thumb_code_src.'); '.$video_ar_padding.'"></div></div></li>';
                }
            }
            $video_output          .= $embed;
            $video_count++;
            $output_n++;
        }

        if (strpos($gallery_options, 'indicators') !== false) {
            $indicators_html .= '</ol>';
        }
        if (strpos($gallery_options, 'pagination') !== false) {
            $thumbnails_html .= '</ul>';
        }

        $video_output          .= '</div>';
        $video_output          .= $arrows_html;
        $video_output          .= $indicators_html;
        $video_output          .= '</div><!--video_gallery_'.$video_gallery_count.'-->';
        $video_output          .= $thumbnails_html;

        $video_gallery_count++;

    } else {

        if ( $type === 'embed' ) {
            $embed                 .= '<div class="rbd-video-base type--embed '.$host.'" id="video_base_'.$video_count.'">';
            $embed                 .= '<div class="rbd-video-content">';
            $embed                 .= $titleHTML;
            $embed                 .= '<div class="rbd-iframe-wrap" style="'.$video_ar_padding.'">';
            $embed                 .= $iframeHTML;
            $embed                 .= $thumbnailHTML;
            $embed                 .= '</div><!--iframe-wrap outer--></div><!--video-content-->';
            $embed                 .= $excerpt_html;
            $embed                 .= '</div><!-- video_base_'.$video_count.' -->';
            if ( !empty($youtube_id) OR !empty($id) ){
                $video_count++;
            }
        }
        if ($type === 'modal'){

            $modal_number++;
            $video_base_modal         = $modal_number;

            $embed                 .= '<div class="rbd-video-base '.$host.'" id="video_base_'.$video_count.'">';
            $embed                 .= '<div class="rbd-video-content" onclick="jQuery(this).addClass(\'modal-open\');jQuery(\'#video-modal-'.$video_base_modal.'\').modal(\'show\');">';
            $embed                 .= $titleHTML;
            $embed                 .= '<div class="rbd-iframe-wrap" style="'.$video_ar_padding.'">';
            $embed                 .= $thumbnailHTML;
            $embed                 .= '</div><!--iframe-wrap--></div><!--video-content-->';
            $embed                 .= $excerpt_html;
            $embed                 .= '</div><!-- video_base_'.$video_count.' -->';

            // This content gets output by the modals function above the footer;
            $modal                 .= '<div id="video-modal-'.$video_base_modal.'" class="rbd-modal fade play-on-open" role="dialog">';
            $modal                 .= '<div class="modal-dialog"><div class="rbd-modal-content text-center">';
            if (strpos($modal_options, 'cross')!== false){
                $modal             .= '<button type="button" class="close" data-dismiss="modal">&times;</button>';
            }
            $modal                 .= '<div class="rbd-video-base type--modal youtube">';
            $modal                 .= '<div class="rbd-iframe-wrap" style="'.$video_ar_padding.'">';
            $modal                 .= $iframeHTML;
            $modal                 .= '</div><!--iframe-wrap--></div><!--video-base-->';
            if (strpos($modal_options, 'button')!== false){
                $modal             .= '<button type="button" class="btn btn-default" data-dismiss="modal">Close</button>';
            }
            $modal                 .= '</div><!-- Modal content--></div><!-- Modal dialog-->';
            $modal                 .= '</div><!--video-modal-'.$video_base_modal.'-->';
            if ( !empty($youtube_id) OR !empty($id) ){
                $video_count++;
            }
        }
    }

    $modals_html .= $modal; // Adds modal to global variable so they can all get brought out together.

    if ($type === 'embed' || $type === 'modal') {
        $video_output = $embed;
    }

    // $video_output .= $modal;

    return $video_output;
}


add_action( 'wp_footer', 'rbd_video_footercontent', 1);

function rbd_video_footercontent(){
    global $modal_number;
    global $modals_html;
    if ($modal_number > 0){
        echo '<div id="rbd-modals-wrap">'.$modals_html.'</div>';
    }
}

/**
 * Gets the aspect ratio of a youtube video by id
 * @param (String) $the_video_id - Youtube video ID
 * @return (Number) - The aspect ratio
 */

function get_ar_from_youtube_id($the_video_id){
    $video_yt_info_json            = @file_get_contents('https://www.youtube.com/oembed?url=https://www.youtube.com/watch?v='.$the_video_id.'&format=json');
    if( $video_yt_info_json === FALSE ) {
        // Fallback padding value:
        $video_ar                = 56.25;
    } else {
        // Decodes json and calculates the aspect ratio.
        $video_yt_info            = json_decode($video_yt_info_json);
        $video_ar                = (intval($video_yt_info->height) / intval($video_yt_info->width)) * 100;
    }
    return $video_ar;
}

?>