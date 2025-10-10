<?php
/**
 * Client Update Form View
 *
 * @package WeCozaClients
 * @since 1.0.0
 */

use WeCozaClients\Helpers\ViewHelpers;

// Extract variables
$client = $client ?? null;
$errors = $errors ?? array();
$success = $success ?? false;
$seta_options = $seta_options ?? array();
$status_options = $status_options ?? array();
$location_data = $location_data ?? array();
$location_selected = $location_data['selected'] ?? array();
$location_hierarchy = $location_data['hierarchy'] ?? array();
$sites = $sites ?? array('head' => null, 'sub_sites' => array());
$main_clients = $main_clients ?? array();
$contact_defaults = isset($contact_defaults) && is_array($contact_defaults) ? $contact_defaults : array();
$is_update_mode = $is_update_mode ?? false;

// Sub-client variables
$is_sub_client = !empty($client['main_client_id']);
$selected_main_client_id = $client['main_client_id'] ?? '';
$is_sub_client_checked = $is_sub_client ? 'checked' : '';

$headSite = $sites['head'] ?? null;

$headSiteId = $headSite['site_id'] ?? ($client['site_id'] ?? '');
$headSiteName = $headSite['site_name'] ?? ($client['site_name'] ?? ($client['client_name'] ?? ''));
$headSiteAddress1 = $headSite['address_line_1'] ?? ($client['client_street_address'] ?? '');
$headSiteAddress2 = $headSite['address_line_2'] ?? ($client['client_address_line_2'] ?? '');

$selected_province = $location_selected['province'] ?? ($client['client_province'] ?? '');
$selected_town = $location_selected['town'] ?? ($client['client_town'] ?? '');
$selected_location_id = $location_selected['locationId'] ?? ($client['client_town_id'] ?? '');
$selected_suburb = $location_selected['suburb'] ?? ($client['client_suburb'] ?? '');
$selected_postal_code = $location_selected['postalCode'] ?? ($client['client_postal_code'] ?? '');

$province_options = array();
$town_options = array();
$suburb_options = array();

foreach ($location_hierarchy as $provinceData) {
    $provinceName = $provinceData['name'] ?? '';
    if ($provinceName === '') {
        continue;
    }

    $province_options[$provinceName] = $provinceName;

    if ($provinceName !== $selected_province || empty($provinceData['towns'])) {
        continue;
    }

    foreach ($provinceData['towns'] as $townData) {
        $townName = $townData['name'] ?? '';
        if ($townName === '') {
            continue;
        }

        $town_options[$townName] = $townName;

        if ($townName !== $selected_town || empty($townData['suburbs'])) {
            continue;
        }

        foreach ($townData['suburbs'] as $suburbData) {
            $locationId = isset($suburbData['id']) ? (int) $suburbData['id'] : 0;
            if ($locationId <= 0) {
                continue;
            }

            $label = $suburbData['name'] ?? '';
            $suburb_options[$locationId] = array(
                'label' => $label,
                'data' => array(
                    'postal_code' => $suburbData['postal_code'] ?? '',
                    'suburb' => $label,
                    'town' => $townName,
                    'province' => $provinceName,
                ),
            );
        }
    }
}

$has_province = $selected_province !== '';
$has_town = $selected_town !== '';
$has_location = !empty($selected_location_id);

$is_edit = !empty($client['id']);

$resolved_contact_person = $client['contact_person'] ?? ($contact_defaults['name'] ?? '');
$resolved_contact_email = $client['contact_person_email'] ?? ($contact_defaults['email'] ?? '');
$resolved_contact_cell = $client['contact_person_cellphone'] ?? ($contact_defaults['cellphone'] ?? '');
$resolved_contact_tel = $client['contact_person_tel'] ?? ($contact_defaults['telephone'] ?? '');
$resolved_contact_position = $client['contact_person_position'] ?? ($contact_defaults['position'] ?? '');
?>

<div class="wecoza-clients-form-container">
    <?php if ($success) : ?>
        <?php echo ViewHelpers::renderAlert(
            'Client updated successfully!',
            'success',
            true
        ); ?>
    <?php endif; ?>
    
    <?php if (!empty($errors)) : ?>
        <?php if (isset($errors['general'])) : ?>
            <?php echo ViewHelpers::renderAlert($errors['general'], 'error', true); ?>
        <?php else : ?>
            <?php echo ViewHelpers::renderAlert('Please correct the errors below.', 'error', true); ?>
        <?php endif; ?>
    <?php endif; ?>
    
    <h4 class="mb-1 mt-4">Update Client</h4>
    
    <form id="clients-form" class="needs-validation ydcoza-compact-form" novalidate method="POST" enctype="multipart/form-data">
        <?php wp_nonce_field('wecoza_clients_ajax', 'nonce'); ?>
        
        <input type="hidden" name="id" value="<?php echo esc_attr($client['id']); ?>">
        <input type="hidden" name="head_site_id" value="<?php echo esc_attr($headSiteId); ?>">
        
        <!-- Basic Information -->
        <div class="row">
            <?php
            echo ViewHelpers::renderField('text', 'client_name', 'Client Name', 
                $client['client_name'] ?? '', 
                array(
                    'required' => true,
                    'col_class' => 'col-md-3',
                    'error' => $errors['client_name'] ?? ''
                )
            );
            
            echo ViewHelpers::renderField('text', 'site_name', 'Site Name', 
                $headSiteName, 
                array(
                    'required' => true,
                    'col_class' => 'col-md-3',
                    'error' => $errors['site_name'] ?? ''
                )
            );
            
            echo ViewHelpers::renderField('text', 'company_registration_nr', 'Company Registration Nr', 
                $client['company_registration_nr'] ?? '', 
                array(
                    'required' => true,
                    'col_class' => 'col-md-3',
                    'error' => $errors['company_registration_nr'] ?? ''
                )
            );
            ?>
        </div>
        
        <!-- Sub-Client Information -->
        <div class="row mt-4">
            <div class="col-3">
                <div class="form-check mb-3">
                    <input type="checkbox" class="form-check-input" id="is_sub_client" name="is_sub_client" <?php echo $is_sub_client_checked; ?>>
                    <label class="form-check-label" for="is_sub_client">
                        <strong>Is SubClient</strong><br>
                        <small class="text-muted">Check if this client is a branch/subsidiary of another main client</small>
                    </label>
                </div>
            </div>
            <div class="col-3">
                <div id="main_client_dropdown_container" style="<?php echo $is_sub_client ? '' : 'display: none;'; ?>">
                    <?php
                    // Prepare enhanced main client options with company registration numbers
                    $main_client_options = array('' => 'Select Main Client...');
                    if (!empty($main_clients_raw)) {
                        foreach ($main_clients_raw as $main_client) {
                            $label = $main_client['client_name'];
                            if (!empty($main_client['company_registration_nr'])) {
                                $label .= ' (' . $main_client['company_registration_nr'] . ')';
                            }
                            $main_client_options[$main_client['id']] = $label;
                        }
                    } else {
                        // Fallback to basic format if raw data not available
                        $main_client_options = $main_clients;
                    }
                    
                    echo ViewHelpers::renderField('select', 'main_client_id', 'Main Client', 
                        $selected_main_client_id, 
                        array(
                            'required' => true,
                            'col_class' => 'js-main-client-field',
                            'class' => 'js-main-client-select',
                            'options' => $main_client_options,
                            'error' => $errors['main_client_id'] ?? ''
                        )
                    );
                    ?>
                </div>
            </div>
        </div>

        <div class="border-top border-opacity-25 border-3 border-discovery my-5 mx-1"></div>
        
        <!-- Address Information -->
        <div class="row">
            <p class="text-muted">The correct address should already be registered in the Locations table. If not, please add it there first.</p>

            <?php
            echo ViewHelpers::renderField('select', 'client_province', 'Province', 
                $selected_province, 
                array(
                    'required' => true,
                    'col_class' => 'col-md-3 js-province-field',
                    'class' => 'js-province-select',
                    'options' => $province_options,
                    'error' => $errors['client_province'] ?? ''
                )
            );

            echo ViewHelpers::renderField('select', 'client_town', 'Town', 
                $selected_town, 
                array(
                    'required' => true,
                    'col_class' => 'col-md-3 js-town-field' . ($has_province ? '' : ' d-none'),
                    'class' => 'js-town-select',
                    'options' => $town_options,
                    'error' => ''
                )
            );

            echo ViewHelpers::renderField('select', 'client_town_id', 'Suburb', 
                $selected_location_id, 
                array(
                    'required' => true,
                    'col_class' => 'col-md-3 js-suburb-field' . ($has_town ? '' : ' d-none'),
                    'class' => 'js-suburb-select',
                    'options' => $suburb_options,
                    'error' => $errors['client_town_id'] ?? ($errors['client_suburb'] ?? ($errors['site_place_id'] ?? ''))
                )
            );

            echo ViewHelpers::renderField('text', 'client_postal_code', 'Client Postal Code', 
                $selected_postal_code, 
                array(
                    'required' => true,
                    'readonly' => true,
                    'col_class' => 'col-md-3 js-postal-field' . ($has_location ? '' : ' d-none'),
                    'error' => $errors['client_postal_code'] ?? ''
                )
            );
            ?>
        </div>

        <input type="hidden" name="client_suburb" value="<?php echo esc_attr($selected_suburb); ?>" class="js-suburb-hidden">
        <input type="hidden" name="client_town_name" value="<?php echo esc_attr($selected_town); ?>" class="js-town-hidden">

        <div class="row mt-3">
            <?php
            echo ViewHelpers::renderField('text', 'client_street_address', 'Client Street Address', 
                $headSiteAddress1, 
                array(
                    'required' => true,
                    'readonly' => $has_location,
                    'title' => $has_location ? 'Address auto-populated from location data' : '',
                    'col_class' => 'col-md-3 js-address-field js-street-address-field' . ($has_location ? '' : ' d-none'),
                    'error' => $errors['site_address_line_1'] ?? ($errors['client_street_address'] ?? '')
                )
            );

            echo ViewHelpers::renderField('text', 'client_address_line_2', 'Address Line 2', 
                $headSiteAddress2, 
                array(
                    'readonly' => $has_location,
                    'title' => $has_location ? 'Address line 2 is managed by location system' : '',
                    'col_class' => 'col-md-3 js-address-2-field' . ($has_location ? '' : ' d-none'),
                    'error' => $errors['site_address_line_2'] ?? ''
                )
            );
            ?>
        </div>
        
        <div class="border-top border-opacity-25 border-3 border-discovery my-5 mx-1"></div>
        
        <!-- Contact Information -->
        <div class="row">
            <?php
            echo ViewHelpers::renderField('text', 'contact_person', 'Contact Person', 
                $resolved_contact_person, 
                array(
                    'required' => true,
                    'col_class' => 'col-md-3',
                    'error' => $errors['contact_person'] ?? ''
                )
            );
            
            echo ViewHelpers::renderField('email', 'contact_person_email', 'Contact Person Email', 
                $resolved_contact_email, 
                array(
                    'required' => true,
                    'col_class' => 'col-md-3',
                    'error' => $errors['contact_person_email'] ?? ''
                )
            );
            
            echo ViewHelpers::renderField('tel', 'contact_person_cellphone', 'Contact Person Cellphone', 
                $resolved_contact_cell, 
                array(
                    'required' => true,
                    'col_class' => 'col-md-3',
                    'error' => $errors['contact_person_cellphone'] ?? ''
                )
            );
            
            echo ViewHelpers::renderField('tel', 'contact_person_tel', 'Contact Person Tel Number', 
                $resolved_contact_tel, 
                array(
                    'col_class' => 'col-md-3',
                    'error' => $errors['contact_person_tel'] ?? ''
                )
            );

            echo ViewHelpers::renderField('text', 'contact_person_position', 'Contact Person Position', 
                $resolved_contact_position, 
                array(
                    'col_class' => 'col-md-3',
                    'error' => $errors['contact_person_position'] ?? ''
                )
            );
            ?>
        </div>
        
        <div class="border-top border-opacity-25 border-3 border-discovery my-5 mx-1"></div>
        
        <!-- Business Information -->
        <div class="row mt-3">
            <?php
            // Prepare SETA options for select
            $seta_select_options = array();
            foreach ($seta_options as $seta) {
                $seta_select_options[$seta] = $seta;
            }
            
            echo ViewHelpers::renderField('select', 'seta', 'SETA', 
                $client['seta'] ?? '', 
                array(
                    'required' => true,
                    'col_class' => 'col-md-3',
                    'options' => $seta_select_options,
                    'error' => $errors['seta'] ?? ''
                )
            );
            
            echo ViewHelpers::renderField('select', 'client_status', 'Client Status', 
                $client['client_status'] ?? '', 
                array(
                    'required' => true,
                    'col_class' => 'col-md-3',
                    'options' => $status_options,
                    'error' => $errors['client_status'] ?? ''
                )
            );
            
            echo ViewHelpers::renderField('date', 'financial_year_end', 'Financial Year End', 
                $client['financial_year_end'] ?? '', 
                array(
                    'required' => true,
                    'col_class' => 'col-md-3',
                    'error' => $errors['financial_year_end'] ?? ''
                )
            );

            echo ViewHelpers::renderField('date', 'bbbee_verification_date', 'BBBEE Verification Date', 
                $client['bbbee_verification_date'] ?? '', 
                array(
                    'required' => true,
                    'col_class' => 'col-md-3',
                    'error' => $errors['bbbee_verification_date'] ?? ''
                )
            );
            ?>
        </div>
        
        <div class="border-top border-opacity-25 border-3 border-discovery my-5 mx-1"></div>
        
        <!-- Form Actions -->
        <div class="row">
            <div class="col-md-3">
                <div class="d-flex gap-2">
                    <a href="<?php echo esc_url(remove_query_arg(['mode', 'client_id'])); ?>" class="btn btn-outline-secondary mt-3">
                        Cancel
                    </a>
                    <button type="submit" class="btn btn-subtle-primary mt-3" id="saveClientBtn">
                        Update Client
                    </button>
                </div>
            </div>
        </div>
    </form>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Form validation
    var form = document.getElementById('clients-form');
    var submitBtn = document.getElementById('saveClientBtn');
    
    form.addEventListener('submit', function(event) {
        event.preventDefault();
        
        if (!form.checkValidity()) {
            event.stopPropagation();
            form.classList.add('was-validated');
            return;
        }
        
        // Disable submit button and show loading
        submitBtn.disabled = true;
        submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-1" role="status"></span>Updating...';
        
        // Create form data
        var formData = new FormData(form);
        
        // Submit via AJAX
        fetch('<?php echo esc_url(admin_url('admin-ajax.php')); ?>', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Show success message and reload
                location.reload();
            } else {
                // Show error message
                alert('Error: ' + (data.message || 'Failed to update client. Please try again.'));
                submitBtn.disabled = false;
                submitBtn.innerHTML = 'Update Client';
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('An error occurred. Please try again.');
            submitBtn.disabled = false;
            submitBtn.innerHTML = 'Update Client';
        });
    }, false);

    // Sub-client checkbox functionality
    var isSubClientCheckbox = document.getElementById('is_sub_client');
    var mainClientDropdownContainer = document.getElementById('main_client_dropdown_container');
    var mainClientSelect = document.querySelector('.js-main-client-select');
    
    if (isSubClientCheckbox && mainClientDropdownContainer) {
        isSubClientCheckbox.addEventListener('change', function() {
            var isChecked = this.checked;
            mainClientDropdownContainer.style.display = isChecked ? 'block' : 'none';
            
            // Handle required attribute
            if (mainClientSelect) {
                if (isChecked) {
                    mainClientSelect.setAttribute('required', 'required');
                } else {
                    mainClientSelect.removeAttribute('required');
                    mainClientSelect.value = '';
                }
            }
        });
        
        // Initialize state
        var initiallyChecked = isSubClientCheckbox.checked;
        mainClientDropdownContainer.style.display = initiallyChecked ? 'block' : 'none';
        if (mainClientSelect) {
            if (initiallyChecked) {
                mainClientSelect.setAttribute('required', 'required');
            } else {
                mainClientSelect.removeAttribute('required');
            }
        }
    }

    // Province/Town/Suburb cascade
    const provinceSelect = document.querySelector('.js-province-select');
    const townSelect = document.querySelector('.js-town-select');
    const suburbSelect = document.querySelector('.js-suburb-select');
    const postalField = document.querySelector('.js-postal-field');
    const addressField = document.querySelector('.js-address-field');
    const address2Field = document.querySelector('.js-address-2-field');
    
    const locationData = <?php echo json_encode($location_hierarchy); ?>;
    
    function updateTownOptions() {
        const selectedProvince = provinceSelect.value;
        const province = locationData.find(p => p.name === selectedProvince);
        const towns = province?.towns || [];
        
        // Clear current options
        townSelect.innerHTML = '<option value="">Select Town</option>';
        suburbSelect.innerHTML = '<option value="">Select Suburb</option>';
        
        // Hide town, suburb, postal, and address fields initially
        townSelect.closest('.js-town-field').classList.add('d-none');
        suburbSelect.closest('.js-suburb-field').classList.add('d-none');
        postalField.classList.add('d-none');
        addressField.classList.add('d-none');
        address2Field.classList.add('d-none');
        
        if (towns.length > 0) {
            towns.forEach(town => {
                const option = document.createElement('option');
                option.value = town.name;
                option.textContent = town.name;
                townSelect.appendChild(option);
            });
            
            // Show town field
            townSelect.closest('.js-town-field').classList.remove('d-none');
        }
    }
    
    function updateSuburbOptions() {
        const selectedProvince = provinceSelect.value;
        const selectedTown = townSelect.value;
        const province = locationData.find(p => p.name === selectedProvince);
        const town = province?.towns.find(t => t.name === selectedTown);
        const suburbs = town?.suburbs || [];
        
        // Clear current options
        suburbSelect.innerHTML = '<option value="">Select Suburb</option>';
        
        // Hide suburb, postal, and address fields initially
        suburbSelect.closest('.js-suburb-field').classList.add('d-none');
        postalField.classList.add('d-none');
        addressField.classList.add('d-none');
        address2Field.classList.add('d-none');
        
        if (suburbs.length > 0) {
            suburbs.forEach(suburb => {
                const option = document.createElement('option');
                option.value = suburb.id;
                option.textContent = suburb.name;
                option.dataset.postalCode = suburb.postal_code || '';
                option.dataset.suburb = suburb.name || '';
                option.dataset.address = suburb.address || '';
                option.dataset.address2 = suburb.address_line_2 || '';
                suburbSelect.appendChild(option);
            });
            
            // Show suburb field
            suburbSelect.closest('.js-suburb-field').classList.remove('d-none');
        }
    }
    
    function updateAddressFields() {
        const selectedOption = suburbSelect.options[suburbSelect.selectedIndex];
        const postalCodeInput = document.querySelector('input[name="client_postal_code"]');
        const streetAddressInput = document.querySelector('input[name="client_street_address"]');
        const address2Input = document.querySelector('input[name="client_address_line_2"]');
        const suburbHidden = document.querySelector('.js-suburb-hidden');
        const townHidden = document.querySelector('.js-town-hidden');
        
        if (selectedOption && selectedOption.value) {
            const postalCode = selectedOption.dataset.postalCode || '';
            const address = selectedOption.dataset.address || '';
            const address2 = selectedOption.dataset.address2 || '';
            const suburb = selectedOption.dataset.suburb || '';
            const town = townSelect.value || '';
            
            // Update field values
            postalCodeInput.value = postalCode;
            streetAddressInput.value = address;
            address2Input.value = address2;
            suburbHidden.value = suburb;
            townHidden.value = town;
            
            // Show postal and address fields
            postalField.classList.remove('d-none');
            addressField.classList.remove('d-none');
            address2Field.classList.remove('d-none');
        } else {
            // Hide fields and clear values
            postalField.classList.add('d-none');
            addressField.classList.add('d-none');
            address2Field.classList.add('d-none');
            
            postalCodeInput.value = '';
            streetAddressInput.value = '';
            address2Input.value = '';
            suburbHidden.value = '';
            townHidden.value = '';
        }
    }
    
    if (provinceSelect) {
        provinceSelect.addEventListener('change', updateTownOptions);
    }
    if (townSelect) {
        townSelect.addEventListener('change', updateSuburbOptions);
    }
    if (suburbSelect) {
        suburbSelect.addEventListener('change', updateAddressFields);
    }
    
    // Initialize with existing values
    if (provinceSelect.value) {
        updateTownOptions();
        if (townSelect.value) {
            townSelect.value = '<?php echo esc_js($selected_town); ?>';
            updateSuburbOptions();
            if (suburbSelect.value) {
                suburbSelect.value = '<?php echo esc_js($selected_location_id); ?>';
                updateAddressFields();
            }
        }
    }
});
</script>
