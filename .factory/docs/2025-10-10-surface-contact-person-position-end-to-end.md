## Summary
Introduce the `contact_person_position` field into the capture form, ensure it’s sent through the save pipeline, and display it consistently in the list modal.

## Steps
1. Update `client-capture-form.view.php` to include a Position input in the Contact Information section (matching update form styling) and ensure it’s populated from `$client` data.
2. Extend `client-capture.js` (or submission handler) so the position value is included in the AJAX payload; confirm PHP handlers (`sanitizeFormData`, `handleFormSubmission`) accept and persist it via `ClientContactsModel::upsertPrimaryContact`.
3. Ensure `ClientsModel::hydrateRelatedData()` already exposes the position (it does after recent changes); wire the view modal (`clients-table.view.php`) to render it if missing.
4. Add the same field to any other create/edit contexts sharing the capture form, and verify the AJAX response includes it so the list modal displays the badge using `populateClientModal()`.

## Validation
- Create a client via `[wecoza_capture_clients]` including a position and confirm it persists and pre-fills on edit.
- Open the View Details modal to verify the position appears beside contact info.