## Plan: Fix Suburb Validation Issue in Client Form

### Root Cause:
Form submission fails with "Please select a valid suburb for the site" error, indicating `client_town_id` (place_id) is 0 or invalid.

### Investigation Steps:
1. **Debug JavaScript Location Loading**
   - Check if location hierarchy is loading properly
   - Verify suburb dropdown population
   - Add console logging for suburb selection events

2. **Verify Database Location Data**
   - Check if locations table has the expected suburb data
   - Test `getLocationById()` method with sample IDs
   - Verify location cache functionality

3. **Fix Form Field Handling**
   - Ensure `client_town_id` gets proper value when suburb selected
   - Debug the suburb change handler in JavaScript
   - Verify form data submission includes correct place_id

4. **Add Debug Logging**
   - Add server-side logging for validation failure
   - Log received form data to identify missing values
   - Add client-side logging for JavaScript events

### Implementation Steps:
1. Add debug logging to `validateHeadSite()` method
2. Add console logging to JavaScript suburb selection
3. Test with known valid location data
4. Fix any discovered issues with location loading/validation
5. Remove debug logging after fixing the issue

### Files to Modify:
- `app/Models/SitesModel.php` - Add debug logging to validation
- `assets/js/client-capture.js` - Add suburb selection debugging
- Potentially `app/Controllers/ClientsController.php` - Log form submission data

This should identify and resolve the suburb validation failure preventing client form submission.