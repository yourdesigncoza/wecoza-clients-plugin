# Fix Client Location Association Architecture

## 🎯 **Correct Architecture Understanding**

The client should **NOT** store address fields directly. The proper flow is:
```
Client → Site (with place_id) → Location (from public.locations table)
```

**SitesModel correctly:**
- Stores `site_name` and `place_id` in sites table
- Hydrates client data with virtual location fields from locations table
- Does NOT store address data in clients table

**Current Problem:**
- Controller tries to save location fields to clients table (which don't exist)
- Should only save `client_town_id` (place_id reference) and let SitesModel handle the rest

## 🔧 **Required Fixes**

### **1. Fix Controller Sanitization (ClientsController.php)**
**Remove these assignments - they should NOT be saved to clients table:**
```php
$client['client_province'] = ...
$client['client_town'] = ...
$client['client_suburb'] = ...
$client['client_postal_code'] = ...
$client['client_street_address'] = ...
$client['client_address_line_2'] = ...
```

**Keep only:**
```php
$client['client_town_id'] = $placeId; // This is the place_id reference
```

### **2. Update Form Validation Logic**
**Location validation should:**
- Validate that `client_town_id` (place_id) is valid
- Remove validation for non-existent client table fields
- Ensure location data comes from SitesModel hydration

### **3. Fix Form Field Processing**
**Current issue:** Forms try to populate address fields that don't exist in clients table
**Solution:** 
- Address fields should be read-only and populated from location data
- Only `client_town_id` should be submitted/saved
- Site creation should handle the address association

### **4. Update Client Model Fillable Array**
**Remove non-existent fields from fillable:**
- `client_province`, `client_town`, `client_suburb`, `client_postal_code`
- `client_street_address`, `client_address_line_2`

**Keep only:**
- `client_town_id` (the place_id reference)

### **5. Fix Update Form Pre-population**
**Address fields should be populated from SitesModel data, not from clients table**
- Remove fallback to non-existent client fields
- Use SitesModel hydration to populate address display fields

## 🎯 **Implementation Steps**

1. **Clean up controller sanitization** - remove direct location field assignments
2. **Update ClientsModel fillable array** - remove non-existent fields  
3. **Fix form validation** - only validate client_town_id (place_id)
4. **Update form field rendering** - make address fields read-only, populated from SitesModel
5. **Ensure site creation** properly associates client with location via place_id

This will establish the correct Client → Site → Location architecture where:
- Clients table only stores business data + `client_town_id` reference
- Sites table stores `site_name` and `place_id` 
- Locations table stores actual address data
- SitesModel hydrates client data with virtual location fields for display