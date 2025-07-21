## Start Design Guide

# Bootstrap 5 Design Guide

## Color Scheme & Typography

### Text Colors
- **Primary Text**: `text-body` - Main content text
- **Secondary Text**: `text-body-secondary`, `text-muted` - Less important information
- **Tertiary Text**: `text-body-tertiary` - Least important text
- **Contextual**: `text-primary`, `text-success`, `text-danger`, `text-warning`, `text-info`

### Typography Examples
```html
<h4 class="text-body mb-0">Main Heading</h4>
<h6 class="text-body-tertiary">Subheading with tertiary color</h6>
<p class="text-muted">Secondary information text</p>
<span class="text-danger">* Required field</span>
```

## Phoenix Custom Classes

### Badge Phoenix Variants
Phoenix badges provide subtle, modern styling with contextual colors.

```html
<!-- Primary Badge -->
<span class="badge badge-phoenix fs-10 badge-phoenix-primary">Active</span>

<!-- Success Badge -->
<span class="badge badge-phoenix fs-10 badge-phoenix-success">+ 11</span>

<!-- Warning Badge -->
<span class="badge badge-phoenix fs-10 badge-phoenix-warning">Pending</span>

<!-- Danger Badge -->
<span class="badge badge-phoenix fs-10 badge-phoenix-danger">- 2</span>

<!-- Info Badge -->
<span class="badge badge-phoenix fs-10 badge-phoenix-info">3 Backups</span>

<!-- Secondary Badge -->
<span class="badge badge-phoenix fs-10 badge-phoenix-secondary">Not SETA</span>
```

### Button Phoenix Variants
Phoenix buttons for primary actions with subtle hover effects.

```html
<!-- Primary Action Button -->
<button class="btn btn-phoenix-primary">Save Changes</button>

<!-- Secondary Action Button -->
<button class="btn btn-phoenix-secondary px-3 px-sm-5">Edit Class</button>

<!-- Danger Action Button -->
<button class="btn btn-phoenix-danger">Delete Class</button>
```

## Bootstrap 5 Components

### Tables
Responsive tables with hover effects and proper spacing.

```html
<div class="table-responsive">
    <table class="table table-hover table-sm fs-9 mb-0">
        <thead class="border-bottom">
            <tr>
                <th class="sort border-end" data-sort="class_id">ID</th>
                <th class="sort" data-sort="client_name">Client</th>
                <th class="sort" data-sort="class_type">Type</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td class="border-end">001</td>
                <td>Client Name</td>
                <td>
                    <span class="badge badge-phoenix fs-10 badge-phoenix-primary">
                        Skills Programme
                    </span>
                </td>
                <td>
                    <button class="btn btn-sm btn-phoenix-secondary">Edit</button>
                </td>
            </tr>
        </tbody>
    </table>
</div>
```

### Cards
Content containers with shadow and border options.

```html
<!-- Basic Card -->
<div class="card shadow-none border my-3">
    <div class="card-header p-3 border-bottom">
        <h4 class="text-body mb-0">Card Title</h4>
    </div>
    <div class="card-body p-4">
        <p>Card content goes here</p>
    </div>
</div>

<!-- Card with Sections -->
<div class="card">
    <div class="card-body">
        <div class="border-bottom mb-3 pb-3">
            <h5 class="card-title">Section Title</h5>
            <p class="text-muted">Section content</p>
        </div>
        <div>
            <span class="badge badge-phoenix badge-phoenix-info">Status</span>
        </div>
    </div>
</div>
```

### Alerts
Contextual alert messages with subtle styling.

```html
<!-- Info Alert -->
<div class="alert alert-info d-flex align-items-center">
    <i class="bi bi-info-circle-fill me-3 fs-4"></i>
    <div>
        <h6 class="alert-heading mb-1">No Classes Found</h6>
        <p class="mb-0">There are currently no classes in the database.</p>
    </div>
</div>

<!-- Success Alert (Dismissible) -->
<div class="alert alert-subtle-success alert-dismissible fade show">
    Class created successfully
    <i class="bi bi-check-circle-fill ms-2"></i>
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>

<!-- Warning Alert -->
<div class="alert alert-subtle-warning">
    <strong>Warning:</strong> Please review before submitting.
</div>

<!-- Danger Alert -->
<div class="alert alert-subtle-danger">
    <strong>Error:</strong> Required fields are missing.
</div>
```

### Forms
Form components with proper labeling and validation states.

```html
<!-- Basic Form Layout -->
<form>
    <div class="row g-3">
        <!-- Select Field -->
        <div class="col-md-3">
            <label for="client_id" class="form-label">
                Client Name <span class="text-danger">*</span>
            </label>
            <select id="client_id" class="form-select" required>
                <option value="">Select</option>
                <option value="1">Client One</option>
            </select>
        </div>
        
        <!-- Text Input -->
        <div class="col-md-6">
            <label for="address" class="form-label">Address</label>
            <input type="text" id="address" class="form-control" 
                   placeholder="Street, Suburb, Town, Postal Code">
        </div>
        
        <!-- Textarea -->
        <div class="col-12">
            <label for="notes" class="form-label">Notes</label>
            <textarea id="notes" class="form-control" rows="3"></textarea>
        </div>
        
        <!-- Checkbox -->
        <div class="col-md-4">
            <div class="form-check">
                <input class="form-check-input" type="checkbox" id="exam_class">
                <label class="form-check-label" for="exam_class">
                    Exam Class
                </label>
            </div>
        </div>
    </div>
</form>
```

### Buttons
Various button styles and sizes.

```html
<!-- Primary Buttons -->
<button class="btn btn-primary">Primary Action</button>
<button class="btn btn-primary btn-sm">Small Button</button>

<!-- Outline Buttons -->
<button class="btn btn-outline-primary btn-sm">Outline Primary</button>
<button class="btn btn-outline-secondary btn-sm">Outline Secondary</button>

<!-- Subtle Buttons -->
<button class="btn btn-subtle-primary">Subtle Primary</button>
<button class="btn btn-subtle-danger">Subtle Danger</button>

<!-- Link Button -->
<button class="btn btn-link text-body btn-sm">Link Button</button>

<!-- Button with Icon -->
<button class="btn btn-primary">
    <i class="bi bi-plus-lg me-1"></i> Add New
</button>
```

### Navigation & Dropdowns

```html
<!-- Dropdown Menu -->
<div class="dropdown">
    <button class="btn btn-link text-body btn-sm dropdown-toggle" 
            data-bs-toggle="dropdown">
        Actions
    </button>
    <ul class="dropdown-menu">
        <li><a class="dropdown-item" href="#">View</a></li>
        <li><a class="dropdown-item" href="#">Edit</a></li>
        <li><hr class="dropdown-divider"></li>
        <li><a class="dropdown-item text-danger" href="#">Delete</a></li>
    </ul>
</div>

<!-- Pills Navigation -->
<ul class="nav nav-pills mb-3">
    <li class="nav-item">
        <a class="nav-link active" href="#details">Details</a>
    </li>
    <li class="nav-item">
        <a class="nav-link" href="#notes">Notes</a>
    </li>
</ul>
```

### Search Box
Standard search input with icon.

```html
<div class="search-box">
    <form class="position-relative">
        <input class="form-control search-input search form-control-sm" 
               type="search" placeholder="Search" aria-label="Search">
        <svg class="svg-inline--fa fa-magnifying-glass search-box-icon">
            <!-- Icon SVG content -->
        </svg>
    </form>
</div>
```

### Status Indicators
Various ways to show status information.

```html
<!-- Status with Icon -->
<span class="badge badge-phoenix fs-10 badge-phoenix-success">
    Active
    <svg class="svg-inline--fa fa-check ms-1" data-fa-transform="shrink-2">
        <!-- Check icon -->
    </svg>
</span>

<!-- Status Badge Group -->
<div class="d-flex gap-2">
    <span class="badge badge-phoenix badge-phoenix-warning">Pending</span>
    <span class="badge badge-phoenix badge-phoenix-success">3 Learners</span>
    <span class="badge badge-phoenix badge-phoenix-info">2 Backups</span>
</div>
```

## Spacing & Layout

### Common Spacing Patterns
- **Card Padding**: `p-3` for headers, `p-4` for body
- **Section Spacing**: `mb-3`, `mt-3` for vertical spacing
- **Inline Spacing**: `me-2`, `ms-2` for horizontal gaps
- **Border Spacing**: `pb-3` with `border-bottom` for section dividers

### Responsive Grid
```html
<div class="row g-3">
    <div class="col-12 col-md-6 col-lg-3">
        <!-- Content adapts to screen size -->
    </div>
</div>
```

### Utility Classes
- **Display**: `d-none`, `d-flex`, `d-inline-block`
- **Alignment**: `align-items-center`, `justify-content-between`
- **Text**: `text-center`, `text-end`, `text-truncate`
- **Borders**: `border`, `border-bottom`, `border-end`
- **Shadows**: `shadow-sm`, `shadow-none`

## Best Practices

1. **Consistency**: Use phoenix badges for status indicators, subtle alerts for notifications
2. **Hierarchy**: Use `text-body` for main content, `text-muted` for secondary
3. **Spacing**: Maintain consistent spacing with Bootstrap utility classes
4. **Responsive**: Always wrap tables in `.table-responsive`
5. **Forms**: Mark required fields with `<span class="text-danger">*</span>`
6. **Icons**: Use Bootstrap Icons (bi-*) for consistency
7. **Font Sizes**: Use `fs-9` for tables, `fs-10` for badges


## Badge
badge-phoenix
badge-phoenix-warning
badge-phoenix-success
badge-phoenix-secondary
btn-phoenix-secondary
btn-phoenix-primary

## Alerts
alert-subtle-primary
alert-subtle-success
alert-subtle-danger
alert-subtle-warning
alert-subtle-info

## Buttons
btn-subtle-primary
btn-subtle-secondary
btn-subtle-success
btn-subtle-danger
btn-subtle-warning
btn-subtle-info