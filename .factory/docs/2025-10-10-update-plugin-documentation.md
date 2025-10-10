## Update Plan

### 1. Update README.md Shortcodes Section
Replace the current "Shortcodes" section with a comprehensive list of all 6 active shortcodes:

**Client Management Shortcodes:**
- `[wecoza_capture_clients]` - Client creation/editing form with validation
- `[wecoza_display_clients per_page="10" show_search="true"]` - Paginated clients table with search

**Location Management Shortcodes:**
- `[wecoza_locations_capture]` - Location capture form with Google Maps
- `[wecoza_locations_list]` - Display all locations in table format
- `[wecoza_locations_edit]` - Edit existing location (requires ID parameter)

**Note:** The `[wecoza_display_single_client id="123"]` shortcode mentioned in the current README is NOT implemented and should be removed.

### 2. Update Plugin Class Description
Update `includes/class-wecoza-clients-plugin.php` file header comment to accurately describe the plugin's comprehensive functionality including:
- Client relationship management with hierarchical support
- PostgreSQL backend with JSONB data storage
- Location management with Google Maps integration
- MVC architecture implementation
- SETA compliance features

The changes will provide accurate documentation that matches the actual implemented functionality.