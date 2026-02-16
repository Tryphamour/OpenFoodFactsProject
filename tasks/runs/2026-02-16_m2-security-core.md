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
   - Symfony authenticator + 2FA flow wiring
   - PostgreSQL persistence for user security state / challenges / audit
   - endpoint-level brute-force controls
   - functional tests for full auth journey

## Verification
- TDD cycle completed for domain model:
  - initial test run failed (missing classes)
  - implementation added
  - tests now green
- Current test status:
  - `12 tests, 23 assertions` PASS (`vendor/bin/simple-phpunit`)
- Symfony bootstrap:
  - `php bin/console about` PASS in Docker
- Pending verification:
  - integration/functional coverage once Symfony Security + persistence are wired

## Acceptance Criteria
- Account locks after 5 failed attempts with configurable duration.
- 2FA code expires and verification attempts are brute-force protected.
- Lock/unlock and 2FA critical transitions are auditable.

## Review Notes
M2 is in progress. Core domain and application orchestration are implemented and validated by tests; framework integration and persistence still required before marking M2 complete.
