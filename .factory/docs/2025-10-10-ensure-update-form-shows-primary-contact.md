## Summary
Investigate why the update client shortcode isn’t pre-filling the contact person even though the modal shows it, then normalize the data pipeline so the form reuses the same contact details.

## Investigation
1. Confirm what `ClientsModel::getById()` returns when `updateClientShortcode()` loads—specifically whether `contact_person` and related fields exist prior to filtering.
2. Check `filterClientDataForForm()` to verify it preserves those keys and identify any branch that may unset them (e.g. handling submitted data or empty site/contact fallbacks).
3. Inspect the rendered HTML/JS for `client-update-form.view.php` to ensure the field bindings rely on `$client['contact_person']` and nothing overwrites it on the frontend.

## Fix
1. If the contact fields are missing after filtering, explicitly merge the primary contact record fetched from `ClientContactsModel::getPrimaryContact()` back onto the `$client` array inside `updateClientShortcode()` before passing it to the view.
2. Guard the form view so it falls back to the merged `$contact` data whenever `$client['contact_person']` is empty.

## Validation
- Reload the `[wecoza_update_clients]` page for a client with known contact info and confirm the Contact Person, email, and phone fields all pre-populate.