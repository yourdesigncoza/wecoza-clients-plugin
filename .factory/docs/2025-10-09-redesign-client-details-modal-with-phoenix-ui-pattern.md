## Update Client Details Modal UI

**Goal**: Redesign the "Client Details" modal to match the Phoenix design pattern shown in the example HTML and screenshots.

**Current Issues**: 
- Current modal uses Bootstrap cards with labels above values
- Sparse layout with excessive white space
- Lacks the professional two-column table design with icons

**Design Changes Required**:

### 1. Replace Modal Body Structure
- Remove all card sections and replace with a two-column table layout similar to the example
- Use `table-stats table table-hover table-sm fs-9` classes
- Implement responsive grid system with `col-sm-12 col-xxl-6` columns

### 2. Add Icon-Based Field Labels
- Add colored circular icon backgrounds for each field label
- Use appropriate Bootstrap icons for different field types:
  - Building/Business: `bi-building`, `bi-hash`
  - Location: `bi-geo-alt`, `bi-pin-map`
  - Contact: `bi-person`, `bi-envelope`, `bi-telephone`
  - Dates: `bi-calendar-check`, `bi-calendar-plus`
  - Status: `bi-check-circle`, `bi-info-circle`

### 3. Reorganize Field Grouping
- **Left Column**: Basic client info, location details
- **Right Column**: Contact information, dates, status
- **Bottom Section**: Additional details if needed

### 4. Update CSS Classes
- Use Phoenix badge classes for status indicators
- Apply consistent spacing and typography
- Use `ydcoza-w-150` class for label column width

### 5. Update JavaScript Population Function
- Modify `populateClientModal()` to work with new DOM structure
- Add status badge formatting
- Ensure proper handling of empty/missing values

**Files to Modify**:
1. `app/Views/display/clients-table.view.php` - Update modal HTML structure
2. `assets/js/clients-table.js` - Update population function to match new structure

**Result**: A modern, professional client details modal matching the Phoenix design system with compact icon-based layout.