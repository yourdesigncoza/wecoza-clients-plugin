# WeCoza Clients Plugin

A WordPress plugin for managing business clients with PostgreSQL backend, featuring MVC architecture and comprehensive client relationship management.

## Features

- **Client Management**: Create, edit, view, and soft-delete client records
- **Hierarchical Client Relationships**: Main client and sub-client relationship management with validation
- **MVC Architecture**: Clean separation of concerns with Controllers, Models, and Views
- **PostgreSQL Backend**: External PostgreSQL database with JSONB support for flexible data
- **Search & Filtering**: Advanced client search and filtering capabilities with hierarchy support
- **File Uploads**: Document management with WordPress media integration
- **SETA Integration**: Full support for all South African SETA organizations
- **Location Capture**: Google Maps assisted suburb/town capture with latitude and longitude storage
- **Bootstrap 5 UI**: Phoenix theme integration with responsive design
- **Shortcode Support**: Multiple shortcodes for embedding client and location functionality

## System Requirements

- WordPress 8.0+
- PHP 8+
- PostgreSQL 12+ (external database)
- Bootstrap 5 (Phoenix theme)

## Installation

1. Upload the plugin files to `/wp-content/plugins/wecoza-clients-plugin/`
2. Configure PostgreSQL connection settings in WordPress admin
3. Apply the database schema from `schema/clients_schema.sql`
4. Activate the plugin through WordPress admin

## Database Setup

The plugin uses an external PostgreSQL database. Apply the schema:

```bash
psql -h your-host -p 25060 -U username -d database < schema/clients_schema.sql
```

Connection settings are stored as WordPress options and must be configured during activation.

## Shortcodes

### Client Management Shortcodes

#### Client Capture Form
```shortcode
[wecoza_capture_clients]
```
Renders a comprehensive client creation/editing form with full validation, hierarchical relationships, and file upload support.

#### Client Display Table
```shortcode
[wecoza_display_clients per_page="10" show_search="true"]
```
Shows a paginated table of clients with advanced search and filtering options, including hierarchy support and modal views.

### Location Management Shortcodes

#### Location Capture Form
```shortcode
[wecoza_locations_capture]
```
Captures new locations with Google Maps autocomplete for suburb/town selection, automatically persisting latitude and longitude coordinates.

#### Location List
```shortcode
[wecoza_locations_list]
```
Displays all locations in a searchable, sortable table format with edit and delete functionality.

#### Location Edit Form
```shortcode
[wecoza_locations_edit]
```
Provides an editable form for existing locations (requires location ID parameter for editing specific entries).

## Architecture

### MVC Pattern
- **Controllers**: Handle requests, shortcodes, and AJAX endpoints
- **Models**: Manage data validation, database queries, and business logic
- **Views**: PHP templates for rendering HTML output
- **Services**: Shared functionality like database connections
- **Helpers**: Utility functions and view helpers
- **Locations Module**: Dedicated controller, model, and view for Google Maps-enabled location capture

### Database Schema
- **clients**: Main table with JSONB fields for flexible data storage and hierarchical relationships via `main_client_id`
- **client_meta**: Key-value metadata storage
- **client_notes**: Interaction history and notes
- **locations**: Stores suburbs with town, province, postal code, and geocoordinates for client sites
- Soft delete pattern with `deleted_at` timestamps
- Foreign key constraints for client hierarchy with cascading updates

### File Structure
```
wecoza-clients-plugin/
├── app/
│   ├── Controllers/        # Request handling
│   ├── Models/            # Data layer
│   ├── Views/             # Templates
│   ├── Services/          # Shared services
│   └── Helpers/           # Utilities
├── config/
│   └── app.php           # Central configuration
├── includes/             # WordPress integration
├── schema/               # Database schemas
└── assets/               # Static assets
```

## Configuration

All plugin settings are centralized in `config/app.php`:

- Validation rules
- SETA options
- AJAX endpoints
- Controller registration
- Database field definitions
- Province lists and Google Maps asset configuration

## Security

### Capabilities
- `manage_wecoza_clients`: Full admin access
- `create_wecoza_clients`: Create new clients
- `edit_wecoza_clients`: Edit existing clients
- `delete_wecoza_clients`: Soft delete clients
- `view_wecoza_clients`: View client data
- `export_wecoza_clients`: Export to CSV

### Data Protection
- WordPress nonces for all forms
- Capability checks on all operations
- Prepared statements for database queries
- Input sanitization and validation
- Output escaping in views

## Development

### Adding New Fields
1. Update database schema
2. Add to Model `$fillable` array
3. Add validation rules in config
4. Update form views
5. Apply database changes

### Creating AJAX Endpoints
1. Define in `config/app.php`
2. Create controller method
3. Register in `registerAjaxHandlers()`
4. Implement client-side JavaScript

### Styling
All CSS must be added to the theme directory:
```
/wp-content/themes/wecoza_3_child_theme/includes/css/ydcoza-styles.css
```

### Google Maps Integration
- Provide a valid Places-enabled API key via the `wecoza_agents_google_maps_api_key` option (shared with the agents plugin).
- The `[wecoza_locations_capture]` shortcode loads Google Maps asynchronously and falls back to manual entry when the key is absent.

## Client Data Fields

### Core Information
- Client name and registration details
- Contact person and communication preferences
- Address and location data
- Status tracking (Lead, Active, Lost, etc.)

### Business Details
- Company registration number
- SETA affiliation
- Hierarchical relationships (main client/sub-client structure)
- Branch management with validation to prevent circular references
- Industry and business type

### Flexible Data Storage
JSONB fields for complex data:
- `current_classes`: Array of active class enrollments
- `stopped_classes`: Historical class data
- `deliveries`: Delivery tracking information
- `assessments`: Assessment records
- `progressions`: Progress tracking

## Hierarchical Client Relationships

The plugin supports main client and sub-client relationships for complex organizational structures:

### Features
- **Main Clients**: Parent organizations with `main_client_id` set to NULL
- **Sub-Clients**: Branches or subsidiaries that reference a main client
- **Validation**: Prevents circular references and self-relationships
- **Cascading Updates**: Main client ID changes automatically propagate to sub-clients
- **Soft Delete Protection**: If a main client is deleted, sub-clients become independent

### Form Interface
- **Sub-Client Checkbox**: Marks a client as a sub-client
- **Main Client Dropdown**: Populated dynamically with available main clients
- **Real-time Validation**: AJAX-powered validation to ensure data integrity

### Database Constraints
```sql
-- Foreign key with cascading behavior
ALTER TABLE clients 
ADD CONSTRAINT clients_main_client_id_fkey 
FOREIGN KEY (main_client_id) REFERENCES clients(client_id) 
ON UPDATE CASCADE ON DELETE SET NULL;

-- Performance index
CREATE INDEX ix_clients_main_client_id ON clients(main_client_id);
```

### Model Methods
- `getMainClients()`: Retrieve only main clients
- `getSubClients($mainClientId)`: Get sub-clients of a specific main client
- `getAllWithHierarchy()`: Get all clients with hierarchy information
- `updateClientHierarchy()`: Update client relationships with validation

## License

This plugin is proprietary software developed for WeCoza business operations.

## Support

For technical support and bug reports, contact the development team.