# SubClient & Main Site Implementation Summary

**Date**: 2025-10-06  
**Task**: Implement sub-client functionality with main site dropdown for WeCoza Clients Plugin

## Overview
Successfully implemented a comprehensive sub-client relationship system that allows users to create hierarchical client relationships while maintaining the existing site management functionality. The implementation follows the user's specific requirements for checkbox-driven sub-client selection and automatic main site creation.

## Database Schema Changes

### Modified Files:
- `schema/wecoza_db_schema.sql`

### Changes Made:
1. **Added `main_client_id` column** to clients table
   - Type: `integer` 
   - Nullable: `YES` (NULL for main clients)
   - Foreign Key: References `clients.client_id`
   - Constraint: `ON UPDATE CASCADE ON DELETE SET NULL`

2. **Added Performance Index**
   - Index: `ix_clients_main_client_id` 
   - Type: btree on `main_client_id` field

3. **Added Documentation** 
   - Full comment explaining the field's purpose for sub-client relationships

### Schema Update SQL:
```sql
ALTER TABLE public.clients ADD COLUMN main_client_id integer;

CREATE INDEX ix_clients_main_client_id ON public.clients USING btree (main_client_id);

ALTER TABLE ONLY public.clients
    ADD CONSTRAINT clients_main_client_id_fkey 
    FOREIGN KEY (main_client_id) REFERENCES public.clients(client_id) 
    ON UPDATE CASCADE ON DELETE SET NULL;

COMMENT ON COLUMN public.clients.main_client_id 
    IS 'Reference to the main client for sub-client relationships (NULL for main clients)';
```

## Model Layer Implementation

### Modified Files:
- `app/Models/ClientsModel.php`

### New Methods Added:
1. **`getMainClients()`**: Returns only main clients (NULL main_client_id)
2. **`getSubClients($mainClientId)`**: Returns sub-clients of specific main client
3. **`getAllWithHierarchy()`**: Returns all clients with hierarchy information
4. **`updateClientHierarchy($clientId, $mainClientId)`**: Updates client relationships

### Enhanced Functionality:
1. **Extended Fillable Fields**: Added `main_client_id` to column candidates and fillable array
2. **Advanced Validation**: 
   - Prevents self-reference (client can't be its own parent)
   - Validates selected main client exists and is actually a main client
   - Prevents circular references in hierarchy
3. **Custom Error Messages**: Specific validation errors for sub-client relationships

### Validation Logic:
```php
// Validates main_client_id with comprehensive checks
if (!empty($data['main_client_id'])) {
    $mainClientId = (int) $data['main_client_id'];
    
    if ($mainClientId <= 0) {
        $errors['main_client_id'] = __('Invalid main client selected.', 'wecoza-clients');
    } elseif ($id && $mainClientId === (int) $id) {
        $errors['main_client_id'] = __('A client cannot be its own parent.', 'wecoza-clients');
    } else {
        $mainClient = $this->getById($mainClientId);
        if (!$mainClient) {
            $errors['main_client_id'] = __('Selected main client does not exist.', 'wecoza-clients');
        } elseif (!empty($mainClient['main_client_id'])) {
            $errors['main_client_id'] = __('Selected client is already a sub-client. Please select a main client.', 'wecoza-clients');
        }
    }
}
```

## Configuration Updates

### Modified Files:
- `config/app.php`

### Changes Made:
1. **Added Validation Rules** for `main_client_id`:
   - Required: `false` (optional field)
   - Type: `integer` validation
   - Minimum: `0` (to filter invalid values)

### Validation Configuration:
```php
'main_client_id' => array(
    'required' => false,
    'integer' => true,
    'min' => 0,
),
```

## View Layer Implementation

### Modified Files:
- `app/Views/components/client-capture-form.view.php`

### New UI Components:
1. **"Is SubClient" Checkbox**:
   - Bootstrap 5 form-check styling
   - Explanatory text with help description
   - Dynamic state management for edit mode

2. **"Main Client" Dropdown**:
   - Conditionally displayed (only when checkbox checked)
   - Populated with main clients formatted as "Company Name (Reg #)"
   - Includes "Select Main Client..." placeholder option
   - Dynamic required attribute management

### JavaScript Functionality:
```javascript
// Dynamic show/hide and validation management
isSubClientCheckbox.addEventListener('change', function() {
    var isChecked = this.checked;
    mainClientDropdownContainer.style.display = isChecked ? 'block' : 'none';
    
    if (mainClientSelect) {
        if (isChecked) {
            mainClientSelect.setAttribute('required', 'required');
        } else {
            mainClientSelect.removeAttribute('required');
            mainClientSelect.value = '';
        }
    }
});
```

### View Variables Added:
- `$main_clients`: Array of main clients for dropdown options
- `$is_sub_client`: Boolean indicating if client is a sub-client
- `$selected_main_client_id`: Current main client selection
- `$is_sub_client_checked`: Checked state for edit mode

## Controller Layer Updates

### Modified Files:
- `app/Controllers/ClientsController.php`

### Form Processing Enhancements:
1. **Extended `sanitizeFormData()`**: 
   - Handles `is_sub_client` checkbox state
   - Processes `main_client_id` with proper validation
   - Sets null for unchecked non-sub-client scenarios

2. **Enhanced View Data**:
   - Added `main_clients` data to view parameters
   - Integrated with existing site and location data

3. **New AJAX Endpoint**:
   - `ajaxGetMainClients()`: Returns main clients for dropdown population
   - Proper security with nonces and capability checks

### Sanitization Logic:
```php
// Handle sub-client relationship
$isSubClient = isset($data['is_sub_client']) && $data['is_sub_client'] === 'on';
if ($isSubClient && !empty($data['main_client_id'])) {
    $client['main_client_id'] = (int) $data['main_client_id'];
    if ($client['main_client_id'] <= 0) {
        $client['main_client_id'] = null;
    }
} else {
    $client['main_client_id'] = null;
}
```

## User Experience Flow

### Main Client Creation:
1. User unchecks "Is SubClient" (default state)
2. Form shows standard client fields
3. Upon save, existing auto-site creation uses `client_name`
4. `main_client_id` remains NULL

### Sub-Client Creation:
1. User checks "Is SubClient" checkbox
2. Main Client dropdown appears dynamically
3. User selects from available main clients
4. Form validates selection exists and is valid
5. Upon save, `main_client_id` is set to selected client

### Edit Mode Behavior:
1. Current relationship state is detected and displayed
2. Checkbox shows current sub-client status
3. Dropdown pre-populated with current main client selection
4. User can change relationship type with validation

## Technical Validation Features

### Database Constraints:
- Foreign key ensures referential integrity
- `ON DELETE SET NULL` handles main client deletion gracefully
- Index optimizes sub-client relationship queries

### Application Validation:
- Prevents self-reference relationships
- Prevents circular references in hierarchy
- Validates main client exists and is truly a main client
- Ensures proper data type handling

### Security Measures:
- All AJAX endpoints protected with nonces
- Proper capability checks (`view_wecoza_clients`, `manage_wecoza_clients`)
- Form data properly sanitized
- SQL injection prevention through prepared statements

## Integration with Existing Features

### Site Management:
- Existing head site functionality preserved
- Auto-site creation continues to work for main clients
- No changes to site table structure or relationships

### Location Management:
- Existing location hierarchy unaffected
- Address and location validation maintained
- Integration with existing location data structure

### Data Consistency:
- Maintains separation between client entities and site entities
- Sub-client relationships at client level only
- Site hierarchy independent of client hierarchy

## Files Modified Summary

1. **Database Layer**:
   - `schema/wecoza_db_schema.sql` - Added main_client_id column and constraints

2. **Model Layer**:
   - `app/Models/ClientsModel.php` - Added hierarchy methods and validation

3. **Configuration**:
   - `config/app.php` - Added validation rules for new field

4. **View Layer**:
   - `app/Views/components/client-capture-form.view.php` - Added sub-client UI and JavaScript

5. **Controller Layer**:
   - `app/Controllers/ClientsController.php` - Enhanced form processing and added AJAX endpoint

## Future Considerations

### Potential Enhancements:
1. **Bulk Sub-Client Operations**: Allow moving multiple sub-clients between main clients
2. **Hierarchy Visualization**: Display client relationships in tree view
3. **Impact Analysis**: Show effects of main client changes on sub-clients
4. **Audit Trail**: Track changes in client relationships over time
5. **Reporting**: Generate reports by main client with sub-client breakdowns

### Migration Path:
1. **Existing Data**: Current clients remain as main clients (NULL main_client_id)
2. **Data Validation**: Run validation script to ensure data integrity
3. **User Training**: Document new sub-client workflow for administrators

## Testing Recommendations

### Manual Testing Scenarios:
1. Create new main client without sub-client checkbox
2. Create new sub-client with main client selection
3. Edit existing main client to become sub-client
4. Edit existing sub-client to become main client
5. Attempt invalid operations (self-reference, invalid selections)
6. Test data integrity after main client deletion

### Automated Tests:
1. Database constraint validation
2. Model method unit tests
3. Form sanitization tests
4. AJAX endpoint security tests

## Success Metrics

### Functional Requirements Met:
✅ Checkbox-driven sub-client selection  
✅ Main client dropdown populated with valid options  
✅ Automatic main site creation for non-sub-clients  
✅ Edit mode relationship changes with validation  
✅ Separation of client and site hierarchies maintained  

### Technical Requirements Met:
✅ Database schema properly extended  
✅ All validation rules implemented  
✅ Security measures in place  
✅ Integration with existing functionality preserved  
✅ Performance considerations addressed (indexing)  

This implementation provides a robust, scalable foundation for client hierarchy management while maintaining the plugin's existing architecture and functionality.


   Key Features Implemented:

   1. Checkbox-Driven Interface: "Is SubClient" checkbox reveals main
      client selection
   2. Smart Dropdown: Shows main clients with company names and
      registration numbers
   3. Data Validation: Prevents self-reference, invalid selections,
      and circular relationships
   4. Edit Mode Support: Handles existing sub-client relationships
      properly
   5. Auto-Site Creation: Existing functionality still creates head
      sites for main clients
   6. Security: All endpoints properly secured with nonces and
      capability checks

   User Experience Flow:

   1. Main Client Creation: User unchecks "Is SubClient" → site
      auto-creates with client name
   2. Sub-Client Creation: User checks "Is SubClient" → selects from
      main client dropdown → saves relationship
   3. Edit Mode: Form shows current relationship state with proper
      validation
   4. Dynamic Behavior: Real-time form updates based on checkbox state

   The implementation maintains clean separation between client 
   entities (company-level) and site entities (location-level) while
   adding the requested sub-client relationship functionality at the
   client level.