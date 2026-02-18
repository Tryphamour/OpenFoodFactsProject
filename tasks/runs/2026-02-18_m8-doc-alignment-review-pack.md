# Run: M8 Documentation Alignment and Review Pack

## Context
Align high-level planning documents with the delivered M1..M6 execution and provide a reviewer-oriented release/readiness pack.

## Plan
1. Identify outdated planning references and reconcile them.
2. Produce a concise review pack with scope, evidence, and known tradeoffs.
3. Ensure all security/architecture/dashboard docs are synchronized.

## Risks
- Divergence between roadmap narrative and implemented milestones.
- Overly verbose review notes that hide decision-critical information.
- Missing explicit statement of out-of-scope items.

## Implementation Steps
1. Update `docs/roadmap.md` to reflect current milestone structure/status.
2. Add `docs/release-checklist.md` (or equivalent) with:
   - Runtime readiness checks
   - Test evidence
   - Security and architecture verification points
3. Add `docs/reviewer-guide.md` summarizing:
   - Key flows to verify manually
   - Internal API contract
   - Degraded OFF behavior expectations
4. Document explicit out-of-scope/future improvements section.
5. Cross-link all new docs from README and existing spec docs.

## Verification
- Consistency pass across `docs/*.md` and `tasks/runs/*.md`.
- Confirm all references point to existing files and current commands.
- Quick reviewer dry-run using only the review pack.

## Acceptance Criteria
- No major documentation contradictions remain.
- Reviewer can evaluate the project with minimal back-and-forth.
- Tradeoffs and scope boundaries are explicit.

## Review Notes
Pending implementation.
