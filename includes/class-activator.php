<?php
/**
 * Fired during plugin activation
 *
 * @package WeCozaClients
 * @since 1.0.0
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Plugin activation class
 */
class WeCoza_Clients_Activator {

    /**
     * Plugin activation handler
     *
     * @since 1.0.0
     */
    public static function activate() {
        // Set default options
        self::set_default_options();
        
        // Create database tables
        self::create_database_tables();
        
        // Set up capabilities
        self::setup_capabilities();
        
        // Flush rewrite rules
        flush_rewrite_rules();
        
        // Log activation
        if (defined('WP_DEBUG') && WP_DEBUG) {
            error_log('WeCoza Clients Plugin activated');
        }
    }

    /**
     * Set default plugin options
     *
     * @since 1.0.0
     */
    private static function set_default_options() {
        // Plugin settings
        if (!get_option('wecoza_clients_settings')) {
            $default_settings = array(
                'enable_debug' => false,
                'enable_logging' => true,
                'items_per_page' => 10,
                'enable_search' => true,
                'enable_export' => true,
                'date_format' => 'Y-m-d',
                'time_format' => 'H:i',
            );
            update_option('wecoza_clients_settings', $default_settings);
        }

        // PostgreSQL connection settings (if not already set)
        if (!get_option('wecoza_postgres_host')) {
            update_option('wecoza_postgres_host', '');
            update_option('wecoza_postgres_port', '');
            update_option('wecoza_postgres_dbname', '');
            update_option('wecoza_postgres_user', '');
            // Password should be set manually for security
        }

        // Plugin version
        update_option('wecoza_clients_version', WECOZA_CLIENTS_VERSION);
        
        // Installation date
        if (!get_option('wecoza_clients_installed')) {
            update_option('wecoza_clients_installed', current_time('timestamp'));
        }
    }

    /**
     * Create database tables
     *
     * @since 1.0.0
     */
    private static function create_database_tables() {
        // Note: In production, the PostgreSQL tables should be created manually
        // using the schema file. This is just a placeholder for the activation process.
        
        // For WordPress tables (if needed in the future)
        global $wpdb;
        $charset_collate = $wpdb->get_charset_collate();
        
        // Log table creation attempt
        if (defined('WP_DEBUG') && WP_DEBUG) {
            error_log('WeCoza Clients Plugin: Database tables should be created using schema/clients_schema.sql');
        }
    }

    /**
     * Set up user capabilities
     *
     * @since 1.0.0
     */
    private static function setup_capabilities() {
        // Get administrator role
        $admin_role = get_role('administrator');
        
        if ($admin_role) {
            // Add custom capabilities
            $admin_role->add_cap('manage_wecoza_clients');
            $admin_role->add_cap('create_wecoza_clients');
            $admin_role->add_cap('edit_wecoza_clients');
            $admin_role->add_cap('delete_wecoza_clients');
            $admin_role->add_cap('view_wecoza_clients');
            $admin_role->add_cap('export_wecoza_clients');
        }
        
        // Get editor role
        $editor_role = get_role('editor');
        
        if ($editor_role) {
            // Add limited capabilities
            $editor_role->add_cap('create_wecoza_clients');
            $editor_role->add_cap('edit_wecoza_clients');
            $editor_role->add_cap('view_wecoza_clients');
        }
    }
}