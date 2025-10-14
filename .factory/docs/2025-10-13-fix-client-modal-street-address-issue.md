# Fix Street Address Not Showing in Client Details Modal

## Problem Analysis
The client details modal is not displaying street address data because:

1. **Missing columns in SQL query**: The `fetchHeadSitesFromDatabase()` method in `SitesModel` is not selecting `address_line_1` and `address_line_2` columns from the sites table
2. **Data flow**: Client details → `ajaxGetClientDetails()` → `getById()` → `hydrateRows()` → `hydrateClients()` → tries to access `address_line_1`/`address_line_2` but they're null

## Root Cause
In `/app/Models/SitesModel.php`, the `fetchHeadSitesFromDatabase()` method only selects:
```sql
SELECT s.site_id, s.client_id, s.site_name, s.place_id, s.parent_site_id, s.created_at, s.updated_at
```

But the `hydrateClients()` method tries to access:
- `$site['address_line_1']` → `$row['client_street_address']`
- `$site['address_line_2']` → `$row['client_address_line_2']`

## Solution
Update the SQL query in `fetchHeadSitesFromDatabase()` to include the missing address columns:
```sql
SELECT s.site_id, s.client_id, s.site_name, s.place_id, s.parent_site_id, 
       s.address_line_1, s.address_line_2, s.created_at, s.updated_at
```

## Files to Modify
1. `/app/Models/SitesModel.php` - Update `fetchHeadSitesFromDatabase()` method
2. Clear cache after fix to ensure fresh data loading

## Testing Plan
1. Test client details modal displays street address correctly
2. Verify address line 2 also appears 
3. Test with clients that have no sites (should show 'N/A')
4. Clear site cache to ensure changes take effect