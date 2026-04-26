# Task Spec

This managed file defines the expected shape of a durable task record in a downstream `code-repo`.

## Purpose

- Represent meaningful implementation work as a repository-native task record rather than as chat-only context.
- Preserve important ideas, follow-ups, and conversation outcomes in files that can be resumed later.
- Preserve enough structure for execution, review, decision logging, validation, and later maintenance.
- Support both direct tasks and parent tasks that split into subtasks while keeping one clear trail of responsibility.
- Allow lightweight idea capture so repositories can preserve resumable work without forcing full task expansion too early.

## Lightweight Idea Shape

```md
# <title>

## Summary

- Short idea or opportunity statement.

## State

- status: inbox

## Next Step

- Next possible step if this idea is later activated.
```

- Use this lighter shape by default for new inbox ideas unless the user asks for more detail or the agent judges that the work needs fuller structure immediately.
- Expand the record into the fuller task shape when implementation begins, when prioritization depends on clearer scope, or when the idea has enough complexity or risk that ambiguity would be costly.

## Lightweight Drift-os Feedback Shape

```md
# <title>

## Summary

- Short description of the `drift-os` improvement idea discovered in downstream work.

## State

- status: inbox
- kind: drift-os-feedback

## Drift-os Feedback

- source_repository:
- source_task:
- area: workflow | routing | continuity | task-shape | installation | extension | documentation | automation | other
- local_problem:
- proposed_improvement:
- forwarding_note:

## Next Step

- Decide whether to forward this into the `drift-os` source repository or keep gathering examples first.
```

- Use this shape when the downstream repository discovers an improvement opportunity for `drift-os` itself rather than only for local implementation.
- Keep it lightweight by default and expand it only if the feedback becomes an active `drift-os` task or needs stronger validation and scope control.

## Required Sections

```md
# <title>

## Summary

- What is changing.
- Why it is needed.

## Scope

- In scope:
- Out of scope:

## State

- status: inbox
- phase: clarification
- execution_mode:

## Task Shape

- task_id:
- task_scope: standalone
- parent_task:
- child_tasks:

## Technical Context

- Affected areas:
- Constraints:
- Assumptions:
- Dependencies:

## Validation Plan

- Expected checks:
- Success signal:

## Subtasks

- None yet.
- Or list the child tasks that refine this work.

## Next Step

- Next intended step:

## Decision Log

- YYYY-MM-DD HH:MM:SS +/-ZZZZ: Task created or clarified.
- YYYY-MM-DD HH:MM:SS +/-ZZZZ: Validation path agreed.
- YYYY-MM-DD HH:MM:SS +/-ZZZZ: Important design or scope decision recorded.
- YYYY-MM-DD HH:MM:SS +/-ZZZZ: Task split, narrowed, or redirected.
- YYYY-MM-DD HH:MM:SS +/-ZZZZ: Implementation completed or paused.

## Validation

- Pending.
- Record what was checked, what passed or failed, and what remains unverified.

## Tracking

- created_at:
- updated_at:
- started_at:
- completed_at:
```

## Guidance

- `status` may be adapted locally, but should usually make it easy to distinguish not-started, active, blocked, and completed work.
- A repository may use the same structure for idea capture in `spec/inbox/` and for active implementation in `spec/active/`, as long as the current state remains explicit.
- `execution_mode` should make the expected pace of the task visible, such as `direct`, `validate-then-implement`, or `staged-checkpoints`.
- Use `validate-then-implement` when the user should first approve the approach or validation target before implementation begins.
- Use `staged-checkpoints` for work with higher path-dependent risk, such as migrations, refactors, or architectural reshaping that should pause for confirmation between meaningful steps.
- By default, use full timestamps with timezone in both `Decision Log` and `Tracking`, such as `YYYY-MM-DD HH:MM:SS +/-ZZZZ`, when the repository is using the full task shape.
- When migrating older work into the newer model, do not invent exact historical timestamps just to satisfy the format.
- For migrated legacy work, simple or estimated timestamps are acceptable when they are clearly the best available approximation.
- In those migrations, earlier untracked work should be treated as work that was not recorded under this model rather than as missing exact history that must be reconstructed.
- Use date-only entries only when exact time is genuinely unavailable and that lower precision is acceptable locally.
- Do not force detailed logs, exact hours, or fine-grained timestamp churn for straightforward work that only needs resumable continuity.
- A repository may keep `drift-os` improvement records in the same `spec/inbox/` area as other ideas, as long as `kind: drift-os-feedback` keeps them distinguishable.
- `task_id` should give the task a stable local identifier when parent-child relationships matter.
- `task_scope` should usually be one of `standalone`, `parent`, or `child`.
- Use `standalone` when the task does not participate in a parent-child tree.
- Use `parent` when the task is the main record for a larger effort that may split into multiple child tasks.
- Use `child` when the task is one part of a larger parent task and should point back through `parent_task`.
- `child_tasks` may remain empty, list inline subtasks, or point to separate child task files depending on local workflow needs.
- Subtasks may stay inline inside the same task when they are small and tightly coupled, or they may become separate files when independent tracking, ownership, pacing, or validation is more useful.
- The parent task should remain the main source of truth for the overall objective, scope, and close-out status of the larger effort.
- Child tasks should preserve their own local execution details, decisions, and validation when those details would otherwise overload the parent task.
- Important global decisions should stay in the parent task, while local implementation decisions may live in the child task that owns that work.
- A parent task should not close until relevant child tasks are completed, intentionally dropped, or clearly removed from scope.
- Parent and child tasks do not need to share the same `execution_mode`; a cautious parent may contain faster child tasks when that split is useful.
- If another repository matters to the task, the record should point to that repository explicitly through stable names, paths, or references rather than relying on chat memory.
- The `Decision Log` should capture the important decisions made during execution, not only the final outcome.
- `created_at` and `updated_at` should preserve exact timestamp precision.
- `started_at` and `completed_at` should be filled when the task actually entered execution or completed, so duration can be reasoned about later.
- The `Validation` section should make explicit whether validation passed, failed, was partial, or remains blocked.
- When a repository keeps `spec/current-focus.md`, that continuity file should be updated alongside major state transitions so another session can resume work without inferring the latest focus from task timestamps alone.
