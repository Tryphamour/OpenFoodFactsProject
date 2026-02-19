# Run: M12 CSS Extraction and Template Cleanup

## Context
M11 introduced a better dark UI but styles were embedded directly in Twig templates. The objective now is to align with maintainability best practices by moving styles to dedicated CSS assets.

## Plan
1. Move global and component styles from Twig into `assets/styles/app.css`.
2. Replace inline style attributes in templates with reusable CSS classes.
3. Keep behavior/routes untouched.
4. Validate with targeted functional tests.
5. Update run tracking and lessons if needed.

## Risks
- Breaking layout or readability while migrating style declarations.
- Accidentally changing test-sensitive content/structure.
- Leaving inconsistent class usage across templates.

## Implementation Steps
1. Create run file and mark task active. Done.
2. Refactor `base.html.twig` to remove embedded `<style>` block. Done.
3. Expand `assets/styles/app.css` with dark theme design system + component classes. Done.
4. Refactor all affected templates to class-based markup. Done.
5. Execute targeted tests and finalize tracking. Done.

## Verification
- `make test-file FILE=tests/Dashboard/UI/DashboardFlowTest.php` -> OK (5 tests, 51 assertions)
- `make test-file FILE=tests/IdentityAccess/UI/AdminUserCreationFlowTest.php` -> OK (2 tests, 18 assertions)
- No inline `style=""` attributes remain in Twig templates (`templates/`).
- Manual visual pass in demo mode pending user check.

## Review Notes
Styles are now centralized in `assets/styles/app.css`, imported by the existing AssetMapper entrypoint. Template markup was simplified to semantic utility classes only.
