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
   - PostgreSQL persistence for user security state / challenges / audit
   - functional tests for full auth journey
6. Implemented Symfony runtime integration:
   - login authenticator (`LoginFormAuthenticator`)
   - 2FA verification endpoint/controller
   - route protection and 2FA enforcement subscriber
   - login throttling + dedicated 2FA rate limiter
7. Implemented temporary in-memory infrastructure adapters to validate end-to-end flow before DB migration.

## Verification
- TDD cycle completed for domain model:
  - initial test run failed (missing classes)
  - implementation added
  - tests now green
- Current test status:
  - `13 tests, 27 assertions` PASS (`vendor/bin/simple-phpunit`)
- Symfony bootstrap:
  - `php bin/console debug:router` PASS in Docker (routes registered for login/2fa/dashboard)
- Pending verification:
  - integration/functional coverage once PostgreSQL persistence is wired

## Acceptance Criteria
- Account locks after 5 failed attempts with configurable duration.
- 2FA code expires and verification attempts are brute-force protected.
- Lock/unlock and 2FA critical transitions are auditable.

## Review Notes
M2 is in progress. Domain model and Symfony runtime integration are in place and tested; PostgreSQL-backed persistence and full functional auth tests are still required before closure.
