## Fix Missing Data in Update Client Form

### Problem Analysis:
The update form is missing critical data that appears in the "View Client" function due to:

1. **Over-aggressive filtering**: `filterClientDataForForm()` is removing too much data
2. **Field name mismatches**: Database uses `company_registration_number` but form expects `company_registration_nr`
3. **Missing site/contact data**: `head_site` and contact information are being filtered out
4. **Missing locations data**: Site location information is not being properly extracted and displayed

### Root Causes:

**1. Field Mapping Issues:**
- Database: `company_registration_number` → Form: `company_registration_nr`
- Missing proper field resolution in the normalization process

**2. Data Filtering Too Restrictive:**
- `head_site` array contains site data that should be used
- `contact_site_id` and other IDs being filtered out
- Location data not being properly extracted from `head_site.location`

**3. Site Data Not Being Processed:**
- `head_site` contains site information that should populate form fields
- Address fields not being mapped from site data
- Location data not being extracted from nested site arrays

### Solution Strategy:

**Option 1: Fix Data Processing (Recommended)**
- Improve `filterClientDataForForm()` to preserve important data
- Add proper field mapping for `company_registration_number` → `company_registration_nr`
- Extract site data from `head_site` array and populate form fields
- Extract location data from nested site arrays

**Option 2: Alternative Data Loading**
- Load client data using the same method as display clients
- Use `getAll()` method with client ID filter instead of `getById()`
- Ensure consistent data loading across all functions

### Implementation Plan:

**Step 1: Fix Field Name Mapping**
- Add `company_registration_number` to columnCandidates
- Ensure proper resolution to `company_registration_nr`

**Step 2: Improve Data Filtering**
- Update `filterClientDataForForm()` to preserve site and contact data
- Extract address information from `head_site` array
- Handle nested location data properly

**Step 3: Populate Site Data in Form**
- Map `head_site.site_name` → `site_name` field
- Map `head_site.address_line_1` → `client_street_address` field
- Map `head_site.location.*` → address fields
- Handle `head_site.place_id` → `client_town_id`

**Step 4: Ensure Data Consistency**
- Test that update form shows same data as view client
- Verify all fields are properly populated
- Ensure no data loss in filtering process

This approach will fix the missing data while maintaining the array conversion safety we've already implemented.