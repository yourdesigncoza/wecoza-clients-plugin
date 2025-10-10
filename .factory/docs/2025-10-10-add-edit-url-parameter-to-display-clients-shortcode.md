## Add `edit_url` Parameter to Display Clients Shortcode

### Current Implementation:
- Edit URLs are hardcoded to `/client-management` in the clients table view
- "Add New Client" button also uses hardcoded URL
- Edit links append `?mode=update&client_id={id}` to the URL

### Proposed Changes:

#### 1. Update `displayClientsShortcode()` in `ClientsController.php`
- Add `edit_url` to shortcode attributes with default value `/app/all-clients`
- Pass the `edit_url` to the view data

#### 2. Update `clients-table.view.php`
- Replace hardcoded `/client-management` with dynamic `$edit_url` from `$atts`
- Update both "Edit" buttons and "Add New Client" button to use the dynamic URL
- Maintain existing query parameter structure: `?mode=update&client_id={id}`

### Benefits:
- Flexible routing for client editing
- Default to `/app/all-clients` as requested
- Backward compatible (existing usage without `edit_url` uses default)
- No breaking changes to existing functionality

### Files to Modify:
1. `app/Controllers/ClientsController.php` - Add `edit_url` attribute handling
2. `app/Views/display/clients-table.view.php` - Use dynamic edit URL