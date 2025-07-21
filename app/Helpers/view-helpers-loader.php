<?php
/**
 * View Helpers Loader
 *
 * @package WeCozaClients
 * @since 1.0.0
 */

namespace WeCozaClients\Helpers;

// Load ViewHelpers class
require_once __DIR__ . '/ViewHelpers.php';

/**
 * Render form field
 */
function render_field($type, $name, $label, $value = '', $options = array()) {
    return ViewHelpers::renderField($type, $name, $label, $value, $options);
}

/**
 * Render alert message
 */
function render_alert($message, $type = 'info', $dismissible = true) {
    return ViewHelpers::renderAlert($message, $type, $dismissible);
}

/**
 * Render pagination
 */
function render_pagination($currentPage, $totalPages, $baseUrl, $queryArgs = array()) {
    return ViewHelpers::renderPagination($currentPage, $totalPages, $baseUrl, $queryArgs);
}

/**
 * Format date
 */
function format_date($date, $format = 'Y-m-d') {
    return ViewHelpers::formatDate($date, $format);
}

/**
 * Format phone number
 */
function format_phone($phone) {
    return ViewHelpers::formatPhone($phone);
}