<?php
/**
 * Fired during plugin uninstallation
 *
 * @package WeCozaClients
 * @since 1.0.0
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Plugin uninstaller class
 */
class WeCoza_Clients_Uninstaller {

    /**
     * Plugin uninstall handler
     *
     * @since 1.0.0
     */
    public static function uninstall() {
        // Only run if user has proper permissions
        if (!current_user_can('activate_plugins')) {
            return;
        }
        
        // Remove plugin options
        self::remove_plugin_options();
        
        // Remove capabilities
        self::remove_capabilities();
        
        // Note: Database tables are NOT removed automatically for data safety
        // They must be removed manually if needed
        
        // Log uninstallation
        if (defined('WP_DEBUG') && WP_DEBUG) {
            error_log('WeCoza Clients Plugin uninstalled');
        }
    }

    /**
     * Remove plugin options
     *
     * @since 1.0.0
     */
    private static function remove_plugin_options() {
        // Remove plugin settings
        delete_option('wecoza_clients_settings');
        delete_option('wecoza_clients_version');
        delete_option('wecoza_clients_installed');
        
        // Note: PostgreSQL connection settings are NOT removed
        // as they might be used by other plugins
    }

    /**
     * Remove user capabilities
     *
     * @since 1.0.0
     */
    private static function remove_capabilities() {
        // Get all roles
        global $wp_roles;
        
        if (!isset($wp_roles)) {
            $wp_roles = new WP_Roles();
        }
        
        // Remove capabilities from all roles
        foreach ($wp_roles->roles as $role_name => $role_info) {
            $role = get_role($role_name);
            
            if ($role) {
                $role->remove_cap('manage_wecoza_clients');
                $role->remove_cap('create_wecoza_clients');
                $role->remove_cap('edit_wecoza_clients');
                $role->remove_cap('delete_wecoza_clients');
                $role->remove_cap('view_wecoza_clients');
                $role->remove_cap('export_wecoza_clients');
            }
        }
    }
}