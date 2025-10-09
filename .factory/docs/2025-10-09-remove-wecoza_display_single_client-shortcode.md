# Plan to Safely Remove `wecoza_display_single_client` Shortcode

## Analysis Summary
The `wecoza_display_single_client` shortcode is self-contained and can be safely removed without affecting other functionality. Here are the dependencies I found:

### Dependencies to Remove:
1. **Shortcode registration** in `registerShortcodes()` method (line 65 in ClientsController.php)
2. **Handler method** `displaySingleClientShortcode()` (lines 391-423 in ClientsController.php)
3. **Configuration entry** in `config/app.php` under 'shortcodes' section
4. **Asset loading check** `has_single_display` in `enqueueAssets()` method (line 99 in ClientsController.php)
5. **View file** `app/Views/display/single-client-display.view.php`

### Safe to Keep:
- `ajaxGetClientDetails()` method - used by the clients table modal functionality
- `ajaxGetClient()` method - used by the edit functionality
- All other AJAX handlers and methods

## Removal Steps:
1. Remove the shortcode registration from `registerShortcodes()`
2. Remove the `displaySingleClientShortcode()` method entirely
3. Remove the `$has_single_display` variable and its check from `enqueueAssets()`
4. Remove the shortcode configuration from `config/app.php`
5. Delete the `single-client-display.view.php` view file

## Impact Assessment:
- **No breaking changes** to other shortcodes or functionality
- **No shared dependencies** with other features
- **Safe removal** as the functionality is isolated
- **Modal view functionality** in the clients table will continue to work (uses `ajaxGetClientDetails`)

This removal will clean up unused code while preserving all existing client management functionality.