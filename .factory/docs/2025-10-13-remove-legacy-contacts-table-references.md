# Fix Client Contact Field Mappings After Dropping Legacy Table

## Issue Analysis

The `client_contact_persons` table was dropped from the database, but the code still tries to query it, causing SQL errors. The system has already moved to a **consolidated approach** where contact information is stored directly in the `clients` table fields.

## Required Changes

### 1. **Remove Legacy Contacts Table References**
- Remove or modify `ClientContactsModel` to stop querying the dropped table
- Update `ClientsModel::hydrateRelatedData()` to remove the hybrid fallback logic
- Remove initialization of `$this->contactsModel` in `ClientsModel`

### 2. **Update Contact Field Handling**
- Ensure all contact data comes from consolidated fields in `clients` table:
  - `contact_person`
  - `contact_person_email` 
  - `contact_person_cellphone`
  - `contact_person_tel`
  - `contact_person_position`

### 3. **Clean Up Controller Code**
- Remove any references to `getContactsModel()` in controller
- Ensure form submission only works with consolidated fields

### 4. **Update Validation Rules**
- Ensure contact validation rules only apply to consolidated fields
- Remove any validation that expects legacy contact structure

## Files to Modify

1. **`app/Models/ClientsModel.php`**
   - Remove `contactsModel` property and initialization
   - Simplify `hydrateRelatedData()` to only handle communications
   - Remove `getContactsModel()` method

2. **`app/Models/ClientContactsModel.php`**
   - Either delete entirely or modify to return empty arrays
   - Remove all SQL queries to `client_contact_persons` table

3. **`app/Controllers/ClientsController.php`**
   - Remove `getContactsModel()` calls
   - Ensure contact data handling only uses consolidated fields

## Expected Result

- No more SQL errors about missing `client_contact_persons` table
- All client operations (create, read, update, display) work with consolidated contact fields
- Forms correctly populate and save contact information
- Client display table shows contact data properly

This will complete the transition to the simplified consolidated contact system without legacy compatibility.