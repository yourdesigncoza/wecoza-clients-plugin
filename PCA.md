# Project Context Analysis

**Importent:** 
This is a project analysis for the "Classes" plugin, we will use mostly the same architecture to develop the "Clients" CRUD plugin & shortcodes

**Project**: WeCoza Classes Plugin
**Type**: WordPress Plugin
**Purpose**: Class Management System for Training Programs

---

## Architecture Overview

**Style**: MVC (Model-View-Controller) Architecture
**Database**: PostgreSQL (external) + WordPress MySQL
**Frontend**: Bootstrap 5 + JavaScript
**Backend**: PHP 7.4+ with WordPress 5.0+

## Technology Stack

**Backend:**
- PHP 7.4+ with WordPress integration
- PostgreSQL database for class data
- PDO for database connectivity
- Custom MVC framework

**Frontend:**
- Bootstrap 5 responsive design
- FullCalendar integration
- jQuery for DOM manipulation
- Real-time search & pagination

**Key Libraries:**
- FullCalendar for calendar functionality
- Bootstrap 5 for responsive UI
- Custom JavaScript modules for form handling

---

## Core Components

```yaml
Controllers:
  - ClassController: Class CRUD operations, shortcodes, AJAX handlers
  - ClassTypesController: Class type management and subject selection
  - PublicHolidaysController: Holiday integration and conflict detection

Models:
  - ClassModel: Class data management with PostgreSQL integration
  - DatabaseService: PDO wrapper for PostgreSQL operations

Views:
  - class-capture-form: Class creation/editing interface
  - classes-display: Table view with search/pagination
  - single-class-display: Detailed class information

Services:
  - DatabaseService: PostgreSQL connection management
  - ViewHelpers: Template utility functions
```

---

## Database Schema

**Primary Table**: `classes` (PostgreSQL)
- Standard relational fields (id, dates, text fields)
- JSONB fields for flexible data:
  - `learner_ids`: Student assignments
  - `schedule_data`: Class scheduling
  - `qa_reports`: Quality assurance data
  - `exam_learners`: Exam participant data

**Foreign Keys**: Links to clients, agents, sites, users tables

---

## Key Features

**Class Management:**
- Create/edit/delete training classes
- SETA funding tracking
- Exam class designation
- Agent assignment with backup support

**Scheduling System:**
- Per-day time management
- Public holiday integration
- Conflict detection and override capabilities
- Calendar visualization

**Learner Management:**
- Auto-population based on class subjects
- Status tracking (CIC, RBE, DRO)
- Exam learner selection
- Level assignment (50+ types)

**Search & Display:**
- Real-time client-side search
- Pagination (5 items per page)
- Responsive table design
- Loading indicators

---

## WordPress Integration

**Shortcodes:**
- `[wecoza_capture_class]`: Class creation form
- `[wecoza_display_classes]`: Classes table view
- `[wecoza_display_single_class]`: Individual class details

**AJAX Endpoints:**
- Class CRUD operations
- Calendar event fetching
- Subject selection
- Holiday data retrieval

**Assets Management:**
- Conditional script loading
- Version control for cache busting
- Development/production modes

---

## Configuration System

**Config File**: `config/app.php`
- Database connection settings
- Controller registration
- AJAX endpoint mapping
- Asset management
- Validation rules
- Capabilities management

**Environment Settings:**
- PostgreSQL credentials via WordPress options
- Debug mode integration
- Cache configuration
- File upload settings

---

## Quality Metrics

**Code Quality**: High
- Follows WordPress coding standards
- Proper namespace usage
- Security best practices (nonce verification)
- Error handling and logging

**Architecture**: Well-structured
- Clean MVC separation
- Reusable components
- Modular design
- Comprehensive documentation

**Security**: Adequate
- Input sanitization
- CSRF protection
- Database prepared statements
- Access control integration

---

## Documentation

**Developer Guide**: `CLAUDE.md` (comprehensive)
**User Manual**: `README.md`
**Schema**: `schema/classes_schema.sql`
**Daily Reports**: `daily-updates/`

**Current Status**: Production-ready with ongoing enhancements for search, pagination, and learner management features.