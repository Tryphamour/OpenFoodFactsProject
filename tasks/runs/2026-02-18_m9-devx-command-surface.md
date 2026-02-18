# Run: M9 Developer Experience Command Surface

## Context
Improve day-to-day usability by providing a small, reliable command surface for common tasks and documenting it.

## Plan
1. Define high-value developer commands.
2. Implement command wrappers (`Makefile` or equivalent scripts).
3. Verify wrappers against Docker-only workflow.
4. Document command usage and expected outputs.

## Risks
- Wrapper commands drifting from actual container/runtime behavior.
- Platform-specific command issues (Windows/Linux shell differences).
- Overly broad command set that becomes maintenance overhead.

## Implementation Steps
1. Add minimal command surface: ✅
   - `up`, `down`, `down-v`, `ps`, `logs`, `install`, `migrate`, `about`, `test`, `test-default`, `test-file`, `mailhog-url`, `smoke`
2. Ensure test command enforces `APP_ENV=test` in containerized execution. ✅
3. Add optional helper command for smoke checks (`bin/console about`, container status). ✅
4. Document all commands in README with expected prerequisites. ✅
5. Keep command scope intentionally small and aligned with current project needs. ✅

Delivered command surface (`Makefile`):
- `help`, `up`, `down`, `down-v`, `ps`, `logs`, `install`, `migrate`, `about`, `test`, `test-default`, `test-file`, `mailhog-url`, `smoke`

## Verification
- Run each command at least once on the current environment.
- Confirm command failures provide actionable output.
- Ensure no command bypasses Docker-first project constraint.

Validated commands:
- `make help` ✅
- `make ps` ✅
- `make about` ✅
- `make mailhog-url` ✅
- `make smoke` ✅
- `make test-default` ✅ (`OK (3 tests, 29 assertions)`)
- `make test` ✅ (`OK (34 tests, 139 assertions)`)

Scope note:
- `make down-v` remains intentionally destructive and was not executed during validation.

## Acceptance Criteria
- Contributors can use a compact, documented command interface.
- Common workflows are faster and less error-prone.
- Commands remain consistent with repository configuration.

## Review Notes
M9 accepted. A compact and documented Makefile command surface now standardizes Docker-only developer workflows.
