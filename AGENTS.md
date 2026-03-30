# AGENTS

This repository uses a spec-driven workflow under `spec/`.

## Read First

1. This file
2. `spec/active/`
3. `docs/`
4. `docs/integracion_escripta_para_otro_repo.md`
5. `.drift-os/install.yaml`

## Project Context

- `AGENTS.md` is the operating entrypoint, not the only source of project knowledge.
- Read active specs in `spec/active/` before changing in-flight work.
- Read `docs/` for repository-specific context, integrations, and operational notes.
- Read `docs/integracion_escripta_para_otro_repo.md` when the task touches how `escripta` is used from or connected to other repositories.
- Treat `.drift-os/install.yaml` as installation metadata, not as project domain documentation.

## Structure

- `spec/inbox/`: new ideas and tasks, not started yet.
- `spec/active/`: tasks currently being worked on.
- `spec/done/`: completed tasks.

## Required Flow

1. Create a new spec in `spec/inbox/`.
2. Define problem, scope, success criteria, and checklist.
3. Move the spec to `spec/active/` when implementation starts.
4. Update the spec during execution with decisions, progress, and blockers.
5. Move the spec to `spec/done/` when delivered and validated.

## Agent Rules

- Do not start implementation without a spec in `spec/inbox/` or `spec/active/`.
- Keep one spec per task.
- If task scope changes, document the change in the same spec.
- Do not move a task to `spec/done/` without validation evidence. If validation is still pending or blocked, keep the spec in `spec/active/` and document the blocker.
- A task may be moved to `spec/done/` without validation evidence only with explicit user consent, and that exception must be recorded in the spec `Change Log` and `Validation` section.
- At close-out, leave minimum evidence: what changed and how it was validated.
- Prefer reusable operational workflows under `actions/` instead of ad hoc command sequences when the repository already defines those flows.
- Follow the repository's established operational process conventions when proposing new reusable workflows.
- If `composer` is not available globally, use the repository's defined bootstrap flow for local Composer and dependency installation before declaring Composer-based validation blocked.
- Before implementing a non-trivial design decision, present the plan and wait for explicit user validation.

## Spec Requirements

- Every spec must include:
  - `Created At` (YYYY-MM-DD HH:MM:SS)
  - `Last Updated At` (YYYY-MM-DD HH:MM:SS)
  - `Template Version`
  - `Change Log`
- `Last Updated At` must be updated on every meaningful spec change.
- `Change Log` must record relevant updates and additions in chronological order.
- `Change Log` must explicitly record state transitions, including moves from `spec/inbox/` to `spec/active/` and from `spec/active/` to `spec/done/`.

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

## Drift OS Installation

- This repository is intended to operate as a `drift-os` `code-repo` installation.
- The installation manifest should live at `.drift-os/install.yaml`.
- Managed `drift-os` files should be updated through the governed `drift-os` update flow, not by silent drift.

## Local Adaptation

- The spec-driven workflow above is shared with the `drift-os` `code-repo` profile.
- Exact path choices under `actions/` remain repository-local conventions.
- The Composer bootstrap path remains a local repository detail even when the fallback behavior is shared through the `php-composer` extension.
