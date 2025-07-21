<?php

namespace WeCozaClients;

/**
 * Bootstrap file for WeCoza Clients Plugin MVC Architecture
 *
 * @package WeCozaClients
 * @since 1.0.0
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

// Define plugin-specific constants
define('WECOZA_CLIENTS_PATH', dirname(__DIR__));
define('WECOZA_CLIENTS_APP_PATH', WECOZA_CLIENTS_PATH . '/app');
define('WECOZA_CLIENTS_CONFIG_PATH', WECOZA_CLIENTS_PATH . '/config');
define('WECOZA_CLIENTS_VIEWS_PATH', WECOZA_CLIENTS_APP_PATH . '/Views');

/**
 * Autoloader function for plugin classes
 */
spl_autoload_register(function ($class) {
    // Only handle our namespace
    if (strpos($class, 'WeCozaClients\\') !== 0) {
        return;
    }

    // Convert namespace to path
    $class = str_replace('WeCozaClients\\', '', $class);
    $class = str_replace('\\', '/', $class);
    $path = WECOZA_CLIENTS_APP_PATH . '/' . $class . '.php';

    if (file_exists($path)) {
        require_once $path;
    }
});

/**
 * Load configuration
 *
 * @param string $config_name Configuration file name
 * @return array Configuration array
 */
function config($config_name) {
    $config_file = WECOZA_CLIENTS_CONFIG_PATH . '/' . $config_name . '.php';
    
    if (file_exists($config_file)) {
        return require $config_file;
    }
    
    return array();
}

/**
 * Render a view
 *
 * @param string $view View name (without .view.php extension)
 * @param array $data Data to pass to the view
 * @param bool $return Whether to return the output or echo it
 * @return string|void
 */
function view($view, $data = array(), $return = true) {
    // Extract data to variables
    extract($data);
    
    // Build view path
    $view_file = WECOZA_CLIENTS_VIEWS_PATH . '/' . $view . '.view.php';
    
    if (!file_exists($view_file)) {
        if (defined('WP_DEBUG') && WP_DEBUG) {
            error_log("WeCoza Clients Plugin: View file not found: {$view_file}");
        }
        return $return ? '' : null;
    }
    
    if ($return) {
        ob_start();
        include $view_file;
        return ob_get_clean();
    } else {
        include $view_file;
    }
}

/**
 * Load view helpers
 */
function load_view_helpers() {
    // Include the view helpers loader
    $helpers_file = WECOZA_CLIENTS_APP_PATH . '/Helpers/view-helpers-loader.php';
    if (file_exists($helpers_file)) {
        require_once $helpers_file;
    }
}

/**
 * Initialize application
 */
function init() {
    // Load configuration
    $config = config('app');

    // Load view helpers
    load_view_helpers();

    // Initialize controllers
    if (isset($config['controllers']) && is_array($config['controllers'])) {
        foreach ($config['controllers'] as $controller) {
            if (class_exists($controller)) {
                new $controller();
            }
        }
    }

    // Load AJAX handlers
    $ajax_handlers_file = WECOZA_CLIENTS_APP_PATH . '/ajax-handlers.php';
    if (file_exists($ajax_handlers_file)) {
        require_once $ajax_handlers_file;
    }
}

/**
 * Get plugin asset URL
 *
 * @param string $asset Asset path relative to assets directory
 * @return string Full URL to asset
 */
function asset_url($asset) {
    return WECOZA_CLIENTS_ASSETS_URL . ltrim($asset, '/');
}

/**
 * Get plugin directory path
 *
 * @param string $path Path relative to plugin directory
 * @return string Full path
 */
function plugin_path($path = '') {
    return WECOZA_CLIENTS_PATH . '/' . ltrim($path, '/');
}

/**
 * Check if we're in admin area
 *
 * @return bool
 */
function is_admin_area() {
    return is_admin() && !wp_doing_ajax();
}

/**
 * Check if we're doing AJAX
 *
 * @return bool
 */
function is_ajax_request() {
    return wp_doing_ajax();
}

/**
 * Get current user capabilities for client management
 *
 * @return array
 */
function get_user_client_capabilities() {
    $capabilities = array(
        'can_create_clients' => current_user_can('edit_posts'),
        'can_edit_clients' => current_user_can('edit_posts'),
        'can_delete_clients' => current_user_can('delete_posts'),
        'can_view_clients' => current_user_can('read'),
        'is_admin' => current_user_can('manage_options')
    );
    
    return apply_filters('wecoza_clients_user_capabilities', $capabilities);
}

/**
 * Log plugin messages
 *
 * @param string $message Message to log
 * @param string $level Log level (info, warning, error)
 */
function plugin_log($message, $level = 'info') {
    if (defined('WP_DEBUG') && WP_DEBUG) {
        error_log("WeCoza Clients Plugin [{$level}]: {$message}");
    }
}

/**
 * Initialize the plugin application
 */
add_action('init', function() {
    // Only initialize if not already done
    if (!defined('WECOZA_CLIENTS_INITIALIZED')) {
        init();
        define('WECOZA_CLIENTS_INITIALIZED', true);
    }
}, 10);

// Initialize immediately if called directly
if (!did_action('init')) {
    init();
    if (!defined('WECOZA_CLIENTS_INITIALIZED')) {
        define('WECOZA_CLIENTS_INITIALIZED', true);
    }
}