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
1. Scaffolded Symfony 8 baseline in Docker (`composer:2 create-project symfony/skeleton:^8.0`), then merged it into repository root.
2. Created bounded context structure:
   - `src/IdentityAccess/{Domain,Application,Infrastructure,UI}`
   - `src/Dashboard/{Domain,Application,Infrastructure,UI}`
   - `src/FoodCatalog/{Domain,Application,Infrastructure,UI}`
   - `src/Audit/{Domain,Application,Infrastructure,UI}`
   - `src/Shared/{Domain,Application,Infrastructure,UI}`
3. Added baseline contracts/ports:
   - `src/FoodCatalog/Application/Port/ProductCatalogGateway.php`
   - `src/FoodCatalog/Application/Port/ProductSearchQuery.php`
   - `src/IdentityAccess/Application/Port/SecondFactorCodeSender.php`
   - `src/Audit/Application/Port/AuditTrail.php`
   - `src/Dashboard/Application/Port/DashboardRepository.php`
   - shared abstractions in `src/Shared/*`
4. Restricted service registration in `config/services.yaml` to `Application`, `Infrastructure`, and `UI` namespaces by context (domain excluded by convention).
5. Updated `docs/architecture.md` with explicit dependency rules and added `docs/review-checklist.md` for review discipline.
6. Fixed a bootstrap issue detected during verification by creating missing `src/Shared/UI/`.

## Verification
- Symfony bootstrap check in Docker:
  - `docker run --rm -v "${PWD}:/app" -w /app composer:2 php bin/console about` -> PASS
- PHP syntax lint in Docker:
  - `docker run --rm -v "${PWD}:/app" -w /app composer:2 sh -lc "find src -name '*.php' -print0 | xargs -0 -n1 php -l"` -> PASS
- Static structure check:
  - service container registration excludes domain namespaces by path selection.
  - only ports/contracts exist in application at this stage; no business rules in UI.

## Acceptance Criteria
- DDD folder/module structure is committed and documented.
- Architectural rules are explicit enough for a reviewer to enforce.
- No business rule appears in controller/form/live component classes.

## Review Notes
M1 accepted. The architecture baseline is now executable, documented, and constrained enough for staff-level review to evaluate layering decisions before business implementation starts.
