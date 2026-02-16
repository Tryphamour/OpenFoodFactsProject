# Open Food Facts Integration

Use Open Food Facts public API.

Requirements:
- Search products
- Filtering
- Sorting
- Aggregations (if possible)

Must:
- Handle API errors gracefully
- Timeout protection
- Caching strategy
- Fallback behavior

Use Symfony HttpClient.
Encapsulate client in Infrastructure layer.

## M4 Implementation Notes

### Application Contracts

- Query DTO: `App\FoodCatalog\Application\Port\ProductSearchQuery`
  - validates page/limit bounds
  - normalizes filters for deterministic caching
- Gateway port: `App\FoodCatalog\Application\Port\ProductCatalogGateway`
- Read models:
  - `ProductView`
  - `ProductSearchResult` (supports degraded mode + reason)
- Use case:
  - `App\FoodCatalog\Application\UseCase\SearchProducts\SearchProductsHandler`

### Infrastructure Adapter

- Adapter: `App\FoodCatalog\Infrastructure\Api\OpenFoodFactsProductCatalogGateway`
- Transport: Symfony HttpClient (`http_client`)
- Endpoint: `GET /cgi/search.pl` on `https://world.openfoodfacts.org`
- Timeout protection:
  - request timeout configured in service parameters
- Mapping:
  - typed mapping from OFF payload into `ProductView`
- Query capabilities:
  - search term
  - local filter support (`brand`, `nutriscore`)
  - local sorting (`name_asc`, `name_desc`, `nutriscore_asc`, `nutriscore_desc`)
  - NutriScore aggregation distribution (`a`..`e`, `unknown`)

### Resilience and Fallback

- Primary cache key:
  - deterministic hash based on normalized query payload
- Stale cache key:
  - same hash, longer TTL for fallback reads
- Failure handling:
  - on OFF transport/HTTP/payload failure, use stale cache if available
  - otherwise return degraded empty result (`off_unavailable`)
- Degradation is explicit and propagated to UI through `ProductSearchResult`.

### Dashboard Integration

- Product search widgets now render OFF preview results via `SearchProductsHandler`.
- When OFF is unavailable, widget still renders with explicit degraded status, without breaking dashboard page rendering.

### Test Coverage

- `tests/FoodCatalog/Infrastructure/Api/OpenFoodFactsProductCatalogGatewayTest.php`
  - success mapping + aggregation
  - stale-cache fallback on transport failure
  - degraded empty response when no fallback exists
- `tests/Dashboard/UI/DashboardFlowTest.php`
  - dashboard remains renderable with degraded catalog responses in test environment.
