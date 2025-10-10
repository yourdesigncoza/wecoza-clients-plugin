## Plan to Update Client Update Form View

### Key Changes Required:

1. **Container Structure**
   - Remove Bootstrap card wrapper and header
   - Use `wecoza-clients-form-container` class like capture form
   - Remove update-specific header and ID badge

2. **Layout Restructuring**
   - Convert from section-based layout to streamlined 3-column rows
   - Add discovery-themed border separators between logical sections
   - Match field grouping: Basic Info → Sub-Client → Address → Contact → Business

3. **Address Field Enhancement**
   - Implement location cascade with proper field visibility
   - Add JavaScript for province/town/suburb interaction
   - Include auto-populated address fields based on selection
   - Add hidden fields for suburb/town names

4. **Field Styling Consistency**
   - Add `form-control-sm` classes to all form controls
   - Ensure consistent field sizing and spacing
   - Match validation feedback styling

5. **JavaScript Functionality**
   - Add location cascade logic from capture form
   - Implement sub-client toggle functionality
   - Update form submission handling

6. **Submit Button**
   - Reposition to match capture form layout
   - Update text to "Update Client" and use consistent styling

### Files to Modify:
- `app/Views/components/client-update-form.view.php` - Main form view update

The update form will maintain its update-specific functionality while adopting the capture form's modern, streamlined design and improved user experience.