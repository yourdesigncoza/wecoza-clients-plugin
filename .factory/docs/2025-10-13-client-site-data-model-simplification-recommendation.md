### Recommendation Overview
I recommend **keeping three tables but clarifying their responsibilities** rather than collapsing clients and sites into one table.  Locations stay as the immutable catalog of physical places; sites remain the join layer that links a business entity to a location; clients hold the legal/business metadata.  The core change is to let sites capture the head/sub hierarchy directly while clients reference their head site, so that "sub-clients" become ordinary clients linked to a parent site instead of forcing cross-client site ownership.

### Proposed Schema Shape
1. **locations** (unchanged): master list, never deleted, contains address/geo data.
2. **sites** (refined): `client_id`, `location_id`, `parent_site_id`, `type` (head/sub/virtual), status, metadata.  Parent/child hierarchy lives here, and every client—main or sub—has exactly one primary site row.  Optional extra sites for logistics remain possible by allowing multiple site rows per client.
3. **clients** (leaner): legal/trading info, contact details, and a `primary_site_id` foreign key.  Sub-clients reference their parent client via a simple `parent_client_id` column (nullable for main clients).

### Key Advantages
- **Clean hierarchy**: parent/child logic stays inside the sites table, matching the domain (sites relate to sites).  The trigger `fn_sites_same_client` continues to guarantee integrity because child sites share the same `client_id` as the owning client.
- **Predictable CRUD**: creating any client = create client row + site row (head or sub).  No special fallback paths or mixed responsibilities.
- **Future-proof**: we can extend sites with capacity, staff, or scheduling data without touching client records, and locations remain the canonical address store.

### Implementation Notes
- Migrations: ensure all clients have a primary site; backfill `primary_site_id`; introduce `parent_client_id` on clients if not already present; adjust foreign keys and triggers accordingly.
- Application code: when saving a “sub-client”, first resolve the parent’s head site, set `parent_client_id`, and create the child’s site with `parent_site_id` referencing the parent site.  Validation remains straightforward (sites share the same client).
- UI: expose parent-client selection, but keep address selection tied to the site/location cascade so data entry stays accurate.

This approach keeps the data model expressive without introducing a single overloaded table that mixes legal entities with physical locations.