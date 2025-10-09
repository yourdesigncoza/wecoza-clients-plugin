# Plan: Enhance [wecoza_display_clients] shortcode with Phoenix styling

## Overview
Update the existing `[wecoza_display_clients]` shortcode to use the Phoenix-styled template from `example-all-clients.html`, adapting it for clients instead of sites.

## Implementation Steps

### 1. Update ClientsModel::getAll() method
- Ensure it supports search across client_name, company_registration_nr, seta, client_status
- Add proper ordering by client_name alphabetically
- Verify pagination works correctly

### 2. Create new clients table view
- Create `app/Views/display/clients-table.view.php` based on `example-all-clients.html`
- Adapt columns for clients: ID, Client Name, Company Registration, SETA, Status, Created, Actions
- Include Phoenix card styling with header, search box, and summary statistics
- Add responsive table with hover effects
- Include pagination component matching the example

### 3. Update existing clients-display.view.php
- Replace content to include the new clients-table view
- Maintain existing shortcode attributes: per_page, show_search, show_filters, show_export
- Keep existing search/filter functionality but with new styling

### 4. Enhance controller method
- Update `displayClientsShortcode()` to pass additional data for the new template
- Include statistics for summary strip (total clients, active, leads, etc.)
- Maintain existing GET parameters for pagination and filtering

### 5. Add JavaScript enhancements
- Create `assets/js/clients-table.js` for table interactions
- Include search functionality, sorting capabilities, and dynamic pagination
- Follow the same pattern as the existing locations-list.js

## Key Features
- Phoenix-styled card with search and summary statistics
- Responsive table with hover effects
- Sortable columns (ID, Client Name, Created)
- Pagination with proper URL handling
- Edit action linking to client edit functionality
- Status badges with Phoenix styling
- Search across multiple client fields

## Files to Modify
- `app/Views/display/clients-display.view.php` - Update main view
- `app/Controllers/ClientsController.php` - Enhance controller method
- `assets/js/clients-table.js` - Add table interactions (create new)

## Files to Create  
- `app/Views/display/clients-table.view.php` - New styled table component

This will provide a modern, professional client management interface matching the Phoenix design system while maintaining all existing functionality.