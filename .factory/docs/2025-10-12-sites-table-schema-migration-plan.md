# Sites Table Migration Plan

## 1. Address Columns Rationalization

**Strategy**: Remove redundant `address`, `address_line_1`, `address_line_2` columns since all address data comes from `locations` via `place_id`. Any external consumers will be updated as needed since this is beta code.

## 2. Sub-site Creation Implementation

**Business Rules**: 
- Sub-sites can have different `place_id` values than their parent site (different physical locations allowed)
- Remove the hard-coded `parent_site_id = NULL` in `saveHeadSite()`
- Implement full hierarchy functionality

## 3. Implementation Plan

**Phase 1: Database Schema Changes**
- Create SQL migration file with all DDL changes
- Remove redundant address columns and indexes
- Add unique constraints for hierarchical site names
- Update views to include location data

**Phase 2: Application Code Updates**
- Modify `SitesModel` to support sub-site creation
- Remove address field handling from payload filtering and validation
- Add new methods for hierarchy management
- Update controllers and views

**Phase 3: Form Updates**
- Add site type selection (Head Site / Sub-site)
- Add parent site dropdown for sub-sites
- Update form validation

## 4. Files to Create/Modify

1. **New SQL file**: `schema/sites_table_migration.sql`
2. **Update**: `app/Models/SitesModel.php`
3. **Update**: `app/Controllers/ClientsController.php`
4. **Update**: Form views for site capture/update

The SQL file will contain all DDL statements for easy execution. Application code changes will implement the sub-site hierarchy functionality while removing address field dependencies.