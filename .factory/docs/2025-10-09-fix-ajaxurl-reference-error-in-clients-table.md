## Fix AJAX URL Issue in Clients Table

**Problem**: The `clients-table.js` file is using `ajaxurl` (undefined) instead of the properly localized `wecozaClients.ajaxUrl`, causing JavaScript errors and breaking modal functionality.

**Root Cause**: Inconsistent variable naming between the localization object (`ajaxUrl`) and JavaScript usage (`ajaxurl`).

**Solution**: 
1. Update `assets/js/clients-table.js` to replace all instances of `ajaxurl` with `wecozaClients.ajaxUrl`
2. Verify the localization is working correctly in `ClientsController.php`

**Changes Required**:
- Line 135: `url: ajaxurl,` → `url: wecozaClients.ajaxUrl,`
- Line 215: `form.action = ajaxurl;` → `form.action = wecozaClients.ajaxUrl;`  
- Line 237: `url: ajaxurl,` → `url: wecozaClients.ajaxUrl,`

**Testing**: Verify the "View Details" modal opens correctly and AJAX operations work as expected.

This is a targeted fix that will resolve the immediate JavaScript error without affecting other functionality.