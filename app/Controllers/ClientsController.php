<?php

namespace WeCozaClients\Controllers;

use WeCozaClients\Models\ClientsModel;
use WeCozaClients\Helpers\ViewHelpers;

/**
 * Clients Controller for handling client operations
 *
 * @package WeCozaClients
 * @since 1.0.0
 */
class ClientsController {
    
    /**
     * Model instance
     *
     * @var ClientsModel
     */
    protected $model;
    
    /**
     * Constructor
     */
    public function __construct() {
        $this->model = new ClientsModel();
        
        // Register shortcodes
        $this->registerShortcodes();
        
        // Register AJAX handlers
        $this->registerAjaxHandlers();
        
        // Enqueue assets
        add_action('wp_enqueue_scripts', array($this, 'enqueueAssets'));
    }
    
    /**
     * Register shortcodes
     */
    protected function registerShortcodes() {
        add_shortcode('wecoza_capture_clients', array($this, 'captureClientShortcode'));
        add_shortcode('wecoza_display_clients', array($this, 'displayClientsShortcode'));
        add_shortcode('wecoza_display_single_client', array($this, 'displaySingleClientShortcode'));
    }
    
    /**
     * Register AJAX handlers
     */
    protected function registerAjaxHandlers() {
        // Public AJAX handlers
        add_action('wp_ajax_wecoza_save_client', array($this, 'ajaxSaveClient'));
        add_action('wp_ajax_wecoza_get_client', array($this, 'ajaxGetClient'));
        add_action('wp_ajax_wecoza_delete_client', array($this, 'ajaxDeleteClient'));
        add_action('wp_ajax_wecoza_search_clients', array($this, 'ajaxSearchClients'));
        add_action('wp_ajax_wecoza_get_branch_clients', array($this, 'ajaxGetBranchClients'));
        add_action('wp_ajax_wecoza_export_clients', array($this, 'ajaxExportClients'));
        
        // Non-logged in users (if needed)
        add_action('wp_ajax_nopriv_wecoza_search_clients', array($this, 'ajaxSearchClients'));
    }
    
    /**
     * Enqueue plugin assets
     */
    public function enqueueAssets() {
        global $post;
        
        // Check if we're on a page with our shortcodes
        if (!is_a($post, 'WP_Post')) {
            return;
        }
        
        $has_capture_form = has_shortcode($post->post_content, 'wecoza_capture_clients');
        $has_display_table = has_shortcode($post->post_content, 'wecoza_display_clients');
        $has_single_display = has_shortcode($post->post_content, 'wecoza_display_single_client');
        $nonce = wp_create_nonce('wecoza_clients_ajax');
        
        // Enqueue scripts based on shortcode presence
        if ($has_capture_form) {
            wp_enqueue_script(
                'wecoza-client-capture',
                \WeCozaClients\asset_url('js/client-capture.js'),
                array('jquery'),
                WECOZA_CLIENTS_VERSION,
                true
            );
            
            // Localize script
            wp_localize_script(
                'wecoza-client-capture',
                'wecoza_clients',
                $this->getLocalizationPayload($nonce, array(
                    'locations' => array(
                        'hierarchy' => $this->model->getLocationHierarchy(),
                    ),
                ))
            );
        }
        
        if ($has_display_table) {
            wp_enqueue_script(
                'wecoza-clients-display',
                \WeCozaClients\asset_url('js/clients-display.js'),
                array('jquery'),
                WECOZA_CLIENTS_VERSION,
                true
            );
            
            wp_enqueue_script(
                'wecoza-client-search',
                \WeCozaClients\asset_url('js/client-search.js'),
                array('jquery'),
                WECOZA_CLIENTS_VERSION,
                true
            );
            
            // Localize script
            $localization = $this->getLocalizationPayload($nonce);
            wp_localize_script('wecoza-clients-display', 'wecoza_clients', $localization);
            wp_localize_script('wecoza-client-search', 'wecoza_clients', $localization);
        }
    }

    /**
     * Build localization payload for frontend scripts
     *
     * @param string $nonce Nonce value shared across scripts
     * @param array $overrides Additional data to merge
     * @return array
     */
    protected function getLocalizationPayload($nonce, array $overrides = array()) {
        $base = array(
            'ajaxUrl' => admin_url('admin-ajax.php'),
            'nonce' => $nonce,
            'actions' => array(
                'save' => 'wecoza_save_client',
                'get' => 'wecoza_get_client',
                'delete' => 'wecoza_delete_client',
                'search' => 'wecoza_search_clients',
                'branches' => 'wecoza_get_branch_clients',
                'export' => 'wecoza_export_clients',
            ),
            'messages' => array(
                'form' => array(
                    'saving' => __('Saving client...', 'wecoza-clients'),
                    'saved' => __('Client saved successfully!', 'wecoza-clients'),
                    'error' => __('An error occurred. Please try again.', 'wecoza-clients'),
                ),
                'list' => array(
                    'confirmDelete' => __('Are you sure you want to delete this client?', 'wecoza-clients'),
                    'deleting' => __('Deleting client...', 'wecoza-clients'),
                    'deleted' => __('Client deleted successfully!', 'wecoza-clients'),
                    'exporting' => __('Preparing export...', 'wecoza-clients'),
                    'error' => __('An error occurred. Please try again.', 'wecoza-clients'),
                ),
                'general' => array(
                    'error' => __('Something went wrong. Please try again.', 'wecoza-clients'),
                ),
            ),
            'locations' => array(
                'hierarchy' => array(),
            ),
        );

        return array_replace_recursive($base, $overrides);
    }
    
    /**
     * Client capture form shortcode
     *
     * @param array $atts Shortcode attributes
     * @return string
     */
    public function captureClientShortcode($atts) {
        // Check permissions
        if (!current_user_can('create_wecoza_clients')) {
            return '<p>' . __('You do not have permission to create clients.', 'wecoza-clients') . '</p>';
        }
        
        $atts = shortcode_atts(array(
            'id' => 0,
        ), $atts);
        
        $client = null;
        $errors = array();
        $success = false;
        
        // Get client data if editing
        if ($atts['id']) {
            $client = $this->model->getById($atts['id']);
            if (!$client) {
                return '<p>' . __('Client not found.', 'wecoza-clients') . '</p>';
            }
        }
        
        // Handle form submission
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['wecoza_clients_form_nonce'])) {
            if (!wp_verify_nonce($_POST['wecoza_clients_form_nonce'], 'submit_clients_form')) {
                $errors[] = __('Security check failed. Please try again.', 'wecoza-clients');
            } else {
                $result = $this->handleFormSubmission($atts['id']);
                if ($result['success']) {
                    $success = true;
                    $client = $result['client'];
                } else {
                    $errors = $result['errors'];
                }
            }
        }
        
        // Get dropdown data
        $branches = $this->model->getForDropdown();
        $config = \WeCozaClients\config('app');
        $seta_options = $config['seta_options'];
        $status_options = $config['client_status_options'];

        $selectedProvince = $client['client_province'] ?? ($client['client_location']['province'] ?? '');
        $selectedTown = $client['client_town'] ?? ($client['client_location']['town'] ?? '');
        $selectedSuburb = $client['client_suburb'] ?? ($client['client_location']['suburb'] ?? '');
        $selectedLocationId = !empty($client['client_town_id']) ? (int) $client['client_town_id'] : null;
        $selectedPostal = $client['client_postal_code'] ?? ($client['client_location']['postal_code'] ?? '');

        $hierarchy = $this->model->getLocationHierarchy();

        $locationData = array(
            'hierarchy' => $hierarchy,
            'selected' => array(
                'province' => $selectedProvince,
                'town' => $selectedTown,
                'suburb' => $selectedSuburb,
                'locationId' => $selectedLocationId,
                'postalCode' => $selectedPostal,
            ),
        );
        
        // Load view
        return \WeCozaClients\view('components/client-capture-form', array(
            'client' => $client,
            'errors' => $errors,
            'success' => $success,
            'branches' => $branches,
            'seta_options' => $seta_options,
            'status_options' => $status_options,
            'location_data' => $locationData,
        ));
    }
    
    /**
     * Display clients table shortcode
     *
     * @param array $atts Shortcode attributes
     * @return string
     */
    public function displayClientsShortcode($atts) {
        // Check permissions
        if (!current_user_can('view_wecoza_clients')) {
            return '<p>' . __('You do not have permission to view clients.', 'wecoza-clients') . '</p>';
        }
        
        $atts = shortcode_atts(array(
            'per_page' => 10,
            'show_search' => true,
            'show_filters' => true,
            'show_export' => true,
        ), $atts);
        
        // Get query parameters
        $page = isset($_GET['client_page']) ? max(1, intval($_GET['client_page'])) : 1;
        $search = isset($_GET['client_search']) ? sanitize_text_field($_GET['client_search']) : '';
        $status = isset($_GET['client_status']) ? sanitize_text_field($_GET['client_status']) : '';
        $seta = isset($_GET['client_seta']) ? sanitize_text_field($_GET['client_seta']) : '';
        
        // Build query parameters
        $params = array(
            'search' => $search,
            'status' => $status,
            'seta' => $seta,
            'limit' => $atts['per_page'],
            'offset' => ($page - 1) * $atts['per_page'],
        );
        
        // Get clients
        $clients = $this->model->getAll($params);
        $total = $this->model->count($params);
        $totalPages = ceil($total / $atts['per_page']);
        
        // Get statistics
        $stats = $this->model->getStatistics();
        
        // Get filter options
        $config = \WeCozaClients\config('app');
        $seta_options = $config['seta_options'];
        $status_options = $config['client_status_options'];
        
        // Load view
        return \WeCozaClients\view('display/clients-display', array(
            'clients' => $clients,
            'total' => $total,
            'page' => $page,
            'totalPages' => $totalPages,
            'search' => $search,
            'status' => $status,
            'seta' => $seta,
            'stats' => $stats,
            'seta_options' => $seta_options,
            'status_options' => $status_options,
            'atts' => $atts,
        ));
    }
    
    /**
     * Display single client shortcode
     *
     * @param array $atts Shortcode attributes
     * @return string
     */
    public function displaySingleClientShortcode($atts) {
        // Check permissions
        if (!current_user_can('view_wecoza_clients')) {
            return '<p>' . __('You do not have permission to view clients.', 'wecoza-clients') . '</p>';
        }
        
        $atts = shortcode_atts(array(
            'id' => 0,
        ), $atts);
        
        // Get client ID from URL if not in attributes
        if (!$atts['id'] && isset($_GET['client_id'])) {
            $atts['id'] = intval($_GET['client_id']);
        }
        
        if (!$atts['id']) {
            return '<p>' . __('No client specified.', 'wecoza-clients') . '</p>';
        }
        
        // Get client data
        $client = $this->model->getById($atts['id']);
        if (!$client) {
            return '<p>' . __('Client not found.', 'wecoza-clients') . '</p>';
        }
        
        // Get branch clients
        $branchClients = $this->model->getBranchClients($client['id']);
        
        // Get parent client if this is a branch
        $parentClient = null;
        if ($client['branch_of']) {
            $parentClient = $this->model->getById($client['branch_of']);
        }
        
        // Load view
        return \WeCozaClients\view('display/single-client-display', array(
            'client' => $client,
            'branchClients' => $branchClients,
            'parentClient' => $parentClient,
        ));
    }
    
    /**
     * Handle form submission
     *
     * @param int $clientId Client ID (for updates)
     * @return array
     */
    protected function handleFormSubmission($clientId = 0) {
        $data = $this->sanitizeFormData($_POST);
        
        // Validate data
        $errors = $this->model->validate($data, $clientId);
        if (!empty($errors)) {
            return array(
                'success' => false,
                'errors' => $errors,
            );
        }
        
        // Handle file upload
        if (!empty($_FILES['quotes']) && !empty($_FILES['quotes']['name'])) {
            $uploadResult = $this->handleFileUpload($_FILES['quotes']);
            if ($uploadResult['success']) {
                $data['quotes'] = $uploadResult['path'];
            } else {
                $errors['quotes'] = $uploadResult['error'];
                return array(
                    'success' => false,
                    'errors' => $errors,
                );
            }
        }
        
        // Save client
        if ($clientId) {
            $success = $this->model->update($clientId, $data);
            $message = __('Client updated successfully!', 'wecoza-clients');
        } else {
            $clientId = $this->model->create($data);
            $success = $clientId !== false;
            $message = __('Client created successfully!', 'wecoza-clients');
        }
        
        if (!$success) {
            return array(
                'success' => false,
                'errors' => array('general' => __('Failed to save client. Please try again.', 'wecoza-clients')),
            );
        }
        
        // Get updated client data
        $client = $this->model->getById($clientId);
        
        return array(
            'success' => true,
            'client' => $client,
            'message' => $message,
        );
    }
    
    /**
     * Sanitize form data
     *
     * @param array $data Raw form data
     * @return array
     */
    protected function sanitizeFormData($data) {
        $sanitized = array();
        
        // Text fields
        $textFields = array(
            'client_name', 'company_registration_nr', 'client_street_address',
            'client_suburb', 'client_postal_code', 'client_province', 'client_town_name',
            'contact_person', 'contact_person_cellphone', 'contact_person_tel',
            'client_communication', 'seta', 'client_status'
        );
        
        foreach ($textFields as $field) {
            if (isset($data[$field])) {
                $sanitized[$field] = sanitize_text_field($data[$field]);
            }
        }
        
        // Email field
        if (isset($data['contact_person_email'])) {
            $sanitized['contact_person_email'] = sanitize_email($data['contact_person_email']);
        }
        
        // Integer fields
        if (isset($data['branch_of']) && $data['branch_of']) {
            $sanitized['branch_of'] = intval($data['branch_of']);
        }

        if (isset($data['client_town_id'])) {
            $townId = intval($data['client_town_id']);
            if ($townId > 0) {
                $location = $this->model->getLocationById($townId);
                if ($location) {
                    $sanitized['client_town_id'] = $townId;
                    $sanitized['client_suburb'] = $location['suburb'] ?? ($sanitized['client_suburb'] ?? '');
                    $sanitized['client_postal_code'] = $location['postal_code'] ?? ($sanitized['client_postal_code'] ?? '');
                    $sanitized['client_province'] = $location['province'] ?? ($sanitized['client_province'] ?? '');
                    $sanitized['client_town'] = $location['town'] ?? '';
                }
            }
        }

        if (!empty($sanitized['client_town_name']) && empty($sanitized['client_town'])) {
            $sanitized['client_town'] = $sanitized['client_town_name'];
        }

        unset($sanitized['client_town_name']);
        
        // Date fields
        $dateFields = array('financial_year_end', 'bbbee_verification_date');
        foreach ($dateFields as $field) {
            if (isset($data[$field]) && $data[$field]) {
                $sanitized[$field] = sanitize_text_field($data[$field]);
            }
        }
        
        return $sanitized;
    }
    
    /**
     * Handle file upload
     *
     * @param array $file File data from $_FILES
     * @return array
     */
    protected function handleFileUpload($file) {
        if (!function_exists('wp_handle_upload')) {
            require_once(ABSPATH . 'wp-admin/includes/file.php');
        }
        
        $upload_overrides = array(
            'test_form' => false,
            'mimes' => array(
                'pdf' => 'application/pdf',
                'doc' => 'application/msword',
                'docx' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
                'xls' => 'application/vnd.ms-excel',
                'xlsx' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            )
        );
        
        $movefile = wp_handle_upload($file, $upload_overrides);
        
        if ($movefile && !isset($movefile['error'])) {
            return array(
                'success' => true,
                'path' => $movefile['url'],
                'file' => $movefile['file'],
            );
        } else {
            return array(
                'success' => false,
                'error' => $movefile['error'] ?? __('File upload failed.', 'wecoza-clients'),
            );
        }
    }
    
    /**
     * AJAX: Save client
     */
    public function ajaxSaveClient() {
        // Verify nonce
        if (!check_ajax_referer('wecoza_clients_ajax', 'nonce', false)) {
            wp_die(json_encode(array('success' => false, 'message' => 'Security check failed.')));
        }
        
        // Check permissions
        $clientId = isset($_POST['id']) ? intval($_POST['id']) : 0;
        $capability = $clientId ? 'edit_wecoza_clients' : 'create_wecoza_clients';
        
        if (!current_user_can($capability)) {
            wp_die(json_encode(array('success' => false, 'message' => 'Permission denied.')));
        }
        
        // Handle form submission
        $result = $this->handleFormSubmission($clientId);
        
        wp_die(json_encode($result));
    }
    
    /**
     * AJAX: Get client
     */
    public function ajaxGetClient() {
        // Verify nonce
        if (!check_ajax_referer('wecoza_clients_ajax', 'nonce', false)) {
            wp_die(json_encode(array('success' => false, 'message' => 'Security check failed.')));
        }
        
        // Check permissions
        if (!current_user_can('view_wecoza_clients')) {
            wp_die(json_encode(array('success' => false, 'message' => 'Permission denied.')));
        }
        
        $clientId = isset($_GET['id']) ? intval($_GET['id']) : 0;
        if (!$clientId) {
            wp_die(json_encode(array('success' => false, 'message' => 'Invalid client ID.')));
        }
        
        $client = $this->model->getById($clientId);
        if (!$client) {
            wp_die(json_encode(array('success' => false, 'message' => 'Client not found.')));
        }
        
        wp_die(json_encode(array('success' => true, 'client' => $client)));
    }
    
    /**
     * AJAX: Delete client
     */
    public function ajaxDeleteClient() {
        // Verify nonce
        if (!check_ajax_referer('wecoza_clients_ajax', 'nonce', false)) {
            wp_die(json_encode(array('success' => false, 'message' => 'Security check failed.')));
        }
        
        // Check permissions
        if (!current_user_can('delete_wecoza_clients')) {
            wp_die(json_encode(array('success' => false, 'message' => 'Permission denied.')));
        }
        
        $clientId = isset($_POST['id']) ? intval($_POST['id']) : 0;
        if (!$clientId) {
            wp_die(json_encode(array('success' => false, 'message' => 'Invalid client ID.')));
        }
        
        $success = $this->model->delete($clientId);
        
        wp_die(json_encode(array(
            'success' => $success,
            'message' => $success ? 'Client deleted successfully.' : 'Failed to delete client.',
        )));
    }
    
    /**
     * AJAX: Search clients
     */
    public function ajaxSearchClients() {
        // Verify nonce
        if (!check_ajax_referer('wecoza_clients_ajax', 'nonce', false)) {
            wp_die(json_encode(array('success' => false, 'message' => 'Security check failed.')));
        }
        
        // Check permissions
        if (!current_user_can('view_wecoza_clients')) {
            wp_die(json_encode(array('success' => false, 'message' => 'Permission denied.')));
        }
        
        $search = isset($_GET['search']) ? sanitize_text_field($_GET['search']) : '';
        $limit = isset($_GET['limit']) ? intval($_GET['limit']) : 10;
        
        $clients = $this->model->getAll(array(
            'search' => $search,
            'limit' => $limit,
        ));
        
        wp_die(json_encode(array('success' => true, 'clients' => $clients)));
    }

    /**
     * AJAX: Get branch clients
     */
    public function ajaxGetBranchClients() {
        // Verify nonce
        if (!check_ajax_referer('wecoza_clients_ajax', 'nonce', false)) {
            wp_die(json_encode(array('success' => false, 'message' => 'Security check failed.')));
        }
        
        // Check permissions
        if (!current_user_can('view_wecoza_clients')) {
            wp_die(json_encode(array('success' => false, 'message' => 'Permission denied.')));
        }
        
        $parentId = isset($_GET['parent_id']) ? intval($_GET['parent_id']) : 0;
        if (!$parentId) {
            wp_die(json_encode(array('success' => false, 'message' => 'Invalid parent ID.')));
        }
        
        $clients = $this->model->getBranchClients($parentId);
        
        wp_die(json_encode(array('success' => true, 'clients' => $clients)));
    }
    
    /**
     * AJAX: Export clients
     */
    public function ajaxExportClients() {
        // Verify nonce
        if (!check_ajax_referer('wecoza_clients_ajax', 'nonce', false)) {
            wp_die('Security check failed.');
        }
        
        // Check permissions
        if (!current_user_can('export_wecoza_clients')) {
            wp_die('Permission denied.');
        }
        
        // Get all clients
        $clients = $this->model->getAll();
        
        // Set headers for CSV download
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename=clients-export-' . date('Y-m-d') . '.csv');
        
        // Create output
        $output = fopen('php://output', 'w');
        
        // Add BOM for UTF-8
        fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));
        
        // Add headers
        $headers = array(
            'ID',
            'Client Name',
            'Company Registration Nr',
            'Contact Person',
            'Email',
            'Cellphone',
            'Town',
            'Status',
            'SETA',
            'Created Date',
        );
        fputcsv($output, $headers);
        
        // Add data
        foreach ($clients as $client) {
            $row = array(
                $client['id'],
                $client['client_name'],
                $client['company_registration_nr'],
                $client['contact_person'],
                $client['contact_person_email'],
                $client['contact_person_cellphone'],
                $client['client_town'],
                $client['client_status'],
                $client['seta'],
                $client['created_at'],
            );
            fputcsv($output, $row);
        }
        
        fclose($output);
        exit;
    }
}