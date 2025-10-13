## Database Consolidation Plan: Merge Contact Person Fields into Clients Table

**Goal**: Add contact person fields directly to the `clients` table for new clients, while preserving existing `client_contact_persons` table for legacy data.

### Phase 1: Database Schema Changes

**File**: `schema/sites_table_migration_01.sql`

**Changes to `clients` table**:
1. Add contact person columns directly to clients table:
   ```sql
   -- Add contact person fields to clients table for new consolidated approach
   ALTER TABLE public.clients 
   ADD COLUMN contact_person character varying(100),
   ADD COLUMN contact_person_email character varying(100),
   ADD COLUMN contact_person_cellphone character varying(20),
   ADD COLUMN contact_person_tel character varying(20),
   ADD COLUMN contact_person_position character varying(50);
   ```

2. Add indexes for performance:
   ```sql
   -- Add index for contact email searches
   CREATE INDEX idx_clients_contact_email ON public.clients USING btree (contact_person_email) WHERE (contact_person_email IS NOT NULL);
   ```

3. Add comments for documentation:
   ```sql
   -- Add column comments
   COMMENT ON COLUMN public.clients.contact_person IS 'Primary contact person name (consolidated approach)';
   COMMENT ON COLUMN public.clients.contact_person_email IS 'Primary contact person email';
   COMMENT ON COLUMN public.clients.contact_person_cellphone IS 'Primary contact person cellphone';
   COMMENT ON COLUMN public.clients.contact_person_tel IS 'Primary contact person landline';
   COMMENT ON COLUMN public.clients.contact_person_position IS 'Primary contact person job position';
   ```

### Phase 2: Application Code Updates

1. **ClientsModel.php**: 
   - Update `columnCandidates` to include new contact fields
   - Update `$fillable` array to include contact fields
   - Remove contact person hydration logic from `getAll()` method
   - Keep backward compatibility logic for legacy data

2. **ClientsController.php**: 
   - Simplify `sanitizeFormData()` - contact fields go directly into client array
   - Update `handleFormSubmission()` to save contact data with client (no separate contact model)
   - Remove contact person separate processing

3. **Form Processing**: 
   - Forms will now save contact info directly to clients table
   - Legacy clients will continue to work through contact persons table
   - New clients will use consolidated approach

### Phase 3: Backward Compatibility Strategy

**Hybrid Approach**:
- **Legacy clients**: Continue using `client_contact_persons` table
- **New clients**: Use consolidated `clients` table fields
- **Model logic**: Check if `contact_person` field is populated, fallback to contact persons table
- **Gradual migration**: Can migrate legacy clients later if needed

### Phase 4: Update Form Logic

**Controller Changes**:
- Remove `$contact` array separation
- Save all contact fields directly with client data
- Update validation to work with consolidated structure

### Expected Benefits:
- Simplified new client creation (single table operation)
- Better performance for new client queries
- Cleaner form handling
- No disruption to existing legacy data
- Future-proof architecture

### Files to be Created/Modified:
- **New**: `schema/sites_table_migration_01.sql`
- **Modified**: `app/Models/ClientsModel.php`
- **Modified**: `app/Controllers/ClientsController.php`
- **Preserved**: `app/Models/ClientContactPersonsModel.php` (for legacy support)