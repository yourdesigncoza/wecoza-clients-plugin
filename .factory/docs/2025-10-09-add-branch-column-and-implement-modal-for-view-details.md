# Plan: Add Branch column and implement Modal for View Details

## Overview
Update the clients table to:
1. Add a new "Branch" column between Client Name and Company Reg
2. Move branch logic from under Client Name to the new Branch column  
3. Change "View Details" to open a modal window instead of navigation
4. Modal displays all client details using the capture form field layout
5. Modal includes an update button for editing

## Implementation Steps

### 1. Update Table Headers
- Add new "Branch" column header between Client Name and Company Reg
- Include proper icon and sorting indicators
- Keep existing columns unchanged (ID, Client Name, Company Reg, SETA, Status, Created, Actions)

### 2. Update Table Body Structure  
- Clean up Client Name cell to show only the client name (remove branch info)
- Add new Branch cell showing "ID: X" for branches, "N/A" for main clients
- Remove "Address" column requirement (keep it simple)

### 3. Modal Implementation
- Create modal HTML structure that will be triggered by "View Details"
- Use Bootstrap 5 modal component with Phoenix styling
- Modal will display all client fields from the capture form:
  - Client Name, Site Name, Company Registration
  - Location: Province, Town, Suburb, Postal Code, Address
  - Contact: Person, Email, Cellphone, Tel
  - Details: SETA, Status, Financial Year End, BBBEE Verification
- Include "Update" button that links to edit functionality
- Include "Close" button

### 4. JavaScript Integration
- Update `clients-table.js` to handle modal functionality
- AJAX call to fetch complete client details when modal opens
- Dynamic population of modal fields
- Handle update button click to navigate to edit form

### 5. Branch Column Logic
- Show "ID: X" when `main_client_id` exists  
- Show "N/A" for main clients (no main_client_id)
- Use consistent styling with other columns
- Make it sortable if desired

## Files to Modify
- `app/Views/display/clients-table.view.php` - Update table structure and add modal
- `assets/js/clients-table.js` - Add modal functionality and AJAX calls

## Expected Layout (8 columns)
1. ID
2. Client Name (clean, no branch info)
3. Branch (NEW) - Shows "ID: X" for branches, "N/A" for main clients  
4. Company Reg
5. SETA
6. Status
7. Created  
8. Actions (View Details opens modal, Edit, Delete)

## Modal Features
- Phoenix-styled modal with header, body, and footer
- Read-only display of all client details
- Organized sections matching the capture form layout
- Update button for editing
- Responsive design
- Loading state while fetching client data