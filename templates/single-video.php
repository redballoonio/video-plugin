<?php
if ( ! defined('ABSPATH') ) exit;

get_header();

if ( have_posts() ) :
  while ( have_posts() ) : the_post();

    $videoPost = get_post_meta( get_the_ID(), 'video_post', true );
    $videoPost = is_string($videoPost) ? trim($videoPost) : '';
    ?>

    <main class="rbvb-single-video py-5">
      <div class="container">

        <article <?php post_class('rbvb-single-video__article'); ?>>

          <header class="rbvb-single-video__header mb-4">
            <h1 class="h2 mb-2"><?php the_title(); ?></h1>
          </header>

          <?php if ( $videoPost !== '' ) : ?>
            <div class="rbvb-single-video__embed mb-4">
              <?php echo do_shortcode( $videoPost ); ?>
            </div>
          <?php endif; ?>

          <div class="rbvb-single-video__content">
            <?php the_content(); ?>
          </div>

        </article>

      </div>
    </main>

    <?php
      // "More videos" section (theme can override)
      $theme_more = locate_template(array('rb-video-base/parts/more-videos.php'));
      if ( $theme_more ) {
        include $theme_more;
      } else {
        include RBVB_PATH . 'templates/parts/more-videos.php';
      }
    ?>

    <?php
  endwhile;
endif;

get_footer();
