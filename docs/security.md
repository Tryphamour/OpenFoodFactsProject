# Security Requirements

Authentication:
- Login + Password
- 2FA via email code
- No public registration
- Admin-only user creation

Security rules:
- Account locked after 5 failed attempts
- Lock duration configurable
- Audit trail for lock/unlock
- 2FA token expiration
- Brute-force protection

Must use Symfony security system properly.
Must demonstrate events, voters, attributes where relevant.

## M2 Security Model Decisions

### Domain Model (`IdentityAccess`)

- `SecurityUser` controls failed login counting, lock state, and unlock/reset behavior.
- `AccountLockPolicy` encapsulates configurable lock thresholds and duration.
- `SecondFactorChallenge` controls 2FA lifecycle:
  - issue with TTL
  - verification attempts counter
  - expiration guard
  - already-verified guard

### Application Orchestration

- `RecordFailedLoginAttemptHandler`
  - applies lock policy
  - persists updated state
  - emits audit event when lock transitions from unlocked -> locked
- `RecordSuccessfulLoginHandler`
  - resets lock/failure counters
- `UnlockUserHandler`
  - unlocks account and emits audit event with reason metadata

### Security Audit Event Names

- `identity_access.account_locked`
- `identity_access.account_unlocked`

### Current Coverage

- Domain tests cover:
  - lock threshold and expiration behavior
  - successful login reset behavior
  - manual unlock behavior
  - 2FA verify success/failure/expiration/max attempts/already-verified
- Application tests cover:
  - lock event emission on transition
  - unlock event emission

### Remaining M2 Steps

- Integrate Symfony firewall/authenticator flow with this domain model.
- Persist user security state, 2FA challenges, and audit events in PostgreSQL.
- Add rate-limiters for login + 2FA endpoints.
- Add functional tests for end-to-end login + 2FA + lock scenarios.
