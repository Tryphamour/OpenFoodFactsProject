# Workflow Orchestration

This document defines mandatory execution rules.

Gemini must re-read this file before any non-trivial implementation.

---

# 0) Mandatory Context Reload

Before starting any new task, re-read:

- docs/project.md
- docs/architecture.md
- docs/security.md
- docs/dashboard-spec.md
- docs/openfoodfacts-integration.md
- workflows/git-guidelines.md
- tasks/todo.md
- tasks/lessons.md

If implementation reveals missing specifications:
→ Update the appropriate document.
→ Do not leave architectural decisions undocumented.

Documentation must evolve with the code.

---

# 1) Plan mode by default

For any task involving:
- More than 3 steps
- Business rules
- Security
- Persistence
- UI architecture
- External API integration

You must:

1. Produce a structured plan
2. Identify risks
3. Define verification strategy
4. Validate DDD layer placement

If implementation drifts or fails:
→ STOP
→ Re-plan
→ Document why

---

# 2) Task tracking discipline

1. Create one run file per task:
   tasks/runs/YYYY-MM-DD_<slug>.md

2. Each run file must contain:
   - Context
   - Plan
   - Risks
   - Implementation steps
   - Verification
   - Review notes

3. tasks/todo.md remains a compact index only:
   - Active
   - Backlog
   - Done (latest 20)

4. Close completed runs via:
   npm run task:close -- "runs/<file>.md"

5. Archive finished tasks.

6. After user corrections or failures:
   - Add prevention rules in tasks/lessons.md

---

# 3) Verification before completion

Before marking a task complete:

- Validate behavior (tests, lint, targeted checks)
- Confirm diffs are minimal and intentional
- Ensure no domain logic leaked into controllers
- Ensure no infrastructure logic leaked into domain
- Ask internally:
  "Would this pass staff-level review?"

If not:
→ Refactor before closing task.

---

# 4) Elegance and Pragmatism

- Prefer simple, maintainable solutions
- Avoid clever hacks
- Avoid over-engineering obvious features
- Refactor hacky solutions if impact is meaningful

---

# 5) TDD Enforcement

For domain logic:

1. Write failing test
2. Implement minimal code
3. Refactor
4. Verify coverage

Domain logic without tests is forbidden.

---

# 6) Architectural Integrity Checks

Before merging:

- Does this respect DDD boundaries?
- Is the domain model expressive?
- Are invariants protected?
- Are security rules enforced at correct layer?
- Is persistence ignorance preserved?

If not:
→ Rework before commit.

---

# 7) Git Discipline

- Atomic commits
- Conventional commits format
- No mixed concerns
- Ask user before push

---

# 8) Continuous Documentation Improvement

If new concepts appear:
- Update architecture.md
- Update security.md
- Update dashboard-spec.md if relevant
- Document tradeoffs

Documentation quality is part of evaluation.
