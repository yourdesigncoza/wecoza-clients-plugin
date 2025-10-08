# Plan: Implement Address Autocomplete from Locations Table

## Current State Analysis
- Postal code field already works correctly: auto-populated from `locations` table and made readonly
- Street address fields (`client_street_address`, `client_address_line_2`) are currently editable
- The `street_address` data from locations table is already available in the frontend via data attributes
- Address fields are shown/hidden based on location selection

## Required Changes

### 1. Frontend JavaScript (`assets/js/client-capture.js`)
- **Modify `handleSuburbChange()` function**: Add street address autocomplete logic
- **Extract street address** from suburb option data: `option.data('street_address')`
- **Populate street address fields** with location data
- **Make address fields readonly** when location is selected
- **Clear address fields** when location selection is cleared

### 2. Backend PHP View (`app/Views/components/client-capture-form.view.php`)
- **Add `readonly => true`** to street address fields when location is selected
- **Add CSS classes** for JavaScript targeting: `js-street-address-field` and `js-address-line-2-field`
- **Initialize readonly state** based on whether location is pre-selected

### 3. Controller Updates (`app/Controllers/ClientsController.php`)
- **Handle readonly fields** in form submission (ensure location data takes precedence)
- **Auto-populate address fields** from location when saving client
- **Validate readonly fields** to prevent submission manipulation

## Implementation Details
1. **Address Field Autocomplete**: When suburb is selected, automatically fill street address from locations data
2. **Readonly Implementation**: Make street address fields non-editable when populated from location
3. **Data Flow**: locations table → SitesModel cache → JavaScript data attributes → form fields
4. **Fallback**: If no street address exists in location, fields remain editable
5. **Clear Logic**: When location is cleared, address fields become editable again

## Files to Modify
1. `assets/js/client-capture.js` - Add address autocomplete logic
2. `app/Views/components/client-capture-form.view.php` - Add readonly attributes and classes
3. `app/Controllers/ClientsController.php` - Ensure location data overrides manual input

This approach maintains consistency with the existing postal code implementation while providing the requested autocomplete functionality for street addresses.