## Overview
Implement `[wecoza_locations_edit redirect_url="/redirect-to" id="123"]` to edit an existing location using the same Phoenix-styled capture form, prefilled, with Google Places support. Triggered via links like `/edit-locations?mode=update&location_id={id}`.

## Changes
1) Controller (LocationsController)
- Register shortcode: `add_shortcode('wecoza_locations_edit', [$this,'editLocationShortcode']);`
- Enqueue assets when page has this shortcode (Google Maps + `location-capture.js`).
- `editLocationShortcode($atts)`:
  - Permission: `edit_wecoza_clients` (per your confirmation).
  - Resolve `$id` from `$_GET['location_id']` or `atts['id']`; require `mode=update`.
  - Load via `LocationsModel->getById($id)`.
  - On POST: verify nonce, validate with `validate($data, $id)`, then `update($id,$data)`.
  - If `redirect_url` provided: `wp_safe_redirect(redirect_url)` (optionally with `updated=1`); otherwise render success banner and stay on page.

2) Model (LocationsModel)
- Add `getById($id)` returning `location_id, street_address, suburb, town, province, postal_code, longitude, latitude`.
- Add `update($id,$data)` mirroring `create()`, set timestamps, refresh SitesModel cache, and return updated row.
- Update `validate($data,$id=null)` to ignore duplicates for the current record (duplicate check `AND location_id <> :id` when `$id`).

3) View
- Reuse `components/location-capture-form.view.php`.
- When editing, include hidden `<input type="hidden" name="location_id" value="{id}">`; keep ID non-editable; show success alert on save.

4) Config
- Add to `config/app.php` shortcodes map:
```php
'wecoza_locations_edit' => [
  'controller' => 'WeCozaClients\\Controllers\\LocationsController',
  'method' => 'editLocationShortcode',
  'description' => 'Edit existing location',
],
```

## Snippets
- Controller skeleton:
```php
public function editLocationShortcode($atts){
  if(!current_user_can('edit_wecoza_clients')) return '<p>'.esc_html__('You do not have permission to edit locations.','wecoza-clients').'</p>';
  $atts = shortcode_atts(['id'=>0,'redirect_url'=>''], $atts);
  $mode = isset($_GET['mode']) ? sanitize_text_field($_GET['mode']) : '';
  $id = (int)($_GET['location_id'] ?? $atts['id']);
  if($mode !== 'update' || $id<=0) return '<p>'.esc_html__('No location specified.','wecoza-clients').'</p>';
  $location = $this->getModel()->getById($id); if(!$location) return '<p>'.esc_html__('Location not found.','wecoza-clients').'</p>';
  $errors=[]; $success=false;
  if('POST'=== $_SERVER['REQUEST_METHOD'] && isset($_POST['wecoza_locations_form_nonce']) && wp_verify_nonce($_POST['wecoza_locations_form_nonce'],'submit_locations_form')){
    $data = $this->sanitizeFormData($_POST);
    $errors = $this->getModel()->validate($data,$id);
    if(!$errors){ $updated = $this->getModel()->update($id,$data); if($updated){ if($atts['redirect_url']){ wp_safe_redirect($atts['redirect_url']); exit; } $success=true; $location=$updated; }}
  }
  $provinces = array_values(\WeCozaClients\config('app')['province_options'] ?? []);
  return \WeCozaClients\view('components/location-capture-form', compact('errors','success','location','provinces') + ['google_maps_enabled'=>(bool)$this->getGoogleMapsApiKey()]);
}
```
- Model duplicate ignore (WHERE ... AND location_id <> :id) when `$id` is provided.
- View hidden field:
```php
<?php if (!empty($location['location_id'])): ?>
  <input type="hidden" name="location_id" value="<?php echo (int)$location['location_id']; ?>">
<?php endif; ?>
```

## Confirmed
- Permission: `edit_wecoza_clients`.
- Redirect support: `[wecoza_locations_edit redirect_url="/redirect-to"]` (fallback: stay on page with success alert).
