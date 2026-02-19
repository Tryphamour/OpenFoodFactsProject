# Run: M10-bis Dashboard UI Visibility and Demo Cache Consistency

## Context
User reports stale dashboard widget actions still showing legacy labels and unclear admin user-creation access from dashboard.
The codebase already contains functional widgets and admin route, so this task focuses on visibility and runtime consistency in demo mode.

## Plan
1. Add an explicit admin access block on dashboard so navigation is discoverable.
2. Ensure demo startup refreshes prod cache deterministically to avoid stale UI templates.
3. Add/update verification focused on rendered labels and admin access visibility.
4. Update task tracking and lessons.

## Risks
- Accidentally exposing admin-only actions to non-admin users.
- Introducing slower demo startup with additional cache operations.
- Regression in dashboard template expectations in functional tests.

## Implementation Steps
1. Create run file and mark task active. Done.
2. Update dashboard template for explicit admin access CTA and role-aware messaging. Done.
3. Update Makefile demo startup to clear prod cache before warmup. Done.
4. Add/adjust functional test assertions on UI labels and admin entrypoint visibility. Done.
5. Execute targeted tests and finalize docs/tasks. Done.

## Verification
- `make test-file FILE=tests/Dashboard/UI/DashboardFlowTest.php` -> OK (5 tests, 51 assertions)
- `make test-file FILE=tests/IdentityAccess/UI/AdminUserCreationFlowTest.php` -> OK (2 tests, 18 assertions)
- `make demo-up` executed with prod cache clear + warmup.
- `make ps` -> `frankenphp`, `database`, `mailhog` are up; app container healthy.

## Review Notes
The stale-UI symptom was caused by cache behavior, not missing code in source files. This milestone enforces deterministic cache refresh in demo mode and adds explicit UI discoverability for the admin creation page.
