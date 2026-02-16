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

- Replace temporary in-memory security persistence with PostgreSQL persistence:
  - security user state
  - 2FA challenges
  - audit trail records
- Add functional tests for end-to-end login + 2FA + lock scenarios.

## Symfony Runtime Integration (Implemented)

- Custom login authenticator:
  - `App\IdentityAccess\UI\Security\LoginFormAuthenticator`
- 2FA gate enforcement on authenticated sessions:
  - `App\IdentityAccess\UI\Security\TwoFactorEnforcementSubscriber`
- UI endpoints:
  - `/login`
  - `/2fa`
  - `/logout`
  - `/dashboard` (requires authenticated + 2FA-verified session)
- Brute-force protections:
  - login throttling in security firewall
  - dedicated rate limiter for 2FA verification

## Temporary M2 Tradeoff

To validate flow quickly and keep DDD boundaries stable, current runtime adapters are in-memory:

- `InMemorySecurityUserRepository`
- `InMemorySecondFactorChallengeRepository`
- `InMemoryIdentityStore`

These adapters are intentionally transitional and will be replaced by PostgreSQL-backed infrastructure before M2 closure.
