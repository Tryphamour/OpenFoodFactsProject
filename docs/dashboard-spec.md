# Dashboard

Each user can:

- Add widget
- Remove widget
- Configure widget
- Drag & drop widgets
- Persist layout

Widgets examples:
- Product search widget
- Brand search widget
- NutriScore A finder widget

Dashboard configuration must be stored in database.

Use:
- UX Live Components
- Turbo
- SortableJS

No frontend framework allowed.

## M3 Implementation Notes

### Domain

- Dashboard aggregate:
  - `App\Dashboard\Domain\Model\Dashboard`
- Widget entity:
  - `App\Dashboard\Domain\Model\Widget\Widget`
- Invariants covered:
  - unique widget ids per dashboard
  - normalized positions after remove/reorder
  - widget configuration updates through aggregate methods

### Application Use Cases

- Add widget
- Remove widget
- Configure widget
- Reorder widgets
- Query dashboard for owner

### Persistence

- Doctrine repository:
  - `App\Dashboard\Infrastructure\Repository\DoctrineDashboardRepository`
- Table:
  - `dashboard_widgets`
- Migration:
  - `migrations/Version20260216175000.php`

### UI

- Live Component:
  - `dashboard_board`
- Route for persisted drag-and-drop order:
  - `POST /dashboard/reorder`
- SortableJS integrated on dashboard widget list.

## M4 Dashboard Integration Notes

- `product_search` widgets now call FoodCatalog application use case and display a preview list of products.
- Widget preview gracefully degrades when Open Food Facts is unavailable (degradation reason displayed, page remains functional).

## M5 Internal API Notes

- Internal endpoint:
  - `GET /internal/api/dashboard/{ownerId}`
- Response contract:
  - JSON body: `{ "data": { "ownerId": string, "widgets": [...] } }`
  - Widget shape: `id`, `type`, `position`, `configuration`
- Authorization:
  - Requires authenticated + 2FA-verified session (`ROLE_USER`)
  - Ownership enforced through voter; `ROLE_ADMIN` can read any owner dashboard.
- Error contract:
  - `/internal/api/*` failures return `application/problem+json`
  - Payload fields: `type`, `title`, `status`, `detail`, `instance`, `traceId`

## M6 Stability Notes

- Dashboard behavior remains stable in full containerized test execution.
- Existing dashboard UI flow tests remain green after Docker hardening changes.

## M10 Widget Refresh Notes

- Placeholder widgets were replaced with functional widgets:
  - `brand_search`: search and preview products for a specific brand input.
  - `nutriscore_a_search`: preview products for a term filtered to NutriScore `A`.
- Widget configuration form now adapts input field name/placeholder to widget type.
- Product preview rendering is now available for all dashboard widget types.
