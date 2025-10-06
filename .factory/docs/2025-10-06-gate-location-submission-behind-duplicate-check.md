## Summary
Hide the location form’s submit button until the user runs a duplicate check and receives a response, ensuring data is reviewed before submission.

## Implementation Steps
1. **Initial button state**
   - Assign an `id` to the submit button in `location-capture-form.view.php` and add a `d-none` class so it starts hidden; optionally show a short hint near the duplicate result container prompting the user to run the duplicate check.
2. **Front-end logic updates**
   - In the existing duplicate-check script, cache the submit button element. When the duplicate check succeeds (regardless of duplicates found), remove the `d-none` class so the submit button becomes visible. On error responses or fetch failures, keep it hidden and show the error message.
3. **Re-hide on form edits/reset**
   - Add listeners to relevant input fields (street, suburb, town, reset button) to re-hide the submit button whenever data changes after a check, ensuring each change requires a fresh duplicate verification.

## Testing Plan
- Load the capture form and confirm the submit button is hidden by default with an informative prompt.
- Run the duplicate check with a valid address and observe the submit button appearing after the response.
- Trigger an error (e.g., missing fields) and confirm the submit button stays hidden.
- Modify a field after a successful check and ensure the button hides again until the user reruns the duplicate check.
- Use the reset button to confirm the submit button hides and the form prompts for another duplicate check.