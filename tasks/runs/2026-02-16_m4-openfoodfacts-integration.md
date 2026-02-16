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
1. Implemented FoodCatalog application contracts and read models:
   - `ProductSearchQuery`
   - `ProductCatalogGateway`
   - `ProductView`
   - `ProductSearchResult`
   - `SearchProductsHandler`
2. Implemented Infrastructure adapter:
   - `OpenFoodFactsProductCatalogGateway` using Symfony HttpClient
   - OFF mapping with typed projection
3. Implemented query features:
   - search term
   - filtering (`brand`, `nutriscore`)
   - sorting (`name_*`, `nutriscore_*`)
   - NutriScore distribution aggregation
4. Implemented resilience:
   - timeout protection
   - primary cache + stale cache fallback
   - degraded response model (`stale_cache_fallback`, `off_unavailable`)
5. Integrated dashboard product-search widgets with FoodCatalog use case:
   - preview rendering on product widgets
   - degraded status displayed without breaking dashboard rendering
6. Added deterministic test-environment service bindings:
   - degraded ProductCatalog gateway stub
   - fixed 2FA code generator for stable functional tests

## Verification
- Unit tests:
  - `tests/FoodCatalog/Application/Port/ProductSearchQueryTest.php` PASS
  - `tests/FoodCatalog/Application/UseCase/SearchProducts/SearchProductsHandlerTest.php` PASS
- Integration tests:
  - `tests/FoodCatalog/Infrastructure/Api/OpenFoodFactsProductCatalogGatewayTest.php` PASS
  - covers success, stale fallback, and degraded-empty behavior
- Functional tests:
  - `tests/Dashboard/UI/DashboardFlowTest.php` PASS
  - includes degraded catalog rendering scenario
- Regression:
  - full suite PASS (`31 tests, 110 assertions`)

## Acceptance Criteria
- Product search/filter/sort works through OFF integration.
- API failures do not break full dashboard rendering.
- Timeout, caching, and fallback behavior are documented and tested.

## Review Notes
M4 is complete with infrastructure resilience, explicit degradation semantics, and dashboard-level graceful failure behavior validated by automated tests.
