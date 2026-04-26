# Workflow

This managed file defines the shared `drift-os` workflow for a downstream `code-repo`.

## Core Flow

1. Capture new ideas and follow-ups in `spec/inbox/` when they should survive beyond the current chat.
2. At session start, determine the operating mode from the user's request or, only when needed, from a small routing question such as prioritize work, continue active work, choose the next task, summarize the previous task, propose before implementing, execute a prioritized task, capture a new idea, or handle some other bounded operation.
3. In the first substantive response of a session, follow the response contract categorically: default to Spanish, keep the answer concise and practical, and put an actionable local repository link early when one exists.
4. When `spec/current-focus.md` exists and the user has not named a narrower target yet, use it as the default first continuity artifact instead of starting with a generic greeting or broad option list.
5. If no relevant repository artifact exists yet, say that directly in one short line and propose creating the smallest useful artifact instead of opening with broad narrative explanation.
6. When referenceability matters, prefer verse-style numbering so the user can point to exact lines of guidance or correction.
7. Prefer one clear suggested next move over a long menu of possibilities unless the user explicitly asks for a broader comparison or materially different branches need to be surfaced.
8. Do not open ambiguous fresh-chat replies with a greeting-only line, a generic invitation to describe the task, or a list of multiple routes when one recommended linked next step is already available.
9. Prefer brief idea capture by default, expanding the record only when the user asks for detail or when the agent judges that more structure is needed for complexity, risk, dependency handling, or handoff quality.
10. During execution, send a progress update only when it changes the user's understanding of state, reveals a blocker, records a meaningful result, or needs a decision; do not narrate each intended command.
11. Prefer one short update after a meaningful step over a chain of before-and-after narration around the same action.
12. When choosing what to activate, read `spec/priorities.md` if it exists instead of rereading the full inbox by default.
13. If the repository needs to activate work and `spec/priorities.md` does not exist, read the inbox, generate a prioritization record, save it, and then choose the task to activate.
14. When a new inbox item is added later, update the prioritization record incrementally instead of recomputing it from scratch unless the existing record is clearly stale.
15. When the request clearly implies one of those modes, assume it and proceed; ask only when the operation is genuinely unclear, and prefer offering a suggested mode when clarification is needed.
16. Use `other` as a fallback mode for bounded work that sits outside the common workflow, instead of forcing a misleading routing choice.
17. Move the chosen task into `spec/active/` when implementation starts.
18. Update the active task during execution only as much as needed to preserve meaningful decisions, blockers, scope changes, and validation status.
19. Record validation evidence close to the work or in another stable repository location.
20. Update `spec/current-focus.md` when a task starts, pauses, changes hands, completes, or when prioritization, summary, mode changes, or a confirmed proposal changes the recommended next step.
21. If a proposal is confirmed but implementation will continue later, persist that approved next action before ending the session.
22. When the current chat needs to switch into a materially different workflow mode, persist the smallest useful handoff in repository files and recommend a fresh chat rather than carrying unrelated work in the same thread.
23. When the next step would be clearer in a fresh and narrower thread, recommend a new chat and persist that recommendation in `spec/current-focus.md` together with a short handoff and starter prompt.
24. Capture downstream `drift-os` improvement ideas in the repository's normal task area, usually `spec/inbox/`, using the shared feedback convention instead of scattering them across ad hoc notes.
25. Move the task to `spec/done/` only when delivery and validation are complete, or when an explicit user-approved exception is recorded.

## Shared State Model

- `spec/inbox/`: new ideas, deferred follow-ups, and not-yet-started tasks
- `spec/active/`: tasks currently in progress
- `spec/done/`: completed, closed, or intentionally retired tasks
- `spec/priorities.md`: lightweight prioritization index for choosing what to activate without scanning the full inbox repeatedly
- `spec/current-focus.md`: lightweight continuity record with the latest task worked, its state, and the next recommended step
- `spec/inbox/` `drift-os` feedback items: lightweight downstream records for improvements that should later flow back into the OS source repository

## Expectations

- Keep important ideas and conversation outcomes in repository files instead of relying on chat memory.
- Do not read all active or inbox task files by default; start from `spec/current-focus.md`, `spec/priorities.md`, and then the task files directly needed by the operation.
- Treat the response contract as an execution rule for fresh chats, especially before any broad repository scan or long explanation.
- Treat concise progress reporting as part of the execution rule, not only as startup polish; silence is preferred over low-value narration.
- If `spec/current-focus.md` exists and the user starts broadly, prefer linking and using that file before presenting generic option menus.
- Read active task files before changing in-flight work when those files are directly relevant.
- If validation is still pending or blocked, keep the task active and record the blocker.
- Prefer explicit operational workflows over ad hoc command sequences when the repository already defines them.
- Before implementing a non-trivial design decision, present the intended change and wait for explicit approval.
- Detailed logs, exact hours, and fine-grained timestamps are optional by default; use them when the repository or task genuinely benefits from that precision.
- A routing choice at session start should reduce ambiguity and broad repository reads, not add a new heavyweight planning layer.
- Prefer one primary workflow mode per chat when practical, because narrower threads reduce token cost and make continuity files more reliable.
- A clean-chat recommendation should be used when it materially improves context quality, not as a default after every pause.
