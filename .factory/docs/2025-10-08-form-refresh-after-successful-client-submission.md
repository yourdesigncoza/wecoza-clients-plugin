# Form Refresh/Clear After Successful Submission

## Problem Analysis
The client capture form currently shows a success message after submission but retains all entered data, which could lead to users accidentally submitting duplicate client entries.

## Implementation Plan

### 1. Add Form Clear Configuration
- Add a new configuration option `clear_form_on_success` to the localization payload
- Set it to `true` by default for new client submissions

### 2. Update JavaScript Success Handler
- Modify the success response handler in `client-capture.js`
- Check if it's a new client (no existing ID) and clear_form_on_success is enabled
- Implement form clearing logic:
  - Reset form fields to default values
  - Clear hidden input fields
  - Reset location dropdowns to initial state
  - Remove validation classes
  - Clear success message after a delay

### 3. Form Clear Implementation Details
- Reset all input fields, textareas, and selects
- Reset Bootstrap validation state (`was-validated` class)
- Reset location dropdowns to their default/empty state
- Clear any hidden fields (`id`, `head_site_id`)
- Auto-hide success message after 5 seconds
- Preserve form focus for easy re-entry

### 4. Distinguish Between Create vs Update
- Clear form only for new client creation (when no pre-existing ID)
- Keep form populated for updates to allow further modifications
- Add optional parameter to override behavior if needed

This approach will prevent duplicate submissions while maintaining usability for edit scenarios.