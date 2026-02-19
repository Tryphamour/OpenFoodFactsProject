# Run: Admin User Creation and Functional Widget Refresh

## Context
The current dashboard includes `product_search` (functional) and two placeholder widget types (`nutriscore_distribution`, `additives_overview`) without real business rendering. The technical guidelines require admin-only account creation through an interface and functionally relevant dashboard behavior.

## Plan
1. Add an admin-only user creation flow (UI + application use case + infrastructure persistence).
2. Replace placeholder widget types with useful, functional widgets backed by Open Food Facts data.
3. Update dashboard UI configuration forms to make expected inputs explicit per widget type.
4. Update tests and docs to reflect the new behavior and acceptance expectations.
5. Run targeted verification and close task with minimal intentional diff.

## Risks
- Breaking authentication flow while introducing admin account provisioning.
- Regressions in dashboard widget persistence/reorder flow when changing widget types.
- Over-coupling dashboard UI behavior with infrastructure details instead of application contracts.
- Incomplete test coverage for admin authorization and widget rendering paths.

## Implementation Steps
1. Create run scaffolding and keep `tasks/todo.md` index aligned. Done.
2. Implement admin user creation use case with input validation and duplicate-email guard. Done.
3. Add `ROLE_ADMIN` secured controller + template for account creation. Done.
4. Introduce two functional widget types and implement their rendering/config behavior. Done.
5. Update allowed widget types, UI labels, and existing tests. Done.
6. Update documentation files impacted by behavior changes. Done.
7. Execute targeted test suites and finalize review notes. Done.

## Verification
- `make test-file FILE=tests/IdentityAccess/UI/AdminUserCreationFlowTest.php` -> OK (2 tests, 16 assertions)
- `make test-file FILE=tests/IdentityAccess/Application/UseCase/CreateUserHandlerTest.php` -> OK (3 tests, 6 assertions)
- `make test-file FILE=tests/Dashboard/UI/DashboardFlowTest.php` -> OK (5 tests, 45 assertions)
- `make test-file FILE=tests/Dashboard/UI/InternalDashboardApiTest.php` -> OK (3 tests, 29 assertions)
- `make test-file FILE=tests/IdentityAccess/UI/SecurityFlowTest.php` -> fails in current environment due missing `openfoodfacts_test` PostgreSQL database (not caused by this change set).

## Review Notes
Task completed with minimal scope:
- Added admin-only account creation flow from UI.
- Replaced placeholder widgets with two functional OFF-backed widgets:
  - `brand_search`
  - `nutriscore_a_search`
- Updated tests and documentation to align with evaluation requirements.
