## Plan to Remove Legacy Google Places API Support

### Analysis
The `initializeLegacyAutocomplete()` function exists as a fallback for older Google Maps API versions. Since you want to use only the latest API, I'll remove all legacy-related code.

### Changes Required
1. **Remove `initializeLegacyAutocomplete()` function** (lines ~117-147)
2. **Update `initializeAutocomplete()` function** to remove all fallback logic:
   - Remove the `else` block that calls legacy fallback
   - Remove the `.catch()` block that falls back to legacy
   - Remove the try-catch that falls back to legacy on error
   - Simplify to only use `PlaceAutocompleteElement`
3. **Remove legacy-specific error handling** - replace with appropriate error handling that doesn't fall back
4. **Update the `waitForGoogleMaps()` function** to check for the modern API specifically

### Benefits
- Cleaner, more maintainable code
- Modern API features and better performance
- Reduced bundle size and complexity
- Forces use of latest Google Places API capabilities