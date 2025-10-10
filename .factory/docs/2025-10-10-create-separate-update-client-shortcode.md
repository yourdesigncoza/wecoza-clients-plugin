## Create Separate Update Client Shortcode

### Proposed Implementation:
Create `[wecoza_update_clients]` shortcode as a standalone update function based on `[wecoza_capture_clients]`

### Key Design Decisions Needed:

**Question 1 - URL Parameter Handling:**
- Option 1: Auto-detect from URL (`?mode=update&client_id=17`)
- Option 2: Require attribute (`[wecoza_update_clients id="17"]`)
- Option 3: Support both methods

**Question 2 - Form Behavior:**
- Option A: Same form pre-populated with data
- Option B: Update-specific modifications:
  - Clear "Update Mode" indicator
  - Modified button text ("Save Changes")
  - Update-specific validation
  - Potentially hide immutable fields

### Implementation Plan:
1. **Copy `captureClientShortcode` method** as `updateClientShortcode`
2. **Register new shortcode** `[wecoza_update_clients]`
3. **Modify ID detection logic** based on URL parameter preference
4. **Update form template** based on form behavior preference
5. **Update edit links** in clients table to use new shortcode

### Benefits:
- Clear separation between create and update workflows
- Potential for update-specific features
- More intuitive shortcode naming
- Flexibility in URL routing