## Summary
The duplicate lookup always reports “No duplicates” because the AJAX nonce checked in `ajaxCheckLocationDuplicates()` (`wecoza_locations_form`) doesn’t match the nonce generated in the form (`submit_locations_form`). The controller then emits a failure payload that the JavaScript treats as a successful “no duplicates” result. We’ll align the nonce usage and improve the response handling so front end errors surface correctly.

## Implementation Steps
1. **Align nonce verification**
   - Update `LocationsController::ajaxCheckLocationDuplicates()` to verify against the existing `submit_locations_form` nonce (and adjust the AJAX endpoint config entry for clarity).
2. **Return structured JSON responses**
   - Replace the manual `wp_die(json_encode(...))` calls with `wp_send_json_error()` / `wp_send_json_success()` so failures clearly include error messages and successes wrap the duplicates array.
3. **Update front-end handling**
   - In `location-capture-form.view.php`, adjust the duplicate-check fetch handling to show server-provided error messages when `success` is `false`, and only display the “No duplicates found” notice when the request succeeds with an empty results array.

## Testing Plan
- From the location capture form, trigger the “Check Duplicates” button with known existing data (e.g., “66 Klipper Street”) and verify that duplicates are listed.
- Trigger the duplicate check with no matching entry to confirm the informative “No duplicates found” notice still appears.
- Try submitting without required fields (e.g., blank street/suburb/town) and confirm the UI shows the validation error returned by the server instead of a false-positive success message.