# SubClient & Main Site Implementation Plan

## User Choices Confirmed:
1. **Database Schema**: Add `main_client_id` to clients table 
2. **Dropdown Logic**: Show dropdown of existing clients (main clients only)
3. **Main Site Creation**: Auto-create head site using client_name + address from form
4. **Edit Mode**: Allow changing between main/sub-client relationships

## Implementation Steps:

### 1. Database Schema Updates
- Add `main_client_id` column to clients table (NULL for main clients, references client_id)
- Add foreign key constraint referencing clients.client_id  
- Create index for performance
- Update CREATE TABLE statement in schema

### 2. ClientsModel Updates
- Add `main_client_id` to fillable array
- Update validation rules for main_client_id
- Add methods: `getMainClients()`, `getSubClients()`, `updateClientHierarchy()`
- Modify `save()` method to handle sub-client relationships

### 3. Client Capture Form Updates
- Add "Is SubClient" checkbox field
- Add conditional "Main Client" dropdown (only show when checkbox checked)
- Modify form validation logic
- Update JavaScript for dynamic field show/hide
- Handle existing populated main_site_name field transition

### 4. Head Site Auto-Creation Logic  
- Extend `SitesModel` to auto-create head sites when new main clients are saved
- Use client_name for site_name, address from form for site address
- Link site to client during client creation process

### 5. Controller Updates
- Modify `ClientsController` to handle new form fields
- Add AJAX handler for "Get Main Clients" dropdown population
- Update form processing logic for sub-client relationships
- Add validation for circular references

### 6. Edit Mode Features
- Detect and display current client hierarchy status
- Allow switching between main/sub-client with confirmation
- Handle data migration when changing relationships
- Add warnings/disclaimers for relationship changes

### 7. Validation & Edge Cases
- Prevent self-reference (client can't be its own parent)
- Prevent circular references in hierarchy
- Validate main_client_id exists and is actually a main client
- Handle orphaned sub-clients when main client deleted

## Files to Modify:
- `schema/wecoza_db_schema.sql` - Add main_client_id column
- `app/Models/ClientsModel.php` - Add hierarchy logic
- `app/Controllers/ClientsController.php` - Handle form changes
- `app/Views/components/client-capture-form.view.php` - Add UI fields
- `assets/js/client-capture.js` - Dynamic UI logic
- `config/app.php` - Add validation rules

This approach maintains clean separation: **clients = company entities**, **sites = location hierarchy**, **main_client_id = company hierarchy**.