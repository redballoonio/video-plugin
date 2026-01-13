<?php
if ( ! defined('ABSPATH') ) exit;

class RBVB_Metabox {

  public static function init() {
    add_action('add_meta_boxes', array(__CLASS__, 'add_metabox'));
    add_action('save_post_video', array(__CLASS__, 'save'), 10, 3);
  }

  public static function add_metabox() {
    add_meta_box('video_attributes', 'Video Post', array(__CLASS__, 'render'), 'video', 'normal', 'high');
  }

  public static function render($post) {
    wp_nonce_field('rbvb_video_meta', 'videometa_noncename');

    $video_post = get_post_meta($post->ID, 'video_post', true);
    $video_post = is_string($video_post) ? $video_post : '';

    // Single input value stored so the builder remembers what user entered
    $value = get_post_meta($post->ID, 'video_value', true);
    $value = is_string($value) ? $value : '';

    $poster_url = get_post_meta($post->ID, 'video_poster_url', true);
    $poster_url = is_string($poster_url) ? $poster_url : '';
    ?>

    <div class="rbvb-metabox">
      <p style="margin-bottom:6px;"><strong>Video Shortcode</strong></p>

      <div style="display:flex; gap:8px; align-items:center;">
        <input
          type="text"
          id="rbvb_video_post"
          name="video_post"
          class="widefat"
          value="<?php echo esc_attr($video_post); ?>"
          placeholder='e.g. [video youtube_id="fGox6727qJ4" thumbnail="show"]'
          style="flex:1;"
        />
        <button type="button" class="button" id="rbvb_toggle_builder">
          <?php echo $video_post ? 'Edit Video' : 'Add Video'; ?>
        </button>
      </div>

      <div id="rbvb_builder" style="display:none; margin-top:12px; padding:12px; border:1px solid #ccd0d4; border-radius:6px; background:#fff;">

        <div style="display:flex; gap:10px; align-items:flex-end; flex-wrap:wrap; margin-bottom: 12px;">
          <div style="flex:1; min-width:260px; line-height: normal;">
            <label style="display: block; margin-bottom: 4px;" for="rbvb_value"><strong>YouTube Video ID / URL or MP4 URL</strong></label>
            <input
              type="text"
              name="video_value"
              id="rbvb_value"
              class="widefat"
              value="<?php echo esc_attr($value); ?>"
              placeholder=""
            />
          </div>

          <div style="display:flex; gap:8px;">
            <button type="button" class="button" id="rbvb_pick_mp4">Select MP4 Video</button>
          </div>
        </div>


        <div style="display:flex; gap:10px; align-items:flex-end; flex-wrap:wrap;">
          <div style="flex:1; min-width:260px; line-height: normal;">
            <label style="display: block; margin-bottom: 4px;" for="rbvb_poster_url"><strong>Poster URL</strong> <small>(optional; overrides YouTube thumb)</small></label>
            <input
              type="text"
              name="video_poster_url"
              id="rbvb_poster_url"
              class="widefat"
              value="<?php echo esc_attr($poster_url); ?>"
              placeholder="https://..."
            />
          </div>

          <div style="display:flex; gap:8px;">
            <button type="button" class="button" id="rbvb_pick_poster">Select Video Poster</button>
          </div>
        </div>

        <div style="margin-top:12px; display:flex; gap:8px;">
          <button type="button" class="button button-primary" id="rbvb_apply">Apply</button>
          <button type="button" class="button" id="rbvb_cancel">Cancel</button>
        </div>

        <p style="margin:10px 0 0; color:#646970;">
          Apply updates the shortcode field above. Save/Update the post to store it.
        </p>
      </div>
    </div>
    <?php
  }

  public static function renderBack($post) {
    wp_nonce_field('rbvb_video_meta', 'videometa_noncename');

    $video_type = get_post_meta($post->ID, 'video_type', true);
    if (!$video_type) $video_type = 'youtube';

    $youtube_id = get_post_meta($post->ID, 'video_youtube_id', true);
    $mp4_url    = get_post_meta($post->ID, 'video_mp4_url', true);
    $poster_url = get_post_meta($post->ID, 'video_poster_url', true);
    $video_post = get_post_meta($post->ID, 'video_post', true);
    ?>
      <p><strong>Video Type</strong></p>
      <select name="video_type" id="rbvb_video_type" class="widefat">
        <option value="youtube" <?php selected($video_type, 'youtube'); ?>>YouTube</option>
        <option value="mp4" <?php selected($video_type, 'mp4'); ?>>MP4 (self-hosted)</option>
      </select>

      <div id="rbvb_youtube_fields" style="<?php echo ($video_type === 'youtube') ? '' : 'display:none;'; ?>">
        <p><strong>YouTube ID or URL</strong></p>
        <input type="text" name="video_youtube_id" value="<?php echo esc_attr($youtube_id); ?>" class="widefat"
               placeholder='e.g. fGox6727qJ4 or https://www.youtube.com/watch?v=fGox6727qJ4' />
      </div>

      <div id="rbvb_mp4_fields" style="<?php echo ($video_type === 'mp4') ? '' : 'display:none;'; ?>">
        <p><strong>MP4 URL</strong></p>
        <input type="text" name="video_mp4_url" id="rbvb_mp4_url" value="<?php echo esc_attr($mp4_url); ?>" class="widefat" placeholder="https://..." />
        <p><button type="button" class="button" id="rbvb_pick_mp4">Select MP4 from Media Library</button></p>
      </div>

      <hr>

      <p><strong>Poster Image URL (optional)</strong><br>
      <small>For YouTube: overrides the YouTube thumbnail. For MP4: used as the poster attribute.</small></p>
      <input type="text" name="video_poster_url" id="rbvb_poster_url" value="<?php echo esc_attr($poster_url); ?>" class="widefat" placeholder="https://..." />
      <p><button type="button" class="button" id="rbvb_pick_poster">Select Poster from Media Library</button></p>

      <hr>

      <p><strong>Video Post</strong></p>
      <input type="text" value="<?php echo esc_attr(is_string($video_post) ? $video_post : ''); ?>" class="widefat" />
    <?php
  }

  private static function extract_youtube_id($input) {
    $input = trim((string)$input);
    if ($input === '') return '';

    // Looks like a raw ID
    if ( preg_match('/^[a-zA-Z0-9_-]{6,}$/', $input) ) return $input;

    $parts = wp_parse_url($input);
    if ( empty($parts['host']) ) return '';

    $host = strtolower($parts['host']);

    if ( strpos($host, 'youtu.be') !== false && !empty($parts['path']) ) {
      return trim($parts['path'], '/');
    }

    if ( strpos($host, 'youtube.com') !== false || strpos($host, 'youtube-nocookie.com') !== false ) {
      if ( !empty($parts['query']) ) {
        parse_str($parts['query'], $q);
        if ( !empty($q['v']) ) return $q['v'];
      }
      if ( !empty($parts['path']) && preg_match('~/(shorts|embed)/([^/?#]+)~', $parts['path'], $m) ) {
        return $m[2];
      }
    }

    return '';
  }

  public static function save($post_id, $post, $update) {
    if ( defined('DOING_AUTOSAVE') && DOING_AUTOSAVE ) return;
    if ( wp_is_post_revision($post_id) || wp_is_post_autosave($post_id) ) return;

    if (
      ! isset($_POST['videometa_noncename']) ||
      ! wp_verify_nonce($_POST['videometa_noncename'], 'rbvb_video_meta')
    ) return;

    if ( ! current_user_can('edit_post', $post_id) ) return;

    $video_post = isset($_POST['video_post']) ? wp_kses_post(wp_unslash($_POST['video_post'])) : '';
    $value      = isset($_POST['video_value']) ? sanitize_text_field(wp_unslash($_POST['video_value'])) : '';
    $poster     = isset($_POST['video_poster_url']) ? esc_url_raw(wp_unslash($_POST['video_poster_url'])) : '';

    if ($video_post !== '') update_post_meta($post_id, 'video_post', $video_post);
    else delete_post_meta($post_id, 'video_post');

    update_post_meta($post_id, 'video_value', $value);
    update_post_meta($post_id, 'video_poster_url', $poster);
  }


  public static function saveBackup($post_id, $post, $update) {

    if ( defined('DOING_AUTOSAVE') && DOING_AUTOSAVE ) return;
    if ( wp_is_post_revision($post_id) || wp_is_post_autosave($post_id) ) return;

    if (
      ! isset($_POST['videometa_noncename']) ||
      ! wp_verify_nonce($_POST['videometa_noncename'], 'rbvb_video_meta')
    ) return;

    if ( ! current_user_can('edit_post', $post_id) ) return;

    $video_type = isset($_POST['video_type']) ? sanitize_text_field(wp_unslash($_POST['video_type'])) : 'youtube';
    $poster_url = isset($_POST['video_poster_url']) ? esc_url_raw(wp_unslash($_POST['video_poster_url'])) : '';

    $youtube_raw = isset($_POST['video_youtube_id']) ? sanitize_text_field(wp_unslash($_POST['video_youtube_id'])) : '';
    $youtube_id  = self::extract_youtube_id($youtube_raw);

    $mp4_url = isset($_POST['video_mp4_url']) ? esc_url_raw(wp_unslash($_POST['video_mp4_url'])) : '';

    update_post_meta($post_id, 'video_type', $video_type);
    update_post_meta($post_id, 'video_youtube_id', $youtube_id);
    update_post_meta($post_id, 'video_mp4_url', $mp4_url);
    update_post_meta($post_id, 'video_poster_url', $poster_url);

    // Always thumbnail="show" for YouTube
    // If poster_url set, pass poster_img_url which overrides the thumb in your shortcode
    $shortcode = '';

    if ( $video_type === 'mp4' ) {
      if ( $mp4_url ) {
        $shortcode = '[video mp4="' . esc_attr($mp4_url) . '"';
        if ( $poster_url ) $shortcode .= ' poster="' . esc_attr($poster_url) . '"';
        $shortcode .= ']';
      }
    } else {
      if ( $youtube_id ) {
        $shortcode = '[video youtube_id="' . esc_attr($youtube_id) . '" thumbnail="show"';
        if ( $poster_url ) $shortcode .= ' poster_img_url="' . esc_attr($poster_url) . '"';
        $shortcode .= ']';
      }
    }

    if ( $shortcode ) update_post_meta($post_id, 'video_post', $shortcode);
    else delete_post_meta($post_id, 'video_post');

    update_post_meta($post_id, 'video_attributes', array('video_post' => $shortcode));
  }
}
