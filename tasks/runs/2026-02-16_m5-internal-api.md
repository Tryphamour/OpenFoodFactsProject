# Run: M5 Internal Secured API

## Context
Expose at least one meaningful secured internal API endpoint supporting dashboard data use, with explicit authorization and stable error contracts.

## Plan
1. Define API purpose and response contract from application queries.
2. Secure endpoint with authentication and ownership/role authorization.
3. Standardize error responses and traceability behavior.
4. Validate endpoint with functional and authorization tests.

## Risks
- Overexposing data fields unintentionally.
- Inconsistent error format across API and UI concerns.
- Missing authorization edge-case coverage.

## Implementation Steps
1. Implement internal API controller delegating strictly to application use case. ✅
2. Add DTO transformer layer for JSON response contract. ✅
3. Apply security attributes/voters for access control. ✅
4. Implement structured error responses (problem-details style). ✅
5. Document endpoint usage and constraints. ✅

## Verification
- Functional tests for happy path and denied access path.
- Contract assertions for response shape and error payloads.
- Review confirms no domain logic in controller.

Implemented tests:
- `tests/Dashboard/UI/InternalDashboardApiTest.php`
  - owner happy path
  - denied access for non-owner non-admin
  - admin access to another owner dashboard
  - JSON + problem-details contract assertions

Execution status:
- Passed in containerized execution:
  - `tests/Dashboard/UI/InternalDashboardApiTest.php` → `OK (3 tests, 29 assertions)`
  - Regression check `tests/Dashboard/UI/DashboardFlowTest.php` → `OK (3 tests, 25 assertions)`

## Acceptance Criteria
- Endpoint is meaningful for dashboard/system operations.
- Access is restricted and ownership is enforced.
- Error responses are consistent and test-covered.

## Review Notes
Implementation completed and verified with functional tests.
