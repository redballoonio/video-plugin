<?php
if ( ! defined('ABSPATH') ) exit;

class RBVB_Bunny_Offload {

  const META_URL      = '_rbvb_bunny_url';
  const META_PATH     = '_rbvb_bunny_remote_path';
  const META_ZONE     = '_rbvb_bunny_storage_zone';
  const META_ENDPOINT = '_rbvb_bunny_storage_endpoint';
  const META_ERR      = '_rbvb_bunny_error';
  const META_BYTES    = '_rbvb_bunny_bytes'; // store original bytes so usage works even if local file deleted

  /**
   * Hard limits (NOT editable in settings; change here only)
   */
  private const LIMIT_MAX_FILES = 20;    // max offloaded MP4 attachments
  private const LIMIT_TOTAL_MB  = 1024;  // total offloaded MP4 bytes (approx from local file sizes), in MB
  private const LIMIT_MAX_MB    = 150;   // max single MP4 size to offload, in MB

  /**
   * If true, blocks MP4 upload before it completes (better UX, but no fallback)
   * If false, allows WP upload and just skips Bunny offload (fallback to WP URL).
   */
  private const BLOCK_UPLOAD_EARLY = false;

  public static function init() {

    // Always show attachment field if url exists (harmless), but only offload when enabled.
    add_filter('wp_get_attachment_url', [__CLASS__, 'filter_attachment_url'], 20, 2);
    add_filter('attachment_fields_to_edit', [__CLASS__, 'attachment_fields_to_edit'], 20, 2);

    // Notices (Media Library / uploader)
    add_action('admin_notices', [__CLASS__, 'admin_notices']);

    // Only attach offload actions when enabled
    if ( ! self::enabled() ) return;

    // Optional folder override UI on media uploader
    add_action('post-plupload-upload-ui', [__CLASS__, 'render_upload_ui']);
    add_action('admin_enqueue_scripts', [__CLASS__, 'enqueue_admin_assets']);

    // Enforcement: early (pre-upload) and late (offload step)
    add_filter('wp_handle_upload_prefilter', [__CLASS__, 'upload_prefilter'], 20);

    add_action('add_attachment', [__CLASS__, 'handle_add_attachment'], 20);
    add_action('delete_attachment', [__CLASS__, 'handle_delete_attachment'], 20);
  }

  public static function enabled(): bool {
    return (int) RBVB_Settings::get('bunny_enabled', 0) === 1;
  }

  public static function get_settings(): array {
    // All stored under rbvb_settings via RBVB_Settings
    return [
      'storage_access_key' => (string) RBVB_Settings::get('bunny_storage_access_key', ''),
      'storage_endpoint'   => (string) RBVB_Settings::get('bunny_storage_endpoint', 'storage.bunnycdn.com'),
      'storage_zone'       => (string) RBVB_Settings::get('bunny_storage_zone', ''),
      'base_folder'        => (string) RBVB_Settings::get('bunny_base_folder', 'video'),
      'keep_wp_subdirs'    => (string) RBVB_Settings::get('bunny_keep_wp_subdirs', '1'),
      'delete_local'       => (string) RBVB_Settings::get('bunny_delete_local', '0'),
      'pullzone_base_url'  => rtrim((string) RBVB_Settings::get('bunny_pullzone_base_url', ''), '/'),
    ];
  }

  /* -------------------------------------------------------------------------
   * Admin notices for "skipped offload" warnings
   * ---------------------------------------------------------------------- */

  private static function notice_key(): string {
    $uid = get_current_user_id();
    return 'rbvb_bunny_notice_' . (int) $uid;
  }

  private static function set_notice(string $message, string $type = 'warning'): void {
    // type: success | warning | error | info
    set_transient(self::notice_key(), [
      'message' => $message,
      'type'    => $type,
    ], 60);
  }

  public static function admin_notices(): void {
    if (!is_admin()) return;

    $screen = function_exists('get_current_screen') ? get_current_screen() : null;
    if (!$screen) return;

    // Only show on media upload/library screens
    if (!in_array($screen->base, ['upload', 'media'], true)) return;

    $notice = get_transient(self::notice_key());
    if (!is_array($notice) || empty($notice['message'])) return;

    delete_transient(self::notice_key());

    $type = preg_replace('/[^a-z]/', '', (string) ($notice['type'] ?? 'warning'));
    if (!in_array($type, ['success','warning','error','info'], true)) $type = 'warning';

    printf(
      '<div class="notice notice-%s is-dismissible"><p>%s</p></div>',
      esc_attr($type),
      esc_html((string) $notice['message'])
    );
  }

  /* -------------------------------------------------------------------------
   * Optional upload UI helpers (folder override)
   * ---------------------------------------------------------------------- */

  public static function render_upload_ui() {
    // Appears on Media > Add New (classic uploader UI)
    echo '<div style="margin-top:10px;padding:10px;background:#fff;border:1px solid #ccd0d4;">';
    echo '<label for="rbvb_bunny_folder"><strong>Bunny folder override (optional)</strong></label><br/>';
    echo '<input id="rbvb_bunny_folder" type="text" class="regular-text" placeholder="e.g. video or clientfolder/video" />';
    echo '<p class="description" style="margin:6px 0 0;">If set, this overrides the Base folder for this upload only.</p>';
    echo '</div>';
  }

  public static function enqueue_admin_assets($hook) {
    if ( ! in_array($hook, ['media-new.php', 'upload.php'], true) ) return;

    wp_add_inline_script('plupload-handlers', "
      (function($){
        function applyFolder(uploader){
          var fld = $('#rbvb_bunny_folder').val() || '';
          uploader.settings.multipart_params = uploader.settings.multipart_params || {};
          uploader.settings.multipart_params.rbvb_bunny_folder = fld;
        }
        $(document).on('ready', function(){
          if (typeof wp !== 'undefined' && wp.Uploader && wp.Uploader.queue && wp.Uploader.queue.uploader){
            var up = wp.Uploader.queue.uploader;
            $('#rbvb_bunny_folder').on('change keyup', function(){ applyFolder(up); });
            applyFolder(up);
          }
        });
      })(jQuery);
    ");
  }

  /* -------------------------------------------------------------------------
   * Limits helpers
   * ---------------------------------------------------------------------- */

  private static function max_total_bytes(): int {
    return (int) self::LIMIT_TOTAL_MB * 1024 * 1024;
  }

  private static function max_file_bytes_effective(): int {
    $limit = (int) self::LIMIT_MAX_MB * 1024 * 1024;

    // Also clamp to WP's max upload size so we don't show confusing numbers
    if (function_exists('wp_max_upload_size')) {
      $wpmax = (int) wp_max_upload_size();
      if ($wpmax > 0) {
        $limit = min($limit, $wpmax);
      }
    }
    return $limit;
  }

  private static function human_mb(int $bytes): string {
    if ($bytes <= 0) return '0MB';
    return (string) round($bytes / 1024 / 1024, 2) . 'MB';
  }

  /**
   * Approximate "usage" using local attachment file sizes for MP4s
   * that have been successfully offloaded (have META_URL).
   */
  private static function get_usage_stats(): array {
    $q = new WP_Query([
      'post_type'      => 'attachment',
      'post_status'    => 'inherit',
      'posts_per_page' => -1,
      'fields'         => 'ids',
      'no_found_rows'  => true,
      'meta_key'       => self::META_URL,
      'meta_compare'   => 'EXISTS',
      'tax_query'      => [
        [
          'taxonomy' => 'attachment_category',
          'field'    => 'term_id',
          'terms'    => [], // ignored, attachments don't have default cats; kept empty intentionally
          'operator' => 'IN',
        ],
      ],
    ]);

    // WP_Query with attachment_category is pointless; remove. Keep it simple:
    // We'll just re-run with only needed args if above returns something odd.
    if (!is_array($q->posts)) {
      $ids = [];
    } else {
      $ids = $q->posts;
    }

    // If the above query returned 0 because of weird tax_query, redo without it
    if (empty($ids)) {
      $q2 = new WP_Query([
        'post_type'      => 'attachment',
        'post_status'    => 'inherit',
        'posts_per_page' => -1,
        'fields'         => 'ids',
        'no_found_rows'  => true,
        'meta_key'       => self::META_URL,
        'meta_compare'   => 'EXISTS',
      ]);
      $ids = is_array($q2->posts) ? $q2->posts : [];
    }

    $total = 0;
    $count = 0;

    foreach ($ids as $id) {
      $mime = get_post_mime_type($id);
      if ($mime !== 'video/mp4') continue;

      $count++;

      // Prefer stored bytes (works even if local is deleted)
      $stored = (int) get_post_meta($id, self::META_BYTES, true);
      if ($stored > 0) {
        $total += $stored;
        continue;
      }

      // Fallback to local filesize if available
      $file = get_attached_file($id);
      if ($file && file_exists($file)) {
        $total += (int) filesize($file);
      }
    }

    return [
      'count'       => $count,
      'total_bytes' => $total,
      'total_mb'    => (int) floor($total / 1024 / 1024),
    ];
  }

  /* -------------------------------------------------------------------------
   * Early enforcement (optional) – runs before upload is handled
   * ---------------------------------------------------------------------- */

  public static function upload_prefilter($file) {
    if (!is_admin() || !self::enabled()) return $file;
    if (!is_array($file)) return $file;

    $name = (string) ($file['name'] ?? '');
    $type = (string) ($file['type'] ?? '');
    $size = (int)    ($file['size'] ?? 0);

    $is_mp4 = ($type === 'video/mp4') || (preg_match('/\.mp4$/i', $name) === 1);
    if (!$is_mp4) return $file;

    $usage = self::get_usage_stats();

    // Per-file cap (clamped to WP max upload)
    $max_file = self::max_file_bytes_effective();
    if ($max_file > 0 && $size > $max_file) {
      $max_mb = (int) floor($max_file / 1024 / 1024);

      if (self::BLOCK_UPLOAD_EARLY) {
        $file['error'] = "This MP4 exceeds the Bunny offload per-video limit ({$max_mb}MB).";
        return $file;
      }

      self::set_notice("This MP4 will upload to WordPress, but it won't be offloaded to Bunny because it exceeds the per-video limit ({$max_mb}MB).", 'warning');
      return $file;
    }

    // Count cap
    if ($usage['count'] >= self::LIMIT_MAX_FILES) {
      if (self::BLOCK_UPLOAD_EARLY) {
        $file['error'] = 'Bunny offload video limit reached (' . (int)$usage['count'] . ' / ' . (int)self::LIMIT_MAX_FILES . ').';
        return $file;
      }

      self::set_notice(
        'This MP4 will upload to WordPress, but Bunny offload is skipped: video limit reached (' .
        (int) $usage['count'] . ' / ' . (int) self::LIMIT_MAX_FILES . ').',
        'warning'
      );
      return $file;
    }

    // Total size cap
    if (($usage['total_bytes'] + $size) > self::max_total_bytes()) {
      if (self::BLOCK_UPLOAD_EARLY) {
        $file['error'] = 'Bunny offload storage limit reached (' . self::human_mb((int)$usage['total_bytes']) . ' / ' . (int)self::LIMIT_TOTAL_MB . 'MB).';
        return $file;
      }

      self::set_notice(
        'This MP4 will upload to WordPress, but Bunny offload is skipped: storage limit reached (' .
        self::human_mb((int)$usage['total_bytes']) . ' / ' . (int) self::LIMIT_TOTAL_MB . 'MB).',
        'warning'
      );
      return $file;
    }

    return $file;
  }

  /* -------------------------------------------------------------------------
   * Offload step (hard enforcement) – runs after attachment is created
   * ---------------------------------------------------------------------- */

  public static function handle_add_attachment($attachment_id) {
    $mime = get_post_mime_type($attachment_id);
    if ($mime !== 'video/mp4') return;

    $file = get_attached_file($attachment_id);
    if (!$file || !file_exists($file)) return;

    $s = self::get_settings();
    if (empty($s['storage_access_key']) || empty($s['storage_endpoint']) || empty($s['storage_zone'])) {
      // Not configured properly; leave on WP, no offload.
      return;
    }

    // HARD ENFORCEMENT HERE TOO (ensures limits are respected even if early prefilter didn't run)
    $size  = (int) filesize($file);
    $usage = self::get_usage_stats();

    $max_file = self::max_file_bytes_effective();
    if ($max_file > 0 && $size > $max_file) {
      $max_mb = (int) floor($max_file / 1024 / 1024);
      $msg = "Uploaded to WordPress. Bunny offload skipped: exceeds per-video limit ({$max_mb}MB).";
      update_post_meta($attachment_id, self::META_ERR, $msg);
      self::set_notice($msg, 'warning');
      return;
    }

    if ($usage['count'] >= self::LIMIT_MAX_FILES) {
      $msg = "Uploaded to WordPress. Bunny offload skipped: limit reached ({$usage['count']} / " . self::LIMIT_MAX_FILES . ").";
      update_post_meta($attachment_id, self::META_ERR, $msg);
      self::set_notice($msg, 'warning');
      return;
    }

    if (($usage['total_bytes'] + $size) > self::max_total_bytes()) {
      $msg = "Uploaded to WordPress. Bunny offload skipped: storage limit reached (" . self::human_mb((int)$usage['total_bytes']) . " / " . self::LIMIT_TOTAL_MB . "MB).";
      update_post_meta($attachment_id, self::META_ERR, $msg);
      self::set_notice($msg, 'warning');
      return;
    }

    // Build remote path
    $uploads  = wp_upload_dir();
    $basedir  = rtrim($uploads['basedir'], '/\\') . DIRECTORY_SEPARATOR;
    $relative = ltrim(str_replace($basedir, '', $file), '/\\'); // 2026/01/my.mp4

    $remote_base = trim($s['base_folder'], "/ \t\n\r\0\x0B");

    // Per-upload override
    if (!empty($_REQUEST['rbvb_bunny_folder'])) {
      $remote_base = trim(sanitize_text_field(wp_unslash($_REQUEST['rbvb_bunny_folder'])), "/ \t\n\r\0\x0B");
    }

    if ($s['keep_wp_subdirs'] !== '1') {
      $relative = basename($relative);
    }

    $remote_path = trim($remote_base . '/' . $relative, '/');

    // Upload
    $err = '';
    $ok  = self::bunny_put_file($file, $s['storage_endpoint'], $s['storage_zone'], $remote_path, $s['storage_access_key'], $err);

    if (!$ok) {
      $msg = $err ?: 'Upload failed';
      update_post_meta($attachment_id, self::META_ERR, $msg);
      self::set_notice("Uploaded to WordPress. Bunny offload failed: {$msg}", 'warning');
      return;
    }

    // Public URL (prefer Pull Zone)
    if (!empty($s['pullzone_base_url'])) {
      $public_url = rtrim($s['pullzone_base_url'], '/') . '/' . ltrim($remote_path, '/');
    } else {
      // fallback (not recommended)
      $public_url = 'https://' . preg_replace('#^https?://#', '', trim($s['storage_endpoint'])) . '/'
        . rawurlencode($s['storage_zone']) . '/'
        . str_replace('%2F', '/', rawurlencode($remote_path));
    }

    update_post_meta($attachment_id, self::META_URL, esc_url_raw($public_url));
    update_post_meta($attachment_id, self::META_PATH, $remote_path);
    update_post_meta($attachment_id, self::META_ZONE, $s['storage_zone']);
    update_post_meta($attachment_id, self::META_ENDPOINT, $s['storage_endpoint']);
    delete_post_meta($attachment_id, self::META_ERR);

    self::set_notice('MP4 uploaded and offloaded to Bunny successfully.', 'success');

    // Optionally delete local file (careful!)
    if ($s['delete_local'] === '1') {
      @unlink($file);
    }
  }

  /* -------------------------------------------------------------------------
   * Media URL + attachment UI
   * ---------------------------------------------------------------------- */

  public static function filter_attachment_url($url, $attachment_id) {
    $bunny = get_post_meta($attachment_id, self::META_URL, true);
    return $bunny ? $bunny : $url;
  }

  public static function attachment_fields_to_edit($fields, $post) {
    $bunny = get_post_meta($post->ID, self::META_URL, true);
    $path  = get_post_meta($post->ID, self::META_PATH, true);
    $err   = get_post_meta($post->ID, self::META_ERR, true);

    $fields['rbvb_bunny_url'] = [
      'label' => 'Bunny URL',
      'input' => 'html',
      'html'  => $bunny
        ? '<code style="display:block;white-space:nowrap;overflow:auto;max-width:100%;">' . esc_html($bunny) . '</code>'
        : '<em>Not offloaded.</em>',
      'helps' => $path ? ('Remote path: ' . esc_html($path)) : '',
    ];

    if ($err) {
      $fields['rbvb_bunny_err'] = [
        'label' => 'Bunny offload status',
        'input' => 'html',
        'html'  => '<span style="color:#b32d2e;">' . esc_html($err) . '</span>',
      ];
    }

    return $fields;
  }

  /* -------------------------------------------------------------------------
   * Delete remote file when attachment deleted (best effort)
   * ---------------------------------------------------------------------- */

  public static function handle_delete_attachment($attachment_id) {
    $remote = get_post_meta($attachment_id, self::META_PATH, true);
    $zone   = get_post_meta($attachment_id, self::META_ZONE, true);
    $ep     = get_post_meta($attachment_id, self::META_ENDPOINT, true);

    if (!$remote || !$zone || !$ep) return;

    $s = self::get_settings();
    if (empty($s['storage_access_key'])) return;

    self::bunny_delete_file($ep, $zone, $remote, $s['storage_access_key']);
  }

  /* -------------------------------------------------------------------------
   * Bunny API helpers
   * ---------------------------------------------------------------------- */

  private static function encode_path($path): string {
    $parts = explode('/', ltrim($path, '/'));
    $parts = array_map('rawurlencode', $parts);
    return implode('/', $parts);
  }

  private static function bunny_put_file($local_file, $endpoint, $zone, $remote_path, $access_key, &$err = null): bool {
    $endpoint = preg_replace('#^https?://#', '', trim($endpoint));
    $zone_enc = rawurlencode($zone);
    $url = 'https://' . $endpoint . '/' . $zone_enc . '/' . self::encode_path($remote_path);

    if (function_exists('curl_init')) {
      $fp = fopen($local_file, 'rb');
      if (!$fp) { $err = 'Could not open local file for reading.'; return false; }

      $ch = curl_init($url);
      curl_setopt($ch, CURLOPT_PUT, true);
      curl_setopt($ch, CURLOPT_INFILE, $fp);
      curl_setopt($ch, CURLOPT_INFILESIZE, filesize($local_file));
      curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'AccessKey: ' . $access_key,
        'Content-Type: application/octet-stream',
        'Accept: application/json',
      ]);
      curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
      curl_setopt($ch, CURLOPT_TIMEOUT, 300);

      $resp = curl_exec($ch);
      $code = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);

      if ($resp === false) {
        $err = 'cURL error: ' . curl_error($ch);
        curl_close($ch);
        fclose($fp);
        return false;
      }

      curl_close($ch);
      fclose($fp);

      if ($code < 200 || $code >= 300) {
        $err = 'Bunny upload failed. HTTP ' . $code . ' Response: ' . (is_string($resp) ? $resp : '');
        return false;
      }
      return true;
    }

    // Fallback (loads whole file into memory; not ideal for large MP4s)
    $body = @file_get_contents($local_file);
    if ($body === false) { $err = 'Could not read local file.'; return false; }

    $res = wp_remote_request($url, [
      'method'  => 'PUT',
      'timeout' => 300,
      'headers' => [
        'AccessKey'     => $access_key,
        'Content-Type'  => 'application/octet-stream',
        'Accept'        => 'application/json',
      ],
      'body' => $body,
    ]);

    if (is_wp_error($res)) { $err = $res->get_error_message(); return false; }

    $code = wp_remote_retrieve_response_code($res);
    if ($code < 200 || $code >= 300) {
      $err = 'Bunny upload failed. HTTP ' . $code . ' Response: ' . wp_remote_retrieve_body($res);
      return false;
    }

    return true;
  }

  private static function bunny_delete_file($endpoint, $zone, $remote_path, $access_key): bool {
    $endpoint = preg_replace('#^https?://#', '', trim($endpoint));
    $url = 'https://' . $endpoint . '/' . rawurlencode($zone) . '/' . self::encode_path($remote_path);

    $res = wp_remote_request($url, [
      'method'  => 'DELETE',
      'timeout' => 60,
      'headers' => [
        'AccessKey' => $access_key,
        'Accept'    => 'application/json',
      ],
    ]);

    if (is_wp_error($res)) return false;
    $code = wp_remote_retrieve_response_code($res);
    return ($code >= 200 && $code < 300);
  }
}
