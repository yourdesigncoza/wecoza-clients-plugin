<?php
/**
 * Plugin Name: WeCoza Clients Plugin
 * Plugin URI: https://yourdesign.co.za/wecoza-clients-plugin
 * Description: A comprehensive client management system for WeCoza. Handles client creation, editing, searching, and management with full MVC architecture and PostgreSQL integration.
 * Version: 1.0.0
 * Author: Your Design Co
 * Author URI: https://yourdesign.co.za
 * License: GPL v2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: wecoza-clients
 * Domain Path: /languages
 * Requires at least: 5.0
 * Tested up to: 6.4
 * Requires PHP: 7.4
 * Network: false
 *
 * @package WeCozaClients
 * @version 1.0.0
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

// Define plugin constants
// Keep asset versions stable so browsers can cache them properly
$ver = time();
define('WECOZA_CLIENTS_VERSION', $ver);
define('WECOZA_CLIENTS_PLUGIN_FILE', __FILE__);
define('WECOZA_CLIENTS_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('WECOZA_CLIENTS_PLUGIN_URL', plugin_dir_url(__FILE__));
define('WECOZA_CLIENTS_PLUGIN_BASENAME', plugin_basename(__FILE__));

// Define plugin paths
define('WECOZA_CLIENTS_INCLUDES_DIR', WECOZA_CLIENTS_PLUGIN_DIR . 'includes/');
define('WECOZA_CLIENTS_APP_DIR', WECOZA_CLIENTS_PLUGIN_DIR . 'app/');
define('WECOZA_CLIENTS_ASSETS_DIR', WECOZA_CLIENTS_PLUGIN_DIR . 'assets/');
define('WECOZA_CLIENTS_CONFIG_DIR', WECOZA_CLIENTS_PLUGIN_DIR . 'config/');

// Define plugin URLs
define('WECOZA_CLIENTS_ASSETS_URL', WECOZA_CLIENTS_PLUGIN_URL . 'assets/');
define('WECOZA_CLIENTS_JS_URL', WECOZA_CLIENTS_ASSETS_URL . 'js/');
define('WECOZA_CLIENTS_CSS_URL', WECOZA_CLIENTS_ASSETS_URL . 'css/');

/**
 * Plugin activation hook
 */
function activate_wecoza_clients_plugin() {
    require_once WECOZA_CLIENTS_INCLUDES_DIR . 'class-activator.php';
    WeCoza_Clients_Activator::activate();
}
register_activation_hook(__FILE__, 'activate_wecoza_clients_plugin');

/**
 * Plugin deactivation hook
 */
function deactivate_wecoza_clients_plugin() {
    require_once WECOZA_CLIENTS_INCLUDES_DIR . 'class-deactivator.php';
    WeCoza_Clients_Deactivator::deactivate();
}
register_deactivation_hook(__FILE__, 'deactivate_wecoza_clients_plugin');

/**
 * Plugin uninstall hook
 */
function uninstall_wecoza_clients_plugin() {
    require_once WECOZA_CLIENTS_INCLUDES_DIR . 'class-uninstaller.php';
    WeCoza_Clients_Uninstaller::uninstall();
}
register_uninstall_hook(__FILE__, 'uninstall_wecoza_clients_plugin');

/**
 * Load plugin text domain for internationalization
 */
function wecoza_clients_load_textdomain() {
    load_plugin_textdomain(
        'wecoza-clients',
        false,
        dirname(WECOZA_CLIENTS_PLUGIN_BASENAME) . '/languages/'
    );
}
add_action('plugins_loaded', 'wecoza_clients_load_textdomain');

/**
 * Initialize the plugin
 */
function run_wecoza_clients_plugin() {
    // Load the main plugin class
    require_once WECOZA_CLIENTS_INCLUDES_DIR . 'class-wecoza-clients-plugin.php';
    
    // Initialize the plugin
    $plugin = new WeCoza_Clients_Plugin();
    $plugin->run();
}

/**
 * Check if WordPress and required dependencies are loaded
 */
function wecoza_clients_init() {
    // Check if WordPress is loaded
    if (!function_exists('add_action')) {
        return;
    }
    
    // Check for minimum WordPress version
    if (version_compare(get_bloginfo('version'), '5.0', '<')) {
        add_action('admin_notices', 'wecoza_clients_wordpress_version_notice');
        return;
    }
    
    // Check for minimum PHP version
    if (version_compare(PHP_VERSION, '7.4', '<')) {
        add_action('admin_notices', 'wecoza_clients_php_version_notice');
        return;
    }
    
    // All checks passed, run the plugin
    run_wecoza_clients_plugin();
}

/**
 * WordPress version notice
 */
function wecoza_clients_wordpress_version_notice() {
    echo '<div class="notice notice-error"><p>';
    echo esc_html__('WeCoza Clients Plugin requires WordPress 5.0 or higher.', 'wecoza-clients');
    echo '</p></div>';
}

/**
 * PHP version notice
 */
function wecoza_clients_php_version_notice() {
    echo '<div class="notice notice-error"><p>';
    echo esc_html__('WeCoza Clients Plugin requires PHP 7.4 or higher.', 'wecoza-clients');
    echo '</p></div>';
}

// Initialize the plugin
wecoza_clients_init();
