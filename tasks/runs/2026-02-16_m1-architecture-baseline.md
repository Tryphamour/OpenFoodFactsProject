# Run: M1 Architecture Baseline

## Context
Establish the project skeleton and decision boundaries for a staff-level Symfony 8 delivery with strict DDD layering, Docker-only execution, and documented architectural intent.

## Plan
1. Define codebase structure for Domain, Application, Infrastructure, and UI.
2. Create architecture decision records for key choices (security flow shape, widget strategy, integration boundaries).
3. Set coding and review checklists enforcing boundary integrity.
4. Align documentation with the chosen structure.

## Risks
- Ambiguous boundaries causing business logic leakage into controllers or services.
- Premature framework coupling in domain model.
- Missing architectural documentation leading to drift.

## Implementation Steps
1. Scaffold namespace/module structure reflecting bounded contexts.
2. Add contracts/ports in Domain/Application where external dependencies are needed.
3. Introduce baseline conventions for handlers, repositories, and component placement.
4. Update `docs/architecture.md` with concrete folder and dependency rules.
5. Validate that no infrastructure code appears in Domain.

## Verification
- Static inspection confirms one-way dependencies (UI -> Application -> Domain, Infrastructure plugged via ports).
- Architecture checklist completed and stored in run notes.
- Initial test bootstrap runs in Docker test container.

## Acceptance Criteria
- DDD folder/module structure is committed and documented.
- Architectural rules are explicit enough for a reviewer to enforce.
- No business rule appears in controller/form/live component classes.

## Review Notes
Pending implementation.
