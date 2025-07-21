<?php
   /**
    * Shortcode to capture and edit client information with Bootstrap (needs-validation) style.
    * Includes server-side validation and example client-side markup for valid/invalid feedback.
    */
   
   function clients_capture_shortcode($atts) {
       // global $wpdb;
       $client_id='';
       $locations='';
   
       // Initialize variables for form errors and data
       $form_error = false;
       $error_messages = [];
       $data = [];
   
       // Fetch locations and other dropdown data outside the form submission block
       $db = new learner_DB();
       // Below calls are now called via Ajax
       // $locations = $db->get_locations();
       // $client_statuses = $db->get_client_statuses();
       // $setas = $db->get_setas();
   
       // Check if form is submitted
       if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['wecoza_clients_form_nonce']) && wp_verify_nonce($_POST['wecoza_clients_form_nonce'], 'submit_clients_form')) {
   
           /*------------------YDCOZA-----------------------*/
           /* Sanitize and prepare form inputs              */
           /* Ensures all input fields are properly         */
           /* sanitized before inserting them into the DB   */
           /*-----------------------------------------------*/
           $data = [
               'client_name' => sanitize_text_field($_POST['client_name']),
               'branch_of' => sanitize_text_field($_POST['branch_of']),
               'company_registration_nr' => sanitize_text_field($_POST['company_registration_nr']),
               'client_street_address' => sanitize_text_field($_POST['client_street_address']),
               'client_suburb' => sanitize_text_field($_POST['client_suburb']),
               'client_town' => sanitize_text_field($_POST['client_town']),
               'client_postal_code' => sanitize_text_field($_POST['client_postal_code']),
               'contact_person' => sanitize_text_field($_POST['contact_person']),
               'contact_person_email' => sanitize_email($_POST['contact_person_email']),
               'contact_person_cellphone' => sanitize_text_field($_POST['contact_person_cellphone']),
               'contact_person_tel' => sanitize_text_field($_POST['contact_person_tel']),
               'client_communication' => sanitize_text_field($_POST['client_communication']),
               'seta' => sanitize_text_field($_POST['seta']),
               'client_status' => sanitize_text_field($_POST['client_status']),
               'financial_year_end' => sanitize_text_field($_POST['financial_year_end']),
               'bbbee_verification_date' => sanitize_text_field($_POST['bbbee_verification_date']),
               'current_classes' => sanitize_text_field($_POST['current_classes']),
               'stopped_classes' => sanitize_text_field($_POST['stopped_classes']),
               'deliveries' => sanitize_text_field($_POST['deliveries']),
               'collections' => sanitize_text_field($_POST['collections']),
               'cancellations' => sanitize_text_field($_POST['cancellations']),
               'class_restarts' => sanitize_text_field($_POST['class_restarts']),
               'class_stops' => sanitize_text_field($_POST['class_stops']),
               'assessments' => sanitize_text_field($_POST['assessments']),
               'progressions' => sanitize_text_field($_POST['progressions']),
               'created_at' => date('Y-m-d H:i:s'),
               'updated_at' => date('Y-m-d H:i:s')
           ];
   
           /*------------------YDCOZA-----------------------*/
           /* Validate required fields                      */
           /* Ensure that required fields are provided.     */
           /* If any are missing, show error.               */
           /*-----------------------------------------------*/
   
           if (empty($_POST['client_name'])) {
               $form_error = true;
               $error_messages[] = 'You must provide a Client Name.';
           }
   
           if (empty($_POST['company_registration_nr'])) {
               $form_error = true;
               $error_messages[] = 'You must provide a Company Registration Number.';
           }
   
           // Proceed only if there are no errors
           if (!$form_error) {
               // Ensure date fields are valid
               $data['financial_year_end'] = !empty($data['financial_year_end']) ? $data['financial_year_end'] : null;
               $data['bbbee_verification_date'] = !empty($data['bbbee_verification_date']) ? $data['bbbee_verification_date'] : null;
               $data['class_restarts'] = !empty($data['class_restarts']) ? $data['class_restarts'] : null;
               $data['class_stops'] = !empty($data['class_stops']) ? $data['class_stops'] : null;
   
               // Handle file uploads for quotes if files were submitted
               $quotes_path = '';
               if (isset($_FILES['quotes']) && !empty($_FILES['quotes']['name'][0])) {
                   // Process file upload logic here
                   // $upload_result = $db->saveClientQuotes($client_id, $_FILES['quotes']);
                   // $quotes_path = $upload_result['path'] ?? '';
               }
               $data['quotes'] = $quotes_path;
   
               // Insert client using DB class and get client ID
               // $client_id = $db->insert_client($data);
               
               if ($client_id) {
                   echo '<div class="alert alert-success alert-dismissible fade show ydcoza-notification ydcoza-auto-close" role="alert"><div class="d-flex gap-4"><span><i class="fa-solid fa-circle-check icon-success"></i></span><div>Client Added successfully!</div></div><button type="button" class="btn-close btn-close-sm" data-bs-dismiss="alert" aria-label="Close"></button></div>';
               } else {
                   echo '<div class="alert alert-danger alert-dismissible fade show ydcoza-notification" role="alert"><button type="button" class="btn-close btn-close-sm" data-bs-dismiss="alert" aria-label="Close"></button><div class="d-flex gap-4"><span><i class="fa-solid fa-circle-exclamation icon-danger"></i></span><div class="d-flex flex-column gap-2"><h6 class="mb-0">ERROR !</h6><p class="mb-0">There was an error inserting the client. Please try again.</p></div></div></div>';
               }
           } else {
               // Display all error messages
               foreach ($error_messages as $message) {
                   echo '<p class="text-danger">' . esc_html($message) . '</p>';
               }
           }
       }
   
       // Build the form
       ob_start(); 
       ?>
<!--
   Example of combining the row/col approach from your snippet.
   Add or remove columns as fits your layout.
   Note: The 'needs-validation' class and the HTML5 'required' attributes
   support client-side validation, but the final authority is our server-side checks above.
   -->
<div class="my-5 ms-0">
   <div class="alert alert-discovery" role="alert">
      <div class="d-flex gap-4">
         <span><i class="fa-solid fa-circle-question icon-discovery"></i></span>
         <div class="d-flex flex-column gap-2">
            <h5 class="mb-0">Create a new Client</h5>
            <p class="mb-0">Before you start the upload process ensure you have all info. ready.</p>
         </div>
      </div>
   </div>
</div>
<form id="clients-form" class="needs-validation ydcoza-compact-form" novalidate method="POST" enctype="multipart/form-data">
   <?php wp_nonce_field('submit_clients_form', 'wecoza_clients_form_nonce'); ?>
   <?php wp_nonce_field('save_client_data', 'client_form_nonce'); ?>
   <!-- Client ID (read-only if editing) -->
   <?php if ($client_id) : ?>
   <div class="col-md-3">
      <label for="id" class="form-label">Client ID</label>
      <input type="text" id="id" name="id" class="form-control form-control-sm" value="<?php echo esc_attr($client_id); ?>" readonly>
      <div class="valid-feedback">Looks good!</div>
   </div>
   <?php endif; ?>
   <div class="row">
      <!-- Client Name -->
      <div class="col-md-4">
         <label for="client_name" class="form-label">Client Name <span class="text-danger">*</span></label>
         <input type="text" id="client_name" name="client_name" class="form-control form-control-sm" value="<?php echo esc_attr($client['client_name'] ?? ''); ?>" required>
         <div class="invalid-feedback">Please provide the client name.</div>
         <div class="valid-feedback">Looks good!</div>
      </div>
      <!-- Branch of -->
      <div class="col-md-4">
         <label for="branch_of" class="form-label">Branch of</label>
         <select id="branch_of" name="branch_of" class="form-select form-select-sm">
            <option value="">Select</option>
            <!-- Options will be populated via AJAX -->
         </select>
         <div class="valid-feedback">Looks good!</div>
      </div>
      <!-- Company Registration Nr -->
      <div class="col-md-4">
         <label for="company_registration_nr" class="form-label">Company Registration Nr <span class="text-danger">*</span></label>
         <input type="text" id="company_registration_nr" name="company_registration_nr" class="form-control form-control-sm" value="<?php echo esc_attr($client['company_registration_nr'] ?? ''); ?>" required>
         <div class="invalid-feedback">Please provide the company registration number.</div>
         <div class="valid-feedback">Looks good!</div>
      </div>
   </div>
   <div class="border-top border-opacity-25 border-3 border-discovery my-5 mx-1"></div>
   <div class="row">
      <!-- Client Street Address -->
      <div class="col-md-6">
         <label for="client_street_address" class="form-label">Client Street Address <span class="text-danger">*</span></label>
         <input type="text" id="client_street_address" name="client_street_address" class="form-control form-control-sm" value="<?php echo esc_attr($client['client_street_address'] ?? ''); ?>" required>
         <div class="invalid-feedback">Please provide the client street address.</div>
         <div class="valid-feedback">Looks good!</div>
      </div>
      <!-- Client Suburb -->
      <div class="col-md-6">
         <label for="client_suburb" class="form-label">Client Suburb <span class="text-danger">*</span></label>
         <input type="text" id="client_suburb" name="client_suburb" class="form-control form-control-sm" value="<?php echo esc_attr($client['client_suburb'] ?? ''); ?>" required>
         <div class="invalid-feedback">Please provide the client suburb.</div>
         <div class="valid-feedback">Looks good!</div>
      </div>
   </div>
   <div class="row mt-3">
      <!-- Client Town -->
      <div class="col-md-6">
         <label for="client_town" class="form-label">Client Town <span class="text-danger">*</span></label>
         <input type="text" id="client_town" name="client_town" class="form-control form-control-sm" value="<?php echo esc_attr($client['client_town'] ?? ''); ?>" required>
         <div class="invalid-feedback">Please provide the client town.</div>
         <div class="valid-feedback">Looks good!</div>
      </div>
      <!-- Client Postal Code -->
      <div class="col-md-6">
         <label for="client_postal_code" class="form-label">Client Postal Code <span class="text-danger">*</span></label>
         <input type="text" id="client_postal_code" name="client_postal_code" class="form-control form-control-sm" value="<?php echo esc_attr($client['client_postal_code'] ?? ''); ?>" required>
         <div class="invalid-feedback">Please provide the client postal code.</div>
         <div class="valid-feedback">Looks good!</div>
      </div>
   </div>
   <div class="border-top border-opacity-25 border-3 border-discovery my-5 mx-1"></div>
   <div class="row">
      <!-- Contact Person -->
      <div class="col-md-3">
         <label for="contact_person" class="form-label">Contact Person <span class="text-danger">*</span></label>
         <input type="text" id="contact_person" name="contact_person" class="form-control form-control-sm" value="<?php echo esc_attr($client['contact_person'] ?? ''); ?>" required>
         <div class="invalid-feedback">Please provide the contact person.</div>
         <div class="valid-feedback">Looks good!</div>
      </div>
      <!-- Contact Person Email -->
      <div class="col-md-3">
         <label for="contact_person_email" class="form-label">Contact Person Email <span class="text-danger">*</span></label>
         <input type="email" id="contact_person_email" name="contact_person_email" class="form-control form-control-sm" value="<?php echo esc_attr($client['contact_person_email'] ?? ''); ?>" required>
         <div class="invalid-feedback">Please provide a valid email address.</div>
         <div class="valid-feedback">Looks good!</div>
      </div>
      <!-- Contact Person Cellphone -->
      <div class="col-md-3">
         <label for="contact_person_cellphone" class="form-label">Contact Person Cellphone <span class="text-danger">*</span></label>
         <input type="text" id="contact_person_cellphone" name="contact_person_cellphone" class="form-control form-control-sm" value="<?php echo esc_attr($client['contact_person_cellphone'] ?? ''); ?>" required>
         <div class="invalid-feedback">Please provide the contact person's cellphone number.</div>
         <div class="valid-feedback">Looks good!</div>
      </div>
      <!-- Contact Person Tel Number -->
      <div class="col-md-3">
         <label for="contact_person_tel" class="form-label">Contact Person Tel Number</label>
         <input type="text" id="contact_person_tel" name="contact_person_tel" class="form-control form-control-sm" value="<?php echo esc_attr($client['contact_person_tel'] ?? ''); ?>">
         <div class="valid-feedback">Looks good!</div>
      </div>
   </div>
   <div class="border-top border-opacity-25 border-3 border-discovery my-5 mx-1"></div>
   <div class="row">
      <!-- Quotes -->
      <div class="col-md-6">
         <label for="quotes" class="form-label">Quotes</label>
         <input type="file" id="quotes" name="quotes[]" class="form-control form-control-sm" multiple>
         <?php if (!empty($client['quotes'])): ?>
         <p class="mt-1">
            Current file(s):
            <a href="<?php echo esc_url($client['quotes']); ?>" target="_blank">View</a>
         </p>
         <?php endif; ?>
         <div class="valid-feedback">Looks good!</div>
      </div>
      <!-- Client Communication -->
      <div class="col-md-6">
         <label for="client_communication" class="form-label">Client Communication <span class="text-danger">*</span></label>
         <select id="client_communication" name="client_communication" class="form-select form-select-sm" required>
            <option value="">Select</option>
            <option value="Cold Call" <?php selected($client['client_communication'] ?? '', 'Cold Call'); ?>>Cold Call</option>
            <option value="Lead" <?php selected($client['client_communication'] ?? '', 'Lead'); ?>>Lead</option>
            <option value="Active Client" <?php selected($client['client_communication'] ?? '', 'Active Client'); ?>>Active Client</option>
            <option value="Lost Client" <?php selected($client['client_communication'] ?? '', 'Lost Client'); ?>>Lost Client</option>
         </select>
         <div class="invalid-feedback">Please select the client communication status.</div>
         <div class="valid-feedback">Looks good!</div>
      </div>
   </div>
   <div class="row mt-3">
      <!-- SETA -->
      <div class="col-md-4">
         <label for="seta" class="form-label">SETA <span class="text-danger">*</span></label>
         <select id="seta" name="seta" class="form-select form-select-sm" required>
            <option value="">Select</option>
            <option value="AgriSETA">AgriSETA</option>
            <option value="BANKSETA">BANKSETA</option>
            <option value="CATHSSETA">CATHSSETA</option>
            <option value="CETA">CETA</option>
            <option value="CHIETA">CHIETA</option>
            <option value="ETDP SETA">ETDP SETA</option>
            <option value="EWSETA">EWSETA</option>
            <option value="FASSET">FASSET</option>
            <option value="FP&M SETA">FP&M SETA</option>
            <option value="FoodBev SETA">FoodBev SETA</option>
            <option value="HWSETA">HWSETA</option>
            <option value="INSETA">INSETA</option>
            <option value="LGSETA">LGSETA</option>
            <option value="MICT SETA">MICT SETA</option>
            <option value="MQA">MQA</option>
            <option value="PSETA">PSETA</option>
            <option value="SASSETA">SASSETA</option>
            <option value="Services SETA">Services SETA</option>
            <option value="TETA">TETA</option>
            <option value="W&RSETA">W&RSETA</option>
            <option value="merSETA">merSETA</option>
         </select>
         <div class="invalid-feedback">Please provide the SETA information.</div>
         <div class="valid-feedback">Looks good!</div>
      </div>
      <!-- Client Status -->
      <div class="col-md-4">
         <label for="client_status" class="form-label">Client Status <span class="text-danger">*</span></label>
         <select id="client_status" name="client_status" class="form-select form-select-sm" required>
            <option value="">Select</option>
            <option value="Cold Call" <?php selected($client['client_status'] ?? '', 'Cold Call'); ?>>Cold Call</option>
            <option value="Lead" <?php selected($client['client_status'] ?? '', 'Lead'); ?>>Lead</option>
            <option value="Active Client" <?php selected($client['client_status'] ?? '', 'Active Client'); ?>>Active Client</option>
            <option value="Lost Client" <?php selected($client['client_status'] ?? '', 'Lost Client'); ?>>Lost Client</option>
         </select>
         <div class="invalid-feedback">Please select the client status.</div>
         <div class="valid-feedback">Looks good!</div>
      </div>
      <!-- Financial Year End -->
      <div class="col-md-4">
         <label for="financial_year_end" class="form-label">Financial Year End <span class="text-danger">*</span></label>
         <input type="date" id="financial_year_end" name="financial_year_end" class="form-control form-control-sm" value="<?php echo esc_attr($client['financial_year_end'] ?? ''); ?>" required>
         <div class="invalid-feedback">Please provide the financial year end date.</div>
         <div class="valid-feedback">Looks good!</div>
      </div>
   </div>
   <div class="row mt-3">
      <!-- BBBEE Verification Date -->
      <div class="col-md-4">
         <label for="bbbee_verification_date" class="form-label">BBBEE Verification Date <span class="text-danger">*</span></label>
         <input type="date" id="bbbee_verification_date" name="bbbee_verification_date" class="form-control form-control-sm" value="<?php echo esc_attr($client['bbbee_verification_date'] ?? ''); ?>" required>
         <div class="invalid-feedback">Please provide the BBBEE verification date.</div>
         <div class="valid-feedback">Looks good!</div>
      </div>
   </div>
   <div class="border-top border-opacity-25 border-3 border-discovery my-5 mx-1"></div>
   <h 3 style="color:#007bff;">
   Classes Fields to be integrated after "Classes" are finalised</h3>
   <div class="row">
      <!-- Current Classes -->
      <div class="col-md-6">
         <label for="current_classes" class="form-label">Current Classes</label>
         <textarea id="current_classes" name="current_classes" class="form-control form-control-sm" rows="3"><?php echo esc_textarea($client['current_classes'] ?? ''); ?></textarea>
         <div class="valid-feedback">Looks good!</div>
      </div>
      <!-- Stopped Classes -->
      <div class="col-md-6">
         <label for="stopped_classes" class="form-label">Stopped Classes</label>
         <textarea id="stopped_classes" name="stopped_classes" class="form-control form-control-sm" rows="3"><?php echo esc_textarea($client['stopped_classes'] ?? ''); ?></textarea>
         <div class="valid-feedback">Looks good!</div>
      </div>
   </div>
   <div class="row mt-3">
      <!-- Deliveries -->
      <div class="col-md-6">
         <label for="deliveries" class="form-label">Deliveries</label>
         <textarea id="deliveries" name="deliveries" class="form-control form-control-sm" rows="3"><?php echo esc_textarea($client['deliveries'] ?? ''); ?></textarea>
         <div class="valid-feedback">Looks good!</div>
      </div>
      <!-- Collections -->
      <div class="col-md-6">
         <label for="collections" class="form-label">Collections</label>
         <textarea id="collections" name="collections" class="form-control form-control-sm" rows="3"><?php echo esc_textarea($client['collections'] ?? ''); ?></textarea>
         <div class="valid-feedback">Looks good!</div>
      </div>
   </div>
   <div class="row mt-3">
      <!-- Cancellations -->
      <div class="col-md-6">
         <label for="cancellations" class="form-label">Cancellations</label>
         <textarea id="cancellations" name="cancellations" class="form-control form-control-sm" rows="3"><?php echo esc_textarea($client['cancellations'] ?? ''); ?></textarea>
         <div class="valid-feedback">Looks good!</div>
      </div>
   </div>
   <div class="border-top border-opacity-25 border-3 border-discovery my-5 mx-1"></div>
   <div class="row">
      <!-- Class Restarts -->
      <div class="col-md-6">
         <label for="class_restarts" class="form-label">Class Restarts</label>
         <input type="date" id="class_restarts" name="class_restarts" class="form-control form-control-sm" value="<?php echo esc_attr($client['class_restarts'] ?? ''); ?>">
         <div class="valid-feedback">Looks good!</div>
      </div>
      <!-- Class Stops -->
      <div class="col-md-6">
         <label for="class_stops" class="form-label">Class Stops</label>
         <input type="date" id="class_stops" name="class_stops" class="form-control form-control-sm" value="<?php echo esc_attr($client['class_stops'] ?? ''); ?>">
         <div class="valid-feedback">Looks good!</div>
      </div>
   </div>
   <div class="row mt-3">
      <!-- Assessments -->
      <div class="col-md-6">
         <label for="assessments" class="form-label">Assessments</label>
         <textarea id="assessments" name="assessments" class="form-control form-control-sm" rows="3"><?php echo esc_textarea($client['assessments'] ?? ''); ?></textarea>
         <div class="valid-feedback">Looks good!</div>
      </div>
      <!-- Progressions -->
      <div class="col-md-6">
         <label for="progressions" class="form-label">Progressions</label>
         <textarea id="progressions" name="progressions" class="form-control form-control-sm" rows="3"><?php echo esc_textarea($client['progressions'] ?? ''); ?></textarea>
         <div class="valid-feedback">Looks good!</div>
      </div>
   </div>
   <div class="border-top border-opacity-25 border-3 border-discovery my-5 mx-1"></div>
   <!-- Submit -->
   <div class="col-md-3">
      <button type="submit" class="btn btn-primary mt-3">Add New Client</button>
   </div>
</form>
<!-- JavaScript for form validation and dynamic field behavior -->
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
       
       // AJAX calls to populate dropdowns can be added here
       // Example: Load client list for "Branch of" dropdown
       
       // You can add more JavaScript for dynamic form behavior as needed
   });
</script>
<?php
return ob_get_clean();
}
add_shortcode('wecoza_capture_clients', 'clients_capture_shortcode');