# Dashboard

Each user can:

- Add widget
- Remove widget
- Configure widget
- Drag & drop widgets
- Persist layout

Widgets examples:
- Product search widget
- Nutritional breakdown
- Additives overview
- NutriScore distribution
- Category stats

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
