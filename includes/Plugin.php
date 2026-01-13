<?php
if ( ! defined('ABSPATH') ) exit;

final class RBVB_Plugin {

    private static $instance = null;

    public static function instance() {
        if ( self::$instance === null ) {
            self::$instance = new self();
            self::$instance->setup();
        }
        return self::$instance;
    }

    private function setup() {
        require_once RBVB_PATH . 'includes/CustomPostType.php';
        require_once RBVB_PATH . 'includes/Metabox.php';
        require_once RBVB_PATH . 'includes/Enqueue.php';
        require_once RBVB_PATH . 'includes/Templates.php';
        require_once RBVB_PATH . 'includes/TinyMCE.php';

        RBVB_CPT::init();
        RBVB_Metabox::init();
        RBVB_Enqueue::init();
        RBVB_Templates::init();
        RBVB_TinyMCE::init();

        // Video Taxonomy
        // require_once RBVB_PATH . 'includes/Taxonomy.php';
        // RBVB_Taxonomy::init();

        // Video Settings Page
        require_once RBVB_PATH . 'includes/Settings.php';
        RBVB_Settings::init();

        // Bunny CDN Offload for MP4 video
        require_once RBVB_PATH . 'includes/BunnyOffload.php';
        RBVB_Bunny_Offload::init();
    }

    public static function activate() {
        // Register CPT and flush rewrites
        require_once RBVB_PATH . 'includes/CustomPostType.php';
        RBVB_CPT::register();
        flush_rewrite_rules();
    }

    public static function deactivate() {
        flush_rewrite_rules();
    }

    private function __construct() {}
    private function __clone() {}
    public function __wakeup() {}
}
