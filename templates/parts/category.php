<?php // Get the Category
$cats = get_the_category(); // works because 'category' is attached to the CPT
if ( ! empty($cats) ) : ?>
  <div class="card-video-cats">
    <?php foreach ( $cats as $cat ) : ?>
      <a class="card-video-cats--chip" href="<?php echo esc_url( get_category_link($cat->term_id) ); ?>">
        <span class="icon feather icon-tag"></span>
        <span><?php echo esc_html( $cat->name ); ?></span>
      </a>
    <?php endforeach; ?>
  </div>
<?php endif; ?>