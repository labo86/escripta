# Spec Commands

These are repo-level workflow aliases for working with specs.

They are not native CLI commands configured by the repository itself. They are shorthand instructions to interpret during collaboration.

## Commands

- `/inbox`: create a new draft spec in `specs/inbox/` from `specs/templates/feature-spec.md`.
- `/active`: move an existing spec into `specs/active/` and update its status for implementation.
- `/done`: move a finished spec into `specs/done/` and record final test status.
- `/rules`: open or update the coding rules under `specs/rules/`.
- `/spec <name>`: create or update a spec with the given name.

## `/inbox`

Expected behavior:

- create a new markdown spec draft
- use the feature spec template
- choose a short file name
- leave status as `Draft` unless told otherwise

Suggested filename format:

- `YYYY-MM-DD-short-name.md`

Example:

- `/inbox config-local-by-path`

Expected result:

- `specs/inbox/2026-03-19-config-local-by-path.md`

## Notes

- If the command includes a name, use it in the filename.
- If no name is given, infer one from the request.
- If the user wants true executable slash commands at tool level, that must be supported by the client, not by files in this repo.
