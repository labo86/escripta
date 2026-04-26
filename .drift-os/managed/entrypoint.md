# Drift OS Entrypoint

This managed file is the shared `drift-os` entrypoint for a downstream `code-repo`.

`Drift-os by Labo86 | os 0.1.0 | build 2026-04-24 | commit 5656a31`

- Keep this line brief.
- Populate it from the installed or updated source state so it reflects the actual applied Drift-os version, build date, and source commit.

## Response Contract

- Treat the following response rules as mandatory operating behavior, not as optional style preference.
- Respond in Spanish by default unless the user clearly asks for another language.
- Keep responses concise, dense, and practical; prefer the shortest answer that still moves the work forward.
- Use verse-style numbering when it improves reference, scanning, or precise correction.
- In the first substantive response, include at least one actionable local repository link when such an artifact exists.
- When `spec/current-focus.md` exists and the session is starting or resuming without a narrower explicit target, treat it as the default first continuity link.
- Surface the most useful repository link or next actionable artifact as early as possible when one exists.
- If the relevant artifact does not exist yet, say that plainly and propose creating it instead of filling the opening response with broad explanation.
- Do not open with a greeting-only sentence or any other sentence that delays operational guidance.
- Offer one primary suggested next move by default; offer multiple branches only when the user explicitly asks for options or the difference between branches has important consequences.
- Prefer direct operational statements over long narrative framing, especially at session start.
- Do not narrate obvious execution steps or announce each upcoming command when that narration does not change the next action, unblock a decision, or record a real blocker.
- During execution, prefer fewer updates with actual state change over repeated progress filler such as `voy a...`, `ahora...`, or recaps of work the user did not need to approve.

## Read Next

1. `.drift-os/managed/workflow.md`
2. Repository-specific docs referenced by the local `AGENTS.md`
3. Determine the session mode from the user's request, or ask a small routing question only when the intended operation is genuinely unclear
4. `spec/current-focus.md` when it exists and the session needs current continuity, the latest recommended next step, or a possible clean-chat handoff
5. `spec/priorities.md` when the session needs to choose or activate work
6. `.drift-os/managed/current-focus.md` only when creating or normalizing `spec/current-focus.md`
7. `.drift-os/managed/task-spec.md` only when creating, expanding, or updating task records
8. `.drift-os/managed/repository-linking.md` only when another repository matters
9. `.drift-os/managed/task-linking.md` only when cross-repository task references matter
10. `.drift-os/managed/drift-os-feedback.md` only when the session discovers an improvement idea for the OS itself while working in the repository
11. `.drift-os/managed/operational-processes.md` only when a reusable operational process matters
12. Any active extension files listed in `.drift-os/install.yaml` when relevant
13. `.drift-os/install.yaml`

## Core Rules

- Keep important ideas and follow-ups in repository files, not only in chat.
- Use stable task states such as `spec/inbox/`, `spec/active/`, and `spec/done/` when the repository supports them.
- Read local repository docs before changing architecture, integrations, or workflow.
- Keep repository and task links explicit when another repository matters.
- Treat `.drift-os/install.yaml` as installation metadata, not project domain context.
- When extensions are active, read their managed files from the paths listed in `.drift-os/install.yaml`.
- Do not load all task files by default; read the smallest set of files needed for the current operation.
- Prefer explicit continuity and prioritization files over repeatedly inferring repository state from many task files.
- If the session ends with a confirmed proposal, prioritization decision, pause, or handoff, persist the recommended next step in repository files before relying on chat history.
- When the next unit of work would benefit from a fresh context, recommend a new chat explicitly and leave a short repository-native handoff.
