## Fix Main Client Select Field Array Conversion Error

### Problem Identified:
The debug log shows array conversion warnings are still occurring despite client data filtering. The issue is in the main client dropdown:

```php
['options' => ['' => 'Select Main Client'] + $main_clients, ...]
```

The `$main_clients` is an array of client records (arrays), but select options expect a simple `value => label` format.

### Root Cause:
- `$main_clients` returns: `[0 => ['id' => 1, 'client_name' => 'Client A'], 1 => ['id' => 2, 'client_name' => 'Client B']]`
- Select field expects: `[1 => 'Client A', 2 => 'Client B']`
- Current code is passing arrays as option labels, causing "Array to string conversion"

### Solution Strategy:

**Option 1: Format main_clients correctly in controller** (Recommended)
- Transform `$main_clients` array to proper `id => name` format
- Pass correctly formatted array to view

**Option 2: Handle formatting in view** 
- Transform the array in the view before passing to ViewHelpers
- Less clean but contained to view layer

**Option 3: Update ViewHelpers to handle nested arrays**
- Complex solution that affects all select fields
- Overkill for this specific issue

### Implementation Plan:
1. **Update controller**: Transform `$main_clients` to proper format before passing to view
2. **Format as `id => client_name`**: `[1 => 'Client A', 2 => 'Client B']`
3. **Maintain existing validation**: Keep same filtering and error handling
4. **Test the fix**: Ensure no more array conversion warnings

### Code Changes:
```php
// In updateClientShortcode(), after getting main clients:
$main_clients_options = ['' => 'Select Main Client'];
foreach ($main_clients as $client) {
    $main_clients_options[$client['id']] = $client['client_name'];
}
```

This will resolve the array conversion error in the main client select field.