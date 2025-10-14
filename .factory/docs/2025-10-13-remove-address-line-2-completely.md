# Remove Address Line 2 Completely - Option 1 Implementation

## Problem Analysis
The codebase has inconsistency regarding address line 2:
- Forms show `client_address_line_2` fields 
- Modal expects `client_address_line_2` data
- But database has NO `address_line_2` columns in locations/sites tables
- No processing logic exists for address line 2

## Solution: Remove Address Line 2 Completely
Since the current location-based system uses a single `street_address` field that can contain multi-line addresses, we'll remove all references to address line 2.

## Implementation Steps

### 1. Update Client Details Modal
- Remove `#modalAddressLine2` element and JavaScript reference
- Remove `client_address_line_2` from `populateClientModal()` function

### 2. Update Client Forms  
- Remove `client_address_line_2` field from capture form
- Remove `client_address_line_2` field from update form
- Clean up related JavaScript and data handling

### 3. Update Model Logic
- Remove `client_address_line_2` assignment in `hydrateClients()` method
- Clean up any empty string assignments

### 4. Clean Up View Helpers
- Remove `headSiteAddress2` variable references
- Remove `renderField` calls for address line 2

### 5. Update Documentation
- Remove address line 2 from example documentation
- Clean up any references in HTML examples

## Files to Modify
1. `assets/js/clients-table.js` - Remove modal address line 2
2. `app/Views/components/client-capture-form.view.php` - Remove field
3. `app/Views/components/client-update-form.view.php` - Remove field  
4. `app/Models/SitesModel.php` - Clean up hydrateClients method
5. `docs/example-capture-view.html` - Remove from examples
6. `assets/js/client-capture.js` - Remove JavaScript handling

## Expected Outcome
- Clean, consistent address system using only `street_address`
- Forms no longer show broken address line 2 fields
- Modal displays only relevant address data
- No more empty address line 2 values