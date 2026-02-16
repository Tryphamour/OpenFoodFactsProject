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
1. Add domain entities/value objects for lock policy and 2FA challenge.
2. Implement use cases: initiate login, record failure, lock user, issue 2FA code, verify 2FA code.
3. Wire Symfony authenticator, security events, and voters/attributes where needed.
4. Persist audit events for lock/unlock and critical auth transitions.
5. Configure rate limiting and lock duration settings.

## Verification
- Domain unit tests (TDD) for lock and 2FA invariants.
- Integration tests for persistence and security event recording.
- Functional tests for end-to-end login + 2FA + lock scenarios.

## Acceptance Criteria
- Account locks after 5 failed attempts with configurable duration.
- 2FA code expires and verification attempts are brute-force protected.
- Lock/unlock and 2FA critical transitions are auditable.

## Review Notes
Pending implementation.

