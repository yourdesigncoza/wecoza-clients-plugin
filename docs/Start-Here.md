# Start Here

## Status
- Client contacts and communications now persist via `ClientContactsModel` and `ClientCommunicationsModel`.
- `ClientsModel` enriches read operations by hydrating contact/communication details.
- Frontend form no longer includes legacy class-related fields.

## Pending
- Update controllers/tests to confirm new contact data flows through AJAX endpoints.
- Decide how to map `client_town` text input to the `town_id` foreign key in `clients`.
- Review CSV export and any reporting to ensure newly hydrated fields render as expected.

## Next Steps
1. Exercise the client capture form end-to-end (create + edit) and verify records in `client_contact_persons`/`client_communications`.
2. Confirm listing/search pages display the hydrated contact info and last communication timestamp when available.
3. Plan schema or UI change for Town selection so it aligns with the `locations` table.
