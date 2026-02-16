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
1. Implemented Dashboard domain model (`Dashboard`, `Widget`) with tested invariants:
   - unique widget id
   - normalized positions after remove/reorder
   - aggregate-driven configuration update
2. Added application use cases:
   - `AddWidgetHandler`
   - `RemoveWidgetHandler`
   - `ConfigureWidgetHandler`
   - `ReorderWidgetsHandler`
   - `GetDashboardHandler`
3. Implemented Doctrine persistence:
   - `DoctrineDashboardRepository`
   - `dashboard_widgets` migration (`Version20260216175000`)
4. Added dashboard UI composition:
   - Live Component `dashboard_board`
   - SortableJS reorder posting to `POST /dashboard/reorder`
5. Added route/controller update for persisted reorder.
6. Added M3 domain/application tests; full suite remains green.
7. Remaining:
   - functional test coverage for dashboard interactions through HTTP boundary
   - optional voter hardening if dashboard ownership model expands beyond current authenticated-owner scope

## Verification
- Unit tests:
  - `tests/Dashboard/Domain/Model/DashboardTest.php` PASS
- Application tests:
  - `tests/Dashboard/Application/UseCase/DashboardUseCasesTest.php` PASS
- Regression:
  - full suite PASS (`21 tests, 68 assertions`)
- Routing:
  - dashboard routes + UX live route registered (`debug:router`)
- Pending:
  - full functional dashboard journey tests

## Acceptance Criteria
- Users can manage widgets and reorder via drag/drop.
- Layout and widget config persist per user.
- Unauthorized dashboard access/modification is denied.

## Review Notes
M3 is in progress with domain/application/persistence/UI core implemented and validated by automated tests. Functional journey coverage is the next closure step.
