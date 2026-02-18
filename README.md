# Open Food Facts Dashboard (Symfony 8)

Production-oriented Symfony 8 technical project using DDD boundaries, secure authentication (2FA), a customizable dashboard, and Open Food Facts integration.

## What This Project Does

- Authenticated dashboard with per-user widget layout.
- Product search widgets backed by Open Food Facts.
- Graceful degraded mode when Open Food Facts is unavailable.
- Internal secured API for dashboard data access.
- Docker-first runtime (`frankenphp`, `postgres`, `mailhog`).

## Tech Stack

- PHP 8.4 / Symfony 8
- FrankenPHP
- PostgreSQL 16
- Symfony UX Live Components + Turbo + SortableJS
- Docker Compose

## Prerequisites

- Docker Desktop (or Docker Engine + Compose plugin)
- Git

No local PHP installation is required for normal use.

## Recommended Command Interface

Use the provided `Makefile` for day-to-day actions:

- `make up`
- `make install`
- `make migrate`
- `make test`
- `make ps`
- `make about`
- `make mailhog-url`

Equivalent raw `docker compose ...` commands remain valid and are shown where useful.

## Quickstart (First Run)

1. Clone and enter the repository.
2. Start the stack:

```bash
make up
```

3. Install dependencies inside app container (safe to rerun):

```bash
make install
```

4. Run migrations:

```bash
make migrate
```

5. Open the app:
   - App: `http://localhost`
   - Login page: `http://localhost/login`

6. Open Mailhog UI:

```bash
make mailhog-url
```

Then open `http://<returned-host:port>` in your browser.

## Default Credentials

Initial admin user is seeded by migration:

- Email: `admin@example.com`
- Password: `Admin1234!`

2FA code is sent by email to Mailhog (dev runtime).

## Run Tests

Always force test environment in containerized PHPUnit:

```bash
make test
```

Run a specific test class:

```bash
make test-file FILE=tests/Dashboard/UI/InternalDashboardApiTest.php
```

## Useful Runtime Commands

Start or rebuild:

```bash
make up
```

Stop:

```bash
make down
```

Stop and remove volumes (destructive for DB data):

```bash
make down-v
```

Show status:

```bash
make ps
```

Symfony diagnostics:

```bash
make about
```

Stream app logs:

```bash
make logs
```

## Internal API (Implemented)

- Endpoint: `GET /internal/api/dashboard/{ownerId}`
- Security:
  - Authenticated + 2FA-verified session required (`ROLE_USER`)
  - Owner-only access, except `ROLE_ADMIN` can access any dashboard
- Error contract on `/internal/api/*`:
  - `application/problem+json`
  - fields: `type`, `title`, `status`, `detail`, `instance`, `traceId`

## Project Structure (High-Level)

- `src/IdentityAccess`: auth, account lock, 2FA lifecycle
- `src/Dashboard`: dashboard aggregate, widgets, layout persistence
- `src/FoodCatalog`: Open Food Facts query contract + adapter
- `src/Audit`: audit contracts/events
- `src/Shared`: shared primitives
- `docs/`: architecture, security, dashboard, OFF integration docs
- `tasks/runs/`: milestone execution traces

## Key Documentation

- `docs/project.md`
- `docs/architecture.md`
- `docs/security.md`
- `docs/dashboard-spec.md`
- `docs/openfoodfacts-integration.md`
- `tasks/todo.md`

## Troubleshooting

Docker daemon not reachable:

- Start Docker Desktop and retry.

`frankenphp` is unhealthy:

```bash
docker compose logs --tail=200 frankenphp
docker compose up -d --build
```

`WebTestCase` error about `test.service_container`:

- Run PHPUnit with `APP_ENV=test` (see test command above).

Database issues after schema changes:

```bash
docker compose down -v
docker compose up -d --build
docker compose exec -T frankenphp php bin/console doctrine:migrations:migrate --no-interaction
```

## Contribution Notes

- Conventional commits are required (`feat:`, `fix:`, `test:`, `docs:`, `chore:`, `refactor:`).
- Keep commits atomic.
- Validate behavior before completion (targeted tests at minimum).
- Respect DDD boundaries (no business logic in controllers/forms/live components).
