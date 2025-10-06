# Street Address Field Implementation Plan

## Database Changes
- Add `street_address` column to `public.locations` table (VARCHAR(200), NOT NULL)
- Provide SQL to manually add the column with proper comments and constraint

## Backend Changes
**LocationsModel Updates:**
- Update `::validate()` to add street address as required field (max 200 chars)
- Update `::create()` to include street_address in database insertion
- Update `::sanitizeFormData()` to clean street_address input
- Update `::locationExists()` to include street address in duplicate checking
- Update `::checkDuplicates()` to search by street address as well

**LocationsController Updates:**
- Update `::handleFormSubmission()` to process street_address in form data
- Update `::captureLocationShortcode()` to include street_address in default location array

## Frontend Changes
**Form Layout Restructuring:**
- **First Row:** Street Address, Suburb, Town/City (3 columns, required each)
- **Second Row:** Province, Postal Code, Latitude, Longitude (4 columns with smaller sizing)
- Move Province from first row to second row
- Adjust column classes for responsive 4-field layout in second row

**Street Address Field Features:**
- Required field with validation
- Auto-populated from Google Maps API when address search is used
- Positioned immediately after Google Address Search field
- Included in duplicate checking AJAX calls

**JavaScript Updates:**
- Include street_address in duplicate checking AJAX payload
- Update form validation to handle the new required field
- Ensure Google Maps autocomplete populates street_address field

## SQL for Manual Database Update
```sql
ALTER TABLE public.locations 
ADD COLUMN street_address VARCHAR(200) NOT NULL DEFAULT '' 
COMMENT ON COLUMN public.locations.street_address IS 'Street address line for the location';
```

## Validation Rules
- Required field (NOT NULL in database)
- Maximum 200 characters
- Standard WordPress text sanitization
- Included in duplicate detection logic
- Auto-populated from Google Maps API when available