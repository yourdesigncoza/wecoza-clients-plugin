<?php

namespace WeCozaClients\Controllers;

use WeCozaClients\Models\LocationsModel;

class LocationsController {

    protected $model;

    public function __construct() {
        add_shortcode('wecoza_locations_capture', array($this, 'captureLocationShortcode'));
        add_action('wp_enqueue_scripts', array($this, 'enqueueAssets'));
        
        // Register AJAX handlers
        add_action('wp_ajax_check_location_duplicates', array($this, 'ajaxCheckLocationDuplicates'));
        add_action('wp_ajax_nopriv_check_location_duplicates', array($this, 'ajaxCheckLocationDuplicates'));
    }

    protected function getModel() {
        if (!$this->model) {
            $this->model = new LocationsModel();
        }

        return $this->model;
    }

    public function enqueueAssets() {
        global $post;

        if (!is_a($post, 'WP_Post')) {
            return;
        }

        if (!has_shortcode($post->post_content, 'wecoza_locations_capture')) {
            return;
        }

        $googleMapsKey = $this->getGoogleMapsApiKey();
        $dependencies = array('jquery');

        if ($googleMapsKey) {
            $googleHandle = 'google-maps-api';
            if (!wp_script_is($googleHandle, 'enqueued')) {
                wp_enqueue_script(
                    $googleHandle,
                    'https://maps.googleapis.com/maps/api/js?key=' . esc_attr($googleMapsKey) . '&libraries=places&loading=async&v=weekly',
                    array(),
                    null,
                    true
                );
            }
            $dependencies[] = $googleHandle;
        }

        wp_enqueue_script(
            'wecoza-location-capture',
            \WeCozaClients\asset_url('js/location-capture.js'),
            $dependencies,
            defined('WECOZA_CLIENTS_VERSION') ? WECOZA_CLIENTS_VERSION : time(),
            true
        );

        $config = \WeCozaClients\config('app');
        $provinceOptions = array_values($config['province_options'] ?? array());

        wp_localize_script(
            'wecoza-location-capture',
            'wecoza_locations',
            array(
                'provinces' => $provinceOptions,
                'googleMapsEnabled' => (bool) $googleMapsKey,
                'messages' => array(
                    'autocompleteUnavailable' => __('Google Maps autocomplete is unavailable. You can still complete the form manually.', 'wecoza-clients'),
                    'selectProvince' => __('Please choose a province.', 'wecoza-clients'),
                    'requiredFields' => __('Please complete all required fields.', 'wecoza-clients'),
                ),
            )
        );

        // Also localize WordPress AJAX URL
        wp_localize_script(
            'wecoza-location-capture',
            'wecoza_ajax',
            array(
                'ajax_url' => admin_url('admin-ajax.php'),
            )
        );
    }

    public function captureLocationShortcode($atts) {
        if (!current_user_can('manage_wecoza_clients')) {
            return '<p>' . esc_html__('You do not have permission to capture locations.', 'wecoza-clients') . '</p>';
        }

        $config = \WeCozaClients\config('app');
        $provinces = array_values($config['province_options'] ?? array());
        $errors = array();
        $success = false;
        $location = array(
            'suburb' => '',
            'town' => '',
            'province' => '',
            'postal_code' => '',
            'latitude' => '',
            'longitude' => '',
        );

        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['wecoza_locations_form_nonce'])) {
            if (!wp_verify_nonce($_POST['wecoza_locations_form_nonce'], 'submit_locations_form')) {
                $errors['general'] = __('Security check failed. Please try again.', 'wecoza-clients');
            } else {
                $result = $this->handleFormSubmission();
                if ($result['success']) {
                    $success = true;
                    $location = array(
                        'suburb' => '',
                        'town' => '',
                        'province' => '',
                        'postal_code' => '',
                        'latitude' => '',
                        'longitude' => '',
                    );
                } else {
                    $errors = $result['errors'];
                    $location = $result['data'];
                }
            }
        }

        return \WeCozaClients\view('components/location-capture-form', array(
            'errors' => $errors,
            'success' => $success,
            'location' => $location,
            'provinces' => $provinces,
            'google_maps_enabled' => (bool) $this->getGoogleMapsApiKey(),
        ));
    }

    protected function handleFormSubmission() {
        $data = $this->sanitizeFormData($_POST);
        $errors = $this->getModel()->validate($data);

        if (!empty($errors)) {
            return array(
                'success' => false,
                'errors' => $errors,
                'data' => $data,
            );
        }

        $created = $this->getModel()->create($data);

        if (!$created) {
            return array(
                'success' => false,
                'errors' => array('general' => __('Failed to save location. Please try again.', 'wecoza-clients')),
                'data' => $data,
            );
        }

        return array(
            'success' => true,
            'errors' => array(),
            'data' => $data,
        );
    }

    protected function sanitizeFormData($data) {
        return array(
            'suburb' => isset($data['suburb']) ? sanitize_text_field($data['suburb']) : '',
            'town' => isset($data['town']) ? sanitize_text_field($data['town']) : '',
            'province' => isset($data['province']) ? sanitize_text_field($data['province']) : '',
            'postal_code' => isset($data['postal_code']) ? sanitize_text_field($data['postal_code']) : '',
            'latitude' => isset($data['latitude']) ? sanitize_text_field(str_replace(',', '.', $data['latitude'])) : '',
            'longitude' => isset($data['longitude']) ? sanitize_text_field(str_replace(',', '.', $data['longitude'])) : '',
        );
    }

    public function ajaxCheckLocationDuplicates() {
        // Verify nonce
        if (!wp_verify_nonce($_POST['nonce'], 'wecoza_locations_form')) {
            wp_die(json_encode(array(
                'success' => false,
                'data' => 'Security check failed'
            )));
        }

        // Check capabilities
        if (!current_user_can('view_wecoza_clients')) {
            wp_die(json_encode(array(
                'success' => false,
                'data' => 'Insufficient permissions'
            )));
        }

        $suburb = isset($_POST['suburb']) ? sanitize_text_field($_POST['suburb']) : '';
        $town = isset($_POST['town']) ? sanitize_text_field($_POST['town']) : '';

        if (empty($suburb) && empty($town)) {
            wp_die(json_encode(array(
                'success' => false,
                'data' => 'Please provide suburb or town for duplicate check'
            )));
        }

        $duplicates = $this->getModel()->checkDuplicates($suburb, $town);

        wp_die(json_encode(array(
            'success' => true,
            'data' => array(
                'duplicates' => $duplicates
            )
        )));
    }

    protected function getGoogleMapsApiKey() {
        $apiKey = getenv('GOOGLE_MAPS_API_KEY');
        if (!empty($apiKey)) {
            return $apiKey;
        }

        if (defined('GOOGLE_MAPS_API_KEY') && !empty(GOOGLE_MAPS_API_KEY)) {
            return GOOGLE_MAPS_API_KEY;
        }

        $optionKey = get_option('wecoza_agents_google_maps_api_key');
        return !empty($optionKey) ? $optionKey : '';
    }
}
