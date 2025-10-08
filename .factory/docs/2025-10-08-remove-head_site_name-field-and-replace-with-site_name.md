## Plan: Remove and Replace `head_site_name` Field

### Changes Required:

1. **Update Form Field** (`app/Views/components/client-capture-form.view.php`):
   - Change `head_site_name` field to `site_name`
   - Update field label from "Head Site Name" to "Site Name"

2. **Update Controller** (`app/Controllers/ClientsController.php`):
   - Replace `head_site_name` reference with `site_name` in site array creation
   - Update sanitization to use `site_name` key

3. **Update JavaScript** (`assets/js/client-capture.js`):
   - Replace `head_site_name` references with `site_name`
   - Update form field selectors

4. **Add Validation Rules** (`config/app.php`):
   - Add validation rules for `site_name` field (required, max_length)

### Benefits:
- Consistent naming between frontend and backend
- Eliminates confusion between `head_site_name` and `site_name`
- Proper validation for the site name field
- Cleaner, more maintainable code