<?php // Video Archive Template: templates/archive-video.php
if ( ! defined('ABSPATH') ) exit;
get_header();

global $wp_query;
$paged = max(1, (int) get_query_var('paged'));

$cols_mobile  = (int) RBVB_Settings::get('cols_mobile', 1);
$cols_tablet  = (int) RBVB_Settings::get('cols_tablet', 2);
$cols_desktop = (int) RBVB_Settings::get('cols_desktop', 3);
$gap          = (int) RBVB_Settings::get('grid_gap', 24);

// clamp for safety
$cols_mobile  = max(1, min(2, $cols_mobile));
$cols_tablet  = max(1, min(3, $cols_tablet));
$cols_desktop = max(1, min(4, $cols_desktop));
$gap          = max(0, min(64, $gap));
?>

<main class="rb-video-index py-5">
  <div class="container">

    <header class="rb-video-index__header mb-3">
      <h1 class="h2 mb-0"><?php echo esc_html( RBVB_Settings::get('index_title', 'Videos') ); ?></h1>

      <?php // Video Intro Description
      $desc = RBVB_Settings::get('index_description', '');
      if ( is_string($desc) && trim($desc) !== '' ) : ?>
        <div class="rb-video-index__desc mt-2">
          <?php echo apply_filters('the_content', $desc); ?>
        </div>
      <?php endif; ?>
    </header>

    <?php if ( have_posts() ) : ?>
      <div class="rbvb-grid rb-video-index__row"
        data-cols-mobile="<?php echo esc_attr($cols_mobile); ?>"
        data-cols-tablet="<?php echo esc_attr($cols_tablet); ?>"
        data-cols-desktop="<?php echo esc_attr($cols_desktop); ?>"
        style="--data-gap: <?php echo esc_attr($gap); ?>px;"
      >
        <?php while ( have_posts() ) : the_post(); ?>
          <div class="card-video-column">
            <?php // Get Video Card Template
              $theme_card = locate_template(array('rb-video-base/parts/card-video.php'));
              if ( $theme_card ) {
                include $theme_card;
              } else {
                include RBVB_PATH . 'templates/parts/card-video.php';
              }
            ?>
          </div>
        <?php endwhile; ?>
      </div>

      <nav class="mt-5 pagination news-pagination">
        <?php // Pagination
          echo paginate_links(array(
            'base'      => str_replace(999999999, '%#%', esc_url(get_pagenum_link(999999999))),
            'format'    => '',
            'current'   => $paged,
            'total'     => (int) $wp_query->max_num_pages,
            'type'      => 'list',
            'prev_text'    => 'Previous',
            'next_text'    => 'Next'
          ));
        ?>
      </nav>
    <?php else : ?>
      <p>No videos found.</p>
    <?php endif; ?>
  </div>
</main>

<?php get_footer(); ?>
