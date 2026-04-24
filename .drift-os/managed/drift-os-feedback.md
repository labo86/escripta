# Drift-os Feedback

This managed file defines a lightweight shared convention for capturing `drift-os` improvement ideas discovered while working inside a downstream `code-repo`.

## Purpose

- Preserve `drift-os` workflow, installation, or usability improvement ideas in the downstream repository where they were discovered.
- Avoid losing cross-repository operating-system feedback in chat history or scattered local notes.
- Make later forwarding into the `drift-os` source repository simpler and more repeatable.

## When To Use

- Use this when work in a downstream repository reveals a missing workflow, a confusing convention, a repetitive friction point, or an improvement idea for `drift-os`.
- Prefer this lightweight capture even when the downstream repository will address the local issue immediately, if the insight could improve the shared OS model later.
- Do not create a separate planning layer for this; keep the record in the repository's normal task area, usually under `spec/inbox/`.

## Recommended Shape

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

## Guidance

- Keep the record lightweight by default; it is an improvement signal, not a mandatory full task spec.
- `source_repository` should identify where the idea was discovered.
- `source_task` should point to the local task, ticket, or file when that context matters.
- `area` should help later grouping, but use `other` when the idea does not fit the existing list.
- `forwarding_note` should briefly explain whether the idea is ready to send into `drift-os`, should wait for another example, or depends on a local experiment first.
- When a downstream chat shifts from local implementation into improving `drift-os` itself, prefer leaving this record, updating `spec/current-focus.md` if it exists, and opening a fresh chat for the new objective.
- A later `drift-os` session may collect these records manually or through repository-specific tooling, but the durable source of truth starts in the downstream repository where the insight was observed.
