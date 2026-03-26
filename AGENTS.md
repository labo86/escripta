# AGENTS

This repository uses a spec-driven workflow under `spec/`.

## Structure

- `spec/inbox/`: new ideas and tasks, not started yet.
- `spec/active/`: tasks currently being worked on.
- `spec/done/`: completed tasks.

## Required Flow

1. Create a new spec in `spec/inbox/`.
2. Define problem, scope, success criteria, and checklist.
3. Move the spec to `spec/active/` when implementation starts.
4. Update the spec during execution (decisions, progress, blockers).
5. Move the spec to `spec/done/` when delivered and validated.

## Agent Rules

- Do not start implementation without a spec in `spec/inbox/` or `spec/active/`.
- Keep one spec per task.
- If task scope changes, document the change in the same spec.
- At close-out, leave minimum evidence: what changed and how it was validated.
- Every spec must include:
  - `Created At` (YYYY-MM-DD HH:MM:SS)
  - `Last Updated At` (YYYY-MM-DD HH:MM:SS)
  - `Template Version`
  - `Change Log`
- `Last Updated At` must be updated on every meaningful spec change.
- `Change Log` must record relevant updates and additions in chronological order.

## Recommended Spec Template

```md
# <title>

Created At: YYYY-MM-DD HH:MM:SS
Last Updated At: YYYY-MM-DD HH:MM:SS
Template Version: v1

## Context
## Objective
## Scope
## Out of Scope
## Success Criteria
## Plan
- [ ] Step 1
- [ ] Step 2
## Validation
## Result
## Change Log
- YYYY-MM-DD HH:MM:SS: Spec created.
- YYYY-MM-DD HH:MM:SS: <what changed or was added>
```
