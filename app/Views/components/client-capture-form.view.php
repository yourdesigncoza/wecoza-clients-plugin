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
$branches = $branches ?? array();
$seta_options = $seta_options ?? array();
$status_options = $status_options ?? array();

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
            <?php
            echo ViewHelpers::renderField('text', 'client_name', 'Client Name', 
                $client['client_name'] ?? '', 
                array(
                    'required' => true,
                    'col_class' => 'col-md-4',
                    'error' => $errors['client_name'] ?? ''
                )
            );
            
            // Prepare branch options
            $branch_options = array('' => 'Select');
            foreach ($branches as $branch) {
                if (!$is_edit || $branch['id'] != $client['id']) {
                    $branch_options[$branch['id']] = $branch['client_name'] . ' (' . $branch['company_registration_nr'] . ')';
                }
            }
            
            echo ViewHelpers::renderField('select', 'branch_of', 'Branch of', 
                $client['branch_of'] ?? '', 
                array(
                    'col_class' => 'col-md-4',
                    'options' => $branch_options,
                    'error' => $errors['branch_of'] ?? ''
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
            echo ViewHelpers::renderField('text', 'client_street_address', 'Client Street Address', 
                $client['client_street_address'] ?? '', 
                array(
                    'required' => true,
                    'col_class' => 'col-md-6',
                    'error' => $errors['client_street_address'] ?? ''
                )
            );
            
            echo ViewHelpers::renderField('text', 'client_suburb', 'Client Suburb', 
                $client['client_suburb'] ?? '', 
                array(
                    'required' => true,
                    'col_class' => 'col-md-6',
                    'error' => $errors['client_suburb'] ?? ''
                )
            );
            ?>
        </div>
        
        <div class="row mt-3">
            <?php
            echo ViewHelpers::renderField('text', 'client_town', 'Client Town', 
                $client['client_town'] ?? '', 
                array(
                    'required' => true,
                    'col_class' => 'col-md-6',
                    'error' => $errors['client_town'] ?? ''
                )
            );
            
            echo ViewHelpers::renderField('text', 'client_postal_code', 'Client Postal Code', 
                $client['client_postal_code'] ?? '', 
                array(
                    'required' => true,
                    'col_class' => 'col-md-6',
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
            echo ViewHelpers::renderField('file', 'quotes', 'Quotes', 
                $client['quotes'] ?? '', 
                array(
                    'col_class' => 'col-md-6',
                    'multiple' => true,
                    'accept' => '.pdf,.doc,.docx,.xls,.xlsx',
                    'error' => $errors['quotes'] ?? ''
                )
            );
            
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
        
        <p class="mb4 text-muted">Classes Fields (to be integrated after Classes are finalized)</p>
        
        <!-- Class Related Fields -->
        <div class="row">
            <?php
            echo ViewHelpers::renderField('textarea', 'current_classes', 'Current Classes', 
                $client['current_classes'] ?? '', 
                array(
                    'col_class' => 'col-md-6',
                    'rows' => 3,
                    'error' => $errors['current_classes'] ?? ''
                )
            );
            
            echo ViewHelpers::renderField('textarea', 'stopped_classes', 'Stopped Classes', 
                $client['stopped_classes'] ?? '', 
                array(
                    'col_class' => 'col-md-6',
                    'rows' => 3,
                    'error' => $errors['stopped_classes'] ?? ''
                )
            );
            ?>
        </div>
        
        <div class="row mt-3">
            <?php
            echo ViewHelpers::renderField('textarea', 'deliveries', 'Deliveries', 
                $client['deliveries'] ?? '', 
                array(
                    'col_class' => 'col-md-6',
                    'rows' => 3,
                    'error' => $errors['deliveries'] ?? ''
                )
            );
            
            echo ViewHelpers::renderField('textarea', 'collections', 'Collections', 
                $client['collections'] ?? '', 
                array(
                    'col_class' => 'col-md-6',
                    'rows' => 3,
                    'error' => $errors['collections'] ?? ''
                )
            );
            ?>
        </div>
        
        <div class="row mt-3">
            <?php
            echo ViewHelpers::renderField('textarea', 'cancellations', 'Cancellations', 
                $client['cancellations'] ?? '', 
                array(
                    'col_class' => 'col-md-6',
                    'rows' => 3,
                    'error' => $errors['cancellations'] ?? ''
                )
            );
            ?>
        </div>
        
        <div class="border-top border-opacity-25 border-3 border-discovery my-5 mx-1"></div>
        
        <!-- Date Fields -->
        <div class="row">
            <?php
            echo ViewHelpers::renderField('date', 'class_restarts', 'Class Restarts', 
                $client['class_restarts'] ?? '', 
                array(
                    'col_class' => 'col-md-6',
                    'error' => $errors['class_restarts'] ?? ''
                )
            );
            
            echo ViewHelpers::renderField('date', 'class_stops', 'Class Stops', 
                $client['class_stops'] ?? '', 
                array(
                    'col_class' => 'col-md-6',
                    'error' => $errors['class_stops'] ?? ''
                )
            );
            ?>
        </div>
        
        <div class="row mt-3">
            <?php
            echo ViewHelpers::renderField('textarea', 'assessments', 'Assessments', 
                $client['assessments'] ?? '', 
                array(
                    'col_class' => 'col-md-6',
                    'rows' => 3,
                    'error' => $errors['assessments'] ?? ''
                )
            );
            
            echo ViewHelpers::renderField('textarea', 'progressions', 'Progressions', 
                $client['progressions'] ?? '', 
                array(
                    'col_class' => 'col-md-6',
                    'rows' => 3,
                    'error' => $errors['progressions'] ?? ''
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