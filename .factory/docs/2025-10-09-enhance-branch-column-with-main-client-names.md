## Update Branch Column to Show Main Client Name

**Goal**: Modify the branch column to display "Main Client Name #ID" format instead of just "ID: #ID".

**Current Issue**: The branch column only shows the main client ID (e.g., "ID: 1") but should show the main client name with ID (e.g., "TechCorp Solutions #1").

**Required Changes**:

### 1. Update ClientsModel::getAll() Method
- Modify the SQL query to LEFT JOIN with the clients table to fetch main client name
- Add main_client_name field to the SELECT statement
- Join using `main_client_id` to get the parent client's name

### 2. Update View Template
- Modify the branch column display logic around line 233 in `clients-table.view.php`
- Display format: "Client Name #ID" for branches, empty for main clients
- Use Phoenix badge styling for consistent appearance

### 3. Handle Edge Cases
- Ensure null/empty main client names are handled gracefully
- Maintain backward compatibility for existing data
- Don't break when main_client_id is null or 0

**Implementation Details**:
- SQL JOIN: `LEFT JOIN clients mc ON c.main_client_id = mc.id`
- Display format: `<span class="badge badge-phoenix badge-phoenix-secondary">Client Name #ID</span>`
- Fallback: If main client name is not available, show current "ID: #ID" format

**Files to Modify**:
1. `app/Models/ClientsModel.php` - Update getAll() method with JOIN
2. `app/Views/display/clients-table.view.php` - Update branch column display logic

This will provide more informative branch information while maintaining the clean Phoenix design pattern.