# Review Checklist

Use this checklist before each major commit.

## DDD Integrity

- [ ] Domain contains business rules and invariants (no anemic model).
- [ ] No domain class depends on Symfony, Doctrine, HTTP, or template concerns.
- [ ] Application layer orchestrates use cases only and depends on ports.
- [ ] Infrastructure only implements ports and technical concerns.
- [ ] UI delegates to application use cases (no business logic in controllers/components).

## Security Integrity

- [ ] Authentication/authorization enforced via Symfony Security and voters/attributes where relevant.
- [ ] Lock/2FA/security decisions are traceable and test-covered.
- [ ] Sensitive flows handle brute-force and expiration constraints.

## Delivery Integrity

- [ ] Tests relevant to the change are present and passing.
- [ ] Diff is atomic and scoped to one concern.
- [ ] Architectural decisions introduced by the change are documented.
