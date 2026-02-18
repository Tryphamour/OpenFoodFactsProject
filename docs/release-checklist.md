# Release Checklist

Use this checklist before sharing the project for final review.

## Runtime Readiness

- [ ] `make up` succeeds on a clean environment.
- [ ] `make ps` shows healthy `frankenphp` and `database`.
- [ ] Login page is reachable at `http://localhost/login`.
- [ ] Mailhog UI is reachable using `make mailhog-url`.

## Database and Schema

- [ ] `make migrate` succeeds with no pending blocking issue.
- [ ] Seeded admin user is available (`admin@example.com`).

## Test Evidence

- [ ] `make test` passes.
- [ ] Targeted internal API test passes:
  - `make test-file FILE=tests/Dashboard/UI/InternalDashboardApiTest.php`

## Security and Behavior Checks

- [ ] Login + 2FA flow works end-to-end.
- [ ] Account lock behavior after repeated failures is functional.
- [ ] Dashboard remains usable when OFF gateway is degraded.
- [ ] Internal API access control is enforced (owner/admin policy).
- [ ] Problem-details format is returned on internal API errors.

## Documentation Readiness

- [ ] `README.md` onboarding is aligned with actual commands.
- [ ] `docs/architecture.md`, `docs/security.md`, `docs/dashboard-spec.md` reflect implemented behavior.
- [ ] `docs/roadmap.md` status is aligned with `tasks/todo.md`.
- [ ] `docs/reviewer-guide.md` is up to date.

## Handoff Notes

- [ ] Known tradeoffs and out-of-scope items are explicit in docs.
- [ ] No contradictory instructions remain across docs.
