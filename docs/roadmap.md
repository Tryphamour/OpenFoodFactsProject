### Global Development Roadmap: Open Food Facts Dashboard

This document outlines the strategic plan for developing the Open Food Facts Dashboard project. It serves as our architectural blueprint and phased execution guide.

#### 1. Phase Breakdown

The project will be delivered in five distinct, sequential phases.

*   **Phase 1: Foundation & Domain Core (TDD)**
    *   **Goal:** Establish the project skeleton, Docker environment, and the core domain logic for users and security.
    *   **Activities:**
        *   Setup Docker environment (`docker-compose.yml` with FrankenPHP, PostgreSQL 16, Mailhog).
        *   Initialize Symfony 8 project structure.
        *   Implement a `Makefile` for orchestration as per `workflows/orchestration.md`.
        *   TDD: Model the `Identity & Access` Bounded Context. Create `User` entity, `Email` and `Password` value objects.
        *   TDD: Implement domain logic for login attempts, account locking/unlocking, and password policies.
        *   Create fixtures for administrator accounts.

*   **Phase 2: Security & Authentication**
    *   **Goal:** Implement the complete, secure authentication and 2FA flow.
    *   **Activities:**
        *   Implement the primary firewall and a custom authenticator for email/password login.
        *   Integrate `scheb/2fa-email` for the two-factor authentication flow, including code generation, email transport via Mailhog, and code validation.
        *   Implement event listeners for `LoginFailureEvent` to track failed attempts and trigger account locking after 5 attempts.
        *   Create CLI commands for an administrator to unlock an account and list locked accounts.

*   **Phase 3: Dashboard Architecture & UI**
    *   **Goal:** Build the user-facing dashboard structure and implement widget functionality.
    *   **Activities:**
        *   Model the `Dashboard` Bounded Context: `Dashboard` and `Widget` entities. The `Dashboard` will hold the user's widget layout configuration as a JSON field.
        *   Integrate Symfony UX, Turbo, and Stimulus.
        *   Install and configure `SortableJS` for drag-and-drop.
        *   Create the main dashboard page, secured behind the firewall.
        *   Develop the first widget as a Symfony Live Component (e.g., a simple "Welcome" widget).
        *   Implement the "Add Widget" functionality.
        *   Create the internal API endpoint to persist the dashboard layout.

*   **Phase 4: Open Food Facts Integration**
    *   **Goal:** Connect to the Open Food Facts API and display data in widgets.
    *   **Activities:**
        *   Develop the `OpenFoodFactsClient` in the `Infrastructure` layer, implementing a domain-defined interface.
        *   Utilize Symfony's HTTP Client with caching (HTTP Cache or PSR-6) and a retryable/circuit-breaker policy for resilience.
        *   Create Data Transfer Objects (DTOs) to map API responses, preventing external data structures from leaking into the application.
        *   Develop a data-driven widget (e.g., `ProductNutritionWidget`) that takes a product code, calls the client, and renders the data.
        *   Implement graceful error handling within the Live Component (e.g., show a "Could not load data" state).

*   **Phase 5: Finalization & Review**
    *   **Goal:** Polish, harden, and document the application.
    *   **Activities:**
        *   Conduct a full security review (CSRF, XSS, parameter validation).
        *   Write functional tests for the complete login and dashboard interaction flow.
        *   Stress-test API integration and error handling.
        *   Finalize all documentation.
        *   Prepare the project for final evaluation.

#### 2. Risk Identification

*   **Technical Risk:** Symfony 8 and FrankenPHP are new. While stable, undocumented edge cases may arise, requiring deeper investigation.
    *   **Mitigation:** Allocate time for research and stick to documented features. Maintain close contact with community channels.
*   **Dependency Risk:** The Open Food Facts API is an external dependency. It may have rate limits, downtime, or breaking changes.
    *   **Mitigation:** Implement a robust client with caching, circuit-breaking, and graceful degradation. The application must remain functional even if the API is unavailable.
*   **Scope Creep:** The widget-based architecture is extensible, which can invite feature requests beyond the initial scope defined in `docs/dashboard-spec.md`.
    *   **Mitigation:** Adhere strictly to the defined specification. New widgets or features will be considered only after the core project is delivered and validated.

#### 3. Domain Modeling Strategy

We will employ Domain-Driven Design (DDD) with a Clean Architecture structure.

*   **Bounded Contexts:**
    1.  **Identity & Access:** Manages everything related to users, authentication, and permissions.
        *   **Aggregate Root:** `User`.
        *   **Entities:** `User`, `TwoFactorAuthenticationCode`.
        *   **Value Objects:** `UserId`, `Email`, `HashedPassword`, `LoginAttemptCounter`.
        *   **Domain Events:** `UserRegistered`, `UserLoggedIn`, `LoginFailed`, `UserLocked`, `UserUnlocked`.
    2.  **Dashboard:** Manages the user's personal dashboard configuration.
        *   **Aggregate Root:** `Dashboard`.
        *   **Entities:** `Dashboard`, `Widget`.
        *   **Value Objects:** `DashboardId`, `WidgetConfiguration` (JSON-wrapped VO), `LayoutGrid`.
*   **TDD Approach:** All domain logic within these contexts will be developed via Test-Driven Development (PHPUnit) before any infrastructure or UI code is written. This ensures the business rules are expressive, correct, and decoupled.

#### 4. Security Implementation Strategy

Security is paramount and will be built-in, not bolted on.

*   **Authentication:** A custom Symfony `Authenticator` will handle the multi-step login process (password check -> 2FA check).
*   **Account Lockout:** A listener on `Symfony\Component\Security\Http\Event\LoginFailureEvent` will increment a `login_attempts` counter on the `User` entity. If the count exceeds 5, the `is_locked` flag is set to true. A custom `UserChecker` will prevent locked-out users from proceeding.
*   **2FA:** The `scheb/2fa-email` bundle will be used. Codes will be short-lived, single-use, and generated securely. We will implement brute-force protection on the 2FA code submission form.
*   **Authorization:** Access to the dashboard and internal API will be protected by roles (e.g., `ROLE_USER`).
*   **Data Integrity:** All user-provided data will be validated via Symfony's Validator component (DTOs for forms/API endpoints) to prevent invalid data from reaching the domain.

#### 5. Dashboard Architecture Approach

We will leverage the full power of the Symfony UX stack for a reactive UI without a heavy JS framework.

*   **Live Components:** Each widget will be a self-contained Live Component (e.g., `NutritionScoreWidgetComponent.php`). It will contain the logic for rendering and handling its own state.
*   **Props:** Components will be rendered with props (e.g., `product_code`) passed from the main dashboard Twig template.
*   **Asynchronous Actions:** Data loading (e.g., from the Open Food Facts API) will be handled by `#[LiveAction]` methods within the components, allowing for loading states.
*   **Layout Persistence:**
    1.  `SortableJS` (via a Stimulus controller) will track drag-and-drop events.
    2.  On drop, the Stimulus controller will gather the new layout (widget IDs and their positions).
    3.  It will then make a `fetch` request to our secured internal API endpoint, sending the new layout.
    4.  The API endpoint will persist this layout to the `Dashboard` entity.

#### 6. Open Food Facts Integration Strategy

The integration will be isolated and resilient.

*   **Interface:** An interface (`Application\Contract\FoodDataGateway`) will define the contract for fetching food data.
*   **Implementation:** An `Infrastructure\OpenFoodFacts\Client` class will implement the gateway interface. This isolates our domain from the specific external provider.
*   **Symfony HTTP Client:** We will use this to make the API calls, configured with:
    *   **Caching:** To reduce latency and respect API rate limits.
    *   **Retry/Circuit Breaker:** To handle transient network failures or API downtime gracefully.
*   **DTOs:** API responses will be mapped to clean, typed DTOs. This prevents the API's data structure from dictating our application's data structure and provides a single point of transformation.

#### 7. Testing Strategy

We will maintain a comprehensive test suite with a clear separation of concerns.

*   **Unit Tests (PHPUnit):**
    *   **Scope:** Domain logic (Entities, Value Objects), Application Services.
    *   **Goal:** Verify business rules in complete isolation. Zero I/O.
*   **Integration Tests (PHPUnit + Doctrine Test DB):**
    *   **Scope:** Infrastructure components (Repositories, API Clients).
    *   **Goal:** Verify that our code correctly integrates with external tools (e.g., database queries are correct, API client payload is correctly formatted). We will use a separate test database.
*   **Functional Tests (Symfony WebTestCase):**
    *   **Scope:** Full user workflows (login, 2FA, adding a widget, API errors).
    *   **Goal:** Verify that all layers of the application work together correctly from the perspective of a user making HTTP requests.

#### 8. Docker and Environment Setup Strategy

The environment will be fully containerized, reproducible, and production-oriented.

*   **`docker-compose.yml`:**
    *   `frankenphp`: Official FrankenPHP image for Symfony, with the `Caddyfile` configured for hot reload in development.
    *   `postgres`: Official PostgreSQL 16 image, with a named volume for data persistence.
    *   `mailhog`: To intercept and display emails sent during development (e.g., 2FA codes).
*   **`Makefile`:** A `Makefile` will provide simple commands for common operations (`make up`, `make down`, `make test`, `make lint`) as defined in our workflow guidelines.
*   **Configuration:** Environment variables (`.env`, `.env.local`) will manage service connections and application parameters.

#### 9. Internal API Design Direction

As required, we will expose at least one secured internal API endpoint. It will not use API Platform.

*   **Endpoint:** `PATCH /api/internal/dashboard/layout`
*   **Purpose:** Persists the user's dashboard widget layout.
*   **Security:** This endpoint will be part of a `/api/internal` firewall protected by the same session-based authentication as the main application, ensuring only the logged-in user can modify their own dashboard. CSRF protection will be enabled.
*   **Request Body (JSON):**
    ```json
    {
      "widgets": [
        { "id": "widget-uuid-1", "position": { "x": 0, "y": 0 } },
        { "id": "widget-uuid-2", "position": { "x": 1, "y": 0 } }
      ]
    }
    ```
*   **Controller:** A dedicated `DashboardLayoutController` will handle this request. It will use a DTO with validation constraints to process the payload before passing it to an Application Service.

#### 10. Milestones for Validation

*   **End of Phase 1:** A developer can clone the repo, run `make up`, and see the Symfony welcome page. All domain tests for the User entity pass.
*   **End of Phase 2:** A user can log in with a fixture account, receive a 2FA email, and access a placeholder "dashboard" page. Account locking is verifiable via a CLI command.
*   **End of Phase 3:** The dashboard page is functional. Users can add a widget and drag-and-drop it. The layout changes are persisted and reloaded on refresh.
*   **End of Phase 4:** The first data-driven widget is complete. It successfully fetches and displays data from the Open Food Facts API, and gracefully handles API errors.
*   **End of Phase 5:** All functional tests pass. The codebase is clean, documented, and meets all requirements of the project specification. The project is ready for review.