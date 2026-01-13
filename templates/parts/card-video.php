<?php // Video Card Template: templates/parts/card-video.php
if ( ! defined('ABSPATH') ) exit;

// Get Video Shortcode
$videoPost = get_post_meta( get_the_ID(), 'video_post', true );
$videoPost = is_string($videoPost) ? trim($videoPost) : '';

// Get Video Content
$content = get_post_field('post_content', get_the_ID());
$content = is_string($content) ? trim($content) : '';
?>
<article class="card card-video h-100" id="<?php echo get_the_ID();?>">
  <div class="card-img-top card-video-img  mb-1">
    <?php
      if ( $videoPost ) {
        //echo do_shortcode($sc);
        echo apply_filters('the_content', $videoPost);
      } else {
        echo '<div class="p-3"><em>No video found.</em></div>';
      }
    ?>
  </div>
  <div class="card-body card-video-body">
    <a href="<?php the_permalink( get_the_ID() );?>" title="<?php the_title();?>">
      <h3 class="h5 card-title card-video-title mb-1"><?php the_title(); ?></h3>
    </a>
    
    <?php if ( $content !== '' ) : ?>
      <div class="card-text card-video-text">
        <?php // Render the editor content with normal WP formatting + shortcodes
          echo apply_filters('the_content', $content);
        ?>
      </div>
    <?php endif; ?>
  </div>
  <div class="card-footer card-video-footer mt-1">
    <a href="<?php the_permalink( get_the_ID() );?>" title="<?php the_title();?>">Learn More</a>
  </div>
</article>