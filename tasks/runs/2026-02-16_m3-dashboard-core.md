# Run: M3 Dashboard Core

## Context
Deliver authenticated user dashboard capabilities: widget add/remove/configure, drag-and-drop ordering, and persistent personalized layout.

## Plan
1. Model dashboard aggregate and widget configuration invariants.
2. Implement use cases for widget lifecycle and layout persistence.
3. Build Live Components + Turbo interactions for UX without SPA frameworks.
4. Ensure ownership and authorization constraints.

## Risks
- Widget configuration drift without schema/version handling.
- Race conditions during drag/drop persistence.
- Authorization gaps allowing cross-user dashboard access.

## Implementation Steps
1. Define Dashboard and Widget domain model with invariant methods.
2. Add application commands/queries for widget and layout operations.
3. Implement Live Components and SortableJS integration for reorder.
4. Persist layout state and widget settings in PostgreSQL.
5. Enforce ownership checks with voters/attributes.

## Verification
- Unit tests for dashboard invariants and reorder rules.
- Integration tests for repository persistence and mapping.
- Functional tests for add/remove/configure/reorder user journeys.

## Acceptance Criteria
- Users can manage widgets and reorder via drag/drop.
- Layout and widget config persist per user.
- Unauthorized dashboard access/modification is denied.

## Review Notes
Pending implementation.

