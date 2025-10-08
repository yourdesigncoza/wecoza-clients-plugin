**Plan: Enhanced Suburb Display with Street Address**

**Objective:** Update suburb dropdown to show "SuburbName - StreetAddress" format to distinguish between locations with same suburb name.

**Backend Changes:**
1. Update `SitesModel::fetchAllLocations()` SQL query to include `street_address`
2. Update data mapping in `rebuildLocationCache()` to include `street_address` in location map and hierarchy
3. Add `street_address` field to suburb data structure

**Frontend Changes:**
1. Update `populateSuburbs()` function to include `street_address` data
2. Modify option text to display "SuburbName - StreetAddress" format
3. Add street_address as data attribute for consistency

**Edge Cases:** Handle empty/null street addresses by showing suburb name only

**Files to modify:**
- `app/Models/SitesModel.php` (SQL query + data structure)
- `assets/js/client-capture.js` (option display logic)