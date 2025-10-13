## Fix 500 Internal Server Error

**Issue**: The 500 error is caused by calling a non-existent method `getHeadSiteForClient()` in the `sanitizeFormData()` method.

**Root Cause**: In my recent changes, I updated the `ClientsController::sanitizeFormData()` method to call `$this->getSitesModel()->getHeadSiteForClient($client['main_client_id'])` but this method doesn't exist.

**Solution**: Replace the non-existent method call with the correct method `getHeadSite($clientId)` that is available in the SitesModel.

**Changes Needed**:
1. In `ClientsController::sanitizeFormData()`, change:
   ```php
   $mainClientSite = $this->getSitesModel()->getHeadSiteForClient($client['main_client_id']);
   ```
   to:
   ```php
   $mainClientSite = $this->getSitesModel()->getHeadSite($client['main_client_id']);
   ```

**Expected Result**: The 500 Internal Server Error will be resolved and the form submission will work correctly with the simplified Sub Client logic.