<?php

use WeCozaClients\Helpers\ViewHelpers;

$errors = $errors ?? array();
$success = $success ?? false;
$location = $location ?? array();
$provinces = $provinces ?? array();
$google_maps_enabled = $google_maps_enabled ?? false;

$provinceOptions = array();
foreach ($provinces as $province) {
    $provinceOptions[$province] = $province;
}

?>

<div class="wecoza-locations-form-container">
    <h4 class="mb-3">Capture a Location</h4>
    <p class="mb-4 text-muted">Use the form below to add new locations for suburbs and towns across South Africa.</p>

    <?php if ($success) : ?>
        <?php echo ViewHelpers::renderAlert(__('Location saved successfully.', 'wecoza-clients'), 'success'); ?>
    <?php endif; ?>

    <?php if (!empty($errors)) : ?>
        <?php if (isset($errors['general'])) : ?>
            <?php echo ViewHelpers::renderAlert($errors['general'], 'error'); ?>
        <?php else : ?>
            <?php echo ViewHelpers::renderAlert(__('Please correct the errors below.', 'wecoza-clients'), 'error'); ?>
        <?php endif; ?>
    <?php endif; ?>

    <?php if (!$google_maps_enabled) : ?>
        <?php echo ViewHelpers::renderAlert(__('Google Maps autocomplete is not configured. You can still complete all fields manually.', 'wecoza-clients'), 'warning'); ?>
    <?php endif; ?>

    <form method="POST" class="needs-validation ydcoza-compact-form" novalidate>
        <?php wp_nonce_field('submit_locations_form', 'wecoza_locations_form_nonce'); ?>

        <div class="row mb-4">
            <div class="col-12">
                <label for="google_address_search" class="form-label">Search Address</label>
                <div id="google_address_container" class="position-relative">
                    <input type="text" id="google_address_search" class="form-control form-control-sm" placeholder="Start typing an address...">
                </div>
                <small class="text-muted d-block mt-2">Use the address search to auto-fill suburb, town, province, and coordinates.</small>
            </div>
        </div>

        <div class="row g-3">
            <?php
            echo ViewHelpers::renderField('text', 'suburb', __('Suburb', 'wecoza-clients'), $location['suburb'] ?? '', array(
                'required' => true,
                'col_class' => 'col-md-4',
                'error' => $errors['suburb'] ?? '',
            ));

            echo ViewHelpers::renderField('text', 'town', __('Town / City', 'wecoza-clients'), $location['town'] ?? '', array(
                'required' => true,
                'col_class' => 'col-md-4',
                'error' => $errors['town'] ?? '',
            ));

            echo ViewHelpers::renderField('select', 'province', __('Province', 'wecoza-clients'), $location['province'] ?? '', array(
                'required' => true,
                'col_class' => 'col-md-4',
                'options' => $provinceOptions,
                'error' => $errors['province'] ?? '',
            ));
            ?>
        </div>

        <div class="row g-3 mt-1">
            <?php
            echo ViewHelpers::renderField('text', 'postal_code', __('Postal Code', 'wecoza-clients'), $location['postal_code'] ?? '', array(
                'required' => true,
                'col_class' => 'col-md-4',
                'error' => $errors['postal_code'] ?? '',
            ));

            echo ViewHelpers::renderField('text', 'latitude', __('Latitude', 'wecoza-clients'), $location['latitude'] ?? '', array(
                'required' => true,
                'col_class' => 'col-md-4',
                'error' => $errors['latitude'] ?? '',
                'help_text' => __('e.g. -26.2041', 'wecoza-clients'),
            ));

            echo ViewHelpers::renderField('text', 'longitude', __('Longitude', 'wecoza-clients'), $location['longitude'] ?? '', array(
                'required' => true,
                'col_class' => 'col-md-4',
                'error' => $errors['longitude'] ?? '',
                'help_text' => __('e.g. 28.0473', 'wecoza-clients'),
            ));
            ?>
        </div>

        <div class="mt-4">
            <button type="submit" class="btn btn-subtle-primary">Save Location</button>
        </div>
    </form>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    var form = document.querySelector('.wecoza-locations-form-container form');
    if (!form) {
        return;
    }

    form.addEventListener('submit', function(event) {
        if (!form.checkValidity()) {
            event.preventDefault();
            event.stopPropagation();
        }

        form.classList.add('was-validated');
    }, false);
});
</script>
