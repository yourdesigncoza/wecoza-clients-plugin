# Plan: Add Branch and Address columns to clients table

## Overview
Update the clients table to:
1. Move "Branch of ID: X" from under Client Name into a new "Branch" column
2. Add a new "Address" column showing client's address information
3. Maintain the Phoenix styling and responsive layout

## Implementation Steps

### 1. Update Table Headers
- Add new "Branch" column header between Client Name and Company Reg
- Add new "Address" column header between SETA and Status
- Include proper icons and sorting indicators

### 2. Update Table Body Structure
- Move branch logic from Client Name cell to new Branch cell
- Add Address cell that combines `client_street_address` and `client_address_line_2`
- Clean up Client Name cell to show only the client name

### 3. Address Display Logic
- Combine `client_street_address` and `client_address_line_2` into a single formatted address
- Handle cases where address fields are empty (show "N/A")
- Use text truncation for long addresses with tooltip for full address

### 4. Branch Column Logic
- Show "ID: X" when `main_client_id` exists
- Show "N/A" for main clients (no main_client_id)
- Style consistently with other columns

### 5. Column Width and Responsiveness
- Adjust column widths to accommodate new columns
- Ensure table remains responsive on mobile devices
- Use appropriate text wrapping and truncation

## Files to Modify
- `app/Views/display/clients-table.view.php` - Update table structure

## Key Features
- New Branch column showing parent client ID for branches
- New Address column combining street address and address line 2
- Clean separation of client name from branch information
- Proper tooltips for truncated addresses
- Maintained Phoenix styling and responsive design

## Expected Layout (7 columns)
1. ID
2. Client Name
3. Branch (NEW) - Shows "ID: X" for branches, "N/A" for main clients
4. Company Reg
5. SETA  
6. Address (NEW) - Shows formatted address or "N/A"
7. Status
8. Created
9. Actions