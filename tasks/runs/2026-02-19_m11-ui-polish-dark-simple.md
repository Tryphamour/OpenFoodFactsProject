# Run: M11 UI Polish (Simple Dark Theme)

## Context
The project is functionally complete but visually too raw. The goal is to improve perceived quality with a simple, clean dark theme while preserving existing behavior and without adding any UI libraries.

## Plan
1. Introduce a shared visual system (colors, spacing, typography, components) in base Twig.
2. Restyle authentication, dashboard, admin, and widget UI using semantic utility classes.
3. Keep all form actions/routes/DOM behavior intact (no business logic change).
4. Validate with targeted functional tests.
5. Update task tracking and run notes.

## Risks
- Breaking selectors used in functional tests.
- Reducing readability/contrast in dark mode.
- Accidentally touching behavior while editing templates.

## Implementation Steps
1. Create run file and mark task active. Done.
2. Add global CSS tokens and reusable classes in `base.html.twig`. Done.
3. Refactor page templates to use the shared classes. Done.
4. Refactor dashboard widget component markup styles only. Done.
5. Run targeted tests and finalize documentation tracking. Done.

## Verification
- `make test-file FILE=tests/Dashboard/UI/DashboardFlowTest.php` -> OK (5 tests, 51 assertions)
- `make test-file FILE=tests/IdentityAccess/UI/AdminUserCreationFlowTest.php` -> OK (2 tests, 18 assertions)
- Manual check target: login, 2FA, dashboard, admin user creation render with coherent dark UI.

## Review Notes
M11 completed as a pure presentation pass:
- no new dependencies
- no behavior change
- consistent dark visual language across all pages.
