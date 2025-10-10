<?php
/**
 * WeCoza Clients Plugin - Comprehensive Client and Location Management System
 *
 * A WordPress plugin providing advanced client relationship management with hierarchical
 * client structures, PostgreSQL backend integration, and location management with
 * Google Maps support. Features MVC architecture, SETA compliance, and full CRUD
 * operations for client and location data.
 *
 * @package WeCozaClients
 * @since 1.0.0
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Main WeCoza Clients Plugin class
 */
class WeCoza_Clients_Plugin {

    /**
     * Plugin version
     *
     * @var string
     */
    protected $version;

    /**
     * Plugin name
     *
     * @var string
     */
    protected $plugin_name;

    /**
     * Constructor
     */
    public function __construct() {
        $this->version = WECOZA_CLIENTS_VERSION;
        $this->plugin_name = 'wecoza-clients';
        
        $this->load_dependencies();
        $this->define_admin_hooks();
        $this->define_public_hooks();
    }

    /**
     * Load required dependencies
     */
    private function load_dependencies() {
        // Load the autoloader and bootstrap
        require_once WECOZA_CLIENTS_APP_DIR . 'bootstrap.php';
    }

    /**
     * Define admin hooks
     */
    private function define_admin_hooks() {
        // Admin-specific hooks can be added here if needed in the future
    }

    /**
     * Define public hooks
     */
    private function define_public_hooks() {
        // Initialize shortcodes and AJAX handlers
        add_action('init', array($this, 'init_plugin_features'));
    }

    /**
     * Initialize plugin features
     */
    public function init_plugin_features() {
        // This will be called by the bootstrap to initialize controllers
        // Controllers will register their own shortcodes and AJAX handlers
    }

    /**
     * Run the plugin
     */
    public function run() {
        // Plugin is now running
        do_action('wecoza_clients_plugin_loaded');
    }

    /**
     * Get plugin version
     *
     * @return string
     */
    public function get_version() {
        return $this->version;
    }

    /**
     * Get plugin name
     *
     * @return string
     */
    public function get_plugin_name() {
        return $this->plugin_name;
    }
}