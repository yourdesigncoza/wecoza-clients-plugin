## Summary
Add the contact person position and suburb values to the client details modal so that it matches the available data shown in the reference screenshots.

## Changes
1. Update `ClientsModel::hydrateRelatedData()` to include `contact_person_position` when enriching client rows with primary contact data.
2.Extend the modal markup in `app/Views/display/clients-table.view.php` with new rows and IDs for contact position and suburb, keeping styling consistent.
3. Adjust `assets/js/clients-table.js` so `populateClientModal()` fills the new fields (with graceful fallbacks) using the data returned from the AJAX response.

## Testing
- Manually trigger the "View Details" action for a client with populated contact/ location data to confirm all fields render as expected.