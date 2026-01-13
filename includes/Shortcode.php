<?php // includes/shortcode.php
function video_shortcode( $atts, $content = null)  {
    wp_enqueue_script( 'video-script' );
    //wp_enqueue_style( 'video-styles' );

    extract( shortcode_atts( array(
                'id'                => '',                // id of the video - required to the hook into the video post
                'youtube_id'        => '',                // url of the video - if no id and want to link to a youtube video embed
                'title'             => '',            // SHOW/HIDE the title - default is show.
                'title_style'       => '',                // How to display the title of the page. Default is blank. "overlayed" absolutly positions the title over the video in the bottom left hand corner.
                'thumbnail'         => '',            // whether or not to SHOW/HIDE the thumbnail. default to show and only available on embedded video.
                'excerpt'           => 'hide',            // whether to SHOW, HIDE or replace the excerpt.
                'type'              => 'embed',           // As a MODAL or as an in post EMBED? Default is embed. Gallery
                'iframe_url'        => '',                 // URL to use if id and youtube_id haven't been set.
                'gallery_options'   => '',                 // comma seperated list of options for the gallery
                'mp4'               => '',
                'preload'           => 'metadata',
                'loop'           => '',
                'poster'            => '',
                'poster_id'         => '',
                'playsinline'         => '',
                'poster_img_url'    => '',
                'autoplay'          => ''
            ), $atts
        )
    );

    // Default video behaviour:
    if ( strlen($mp4) > 0 ){
        return wp_video_shortcode(array(
            'mp4' => $mp4,
            'preload' => $preload,
            'poster' => $poster,
            'muted' => 'muted',
            'loop' => $loop,
            'playsinline' => $playsinline,
            'autoplay' => $autoplay
        ));
    }

    // Count for the video to add unique IDs to each video.
    static $videoCount = 0;

    $video_output = ''; // output variable

    $YT_id_array = array(); // intialise $YT_id_array with correct scope

    // If user selects a gallery:
    $id_array = explode(',', $id);
    $youtube_id_array = explode(',', preg_replace('/\s+/', '', $youtube_id) );
    if ((count($id_array) > 1 OR count($youtube_id_array) > 1) AND $type !== 'gallery' ) {
        return "<p><strong>Please only enter a single ID, or set the type to gallery.</strong></p>";
    }
    if ($type === 'gallery') {
        $excerptHTML = '';
        $titleOUT = '';
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
                $videoExcerpt           = apply_filters('the_excerpt', get_post_field('post_excerpt', $video));

        		if ( !empty($youtube_id) ) {
                    $videoYT_ID         = $youtube_id;
                } else {
                    $videoYT_ID         = $video_url_id;
                }

                // decide to show/hide excerpt
                if ( $excerpt === 'show') {
                    $excerptHTML        = '<div class="excerpt">'.$videoExcerpt.'</div>';
                } else {
                    $excerptHTML        = '';
                }
                // decide to replace the title
                if ( !empty($video->post_title) ) {
                    $titleOUT           = $video->post_title;
                }
                // we can also only show video thumbs videos with id's, but tat code is currently below.
            } else {
                $videoYT_ID             = $youtube_id;
                $titleOUT               = $content;
                $excerptHTML            = '';
            }

            $videoAR = getARfromYoutubeID($videoYT_ID);
            $videoARPadding             = 'padding-bottom:'.strval($videoAR).'%;';


            // Creates the HTML for the thumbnail.
            if ( $thumbnail === 'show' && !empty($id) && has_post_thumbnail($id) ) {

                $thumbcodesrc           =  wp_get_attachment_image_src(get_post_thumbnail_id($id), 'full');

                // Fix for multi post thumbnails
                if (gettype($thumbcodesrc) === 'array') {
                    $thumbcodesrc = $thumbcodesrc[0];
                }
            } else {
                //$thumbcodesrc           = 'https://img.youtube.com/vi/'.$videoYT_ID.'/hqdefault.jpg';
                $thumbcodesrc           = 'https://img.youtube.com/vi/'.$videoYT_ID.'/maxresdefault.jpg';
            }

            $thumbnailHTML              = '<div class="video-thumbnail" style="background-image:url('.$thumbcodesrc.');"><div class="play-icon"></div></div>';

            $iframeHTML                 = '<div class="iframe-element" data-id="'.$videoYT_ID.'" id="video_element_'.$videoCount.'"></div>';

            $host                       = 'youtube';

        } else {
            $titleOUT                   = '';
            $videoARPadding             = 'padding-bottom:56.25%;';
            $thumbnailHTML              = '';
            $excerptHTML                = '';
            $iframeHTML                 = '<iframe src="'.$iframe_url.'" frameborder="0" allowfullscreen="" width="660" height="340"></iframe>';
            $host                       = '';
        }

        // Creates html for video title.
        $titleHTML                      = '';
        if ( $title != 'hide' ) {
            if (strlen($content) > 0) {
                $titleHTML              = '<div class="video-title '.$title_style.'"><h4>' . $content . '</h4></div>';
            } else if (strlen($titleOUT) > 0 ) {
                $titleHTML              = '<div class="video-title '.$title_style.'"><h4>' . $titleOUT . '</h4></div>';
            }
        }
    }

    $embed = ''; // Gets added in the main content area where the shortcode is.
    $modal = ''; // Gets added at the bottom of the page.
    if ($type === 'gallery'){

        static $video_gallery_count = 0;

        $video_output .= '<div id="video-base-carousel_'.$video_gallery_count.'" class="carousel slide video-base-carousel" data-ride="carousel" data-interval="false"><div class="carousel-inner">';

        $output_n = 0;

        $iterations = count($YT_id_array) - 1;

        $host                       = 'youtube';
        $indicatorsHTML             = ''; // circluar buttons that control the
        $paginationHTML             = '';
        $arrowsHTML                 = '';

        
        if ( strpos($gallery_options, 'arrows') !== false) {
            $arrowsHTML .= '<a class="carousel-control left" href="#video-base-carousel_'.$video_gallery_count.'" data-slide="prev"><span class="glyphicon glyphicon-chevron-left"></span></a>';
            $arrowsHTML .= '<a class="carousel-control right" href="#video-base-carousel_'.$video_gallery_count.'" data-slide="next"><span class="glyphicon glyphicon-chevron-right"></span></a>';
        }

        if (strpos($gallery_options, 'indicators') !== false) {
            $indicatorsHTML .= '<ol class="carousel-indicators">';
        }

        if (strpos($gallery_options, 'thumbnails') !== false) {
            $paginationHTML .= '<ul class="video-thumbnail-controls">';
        }
        while ($output_n <= $iterations) {
            // Loop through all of the videos and output them here:
            $titleHTML = '';
            if (count($id_array)>1) {
                $titleOUT = get_the_title($id_array[$output_n]);
            } else {
                $titleOUT = '';
            }
            if ( $title != 'hide' ) {
                if (strlen($content) > 0) {
                    $titleHTML              = '<div class="video-title '.$title_style.'"><h4>' . $content . '</h4></div>';
                } else if (strlen($titleOUT) > 0 ) {
                    $titleHTML              = '<div class="video-title '.$title_style.'"><h4>' . $titleOUT . '</h4></div>';
                }
            }
            $videoAR = getARfromYoutubeID($YT_id_array[$output_n]);
            $videoARPadding             = 'padding-bottom:'.strval($videoAR).'%;';

            $iframeHTML                 = '<div class="iframe-element" data-id="'.$YT_id_array[$output_n].'" id="video_element_'.$videoCount.'"></div>';
            if ( count($id_array) > 1 && has_post_thumbnail( $id_array[$output_n] ) ) {
                $thumbcodesrc           =  wp_get_attachment_image_src(get_post_thumbnail_id($id_array[$output_n]), 'full');
                if (gettype($thumbcodesrc) === 'array') {
                    $thumbcodesrc = $thumbcodesrc[0];
                }
            } else {
                $thumbcodesrc           = 'https://img.youtube.com/vi/'.$YT_id_array[$output_n].'/hqdefault.jpg';
            }
            $thumbnailHTML              = '<div class="video-thumbnail" style="background-image:url('.$thumbcodesrc.');"><div class="play-icon"></div></div>';
            $activeItem = '';

            if ($output_n === 0){
                $activeItem = 'active';
            }
            $embed                  = '';
            $embed                 .= '<div class="item '.$activeItem.' ">';
            $embed                 .= '<div class="video-base type--gallery '.$host.'" id="video_base_'.$videoCount.'">';
            $embed                 .= '<div class="video-content">';
            $embed                 .= $titleHTML;
            $embed                 .= '<div class="iframe-wrap" style="'.$videoARPadding.'" data-video-id="'.$videoYT_ID.'">';
            $embed                 .= $iframeHTML;
            // $embed                 .= $thumbnailHTML;
            $embed                 .= '</div><!--iframe-wrap outer--></div><!--video-content-->';
            $embed                 .= '</div><!-- video_base_'.$videoCount.' -->';
            $embed                 .= '</div><!--/item-->';

            if (strpos($gallery_options, 'indicators') !== false) {
                $indicatorsHTML .= '<li data-target="#video-base-carousel_'.$video_gallery_count.'" data-slide-to="'.$output_n.'" class="'.$activeItem.'"></li>';
            }
            if (strpos($gallery_options, 'pagination') !== false) {
                if (strpos($gallery_options, 'pagination-title') !== false) {
                    $paginationHTML .= '<li style="width:'.(100/($iterations+1)).'%"><div data-target="#video-base-carousel_'.$video_gallery_count.'" data-slide-to="'.$output_n.'" class="thumbnail-control"><div class="video-title">'.$titleOUT.'</div><div class="thumbnail-controls-image" style="background-image:url('.$thumbcodesrc.'); '.$videoARPadding.'"></div></div></li>';
                } else {
                    $paginationHTML .= '<li style="width:'.(100/($iterations+1)).'%"><div data-target="#video-base-carousel_'.$video_gallery_count.'" data-slide-to="'.$output_n.'" class="thumbnail-control"><div class="thumbnail-controls-image" style="background-image:url('.$thumbcodesrc.'); '.$videoARPadding.'"></div></div></li>';
                }
            }
            $video_output          .= $embed;
            $videoCount++;
            $output_n++;
        }

        if (strpos($gallery_options, 'indicators') !== false) {
            $indicatorsHTML .= '</ol>';
        }
        if (strpos($gallery_options, 'pagination') !== false) {
            $paginationHTML .= '</ul>';
        }

        $video_output          .= '</div>';
        $video_output          .= $arrowsHTML;
        $video_output          .= $indicatorsHTML;
        $video_output          .= '</div><!--video_gallery_'.$video_gallery_count.'-->';
        $video_output          .= $paginationHTML;

        $video_gallery_count++;

    } else {

        if ( $type === 'embed' ) {
            $embed                 .= '<div class="video-base type--embed '.$host.'" id="video_base_'.$videoCount.'">';
            if ( $thumbnail === 'show' ) {
                $embed                 .= '<div class="video-content video-content-thumb">';
            } else {
                $embed                 .= '<div class="video-content video-content-iframe">';
            }
            $embed                 .= $titleHTML;
            $embed                 .= '<div class="iframe-wrap" style="'.$videoARPadding.'" data-video-id="'.$videoYT_ID.'">';
            if ( $thumbnail != 'show' ) {
                $embed                 .= $iframeHTML;
            }
            if($poster_id) {
                // echo $poster_url;
                $poster_url = wp_get_attachment_url( $poster_id );
                $thumbnailHTML = '<div class="video-thumbnail" style="background-image:url('.$poster_url.');"><div class="play-icon fa fa-play"></div></div>';
                $embed                 .= $thumbnailHTML;
            }
            if($poster_img_url) {
                // echo $poster_url;
                $poster_url = $poster_img_url;
                $thumbnailHTML = '<div class="video-thumbnail" style="background-image:url('.$poster_url.');"><div class="play-icon fa fa-play"></div></div>';
                $embed                 .= $thumbnailHTML;
            }
            if ( !empty( $poster_id ) && !empty($poster_img_url) ) {
                if ($videoYT_ID) {
                    $thumbcodesrc           = 'https://img.youtube.com/vi/'.$videoYT_ID.'/maxresdefault.jpg';
                    $thumbnailHTML          = '<div class="video-thumbnail" style="background-image:url('.$thumbcodesrc.');"><div class="play-icon"></div></div>';
                }
            }
            if ( $thumbnail === 'show' ) {
                $embed                 .= $thumbnailHTML;
                if ( $title ) {
                    $embed             .= '<h4 class="video-title overlayed '.$title_style.'"><i class="fa fa-play"></i> ' . $title . '</h4>';
                }
            }
            $embed                 .= '</div><!--iframe-wrap outer--></div><!--video-content-->';
            $embed                 .= $excerptHTML;
            $embed                 .= '</div><!-- video_base_'.$videoCount.' -->';

            if ( !empty($youtube_id) OR !empty($id) ){
                $videoCount++;
            }
        }
        if ($type === 'modal'){

            // Global variables set in ../video-base.php
            $GLOBALS['videoBaseModals'] = $GLOBALS['videoBaseModals'] + 1;
            $videoBaseModal         = $GLOBALS['videoBaseModals'];

            $embed                 .= '<div class="video-base '.$host.'" id="video_base_'.$videoCount.'">';
            //$embed               .= '<div class="video-content" onclick="jQuery(this).addClass(\'modal-open\');jQuery(\'#video-modal-'.$videoBaseModal.'\').modal(\'show\');">';
            //$embed                 .= '<div class="video-content" onclick="this.classList.add(\'modal-open\'); var modalElement = document.getElementById(\'video-modal-' . $videoBaseModal . '\'); if (modalElement) { var bootstrapModal = new bootstrap.Modal(modalElement); bootstrapModal.show(); }">';
            $embed                 .= '<div class="video-content" onclick="openModalWithCompatibility(this, \'video-modal-' . $videoBaseModal . '\');">';
            $embed                 .= $titleHTML;
            $embed                 .= '<div class="iframe-wrap" style="'.$videoARPadding.'" data-video-id="'.$videoYT_ID.'">';
            $embed                 .= $thumbnailHTML;
            $embed                 .= '</div><!--iframe-wrap--></div><!--video-content-->';
            $embed                 .= $excerptHTML;
            $embed                 .= '</div><!-- video_base_'.$videoCount.' -->';

            // This content gets output by the modals function above the footer;
            $modal                 .= '<div id="video-modal-'.$videoBaseModal.'" class="modal fade play-on-open" role="dialog">';
            $modal                 .= '<div class="modal-dialog"><div class="modal-content text-center"><button type="button" class="close" data-bs-dismiss="modal" data-dismiss="modal">&times;</button>';
            $modal                 .= '<div class="video-base type--modal youtube">';
            $modal                 .= '<div class="iframe-wrap" style="'.$videoARPadding.'">';
            $modal                 .= $iframeHTML;
            $modal                 .= '</div><!--iframe-wrap--></div><!--video-base-->';
            $modal                 .= '<button type="button" class="btn btn-default" data-dismiss="modal">Close</button>';
            $modal                 .= '</div><!-- Modal content--></div><!-- Modal dialog-->';
            $modal                 .= '</div><!--video-modal-'.$videoBaseModal.'-->';
            if ( !empty($youtube_id) OR !empty($id) ){
                $videoCount++;
            }
        }
    }

    $GLOBALS['modalsHTML']     .= $modal; // Adds modal to global variable so they can all get brought out together.
    if ($type === 'embed' || $type === 'modal') {
        $video_output = $embed;
    }
    return $video_output;
}
function getARfromYoutubeID($the_video_id){
    $videoYTInfoJSON            = @file_get_contents('https://www.youtube.com/oembed?url=https://www.youtube.com/watch?v='.$the_video_id.'&format=json');
    if( $videoYTInfoJSON === FALSE ) {
        // Fallback padding value:
        $videoAR                = 56.25;
    } else {
        // Decodes json and calculates the aspect ratio.
        $videoYTInfo            = @json_decode($videoYTInfoJSON);

        if (
            $videoYTInfo &&
            isset($videoYTInfo->height) && intval($videoYTInfo->height) > 0 && 
            isset($videoYTInfo->width) && intval($videoYTInfo->width) > 0 ) {
            $videoAR            = (intval($videoYTInfo->height) / intval($videoYTInfo->width)) * 100;
        } else {
            $videoAR            = 56.25;
        }
    }
    return $videoAR;
}
?>
