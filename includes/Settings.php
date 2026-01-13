<?php
if ( ! defined('ABSPATH') ) exit;

class RBVB_Settings {

    const OPTION_KEY = 'rbvb_settings';
    const PAGE_SLUG  = 'rbvb-settings';
    const GROUP      = 'rbvb_settings_group';

    public static function init() {
        add_action('admin_menu', array(__CLASS__, 'add_menu'));
        add_action('admin_init', array(__CLASS__, 'register_settings'));

        // Admin UI for tabs + Bunny toggle + test
        add_action('admin_enqueue_scripts', array(__CLASS__, 'enqueue_admin_assets'));

        // AJAX test
        add_action('wp_ajax_rbvb_bunny_test', array(__CLASS__, 'ajax_bunny_test'));
    }

    public static function enqueue_admin_assets($hook) {
        // Only on Videos > Settings
        if ( $hook !== 'video_page_' . self::PAGE_SLUG ) return;

        // Ensure admin.js is actually enqueued on this page
        wp_enqueue_script(
            'rbvb-admin',
            RBVB_URL . 'js/admin.js',
            array('jquery'),
            RBVB_VERSION,
            true
        );

        wp_localize_script('rbvb-admin', 'RBVBSettings', array(
            'ajaxUrl' => admin_url('admin-ajax.php'),
            'nonce'   => wp_create_nonce('rbvb_bunny_test'),
        ));
    }

    public static function ajax_bunny_test() {
        if ( ! current_user_can('manage_options') ) {
            wp_send_json_error(['message' => 'Permission denied.'], 403);
        }

        check_ajax_referer('rbvb_bunny_test', 'nonce');

        $enabled  = (int) self::get('bunny_enabled', 0) === 1;
        $zone     = (string) self::get('bunny_storage_zone', '');
        $endpoint = (string) self::get('bunny_storage_endpoint', 'storage.bunnycdn.com');
        $key      = (string) self::get('bunny_storage_access_key', '');

        if ( ! $enabled ) {
            wp_send_json_error(['message' => 'Bunny offload is disabled.'], 400);
        }
        if ( ! $zone || ! $endpoint || ! $key ) {
            wp_send_json_error(['message' => 'Missing required Bunny settings. Save settings first.'], 400);
        }

        // Test call: list root folder (GET /{zone}/)
        $endpoint = preg_replace('#^https?://#', '', trim($endpoint));
        $url = 'https://' . $endpoint . '/' . rawurlencode($zone) . '/';

        $res = wp_remote_get($url, [
            'timeout' => 20,
            'headers' => [
            'AccessKey' => $key,
            'Accept'    => 'application/json',
            ],
        ]);

        if ( is_wp_error($res) ) {
            wp_send_json_error(['message' => $res->get_error_message()], 500);
        }

        $code = (int) wp_remote_retrieve_response_code($res);
        if ($code < 200 || $code >= 300) {
            $body = wp_remote_retrieve_body($res);
            $body = is_string($body) ? trim($body) : '';
            wp_send_json_error(['message' => "Bunny API error. HTTP {$code}. " . ($body ? $body : '')], 500);
        }

        wp_send_json_success(['message' => 'Connection OK: Bunny CDN Storage API responded successfully.']);
    }


    public static function add_menu() {
        add_submenu_page(
            'edit.php?post_type=video',
            __('RB Video Base Settings', 'rbvb'),
            __('Settings', 'rbvb'),
            'manage_options',
            self::PAGE_SLUG,
            array(__CLASS__, 'render_page')
        );
    }

    public static function register_settings() {
        register_setting(self::GROUP, self::OPTION_KEY, array(__CLASS__, 'sanitize'));

        // --- Index page content ---
        add_settings_section(
            'rbvb_section_index',
            __('Video Index Page', 'rbvb'),
            '__return_false',
            self::PAGE_SLUG
        );

        add_settings_field(
            'index_title',
            __('Video Index Title', 'rbvb'),
            array(__CLASS__, 'field_index_title'),
            self::PAGE_SLUG,
            'rbvb_section_index'
        );

        add_settings_field(
            'index_description',
            __('Video Index Description', 'rbvb'),
            array(__CLASS__, 'field_index_description'),
            self::PAGE_SLUG,
            'rbvb_section_index'
        );

        add_settings_field(
            'posts_per_page',
            __('Videos per page', 'rbvb'),
            array(__CLASS__, 'field_posts_per_page'),
            self::PAGE_SLUG,
            'rbvb_section_index'
        );

        // --- Grid settings ---
        add_settings_section(
            'rbvb_section_grid',
            __('Video Grid Layout', 'rbvb'),
            '__return_false',
            self::PAGE_SLUG
        );

        add_settings_field(
            'cols_desktop',
            __('Columns (Desktop)', 'rbvb'),
            array(__CLASS__, 'field_cols_desktop'),
            self::PAGE_SLUG,
            'rbvb_section_grid'
        );

        add_settings_field(
            'cols_tablet',
            __('Columns (Tablet)', 'rbvb'),
            array(__CLASS__, 'field_cols_tablet'),
            self::PAGE_SLUG,
            'rbvb_section_grid'
        );

        add_settings_field(
            'cols_mobile',
            __('Columns (Mobile)', 'rbvb'),
            array(__CLASS__, 'field_cols_mobile'),
            self::PAGE_SLUG,
            'rbvb_section_grid'
        );

        add_settings_field(
            'grid_gap',
            __('Grid Gap (px)', 'rbvb'),
            array(__CLASS__, 'field_grid_gap'),
            self::PAGE_SLUG,
            'rbvb_section_grid'
        );

        add_settings_section(
            'rbvb_section_bunny',
            __('Bunny CDN Offload (MP4)', 'rbvb'),
            '__return_false',
            self::PAGE_SLUG
        );

        add_settings_field('bunny_enabled', __('Enable Bunny offload', 'rbvb'), [__CLASS__,'field_bunny_enabled'], self::PAGE_SLUG, 'rbvb_section_bunny');
        add_settings_field('bunny_storage_zone', __('Storage Zone Name', 'rbvb'), [__CLASS__,'field_bunny_storage_zone'], self::PAGE_SLUG, 'rbvb_section_bunny');
        add_settings_field('bunny_storage_endpoint', __('Storage Endpoint', 'rbvb'), [__CLASS__,'field_bunny_storage_endpoint'], self::PAGE_SLUG, 'rbvb_section_bunny');
        add_settings_field('bunny_storage_access_key', __('Storage AccessKey (Zone password)', 'rbvb'), [__CLASS__,'field_bunny_storage_access_key'], self::PAGE_SLUG, 'rbvb_section_bunny');
        add_settings_field('bunny_pullzone_base_url', __('Pull Zone Base URL', 'rbvb'), [__CLASS__,'field_bunny_pullzone_base_url'], self::PAGE_SLUG, 'rbvb_section_bunny');
        add_settings_field('bunny_base_folder', __('Base folder in Storage', 'rbvb'), [__CLASS__,'field_bunny_base_folder'], self::PAGE_SLUG, 'rbvb_section_bunny');
        add_settings_field('bunny_keep_wp_subdirs', __('Keep WP /YYYY/MM folders', 'rbvb'), [__CLASS__,'field_bunny_keep_wp_subdirs'], self::PAGE_SLUG, 'rbvb_section_bunny');
        add_settings_field('bunny_delete_local', __('Delete local MP4 after upload', 'rbvb'), [__CLASS__,'field_bunny_delete_local'], self::PAGE_SLUG, 'rbvb_section_bunny');

    }

    public static function sanitize($input) {
        $out = array();

        $out['index_title'] = isset($input['index_title'])
            ? sanitize_text_field($input['index_title'])
            : '';

        $out['index_description'] = isset($input['index_description'])
            ? wp_kses_post($input['index_description'])
            : '';

        $pp = isset($input['posts_per_page']) ? absint($input['posts_per_page']) : 0;
        if ($pp < 1) $pp = 3;
        if ($pp > 100) $pp = 100;
        $out['posts_per_page'] = $pp;

        // Grid columns (clamped)
        $cols_desktop = isset($input['cols_desktop']) ? absint($input['cols_desktop']) : 0;
        $cols_tablet  = isset($input['cols_tablet'])  ? absint($input['cols_tablet'])  : 0;
        $cols_mobile  = isset($input['cols_mobile'])  ? absint($input['cols_mobile'])  : 0;

        // sensible defaults
        if ($cols_desktop < 1) $cols_desktop = 3;
        if ($cols_tablet  < 1) $cols_tablet  = 2;
        if ($cols_mobile  < 1) $cols_mobile  = 1;

        // clamp ranges
        $out['cols_desktop'] = max(1, min(6, $cols_desktop)); // allow up to 6 if you want
        $out['cols_tablet']  = max(1, min(4, $cols_tablet));
        $out['cols_mobile']  = max(1, min(2, $cols_mobile));

        // Gap in px (used as CSS var inline, so can be any number)
        $gap = isset($input['grid_gap']) ? absint($input['grid_gap']) : 0;
        if ($gap < 0) $gap = 0;
        if ($gap > 80) $gap = 80;
        $out['grid_gap'] = $gap;

        // Bunny CDN Options
        $out['bunny_enabled'] = ! empty($input['bunny_enabled']) ? 1 : 0;

        $out['bunny_storage_zone'] = sanitize_text_field($input['bunny_storage_zone'] ?? '');
        $out['bunny_storage_endpoint'] = sanitize_text_field($input['bunny_storage_endpoint'] ?? 'storage.bunnycdn.com');
        $out['bunny_storage_access_key'] = sanitize_text_field($input['bunny_storage_access_key'] ?? '');

        $out['bunny_pullzone_base_url'] = rtrim(esc_url_raw($input['bunny_pullzone_base_url'] ?? ''), '/');
        $out['bunny_base_folder'] = trim(sanitize_text_field($input['bunny_base_folder'] ?? 'video'));

        $out['bunny_keep_wp_subdirs'] = ! empty($input['bunny_keep_wp_subdirs']) ? '1' : '0';
        $out['bunny_delete_local'] = ! empty($input['bunny_delete_local']) ? '1' : '0';

        return $out;
    }

    public static function get($key, $default = '') {
        $opts = get_option(self::OPTION_KEY, array());
        return isset($opts[$key]) && $opts[$key] !== '' ? $opts[$key] : $default;
    }

    public static function render_page() {
        if ( ! current_user_can('manage_options') ) return;

        // Bunny summary/test flags
        $bunny_enabled  = (int) self::get('bunny_enabled', 0) === 1;
        $zone           = (string) self::get('bunny_storage_zone', '');
        $endpoint       = (string) self::get('bunny_storage_endpoint', 'storage.bunnycdn.com');
        $pullzone       = (string) self::get('bunny_pullzone_base_url', '');
        $base_folder    = (string) self::get('bunny_base_folder', 'video');
        $has_min_config = $bunny_enabled && $zone && $endpoint && self::get('bunny_storage_access_key', '');

        ?>
        <div class="wrap rbvb-settings-wrap">
            <h1><?php esc_html_e('RB Video Base Settings', 'rbvb'); ?></h1>

            <h2 class="nav-tab-wrapper" style="margin-bottom: 16px;">
                <a href="#rbvb-tab-index" class="nav-tab nav-tab-active" data-rbvb-tab="rbvb-tab-index">Video Index</a>
                <a href="#rbvb-tab-bunny" class="nav-tab" data-rbvb-tab="rbvb-tab-bunny">Bunny CDN Offload (MP4)</a>
            </h2>

            <form method="post" action="options.php">
                <?php settings_fields(self::GROUP); ?>

                <div id="rbvb-tab-index" class="rbvb-tab-panel" style="display:block;">
                    <?php
                    self::render_section('rbvb_section_index', __('Video Index Page', 'rbvb'));
                    self::render_section('rbvb_section_grid', __('Video Grid Layout', 'rbvb'));
                    ?>
                </div>

                <div id="rbvb-tab-bunny" class="rbvb-tab-panel" style="display:none;">
                    <?php
                        self::render_section('rbvb_section_bunny', __('Bunny CDN Offload (MP4)', 'rbvb'), array(
                            'wrap_id' => 'rbvb-bunny-fields-wrap'
                        ));
                    ?>

                    <?php if ( $bunny_enabled ) : ?>
                    <div class="card " style="padding:12px 14px; margin: 0 0 16px; max-width: unset; border-left: 4px solid #00a32a;">
                        <h3 style="margin:0 0 8px;"><strong>Bunny CDN Connection Summary / Test</strong></h3>
                        <ul style="margin:0 0 8px; padding-left: 18px; list-style: disc; ">
                            <li><strong>Storage Zone:</strong> <?php echo esc_html($zone ?: '—'); ?></li>
                            <li><strong>Endpoint:</strong> <?php echo esc_html($endpoint ?: '—'); ?></li>
                            <li><strong>Base folder:</strong> <?php echo esc_html($base_folder ?: '—'); ?></li>
                            <li><strong>Pull Zone:</strong> <?php echo esc_html($pullzone ?: '— (optional but recommended)'); ?></li>
                        </ul>

                        <p style="margin:0;">
                        <button type="button"
                                class="button button-secondary"
                                id="rbvb-bunny-test"
                                <?php disabled(!$has_min_config); ?>>
                            Test connection
                        </button>
                        <span id="rbvb-bunny-test-result" style="margin-left:10px;"></span>
                        <?php if (!$has_min_config): ?>
                            <span style="margin-left:10px; opacity:.8;">(Fill required fields + save first)</span>
                        <?php endif; ?>
                        </p>
                    </div>
                    <?php endif; ?>

                    <div class="card" style="padding:12px 14px; margin: 0 0 16px;max-width: unset;">
                        <h3 style="margin:0 0 8px;"><strong>Where to find these Bunny details</strong></h3>
                        <ol style="margin:0; padding-left: 18px;">
                            <li><strong>Storage Zone Name</strong>: Bunny dashboard → <em>Storage</em> → select your Storage Zone → copy its name.</li>
                            <li><strong>Storage AccessKey</strong> (zone password): Bunny dashboard → <em>Storage</em> → Storage Zone → <em>FTP &amp; API Access</em> / <em>Password</em>.</li>
                            <li><strong>Storage Endpoint</strong>: Bunny dashboard → <em>Storage</em> → Storage Zone → shows the region hostname (e.g. <code>uk.storage.bunnycdn.com</code>).</li>
                            <li><strong>Pull Zone Base URL</strong>: Bunny dashboard → <em>Pull Zones</em> → open Pull Zone → copy <code>https://xxxx.b-cdn.net</code>.</li>
                            <li><strong>Base folder</strong>: your chosen folder inside Storage (e.g. <code>video</code> or <code>client/video</code>).</li>
                        </ol>
                    </div>
                </div>

                <?php submit_button(); ?>
            </form>
        </div>
        <?php
    }

    private static function render_section($section_id, $title, $args = array()) {
        $wrap_id = isset($args['wrap_id']) ? $args['wrap_id'] : '';

        echo '<div class="rbvb-settings-section" ' . ($wrap_id ? 'id="'.esc_attr($wrap_id).'"' : '') . '>';
        echo '<h2>' . esc_html($title) . '</h2>';
        echo '<table class="form-table" role="presentation">';
        do_settings_fields(self::PAGE_SLUG, $section_id);
        echo '</table>';
        echo '</div>';
    }

    /*
        public static function render_page() {
            if ( ! current_user_can('manage_options') ) return;
            ?>
            <div class="wrap">
                <h1><?php esc_html_e('RB Video Base Settings', 'rbvb'); ?></h1>

                <form method="post" action="options.php">
                    <?php
                    settings_fields(self::GROUP);
                    do_settings_sections(self::PAGE_SLUG);
                    submit_button();
                    ?>
                </form>
            </div>
            <?php
        }
    */

    // ---------- Fields ----------

    public static function field_index_title() {
        $val = self::get('index_title', '');
        ?>
        <input type="text"
               name="<?php echo esc_attr(self::OPTION_KEY); ?>[index_title]"
               value="<?php echo esc_attr($val); ?>"
               class="regular-text"
               placeholder="Videos" />
        <p class="description">Fallback is “Videos” if left blank.</p>
        <?php
    }

    public static function field_index_description() {
        $val = self::get('index_description', '');
        wp_editor(
            $val,
            'rbvb_index_description',
            array(
                'textarea_name' => self::OPTION_KEY . '[index_description]',
                'textarea_rows' => 8,
                'media_buttons' => true,
                'teeny'         => false,
            )
        );
        echo '<p class="description">Shown below the title on the /videos index page.</p>';
    }

    public static function field_posts_per_page() {
        $val = (int) self::get('posts_per_page', 3);
        ?>
        <input type="number"
               min="1"
               max="100"
               name="<?php echo esc_attr(self::OPTION_KEY); ?>[posts_per_page]"
               value="<?php echo esc_attr($val); ?>"
               class="small-text" />
        <p class="description">Controls pagination on /videos.</p>
        <?php
    }

    // --- Grid fields ---

    public static function field_cols_desktop() {
        $val = (int) self::get('cols_desktop', 3);
        echo '<div style="display: flex; gap: 12px;">';
            self::render_select(
                self::OPTION_KEY . '[cols_desktop]',
                $val,
                array(1,2,3,4,5,6,7,8,9,10,11,12)
            );
            echo '<p class="description">Controls the number of cards per row on desktop.</p>';
        echo '</div>';
    }

    public static function field_cols_tablet() {
        $val = (int) self::get('cols_tablet', 2);
        echo '<div style="display: flex; gap: 12px;">';
            self::render_select(
                self::OPTION_KEY . '[cols_tablet]',
                $val,
                array(1,2,3,4,5,6,7,8,9,10)
            );
            echo '<p class="description">Controls the number of cards per row on tablet.</p>';
        echo '</div>';
    }

    public static function field_cols_mobile() {
        $val = (int) self::get('cols_mobile', 1);
        echo '<div style="display: flex; gap: 12px;">';
            self::render_select(
                self::OPTION_KEY . '[cols_mobile]',
                $val,
                array(1,2,3,4,5,6)
            );
            echo '<p class="description">Controls the number of cards per row on mobile.</p>';
        echo '</div>';
    }

    public static function field_grid_gap() {
        $val = (int) self::get('grid_gap', 20);
        ?>
        <div style="display: flex; gap: 12px; flex-wrap: wrap;">
            <div>
                <input type="number"
                    min="0"
                    max="80"
                    name="<?php echo esc_attr(self::OPTION_KEY); ?>[grid_gap]"
                    value="<?php echo esc_attr($val); ?>"
                    class="small-text" />
                <span>px</span>
            </div>
            <p class="description">Spacing between cards in the grid (used as CSS grid gap).</p>
        </div>
        <?php
    }

    private static function render_select($name, $current, $choices) {
        echo '<select name="' . esc_attr($name) . '">';
        foreach ($choices as $n) {
            printf(
                '<option value="%d"%s>%d</option>',
                (int) $n,
                selected((int)$current, (int)$n, false),
                (int) $n
            );
        }
        echo '</select>';
    }

    public static function field_bunny_enabled() {
        $val = (int) self::get('bunny_enabled', 0);
        ?>
        <label>
            <input type="checkbox" name="<?php echo esc_attr(self::OPTION_KEY); ?>[bunny_enabled]" value="1" <?php checked($val, 1); ?> />
            Enable MP4 offload to Bunny Edge Storage on upload
        </label>
        <?php
    }

    public static function field_bunny_storage_zone() {
        $val = self::get('bunny_storage_zone', '');
        echo '<input type="text" class="regular-text" name="'.esc_attr(self::OPTION_KEY).'[bunny_storage_zone]" value="'.esc_attr($val).'"/>';
    }

    public static function field_bunny_storage_endpoint() {
        $val = self::get('bunny_storage_endpoint', 'storage.bunnycdn.com');
        echo '<input type="text" class="regular-text" name="'.esc_attr(self::OPTION_KEY).'[bunny_storage_endpoint]" value="'.esc_attr($val).'"/>';
        echo '<p class="description">Examples: storage.bunnycdn.com, uk.storage.bunnycdn.com, ny.storage.bunnycdn.com</p>';
    }

    public static function field_bunny_storage_access_key() {
        $val = self::get('bunny_storage_access_key', '');
        echo '<input type="password" class="regular-text" autocomplete="new-password" name="'.esc_attr(self::OPTION_KEY).'[bunny_storage_access_key]" value="'.esc_attr($val).'"/>';
    }

    public static function field_bunny_pullzone_base_url() {
        $val = self::get('bunny_pullzone_base_url', '');
        echo '<input type="url" class="regular-text" name="'.esc_attr(self::OPTION_KEY).'[bunny_pullzone_base_url]" value="'.esc_attr($val).'"/>';
        echo '<p class="description">Example: https://yourpullzone.b-cdn.net</p>';
    }

    public static function field_bunny_base_folder() {
        $val = self::get('bunny_base_folder', '');
        echo '<input type="text" class="regular-text" name="'.esc_attr(self::OPTION_KEY).'[bunny_base_folder]" value="'.esc_attr($val).'"/>';
    }

    public static function field_bunny_keep_wp_subdirs() {
        $val = (string) self::get('bunny_keep_wp_subdirs', '1');
        ?>
        <label><input type="checkbox" name="<?php echo esc_attr(self::OPTION_KEY); ?>[bunny_keep_wp_subdirs]" value="1" <?php checked($val, '1'); ?>/> Yes</label>
        <?php
    }

    public static function field_bunny_delete_local() {
    $val = (string) self::get('bunny_delete_local', '0');
    ?>
    <label><input type="checkbox" name="<?php echo esc_attr(self::OPTION_KEY); ?>[bunny_delete_local]" value="1" <?php checked($val, '1'); ?>/> Yes</label>
    <p class="description"><strong>Warning:</strong> only enable if you’re sure you want MP4 files removed from local uploads after a successful offload.</p>
    <?php
    }
}
