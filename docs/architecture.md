# Architecture

The project must follow strict DDD separation:

- Domain
    - Entities
    - Value Objects
    - Domain Services
    - Domain Events
    - Business rules
- Application
    - Use Cases
    - DTOs
    - Application Services
- Infrastructure
    - Doctrine Repositories
    - Email provider
    - Open Food Facts client
    - Security persistence
- UI
    - Controllers
    - Live Components
    - Forms
    - Twig templates

No anemic domain model.
Business rules must live in Domain.

TDD required for domain logic.
