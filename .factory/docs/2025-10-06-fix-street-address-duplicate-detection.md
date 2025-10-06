# Fix Duplicate Checking for Street Addresses

## Problem Identified
The duplicate checking system is not finding matches when testing with "66 Klipper Street" even though it exists in the database as location_id 17.

## Root Cause Analysis
The current logic uses single parameter names that could conflict when the same search term applies to multiple conditions, causing SQL parameter binding issues.

## Solution Plan

### 1. Fix SQL Parameter Naming Conflicts
- The current logic uses `:town_like2` and `:suburb_like2` which might have the same values as `:town_like` and `:suburb_like`
- This could cause parameter binding conflicts in prepared statements
- Use unique parameter names for all conditions

### 2. Add Exact Match Logic for Street Addresses
- Current logic only uses LIKE for street addresses
- Add exact matching as primary and LIKE as fallback
- Improve handling of variations in street address formatting

### 3. Add Debugging/Logging
- Add temporary logging to capture the actual SQL query being executed
- Log the parameter values being passed to debug the issue
- This will help identify if the problem is in the query generation or execution

### 4. Strengthen Street Address Matching
- Add trimmed search text handling
- Handle multiple spaces or special characters
- Add case-insensitive exact matching as first priority

### 5. Test Database Connection
- Verify the DatabaseService is working correctly
- Test a simple query to confirm connectivity
- Ensure the table schema matches expectations

## Implementation Steps
1. Fix parameter naming in duplicate check query
2. Add exact match for street addresses
3. Add temporary debugging output
4. Test with "66 Klipper Street" to verify fix
5. Remove debugging after confirmation

## Expected Outcome
The duplicate checker should now correctly identify existing street addresses and return the "66 Klipper Street" record when searching for duplicates.