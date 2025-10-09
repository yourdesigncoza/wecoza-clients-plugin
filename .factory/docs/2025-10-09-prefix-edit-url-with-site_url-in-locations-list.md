## Goal
Ensure the Edit link in `[wecoza_locations_list]` uses a fully-qualified URL built with `site_url()` (HTTPS), per WordPress best practices.

## Changes
1) Controller: app/Controllers/LocationsController.php
- In `listLocationsShortcode($atts)`, convert the provided `edit_url` attribute (path) to an absolute URL once and pass it to the view:
```php
$edit_base = site_url(ltrim((string)$atts['edit_url'], '/'), 'https');
...
'edit_url' => $edit_base,
```

2) View: app/Views/display/locations-list.view.php
- Keep the current `add_query_arg` usage but base it on the absolute URL the controller now provides:
```php
<a href="<?php echo esc_url(add_query_arg(['mode'=>'update','location_id'=>$id], $edit_url)); ?>" class="btn btn-sm btn-outline-secondary border-0" title="<?php esc_attr_e('Edit Location','wecoza-clients'); ?>">
  <i class="bi bi-pencil"></i>
</a>
```

Notes
- We normalize the shortcode attribute to a path (strip leading slash if present) before calling `site_url()` with `'https'`.
- If no `edit_url` is provided, default remains `/edit-locations` which will be resolved to `site_url('/edit-locations','https')`.

Ready to implement on confirmation.