<?php
/**
 * Application configuration for WeCoza Clients Plugin
 *
 * @package WeCozaClients
 * @since 1.0.0
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

return array(
    /**
     * Plugin Information
     */
    'name' => 'WeCoza Clients Plugin',
    'version' => WECOZA_CLIENTS_VERSION,
    'description' => 'A comprehensive client management system for WeCoza.',
    'author' => 'Your Design Co',
    'author_uri' => 'https://yourdesign.co.za',
    'text_domain' => 'wecoza-clients',

    /**
     * Plugin Settings
     */
    'settings' => array(
        'enable_debug' => defined('WP_DEBUG') && WP_DEBUG,
        'enable_logging' => true,
        'cache_duration' => 3600, // 1 hour
        'max_upload_size' => 10485760, // 10MB
        'allowed_file_types' => array('pdf', 'doc', 'docx', 'xls', 'xlsx', 'jpg', 'jpeg', 'png'),
        'items_per_page' => 10,
        'enable_search' => true,
        'enable_export' => true,
    ),

    /**
     * Database Configuration
     */
    'database' => array(
        'use_postgresql' => true,
        'charset' => 'utf8mb4',
        'collate' => 'utf8mb4_unicode_ci',
        // PostgreSQL connection settings (can be overridden via WordPress options)
        'postgresql' => array(
            'host' => get_option('wecoza_postgres_host', 'db-wecoza-3-do-user-17263152-0.m.db.ondigitalocean.com'),
            'port' => get_option('wecoza_postgres_port', '25060'),
            'dbname' => get_option('wecoza_postgres_dbname', 'defaultdb'),
            'user' => get_option('wecoza_postgres_user', 'doadmin'),
            'password' => get_option('wecoza_postgres_password', ''),
        ),
    ),

    /**
     * Controllers to initialize
     */
    'controllers' => array(
        'WeCozaClients\\Controllers\\ClientsController',
        'WeCozaClients\\Controllers\\LocationsController',
    ),

    /**
     * Shortcodes configuration
     */
    'shortcodes' => array(
        'wecoza_capture_clients' => array(
            'controller' => 'WeCozaClients\\Controllers\\ClientsController',
            'method' => 'captureClientShortcode',
            'description' => 'Display client capture form',
        ),
        'wecoza_display_clients' => array(
            'controller' => 'WeCozaClients\\Controllers\\ClientsController',
            'method' => 'displayClientsShortcode',
            'description' => 'Display all clients in a table',
        ),
        'wecoza_display_single_client' => array(
            'controller' => 'WeCozaClients\\Controllers\\ClientsController',
            'method' => 'displaySingleClientShortcode',
            'description' => 'Display single client details',
        ),
        'wecoza_locations_capture' => array(
            'controller' => 'WeCozaClients\\Controllers\\LocationsController',
            'method' => 'captureLocationShortcode',
            'description' => 'Display locations capture form',
        ),
    ),

    /**
     * AJAX endpoints configuration
     */
    'ajax_endpoints' => array(
        'save_client' => array(
            'controller' => 'WeCozaClients\\Controllers\\ClientsController',
            'method' => 'ajaxSaveClient',
            'capability' => 'create_wecoza_clients',
            'nonce' => 'wecoza_clients_ajax',
        ),
        'get_client' => array(
            'controller' => 'WeCozaClients\\Controllers\\ClientsController',
            'method' => 'ajaxGetClient',
            'capability' => 'view_wecoza_clients',
            'nonce' => 'wecoza_clients_ajax',
        ),
        'delete_client' => array(
            'controller' => 'WeCozaClients\\Controllers\\ClientsController',
            'method' => 'ajaxDeleteClient',
            'capability' => 'delete_wecoza_clients',
            'nonce' => 'wecoza_clients_ajax',
        ),
        'search_clients' => array(
            'controller' => 'WeCozaClients\\Controllers\\ClientsController',
            'method' => 'ajaxSearchClients',
            'capability' => 'view_wecoza_clients',
            'nonce' => 'wecoza_clients_ajax',
        ),
        'get_branch_clients' => array(
            'controller' => 'WeCozaClients\\Controllers\\ClientsController',
            'method' => 'ajaxGetBranchClients',
            'capability' => 'view_wecoza_clients',
            'nonce' => 'wecoza_clients_ajax',
        ),
        'export_clients' => array(
            'controller' => 'WeCozaClients\\Controllers\\ClientsController',
            'method' => 'ajaxExportClients',
            'capability' => 'export_wecoza_clients',
            'nonce' => 'wecoza_clients_ajax',
        ),
        'get_locations' => array(
            'controller' => 'WeCozaClients\\Controllers\\ClientsController',
            'method' => 'ajaxGetLocations',
            'capability' => 'view_wecoza_clients',
            'nonce' => 'wecoza_clients_ajax',
        ),
        'check_location_duplicates' => array(
            'controller' => 'WeCozaClients\\Controllers\\LocationsController',
            'method' => 'ajaxCheckLocationDuplicates',
            'capability' => 'view_wecoza_clients',
            'nonce' => 'submit_locations_form',
        ),
    ),

    /**
     * Assets configuration
     */
    'assets' => array(
        'styles' => array(
            // Main plugin styles are added to theme's ydcoza-styles.css
        ),
        'scripts' => array(
            'client-capture' => array(
                'file' => 'js/client-capture.js',
                'deps' => array('jquery'),
                'shortcodes' => array('wecoza_capture_clients'),
            ),
            'clients-display' => array(
                'file' => 'js/clients-display.js',
                'deps' => array('jquery'),
                'shortcodes' => array('wecoza_display_clients'),
            ),
            'client-search' => array(
                'file' => 'js/client-search.js',
                'deps' => array('jquery'),
                'shortcodes' => array('wecoza_display_clients'),
            ),
            'location-capture' => array(
                'file' => 'js/location-capture.js',
                'deps' => array('jquery'),
                'shortcodes' => array('wecoza_locations_capture'),
            ),
        ),
    ),

    /**
     * Form field validation rules
     */
    'validation_rules' => array(
        'client_name' => array(
            'required' => true,
            'max_length' => 255,
        ),
        'company_registration_nr' => array(
            'required' => true,
            'max_length' => 100,
            'unique' => true,
        ),
        'contact_person_email' => array(
            'required' => true,
            'email' => true,
            'max_length' => 255,
        ),
        'contact_person_cellphone' => array(
            'required' => true,
            'max_length' => 50,
        ),
        'client_province' => array(
            'required' => true,
            'max_length' => 50,
        ),
        'client_suburb' => array(
            'required' => true,
            'max_length' => 255,
        ),
        'client_town_id' => array(
            'required' => true,
            'integer' => true,
            'min' => 1,
        ),
        'client_postal_code' => array(
            'required' => true,
            'max_length' => 20,
        ),
        'contact_person' => array(
            'required' => true,
            'max_length' => 255,
        ),
        
        'seta' => array(
            'required' => true,
            'max_length' => 50,
        ),
        'client_status' => array(
            'required' => true,
            'in' => array('Cold Call', 'Lead', 'Active Client', 'Lost Client'),
        ),
        'financial_year_end' => array(
            'required' => true,
            'date' => true,
        ),
        'bbbee_verification_date' => array(
            'required' => true,
            'date' => true,
        ),
        'main_client_id' => array(
            'required' => false,
            'integer' => true,
            'min' => 0,
        ),
    ),

    /**
     * SETA options
     */
    'seta_options' => array(
        'AgriSETA',
        'BANKSETA',
        'CATHSSETA',
        'CETA',
        'CHIETA',
        'ETDP SETA',
        'EWSETA',
        'FASSET',
        'FP&M SETA',
        'FoodBev SETA',
        'HWSETA',
        'INSETA',
        'LGSETA',
        'MICT SETA',
        'MQA',
        'PSETA',
        'SASSETA',
        'Services SETA',
        'TETA',
        'W&RSETA',
        'merSETA',
    ),

    'province_options' => array(
        'Eastern Cape',
        'Free State',
        'Gauteng',
        'KwaZulu-Natal',
        'Limpopo',
        'Mpumalanga',
        'Northern Cape',
        'North West',
        'Western Cape',
    ),

    /**
     * Client status options
     */
    'client_status_options' => array(
        'Cold Call' => 'Cold Call',
        'Lead' => 'Lead',
        'Active Client' => 'Active Client',
        'Lost Client' => 'Lost Client',
    ),

    /**
     * User capabilities
     */
    'capabilities' => array(
        'manage_wecoza_clients' => array('administrator'),
        'create_wecoza_clients' => array('administrator'),
        'edit_wecoza_clients' => array('administrator'),
        'delete_wecoza_clients' => array('administrator'),
        'view_wecoza_clients' => array('administrator', 'editor', 'author'),
        'export_wecoza_clients' => array('administrator'),
    ),
);
