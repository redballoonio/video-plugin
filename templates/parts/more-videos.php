<?php
if ( ! defined('ABSPATH') ) exit;

global $post;

$current_id = get_the_ID();
$target = 3;
$items = array();

// 1) Category-first (if category exists)
$categories = wp_get_post_categories($current_id);

if ( ! empty($categories) ) {
  $cat_query = new WP_Query(array(
    'post_type'           => 'video',
    'post_status'         => 'publish',
    'posts_per_page'      => $target,
    'orderby'             => 'date',
    'order'               => 'DESC',
    'post__not_in'        => array($current_id),
    'category__in'        => array((int) $categories[0]),
    'ignore_sticky_posts' => true,
    'no_found_rows'       => true,
  ));

  if ( $cat_query->have_posts() ) {
    $items = $cat_query->posts; // WP_Post[]
  }
  wp_reset_postdata();
}

// 2) Top up with latest videos (no category restriction)
$missing = $target - count($items);

if ( $missing > 0 ) {
  $exclude = array_merge(
    array($current_id),
    wp_list_pluck($items, 'ID')
  );

  $recent_query = new WP_Query(array(
    'post_type'           => 'video',
    'post_status'         => 'publish',
    'posts_per_page'      => $missing,
    'orderby'             => 'date',
    'order'               => 'DESC',
    'post__not_in'        => $exclude,
    'ignore_sticky_posts' => true,
    'no_found_rows'       => true,
  ));

  if ( $recent_query->have_posts() ) {
    $items = array_merge($items, $recent_query->posts);
  }
  wp_reset_postdata();
}

if ( ! empty($items) ) : ?>
<section class="section-more-videos py-5 bg-light">
  <div class="container">
    <h2 class="h2 mb-2 section-more-videos__title">More Videos</h2>
    <div class="row g-4 section-more-videos__row">
      <?php foreach ( $items as $p ) : ?>
        <?php // Post set as $p
          $post = $p;
          setup_postdata($post);
        ?>
        <div class="card-video-column col-24 col-sm-12 col-md-8 mb-3">
          <?php // Get Video Card Template
            $theme_card = locate_template(array('rb-video-base/parts/card-video.php'));
            if ( $theme_card ) {
              include $theme_card;
            } else {
              include RBVB_PATH . 'templates/parts/card-video.php';
            }
          ?>
        </div>

      <?php endforeach; ?>
      <?php wp_reset_postdata(); ?>
    </div>
  </div>
</section>
<?php endif; ?>