## Update Actions Column with Individual Icon Buttons

**Goal**: Replace the dropdown menu with individual icon-only buttons arranged horizontally, matching the provided HTML example.

**Current Implementation**: 
- Dropdown menu with three dots trigger
- Text-based menu items with icons
- Extra click required to access actions

**Desired Implementation** (based on provided HTML):
- Three separate icon-only buttons in a horizontal group
- Using `d-flex justify-content-center gap-2` for layout
- Each button has `btn btn-sm btn-outline-secondary border-0` classes
- Icons only (no text) with tooltips for accessibility
- Centered alignment in the table cell

**Required Changes**:

### 1. Update HTML Structure
Replace the dropdown structure with:
```html
<td class="text-center">
    <div class="d-flex justify-content-center gap-2" role="group">
        <a href="edit-link" class="btn btn-sm btn-outline-secondary border-0" title="Edit Client">
            <i class="bi bi-pencil"></i>
        </a>
        <button type="button" class="btn btn-sm btn-outline-secondary border-0" title="View Details" onclick="viewClientDetails(id)">
            <i class="bi bi-eye"></i>
        </button>
        <button type="button" class="btn btn-sm btn-outline-secondary border-0" title="Delete Client" onclick="deleteClient(id, name)">
            <i class="bi bi-trash"></i>
        </button>
    </div>
</td>
```

### 2. Button Styling and Functionality
- Use `btn btn-sm btn-outline-secondary border-0` for consistent appearance
- Add `title` attributes for tooltips (accessibility)
- Maintain existing onclick handlers and JavaScript functions
- Preserve edit links and delete confirmation functionality

### 3. Layout Adjustments
- Change table cell to `text-center` alignment
- Use flexbox with `gap-2` for proper button spacing
- Add `role="group"` for semantic accessibility

**Files to Modify**:
- `app/Views/display/clients-table.view.php` - Replace Actions column dropdown with individual buttons around line 270

This creates a cleaner, more accessible interface with immediate access to all actions without dropdown interaction.