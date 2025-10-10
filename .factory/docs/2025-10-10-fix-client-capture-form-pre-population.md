## Summary
The client capture shortcode mistakenly overwrites the `$client` array that should remain `null` for a fresh form. Within `ClientsController::captureClientShortcode` (and the update counterpart) the code builds the main-client dropdown using `foreach ($main_clients_raw as $client)`—reusing the same `$client` variable that holds the form data. After the loop finishes, `$client` now contains the last main client record, so the view receives that array and renders pre-populated fields.

## Proposed Changes
1. Rename the loop variable when building `$main_clients` in both `captureClientShortcode` and `updateClientShortcode` to something like `$mainClient` to avoid clobbering the form data.
2. Double-check that no other loops reuse `$client` in a way that mutates the form payload before rendering.

## Validation
- Load a page with `[wecoza_capture_clients]` and confirm every field is empty on initial page load.
- Load `[wecoza_update_clients]` with a valid client; ensure existing data still populates correctly.
- Submit the capture form with validation errors to confirm the resubmitted values continue to repopulate appropriately.