## Remove Confusing Site Type Radio Buttons

**Goal**: Simplify the form by removing the redundant "Site Type" radio buttons and keeping only the "Sub Client" checkbox approach

### Changes Needed:

1. **Remove Site Type Section**
   - Delete the entire "Site Information" section containing:
     - Site Type radio buttons (`site_type_head` and `site_type_sub`)
     - Parent site dropdown container (`parent_site_container`)
     - Associated JavaScript for site type functionality

2. **Keep Sub Client Logic** 
   - Preserve the existing "Sub-Client Information" section with:
     - "Is SubClient" checkbox
     - Main Client dropdown that shows when checkbox is checked
     - Existing JavaScript for sub-client functionality

3. **Clean Up Forms**
   - Remove site type related JavaScript functions
   - Remove site type variables and logic from both capture and update forms
   - Ensure form validation still works properly

4. **Backend Consistency**
   - Update `ClientsController::sanitizeFormData()` to remove site type handling
   - Keep existing sub-client logic intact

### Expected Result:
- Simple, clear form with one hierarchy concept: Main Client vs Sub Client
- When "Is SubClient" is checked, shows dropdown of Head Sites from Main Clients
- No confusing radio buttons or dual hierarchy system
- Consistent with the previous working approach you preferred