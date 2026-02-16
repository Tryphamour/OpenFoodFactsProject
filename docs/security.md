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
