# Specs

Base directory for spec-driven development.

## Structure

- `inbox/`: raw ideas, requests, and early notes that are not yet refined.
- `active/`: approved specs currently being implemented.
- `done/`: completed specs kept for traceability.
- `rules/`: coding and delivery rules that every active spec should follow.
- `commands.md`: slash-command aliases used as workflow shortcuts in this repo.
- `templates/`: reusable templates for new specs.

## Suggested flow

1. Create a spec in `inbox/` from `templates/feature-spec.md`.
2. Refine scope, constraints, and acceptance criteria.
3. Move the spec to `active/` when implementation starts.
4. Move the spec to `done/` once code and tests are complete.

## Naming

Use names like:

- `YYYY-MM-DD-short-name.md`
- `auth-config-by-path.md`
- `writeinfiles-underscore-suffix.md`

## Minimum quality bar

Every active spec should define:

- problem statement
- scope
- out of scope
- acceptance criteria
- implementation notes
- test plan

Every active spec should also comply with the rules in `rules/`.

Slash-style workflow aliases are documented in `commands.md`.
