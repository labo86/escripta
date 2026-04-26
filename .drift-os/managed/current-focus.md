# Current Focus

This managed file defines the lightweight shared shape for `spec/current-focus.md` in a downstream `code-repo`.

## Purpose

- Preserve the latest active or next-recommended task without rereading many task files.
- Persist the immediate next step after a proposal, pause, handoff, or prioritization decision.
- Make it explicit when a clean new chat would be the better context boundary for the next piece of work.

## Recommended Shape

```md
# Current Focus

## Status

- mode: prioritize | continue-active | choose-next | summarize-previous | propose | execute | capture-idea | other
- state: active | paused | ready | blocked | done

## Focus

- task: <task id, title, or path>
- summary: <one-line statement of the current focus>

## Next Step

- recommended_action: <next concrete action>
- source: <proposal confirmed | prioritization | previous-task summary | active task update | mode switch | handoff>

## Chat Boundary

- new_chat_recommended: no
- reason: <optional brief reason when yes>
- starter_prompt: <optional short prompt for the next chat>

## Updated

- at: YYYY-MM-DD HH:MM:SS +/-ZZZZ
```

## Guidance

- Keep this file lightweight; it is a continuity marker, not a full task record.
- Update it when a task starts, pauses, changes hands, completes, or when prioritization selects a different next task.
- Update it when the user confirms a proposal whose implementation will continue later, so the approved next action survives beyond the current chat.
- Use `mode` to record the session routing choice that best matches the current operation.
- Prefer recording the actual current mode instead of leaving a stale mode after the chat changes purpose.
- Use `task` to point to the active task file when one exists, or to a stable local identifier when the work is still lightweight.
- Set `new_chat_recommended: yes` when the next step would benefit from a cleaner, narrower, or materially different context than the current chat.
- When recommending a new chat, keep the reason short and make `starter_prompt` specific enough that the next session can begin with minimal reconstruction.
- Use `other` only when the operation is real and bounded but does not fit the shared common modes cleanly.
- If no clean-chat recommendation is needed, keep `reason` and `starter_prompt` omitted or brief.
