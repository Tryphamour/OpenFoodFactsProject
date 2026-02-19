# Reviewer Guide

This guide helps a reviewer validate the project quickly.

## 1) Quick Setup

```bash
make up
make install
make migrate
```

Access:

- App: `http://localhost`
- Login: `http://localhost/login`
- Mailhog: run `make mailhog-url` and open `http://<host:port>`

## 2) Suggested Manual Validation Flow

1. Login with:
   - email `admin@example.com`
   - password `Admin1234!`
2. Complete 2FA using the code received in Mailhog.
3. Open dashboard and verify widget board renders.
4. Add/configure/reorder widgets and refresh page to confirm persistence.
5. Verify product search widget returns data.
6. Verify `brand_search` and `nutriscore_a_search` widgets return relevant previews after configuration.
7. Temporarily degrade catalog gateway in test scenario and confirm graceful degraded rendering behavior.
8. As admin, open `/admin/users/new` and create a new account.

## 3) Internal API Contract Checks

Endpoint:

- `GET /internal/api/dashboard/{ownerId}`

Expected behavior:

- Authenticated and 2FA-verified session required.
- Access allowed for owner or `ROLE_ADMIN`.
- Error format for `/internal/api/*` is `application/problem+json`.

Expected success shape:

```json
{
  "data": {
    "ownerId": "string",
    "widgets": [
      {
        "id": "string",
        "type": "string",
        "position": 0,
        "configuration": {}
      }
    ]
  }
}
```

## 4) Automated Validation

```bash
make test
```

Targeted:

```bash
make test-file FILE=tests/Dashboard/UI/InternalDashboardApiTest.php
```

## 5) Architectural Review Pointers

- Bounded contexts are separated under `src/*`.
- Dependency direction and layer rules are documented in `docs/architecture.md`.
- Security decisions and constraints are documented in `docs/security.md`.
- Dashboard and OFF behavior contracts are in `docs/dashboard-spec.md` and `docs/openfoodfacts-integration.md`.

## 6) Known Tradeoffs

- Session-based internal API is intentionally used (no API Platform).
- Docker is the required runtime path for reproducibility.
- Scope intentionally excludes public registration and broader platform deployment concerns.
