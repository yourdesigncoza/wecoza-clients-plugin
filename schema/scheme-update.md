Here’s a tight, IDE-friendly brief you can paste into your repo (e.g., `/docs/CHANGELOG.md` or top of the PR).

# Client & Location Restructure — Dev Update

## What changed (DB model)

* **Keep `clients`** as the legal entity (no physical address on this table).
* **Use `sites`** for all operating locations with hierarchy:

  * `sites(site_id, client_id, parent_site_id NULL, site_name, address_line_1/2, place_id?)`
  * Head office: `parent_site_id IS NULL`; sub-sites: `parent_site_id = head.site_id`.
  * Unique: `(client_id, lower(site_name))` to prevent dupes.
* **Contacts & comms now target sites**:

  * `client_contact_persons.site_id` (nullable; defaulted to client’s head site).
  * `client_communications.site_id` (same rule).
* **Deprecated columns removed from `clients`**:

  * `branch_of`, `town_id`, `address_line`, `suburb`, `province`, `postal_code`.

## App rules (enforced in UI + API)

* **Editors (end users)**: *select only* from existing lists. No create/update.

  * Must choose:

    1. `client_id`
    2. a **head site** (optional if single-site client)
    3. a **sub-site** (when applicable)
* **Admins**: full CRUD for `clients` and `sites` (+ converting single-site → head with subs).

## Picker queries (use as-is)

```sql
-- Clients (active)
SELECT client_id, client_name
FROM public.clients
WHERE client_status = 'active'
ORDER BY client_name;

-- Head sites for a client
SELECT site_id, site_name
FROM public.sites
WHERE client_id = :client_id AND parent_site_id IS NULL
ORDER BY site_name;

-- Sub-sites for a head site
SELECT site_id, site_name
FROM public.sites
WHERE parent_site_id = :head_site_id
ORDER BY site_name;
```

## Validation (server-side)

* On create/update of a sub-site: **parent and child must share the same `client_id`**.
* On writes to contacts/comms:

  * If `site_id` is provided, it **must** belong to the same `client_id` (if client_id also provided).
  * If `site_id` is null, default to the client’s head site (if exists).

## Code touchpoints

* Replace any `clients.*address*` or `clients.town_id` usage with:

  * `JOIN sites ON sites.client_id = clients.client_id` (pick head or specific site)
  * Optionally `JOIN locations ON locations.location_id = sites.place_id`
* Replace “branch/child client” logic with **site hierarchy**:

  * Old: `clients.branch_of`
  * New: `sites.parent_site_id`
* Ensure **contact/comms forms** write `site_id` (not just `client_id`).

## UI tweaks

* Forms: three chained dropdowns (Client → Head Site → Sub-site).
* Read views: display `client_name` + `site_name` (and address if present), not client address.

## Backward compatibility / migrations

* Existing contacts/comms were repointed to each client’s **head site** during migration.
* Duplicate site names within a client are disallowed by the unique index (lowercased compare).

## Acceptance checklist

* [ ] Editors cannot create/update clients or sites (UI/API guard).
* [ ] New records for training sessions/assignments always carry a valid `site_id`.
* [ ] Contacts/comms correctly show/resolve site context.
* [ ] No references remain to removed `clients` columns.
* [ ] Pickers return expected rows for single-site vs head/sub clients.

If you want, I can drop in small PHP helpers (WordPress/vanilla PDO) for the three picker queries and a guard function like `assertSameClient($childSiteId, $parentSiteId)`.
