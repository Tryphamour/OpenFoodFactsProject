# Roadmap

This file reflects the current execution plan and status of the project.

## Delivery Status

Completed milestones:

- M1 Architecture Baseline
- M2 Security Core
- M3 Dashboard Core
- M4 Open Food Facts Integration
- M5 Internal Secured API
- M6 Hardening and Delivery Readiness
- M7 Onboarding README Completion

Planned milestones:

- M8 Documentation Alignment and Review Pack (in progress)
- M9 Developer Experience Command Surface (backlog)

## What Is Already Delivered

- Docker-first reproducible runtime (`frankenphp`, `postgres`, `mailhog`)
- Secure authentication flow (password + email 2FA + lock policy)
- Dashboard aggregate with widget lifecycle and persisted layout
- Open Food Facts integration with degraded fallback behavior
- Internal secured API endpoint for dashboard read access
- Full containerized PHPUnit suite passing

## Scope Boundaries

In scope:

- Symfony full-stack monolith with DDD boundaries
- Turbo + Live Components UI interactions
- Internal API without API Platform
- Staff-level documentation and review readiness

Out of scope for current delivery:

- Public user registration
- External public API productization
- Multi-node deployment/IaC
- Frontend SPA migration (React/Vue)

## Validation Baseline

- Runtime bootstrap: `make up`, `make about`
- Database schema: `make migrate`
- Full tests: `make test`
- Primary documentation entry point: `README.md`

## Next Focus

- Finish M8 reviewer/release documentation alignment.
- Execute M9 command-surface refinements only if additional DX gaps remain after review feedback.
