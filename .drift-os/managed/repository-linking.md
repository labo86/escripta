# Repository Linking

This managed file defines how a downstream `code-repo` should record related repositories.

## Use

- Keep explicit repository-reference records when another repository matters to local implementation.
- Prefer stable files such as `docs/repositories.md`, integration docs, or task files over implicit knowledge in chat.

## A repository reference should make clear

- repository name or stable identifier
- relative local path when available
- repository role or function
- why the repository matters locally
- whether the local repository reads from it, writes to it, integrates with it, or delivers alongside it

## Expectations

- Prefer sibling-repository layout and relative paths when related repositories coexist on the same machine.
- Keep the discovery path explicit from `AGENTS.md`.
- Update references when repository relationships change materially.
