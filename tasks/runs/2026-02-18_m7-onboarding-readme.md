# Run: M7 Onboarding README Completion

## Context
Provide a complete onboarding entry point for reviewers and new contributors who do not know the project.

## Plan
1. Define README audience and navigation structure.
2. Document install/run/test workflows with Docker-first commands.
3. Add troubleshooting, architecture summary, and operational references.
4. Validate clarity by executing documented commands.

## Risks
- Inconsistent commands vs actual Docker setup.
- README too dense without clear quickstart path.
- Missing edge cases for first-time setup failures.

## Implementation Steps
1. Create/expand `README.md` with: ✅
   - Project purpose and scope
   - Prerequisites
   - Quickstart (`docker compose up -d --build`)
   - Access points (app, Mailhog)
   - Test commands (including `APP_ENV=test`)
2. Add architecture and bounded-context summary with links to docs. ✅
3. Add internal API overview and security behavior references. ✅
4. Add troubleshooting section (Docker daemon, container health, test env mismatch). ✅
5. Add contribution flow section (branching, commit conventions, validation checklist). ✅
6. Add a `Makefile` command surface and document it as primary onboarding path. ✅

## Verification
- Execute every command from README in a fresh shell context.
- Ensure links and file references resolve.
- Confirm no contradiction with `compose.yaml` and `tasks/lessons.md`.

Executed commands:
- `make up` (equivalent validated through `docker compose up -d --build`) ✅
- `make install` (equivalent validated through container `composer install`) ✅
- `make migrate` (equivalent validated through container Doctrine migrate) ✅
- `make mailhog-url` ✅
- `make ps` ✅
- `make about` ✅
- `make test` ✅
  - Result: `OK (34 tests, 139 assertions)`
- `make test-file FILE=tests/Dashboard/UI/InternalDashboardApiTest.php` ✅
  - Result: `OK (3 tests, 29 assertions)`

Note:
- `docker compose down -v` is intentionally documented as destructive and was not executed during validation.

## Acceptance Criteria
- A newcomer can run, test, and understand the project without prior context.
- README reflects current runtime and milestone state.
- Setup and test commands are copy-paste reliable.

## Review Notes
M7 accepted. Root `README.md` now provides complete newcomer onboarding aligned with current Docker runtime and test workflow.
