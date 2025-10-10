## Fix Array to String Conversion in Update Form

### Root Cause Analysis:
The `wecoza_display_clients` shortcode works because it only accesses specific known fields from client data. The update form fails because:

1. **Database Schema Mismatch**: The live database may have additional fields (JSONB) not defined in the schema file
2. **ColumnMap Filtering**: `normalizeRow()` only maps known fields, but extra database fields remain in raw data  
3. **ViewHelpers Issue**: `esc_attr($value)` fails when `$value` is an array instead of string
4. **Complete Data Passing**: Update form passes entire `$client` array to field rendering, including unknown array fields

### Solution Strategy:

**Option 1: Filter client data in update form** (Recommended)
- Pre-filter `$client` array to only include known scalar fields before rendering
- Remove any array fields or unknown database fields
- Use the same field whitelist as the display view

**Option 2: Fix ViewHelpers to handle arrays**  
- Modify `renderInput()` to check if value is array before `esc_attr()`
- Convert arrays to empty strings or JSON for display
- Apply same protection to all field renderers

**Option 3: Update columnMap to include all database fields**
- Add missing database fields to columnCandidates
- Ensure all fields are properly normalized
- Handle JSONB fields appropriately

### Implementation Plan:
1. **Immediate Fix**: Add data filtering in `updateClientShortcode()` to only pass known safe fields
2. **Define Safe Fields**: Create whitelist of fields that should be rendered in forms  
3. **Filter Before Rendering**: Remove any array or unknown fields before passing to ViewHelpers
4. **Add Debug Logging**: Log which fields are being filtered to identify database discrepancies

### Safe Fields List (based on schema and usage):
- client_name, company_registration_nr, seta, client_status
- financial_year_end, bbbee_verification_date, main_client_id  
- contact_person, contact_person_email, contact_person_cellphone, contact_person_tel, contact_person_position
- client_street_address, client_address_line_2, client_province, client_town, client_suburb, client_postal_code
- client_town_id, site_name, site_id

This approach ensures only scalar, known-safe values are passed to form rendering functions.