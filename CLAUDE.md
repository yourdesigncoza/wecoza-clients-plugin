# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## WordPress Plugin Development

### Plugin Structure
- **Main Plugin File**: `wecoza-clients-plugin.php` - Entry point with plugin headers, constants, and initialization
- **Core Classes**: `includes/class-wecoza-clients-plugin.php` (main orchestrator), `includes/class-activator.php` (setup), `includes/class-deactivator.php` (cleanup), `includes/class-uninstaller.php` (uninstall)
- **MVC Architecture**: `app/` directory with Controllers, Models, Views, Services, and Helpers namespaces
- **Configuration**: `config/app.php` contains all plugin settings, validation rules, SETA options, and controller registration

### Key Development Patterns
- **MVC Pattern**: Clean separation between Models (data), Views (presentation), and Controllers (logic)
- **Namespace**: All classes under `WeCozaClients\` namespace following PSR-4 autoloading
- **Bootstrap System**: `app/bootstrap.php` handles autoloading, configuration, and initialization
- **WordPress Integration**: Actions, filters, shortcodes, and AJAX handlers registered in controllers
- **No Build Process**: Direct file editing - no compilation or bundling required

### Database Architecture
- **PostgreSQL Primary**: External PostgreSQL database for all client data (DigitalOcean managed)
- **Connection**: `DatabaseService` class handles PDO connections with SSL mode required
- **Main Table**: `clients` with JSONB fields for flexible data storage
- **Related Tables**: `client_meta` (key-value pairs), `client_notes` (interaction history)
- **Schema Location**: `schema/clients_schema.sql` contains complete database structure
- **Soft Delete**: Records marked with `deleted_at` timestamp rather than hard deletion
- **Automatic Timestamps**: `created_at` and `updated_at` managed by database triggers

### Development Commands
```bash
# No build process - direct file editing workflow
# CSS styles must go to theme directory (NOT plugin):
# /opt/lampp/htdocs/wecoza/wp-content/themes/wecoza_3_child_theme/includes/css/ydcoza-styles.css

# Database setup: Apply schema manually to PostgreSQL
psql -h db-wecoza-3-do-user-17263152-0.m.db.ondigitalocean.com -p 25060 -U doadmin -d defaultdb < schema/clients_schema.sql

# Plugin activation via WP-CLI
wp plugin activate wecoza-clients-plugin
```

### Critical Implementation Details

#### Form Handling Pattern
- All forms use WordPress nonces: `wp_nonce_field('submit_clients_form', 'wecoza_clients_form_nonce')`
- Server-side validation in Model layer with configurable rules in `config/app.php`
- Client-side validation with Bootstrap 5 `needs-validation` class
- File uploads handled through `wp_handle_upload()` with MIME type restrictions

#### AJAX Implementation
```php
// Endpoints defined in config/app.php under 'ajax_endpoints'
// All handlers verify nonce and check capabilities
// Standard response format:
wp_die(json_encode(['success' => bool, 'message' => string, 'data' => array]));
```

#### View Rendering System
```php
// Use the view() helper function from bootstrap.php
return \WeCozaClients\view('components/client-capture-form', [
    'client' => $client,
    'errors' => $errors
]);
// Views are in app/Views/ with .view.php extension
// ViewHelpers class provides consistent form field rendering
```

#### Database Query Pattern
```php
// Use DatabaseService static methods with prepared statements
DatabaseService::getAll($sql, $params);  // Returns array of results
DatabaseService::getRow($sql, $params);  // Returns single row
DatabaseService::insert($table, $data);  // Returns inserted ID
DatabaseService::update($table, $data, $where, $params);  // Returns affected rows
```

### Client Management Features

#### Shortcodes
- `[wecoza_capture_clients]` - Client creation/edit form with validation
- `[wecoza_display_clients per_page="10" show_search="true"]` - Clients table with search/filters
- `[wecoza_display_single_client id="123"]` - Single client detailed view

#### JSONB Fields Usage
The following fields store JSON arrays and require special handling:
- `current_classes`, `stopped_classes` - Array of class IDs/references
- `deliveries`, `collections`, `cancellations` - Flexible data storage
- `assessments`, `progressions` - Array of assessment/progression records

Model automatically encodes/decodes these fields via `encodeJsonFields()` and `decodeJsonFields()`

#### Validation Rules
Defined in `config/app.php` under `validation_rules`. The `ClientsModel::validate()` method applies these automatically:
- Required fields validation
- Email format validation
- Maximum length checks
- Unique company registration number
- Date format validation
- Enum value validation (status, communication type)

### CSS and Styling

#### Bootstrap 5 + Phoenix Theme Integration
```html
<!-- Phoenix badges for status indicators -->
<span class="badge badge-phoenix fs-10 badge-phoenix-primary">Active Client</span>
<span class="badge badge-phoenix fs-10 badge-phoenix-warning">Lead</span>
<span class="badge badge-phoenix fs-10 badge-phoenix-danger">Lost Client</span>

<!-- Phoenix buttons -->
<button class="btn btn-phoenix-primary">Save Client</button>
<button class="btn btn-phoenix-secondary">Edit</button>

<!-- Phoenix alerts -->
<div class="alert alert-subtle-success">Client saved successfully!</div>
<div class="alert alert-subtle-danger">Validation errors found</div>
```

#### Form Field Pattern (via ViewHelpers)
```php
echo ViewHelpers::renderField('text', 'client_name', 'Client Name', 
    $client['client_name'] ?? '', 
    ['required' => true, 'col_class' => 'col-md-4', 'error' => $errors['client_name'] ?? '']
);
```

### Security Considerations

#### Permission System
Capabilities checked in controllers before any operation:
- `manage_wecoza_clients` - Full admin access
- `create_wecoza_clients` - Can create new clients
- `edit_wecoza_clients` - Can edit existing clients
- `delete_wecoza_clients` - Can soft delete clients
- `view_wecoza_clients` - Can view client data
- `export_wecoza_clients` - Can export to CSV

#### Data Sanitization Flow
1. jQuery form submission → AJAX request with nonce
2. Controller verifies nonce and capabilities
3. `sanitizeFormData()` cleans all input
4. Model validates against rules
5. DatabaseService uses prepared statements
6. Views escape output with WordPress functions

### PostgreSQL Connection Configuration
Stored as WordPress options (set during activation):
- `wecoza_postgres_host`: db-wecoza-3-do-user-17263152-0.m.db.ondigitalocean.com
- `wecoza_postgres_port`: 25060
- `wecoza_postgres_dbname`: defaultdb
- `wecoza_postgres_user`: doadmin
- `wecoza_postgres_password`: (must be set manually)

### Common Development Tasks

#### Adding New Fields to Clients
1. Update `schema/clients_schema.sql` with new column
2. Add field name to `$fillable` array in `ClientsModel`
3. If JSONB, add to `$jsonFields` array
4. Add validation rules in `config/app.php` under `validation_rules`
5. Update `sanitizeFormData()` in `ClientsController`
6. Add field to form view using `ViewHelpers::renderField()`
7. Run ALTER TABLE statement on production database

#### Creating New AJAX Endpoint
1. Define endpoint in `config/app.php` under `ajax_endpoints`:
   ```php
   'new_endpoint' => [
       'controller' => 'WeCozaClients\\Controllers\\ClientsController',
       'method' => 'ajaxNewEndpoint',
       'capability' => 'view_wecoza_clients',
       'nonce' => 'wecoza_clients_ajax'
   ]
   ```
2. Create handler method in Controller
3. Add to `registerAjaxHandlers()` method
4. Create JavaScript to call via `wp.ajax`

#### Modifying Search Functionality
Search implemented in `ClientsModel::getAll()` using PostgreSQL ILIKE:
```sql
client_name ILIKE :search 
OR company_registration_nr ILIKE :search2
OR contact_person ILIKE :search3
```
Add new fields to the WHERE clause for searchability.

### SETA Options
Full list defined in `config/app.php`. Current SETAs:
AgriSETA, BANKSETA, CATHSSETA, CETA, CHIETA, ETDP SETA, EWSETA, FASSET, FP&M SETA, FoodBev SETA, HWSETA, INSETA, LGSETA, MICT SETA, MQA, PSETA, SASSETA, Services SETA, TETA, W&RSETA, merSETA

### File Structure
```
wecoza-clients-plugin/
├── app/
│   ├── Controllers/         # Request handling, shortcodes, AJAX
│   │   └── ClientsController.php
│   ├── Models/             # Data logic, validation, database queries
│   │   └── ClientsModel.php
│   ├── Views/              # PHP templates (.view.php extension)
│   │   ├── components/     # Reusable view components
│   │   │   └── client-capture-form.view.php
│   │   └── display/        # Display views
│   ├── Services/           # Shared services
│   │   └── Database/       # Database connectivity
│   │       └── DatabaseService.php
│   ├── Helpers/            # View helpers and utilities
│   │   ├── ViewHelpers.php
│   │   └── view-helpers-loader.php
│   └── bootstrap.php       # Application initialization
├── assets/
│   ├── js/                # JavaScript files (to be created)
│   └── css/               # Empty - styles go to theme
├── config/
│   └── app.php            # Central configuration
├── includes/              # WordPress integration classes
├── schema/                # Database schemas
│   └── clients_schema.sql
└── legacy/                # Reference implementation from Classes plugin
```

### Important Implementation Notes
- **Version Control**: Plugin uses datetime for version (cache busting in dev)
- **Soft Delete**: Never hard delete - set `deleted_at` timestamp
- **Branch Support**: Clients can have parent/child relationships via `branch_of`
- **File Uploads**: Stored in WordPress uploads directory, only path saved to DB
- **Empty README**: The README.md file is intentionally empty
- **Legacy Folder**: Contains Classes plugin code for reference only - do not modify