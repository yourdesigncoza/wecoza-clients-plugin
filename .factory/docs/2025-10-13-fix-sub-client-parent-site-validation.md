# Fix Sub-Client Creation Validation Error

## Issue Analysis

When creating a new sub-client, the system throws "Invalid parent site selected" error because:

1. **Root Cause**: The `validateSubSite()` method checks if parent site belongs to the same `$clientId`, but for new sub-clients, `$clientId = 0` (not yet created)
2. **Wrong Logic**: Sub-clients should have parent sites belonging to their main client, not themselves
3. **Validation Flaw**: The method validates `(int) $parentSite['client_id'] !== $clientId` which will always fail for new clients where `$clientId = 0`

## Required Changes

### 1. **Fix validateSubSite Logic** (SitesModel.php)
- **For new sub-clients**: Parent site should belong to `$mainClientId` (not `$clientId`)
- **For existing sub-clients**: Parent site should belong to the same sub-client (current logic)
- **Need to distinguish** between new client creation vs existing client update

### 2. **Update Form Submission Logic** (ClientsController.php)
- **Pass main client ID** to validation for new sub-clients
- **Modify validation call** to handle sub-client scenario correctly
- **Ensure proper error mapping** for validation failures

### 3. **Fix Parent Site Validation**
- **New sub-client**: Validate parent site belongs to `main_client_id`
- **Existing sub-client**: Validate parent site belongs to current client
- **Use appropriate client ID** based on creation vs update scenario

## Implementation Plan

1. **Modify `validateSubSite()` method** to accept an optional `$expectedClientId` parameter
2. **Update `handleFormSubmission()`** to pass correct client ID for validation
3. **Add logic** to determine expected client ID for parent site validation:
   - If `$isNew` and `!empty($main_client_id)`: use `$main_client_id`
   - Otherwise: use current `$clientId`
4. **Test both scenarios**: new main client, new sub-client, existing client updates

## Expected Result

- New main clients create successfully (no parent site validation)
- New sub-clients create successfully (parent site validated against main client)
- Existing client updates work correctly (parent site validated against current client)
- No more "Invalid parent site selected" errors