# Run: M2 Security Core

## Context
Implement robust authentication with password + email 2FA, account lock policy, and auditable security events using native Symfony security mechanisms.

## Plan
1. Model security invariants in Domain (failed attempts, lock state, 2FA challenge lifecycle).
2. Build authentication and 2FA orchestration in Application/UI layers.
3. Persist traceable lock/unlock and 2FA events.
4. Add brute-force protections and configuration controls.

## Risks
- State inconsistencies between login attempts, lock status, and 2FA verification.
- Weak brute-force mitigation on 2FA endpoint.
- Incomplete audit metadata reducing traceability.

## Implementation Steps
1. Added security/testing dependencies:
   - `symfony/security-bundle`
   - `symfony/rate-limiter`
   - `symfony/phpunit-bridge`
2. Implemented domain model:
   - `SecurityUser`
   - `AccountLockPolicy`
   - `SecondFactorChallenge` (+ domain exceptions)
3. Implemented application ports and use cases:
   - `RecordFailedLoginAttemptHandler`
   - `RecordSuccessfulLoginHandler`
   - `UnlockUserHandler`
   - repository/audit/policy provider ports
4. Documented architecture/security decisions in `docs/security.md`.
5. Remaining:
   - functional tests for full auth journey
6. Implemented Symfony runtime integration:
   - login authenticator (`LoginFormAuthenticator`)
   - 2FA verification endpoint/controller
   - route protection and 2FA enforcement subscriber
   - login throttling + dedicated 2FA rate limiter
7. Implemented temporary in-memory infrastructure adapters to validate end-to-end flow before DB migration.
8. Added Doctrine ORM + migrations and replaced security persistence adapters with PostgreSQL-ready Doctrine implementations:
   - `DoctrineSecurityUserRepository`
   - `DoctrineSecondFactorChallengeRepository`
   - `DoctrineSecurityAuditTrail`
9. Added infrastructure entities and migration:
   - `security_users`
   - `second_factor_challenges`
   - `security_audit_events`
   - initial admin seed in `migrations/Version20260216165000.php`

## Verification
- TDD cycle completed for domain model:
  - initial test run failed (missing classes)
  - implementation added
  - tests now green
- Current test status:
  - `15 tests, 48 assertions` PASS (`vendor/bin/simple-phpunit`)
- Symfony bootstrap:
  - `php bin/console debug:router` PASS in Docker (routes registered for login/2fa/dashboard)
  - `php bin/console about` PASS in Docker with `DATABASE_URL` set
- Functional/UI verification:
  - end-to-end login + 2FA verification flow covered by `tests/IdentityAccess/UI/SecurityFlowTest.php`
  - lock behavior after 5 failed attempts covered by `tests/IdentityAccess/UI/SecurityFlowTest.php`
- Migration verification:
  - migration executed against running PostgreSQL container:
    - `doctrine:migrations:migrate --no-interaction` -> PASS

## Acceptance Criteria
- Account locks after 5 failed attempts with configurable duration.
- 2FA code expires and verification attempts are brute-force protected.
- Lock/unlock and 2FA critical transitions are auditable.

## Review Notes
M2 accepted. Security core now includes domain invariants, Symfony runtime flow, Doctrine persistence, brute-force controls, and functional verification.
