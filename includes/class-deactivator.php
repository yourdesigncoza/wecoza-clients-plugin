<?php
/**
 * Fired during plugin deactivation
 *
 * @package WeCozaClients
 * @since 1.0.0
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Plugin deactivation class
 */
class WeCoza_Clients_Deactivator {

    /**
     * Plugin deactivation handler
     *
     * @since 1.0.0
     */
    public static function deactivate() {
        // Clear scheduled events
        self::clear_scheduled_events();
        
        // Flush rewrite rules
        flush_rewrite_rules();
        
        // Log deactivation
        if (defined('WP_DEBUG') && WP_DEBUG) {
            error_log('WeCoza Clients Plugin deactivated');
        }
    }

    /**
     * Clear any scheduled events
     *
     * @since 1.0.0
     */
    private static function clear_scheduled_events() {
        // Clear any scheduled cron jobs
        wp_clear_scheduled_hook('wecoza_clients_daily_cleanup');
        wp_clear_scheduled_hook('wecoza_clients_hourly_sync');
    }
}