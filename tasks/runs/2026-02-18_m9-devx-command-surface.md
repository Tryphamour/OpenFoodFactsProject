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
1. Add minimal command surface:
   - `up`, `down`, `logs`, `test`, `test-unit`/`test-functional` (if useful), `lint` (if available)
2. Ensure test command enforces `APP_ENV=test` in containerized execution.
3. Add optional helper command for smoke checks (`bin/console about`, container status).
4. Document all commands in README with expected prerequisites.
5. Keep command scope intentionally small and aligned with current project needs.

## Verification
- Run each command at least once on the current environment.
- Confirm command failures provide actionable output.
- Ensure no command bypasses Docker-first project constraint.

## Acceptance Criteria
- Contributors can use a compact, documented command interface.
- Common workflows are faster and less error-prone.
- Commands remain consistent with repository configuration.

## Review Notes
Pending implementation.
