# Architecture

This project uses DDD + layered architecture with explicit bounded contexts.

## Bounded Contexts

- `IdentityAccess`: authentication, account lock state, 2FA lifecycle.
- `Dashboard`: widget lifecycle and layout persistence rules.
- `FoodCatalog`: product query language and Open Food Facts access contract.
- `Audit`: traceability of security and operational events.
- `Shared`: cross-cutting contracts and technical primitives.

## Physical Structure

Each bounded context follows the same layer split:

- `Domain`: entities, value objects, domain services, domain events, invariants.
- `Application`: use cases, DTOs, ports (interfaces to infrastructure).
- `Infrastructure`: adapters (Doctrine, HTTP clients, mail, persistence).
- `UI`: controllers, forms, live components, templates integration.

Root namespace structure:

- `src/IdentityAccess/{Domain,Application,Infrastructure,UI}`
- `src/Dashboard/{Domain,Application,Infrastructure,UI}`
- `src/FoodCatalog/{Domain,Application,Infrastructure,UI}`
- `src/Audit/{Domain,Application,Infrastructure,UI}`
- `src/Shared/{Domain,Application,Infrastructure,UI}`

## Repository Layout Rationale

The repository keeps a single Symfony application at root (`src`, `config`, `public`) by design.

- The project is a Symfony full-stack monolith (Twig + Turbo + Live Components), not a separated SPA.
- Splitting into `backend/` and `frontend/` would add indirection without architectural benefit for current constraints.
- Frontend behavior remains in the Symfony app boundary and follows DDD/UI layering inside `src/*/UI`.

## Dependency Rules (Mandatory)

Allowed dependencies:

- `UI -> Application`
- `Application -> Domain`
- `Infrastructure -> Application + Domain`
- `Domain -> Domain (same context) + Shared\Domain`

Forbidden dependencies:

- `Domain -> Infrastructure`
- `Domain -> UI`
- `Application -> UI`
- Cross-context direct infrastructure coupling (use application ports/contracts)

## Symfony Container Policy

Service autowiring is intentionally limited to:

- `*/Application`
- `*/Infrastructure`
- `*/UI`

`Domain` classes are not auto-registered as container services by convention to keep domain purity and explicit composition.

## Design Constraints

- No anemic domain model: business rules and invariants are implemented in domain methods/value objects.
- No business logic in controllers, forms, or live components.
- External APIs (Open Food Facts) are accessed only through infrastructure adapters behind application ports.
- Security and authorization orchestration uses Symfony Security in UI/Application, with rules enforced by domain/application policies.
- External API adapters must expose explicit degraded outcomes (instead of throwing to UI) so dashboards/controllers can fail gracefully.

## Testing Policy

- TDD is mandatory for domain logic.
- Domain tests verify invariants and business behavior.
- Integration tests verify adapters and persistence contracts.
- Functional tests verify end-to-end use-case behavior through HTTP/UI boundaries.
