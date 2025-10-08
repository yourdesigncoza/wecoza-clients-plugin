## Plan: Fix Nonce Mismatch in Client Form Submission

### Root Cause:
The form uses two different nonces:
- Form nonce: `wecoza_clients_form_nonce` with action `submit_clients_form`
- AJAX nonce: `nonce` with action `wecoza_clients_ajax` (added by JavaScript)

### Solution Options:

**Option 1: Use Only AJAX Nonce (Recommended)**
- Remove the form nonce field `wecoza_clients_form_nonce`
- Keep only the AJAX nonce verification
- This is cleaner since the form is submitted via AJAX

**Option 2: Verify Both Nonces**
- Add verification for both nonces in the AJAX handler
- More secure but potentially redundant

### Implementation Steps:
1. **Remove form nonce field** from the form template
2. **Keep existing AJAX nonce verification** (it's already correct)
3. **Test form submission** to ensure it works
4. **Check debug logs** for any remaining errors

### Files to Modify:
- `app/Views/components/client-capture-form.view.php` - Remove form nonce field

This will eliminate the nonce conflict and allow the AJAX form submission to work properly.