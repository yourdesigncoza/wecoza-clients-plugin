## Finalized spec (per your answers)

- Columns: Show ID, Street Address, Suburb, Town, Province, Postal Code. ID is read‑only (never editable in UI). 
- Edit action: Button links to `{edit_url}?mode=update&location_id={id}`.
- Google Address search: Integrate Google Places Autocomplete on the list’s search box; selection fills the search input and submits to filter the table server‑side.

## Implementation plan

1) app/Controllers/LocationsController.php
- Register shortcode: `add_shortcode('wecoza_locations_list', [$this,'listLocationsShortcode']);`
- Extend enqueueAssets: if page contains `[wecoza_locations_list]`, enqueue Google Maps (when key present) and `assets/js/locations-list.js`, localized with `googleMapsEnabled` and `ajax_url`.
- Add `public function listLocationsShortcode($atts)`:
  - Capability: `view_wecoza_clients`.
  - Attribs: `per_page=10`, `show_search=true`, `edit_url='/edit-locations'`.
  - Query: GET `location_page`, `location_search` (sanitized).
  - Call `LocationsModel->getAll(['search'=>..., 'limit'=>..., 'offset'=>...])` and `->count(...)`.
  - Render `display/locations-list` view with: `locations, total, page, totalPages, search, edit_url, atts`.

2) app/Models/LocationsModel.php
- Add methods:
  - `getAll($params)`: SELECT `location_id, street_address, suburb, town, province, postal_code` FROM `public.locations` with WHERE when `search` present:
    `(street_address ILIKE :s OR suburb ILIKE :s OR town ILIKE :s OR province ILIKE :s OR postal_code ILIKE :s)`; ORDER BY `province, town, suburb, street_address`; LIMIT/OFFSET.
  - `count($params)`: COUNT(*) with same WHERE.

3) app/Views/display/locations-list.view.php (new)
- Phoenix UI card and table (matching docs/example-all-clases.html patterns):
  - Header: "All Locations"; right‑side search box (when `atts['show_search']`) with `<input id="location-search-input" ...>`.
  - Optional total badge: `<span class="badge badge-phoenix fs-10 badge-phoenix-primary">Total: <?php echo (int)$total; ?></span>`.
  - Table columns: ID, Street Address, Suburb, Town, Province, Postal Code, Actions.
  - Edit button:
    ```php
    <a class="btn btn-sm btn-outline-secondary border-0" title="Edit Location"
       href="<?php echo esc_url( add_query_arg( array('mode'=>'update','location_id'=>(int)$row['location_id']), $edit_url ) ); ?>">
      <i class="bi bi-pencil"></i>
    </a>
    ```
  - Pagination with `location_page` param using Bootstrap styles.
  - All outputs escaped.

4) assets/js/locations-list.js (new)
- If `googleMapsEnabled`, attach `google.maps.places.Autocomplete` to `#location-search-input` (type: geocode; region ZA if you prefer). On place selection, set input value to a concise string (street/suburb/town) and submit the surrounding form.
- If no Google key, behave as a normal text search.

Notes
- Reuse `getGoogleMapsApiKey()` from LocationsController; follow nonce and security practices already used in capture.
- No edits to IDs; list is read‑only aside from navigation to edit.

Ready to implement on confirmation.