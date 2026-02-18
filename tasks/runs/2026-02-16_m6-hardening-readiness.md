# Run: M6 Hardening and Delivery Readiness

## Context
Finalize production-grade quality: test reliability, Docker reproducibility, documentation completeness, and architectural review readiness.

## Plan
1. Consolidate test coverage across domain, integration, and functional layers.
2. Validate Docker-first developer and CI workflow.
3. Close documentation gaps and record tradeoffs.
4. Perform architecture and security checklist review.

## Risks
- Hidden regressions from cross-cutting refactors.
- Container drift between local and CI environments.
- Documentation lag behind implemented behavior.

## Implementation Steps
1. Run full test suite and address failures/flakes. ✅
2. Validate `docker compose up` startup path end-to-end. ✅
3. Verify Mailhog and PostgreSQL integration in containerized flow. ✅
4. Update docs (`architecture.md`, `security.md`, `dashboard-spec.md`) with final decisions. ✅
5. Execute staff-level readiness checklist and fix findings. ✅

## Hardening Work Performed

- Reintroduced versioned application runtime in Docker:
  - `compose.yaml` now defines `frankenphp`, `database`, `mailhog`
  - `docker/FrankenPHP/Dockerfile` added to build `off-frankenphp`
  - `docker/FrankenPHP/Caddyfile` added for explicit FrankenPHP serving config
  - `docker/FrankenPHP/conf.d/symfony.dev.ini` fixed (directory replaced by proper `.ini` file)
- Updated developer defaults:
  - `.env.example` now matches compose service names (`database`, `mailhog`)
- Removed compose drift:
  - `docker compose up -d --remove-orphans` used to clear stale `mailpit`/legacy containers
- Stabilized container health:
  - reverted to FrankenPHP native health behavior (admin endpoint enabled) to avoid Symfony boot-time false negatives in health checks

## Verification
- Full test suite green in Docker test environment.
- Manual smoke checks of authentication, dashboard, and OFF-dependent widgets.
- Final docs audit confirms architecture/security alignment with code.

Executed checks:
- `docker compose up -d --build` ✅
- `docker compose ps` => `frankenphp`, `database`, `mailhog` up; `frankenphp` healthy ✅
- `docker compose exec -T frankenphp php bin/console about` ✅
- `docker compose exec -T -e APP_ENV=test frankenphp php vendor/bin/simple-phpunit` ✅
  - Result: `OK (34 tests, 139 assertions)`

## Acceptance Criteria
- Project runs with Docker-only setup and passes tests.
- Key flows are stable under expected and failure conditions.
- Documentation is review-ready and reflects final architecture.

## Review Notes
M6 accepted. Delivery readiness checks are green with Docker-only runtime and full containerized test execution.
