<?php
/**
 * Client Capture Form View
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
?>

<div class="wecoza-clients-form-container">
    <?php if ($success) : ?>
        <?php echo ViewHelpers::renderAlert(
            $is_edit ? 'Client updated successfully!' : 'Client created successfully!',
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
    
    <?php if (!$is_edit) : ?>
            <h4 class="mb-1 mt-4">Create a new Client</h4>
            <p class="mb-5 text-muted">Before you start the upload process ensure you have all info. ready.</p>
    <?php endif; ?>
    
    <form id="clients-form" class="needs-validation ydcoza-compact-form" novalidate method="POST" enctype="multipart/form-data">
        <?php wp_nonce_field('submit_clients_form', 'wecoza_clients_form_nonce'); ?>
        
        <?php if ($is_edit) : ?>
            <input type="hidden" name="id" value="<?php echo esc_attr($client['id']); ?>">
        <?php endif; ?>
        
        <!-- Basic Information -->
        <div class="row">
            <input type="hidden" name="head_site_id" value="<?php echo esc_attr($headSiteId); ?>">
            <?php
            echo ViewHelpers::renderField('text', 'client_name', 'Client Name', 
                $client['client_name'] ?? '', 
                array(
                    'required' => true,
                    'col_class' => 'col-md-4',
                    'error' => $errors['client_name'] ?? ''
                )
            );
            
            echo ViewHelpers::renderField('text', 'head_site_name', 'Head Site Name', 
                $headSiteName, 
                array(
                    'required' => true,
                    'col_class' => 'col-md-4',
                    'error' => $errors['site_site_name'] ?? ''
                )
            );
            
            echo ViewHelpers::renderField('text', 'company_registration_nr', 'Company Registration Nr', 
                $client['company_registration_nr'] ?? '', 
                array(
                    'required' => true,
                    'col_class' => 'col-md-4',
                    'error' => $errors['company_registration_nr'] ?? ''
                )
            );
            ?>
        </div>
        
        <div class="border-top border-opacity-25 border-3 border-discovery my-5 mx-1"></div>
        
        <!-- Address Information -->
        <div class="row">
            <?php
            echo ViewHelpers::renderField('select', 'client_province', 'Province', 
                $selected_province, 
                array(
                    'required' => true,
                    'col_class' => 'col-md-4 js-province-field',
                    'class' => 'js-province-select',
                    'options' => $province_options,
                    'error' => $errors['client_province'] ?? ''
                )
            );

            echo ViewHelpers::renderField('select', 'client_town', 'Town', 
                $selected_town, 
                array(
                    'required' => true,
                    'col_class' => 'col-md-4 js-town-field' . ($has_province ? '' : ' d-none'),
                    'class' => 'js-town-select',
                    'options' => $town_options,
                    'error' => ''
                )
            );

            echo ViewHelpers::renderField('select', 'client_town_id', 'Suburb', 
                $selected_location_id, 
                array(
                    'required' => true,
                    'col_class' => 'col-md-4 js-suburb-field' . ($has_town ? '' : ' d-none'),
                    'class' => 'js-suburb-select',
                    'options' => $suburb_options,
                    'error' => $errors['client_town_id'] ?? ($errors['client_suburb'] ?? ($errors['site_place_id'] ?? ''))
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
                    'col_class' => 'col-md-6 js-address-field' . ($has_location ? '' : ' d-none'),
                    'error' => $errors['site_address_line_1'] ?? ($errors['client_street_address'] ?? '')
                )
            );

            echo ViewHelpers::renderField('text', 'client_address_line_2', 'Address Line 2', 
                $headSiteAddress2, 
                array(
                    'col_class' => 'col-md-6 js-address-2-field' . ($has_location ? '' : ' d-none'),
                    'error' => $errors['site_address_line_2'] ?? ''
                )
            );

            echo ViewHelpers::renderField('text', 'client_postal_code', 'Client Postal Code', 
                $selected_postal_code, 
                array(
                    'required' => true,
                    'readonly' => true,
                    'col_class' => 'col-md-6 js-postal-field' . ($has_location ? '' : ' d-none'),
                    'error' => $errors['client_postal_code'] ?? ''
                )
            );
            ?>
        </div>
        
        <div class="border-top border-opacity-25 border-3 border-discovery my-5 mx-1"></div>
        
        <!-- Contact Information -->
        <div class="row">
            <?php
            echo ViewHelpers::renderField('text', 'contact_person', 'Contact Person', 
                $client['contact_person'] ?? '', 
                array(
                    'required' => true,
                    'col_class' => 'col-md-3',
                    'error' => $errors['contact_person'] ?? ''
                )
            );
            
            echo ViewHelpers::renderField('email', 'contact_person_email', 'Contact Person Email', 
                $client['contact_person_email'] ?? '', 
                array(
                    'required' => true,
                    'col_class' => 'col-md-3',
                    'error' => $errors['contact_person_email'] ?? ''
                )
            );
            
            echo ViewHelpers::renderField('tel', 'contact_person_cellphone', 'Contact Person Cellphone', 
                $client['contact_person_cellphone'] ?? '', 
                array(
                    'required' => true,
                    'col_class' => 'col-md-3',
                    'error' => $errors['contact_person_cellphone'] ?? ''
                )
            );
            
            echo ViewHelpers::renderField('tel', 'contact_person_tel', 'Contact Person Tel Number', 
                $client['contact_person_tel'] ?? '', 
                array(
                    'col_class' => 'col-md-3',
                    'error' => $errors['contact_person_tel'] ?? ''
                )
            );
            ?>
        </div>
        
        <div class="border-top border-opacity-25 border-3 border-discovery my-5 mx-1"></div>
        
        <!-- Business Information -->
        <div class="row">
            <?php
            // Prepare status options for select
            $comm_options = array();
            foreach ($status_options as $key => $value) {
                $comm_options[$key] = $value;
            }
            
            echo ViewHelpers::renderField('select', 'client_communication', 'Client Communication', 
                $client['client_communication'] ?? '', 
                array(
                    'required' => true,
                    'col_class' => 'col-md-6',
                    'options' => $comm_options,
                    'error' => $errors['client_communication'] ?? ''
                )
            );
            ?>
        </div>
        
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
                    'col_class' => 'col-md-4',
                    'options' => $seta_select_options,
                    'error' => $errors['seta'] ?? ''
                )
            );
            
            echo ViewHelpers::renderField('select', 'client_status', 'Client Status', 
                $client['client_status'] ?? '', 
                array(
                    'required' => true,
                    'col_class' => 'col-md-4',
                    'options' => $status_options,
                    'error' => $errors['client_status'] ?? ''
                )
            );
            
            echo ViewHelpers::renderField('date', 'financial_year_end', 'Financial Year End', 
                $client['financial_year_end'] ?? '', 
                array(
                    'required' => true,
                    'col_class' => 'col-md-4',
                    'error' => $errors['financial_year_end'] ?? ''
                )
            );
            ?>
        </div>
        
        <div class="row mt-3">
            <?php
            echo ViewHelpers::renderField('date', 'bbbee_verification_date', 'BBBEE Verification Date', 
                $client['bbbee_verification_date'] ?? '', 
                array(
                    'required' => true,
                    'col_class' => 'col-md-4',
                    'error' => $errors['bbbee_verification_date'] ?? ''
                )
            );
            ?>
        </div>
        
        <div class="border-top border-opacity-25 border-3 border-discovery my-5 mx-1"></div>
        
        <!-- Submit Button -->
        <div class="col-md-3">
            <button type="submit" class="btn btn-primary mt-3">
                <?php echo $is_edit ? 'Update Client' : 'Add New Client'; ?>
            </button>
        </div>
    </form>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Form validation
    var form = document.getElementById('clients-form');
    
    form.addEventListener('submit', function(event) {
        if (!form.checkValidity()) {
            event.preventDefault();
            event.stopPropagation();
        }
        
        form.classList.add('was-validated');
    }, false);
});
</script>