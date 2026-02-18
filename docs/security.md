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

### M2 Validation Status

- Functional tests cover end-to-end login + 2FA success flow and lock behavior after failed attempts.
- Doctrine migration has been executed successfully against a running PostgreSQL container in Docker.

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

## M5 Internal API Security Model

- Protected internal route space:
  - `^/internal/api` requires `ROLE_USER` in `access_control`
- Authorization policy:
  - `DashboardOwnerVoter` authorizes dashboard read if requester is:
    - dashboard owner, or
    - `ROLE_ADMIN`
- Error exposure:
  - Internal API failures are normalized as `application/problem+json`
  - Traceable `traceId` included in payload and logged server-side

## PostgreSQL Persistence Integration (Implemented)

The following ports now use Doctrine-backed adapters:

- `SecurityUserRepository` -> `DoctrineSecurityUserRepository`
- `SecondFactorChallengeRepository` -> `DoctrineSecondFactorChallengeRepository`
- `SecurityAuditTrail` -> `DoctrineSecurityAuditTrail`

Persisted infrastructure records:

- `security_users`
- `second_factor_challenges`
- `security_audit_events`

Migration:

- `migrations/Version20260216165000.php` creates tables and seeds initial admin account.
