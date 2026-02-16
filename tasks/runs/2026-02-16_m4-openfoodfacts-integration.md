# Run: M4 Open Food Facts Integration

## Context
Integrate Open Food Facts through an Infrastructure adapter with resilience, caching, timeout safety, and graceful widget-level degradation.

## Plan
1. Define Application-level query contracts independent of OFF payload shape.
2. Build Infrastructure HTTP client adapter using Symfony HttpClient.
3. Add cache strategy for frequent queries and normalized query keys.
4. Implement fallback behavior for upstream failures/timeouts.

## Risks
- OFF response variability breaking mapping logic.
- Overly broad retries increasing latency.
- Missing degradation path causing dashboard-wide failures.

## Implementation Steps
1. Create OFF port in Application and adapter in Infrastructure.
2. Implement search/filter/sort support with typed mapping.
3. Configure timeout/retry rules and classify error types.
4. Add cache with TTL and deterministic keying.
5. Return safe degraded states to widgets on failure.

## Verification
- Integration tests with mocked OFF responses (success, timeout, malformed, upstream error).
- Functional tests validating widget behavior under OFF outage conditions.
- Cache behavior checks for key normalization and expiry.

## Acceptance Criteria
- Product search/filter/sort works through OFF integration.
- API failures do not break full dashboard rendering.
- Timeout, caching, and fallback behavior are documented and tested.

## Review Notes
Pending implementation.

