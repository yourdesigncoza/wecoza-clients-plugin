<?php
/**
 * Clients Management System
 * 
 * This file contains core functionality for managing clients including file loading,
 * asset enqueueing, and AJAX handlers.
 * 

 */
function load_client_files() {
    // Define array of required files
    $required_files = array(
        '/assets/clients/client-capture-shortcode.php',
    );

    // Load each required file
    foreach ($required_files as $file) {
        $file_path = WECOZA_CHILD_DIR . $file;
        if (file_exists($file_path)) {
            require_once $file_path;
        } else {
            error_log("Required file not found: {$file_path}");
        }
    }

}
load_client_files();

?>