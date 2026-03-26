# PHP Rules

## Goals

- Keep changes small and explicit.
- Prefer readable code over clever abstractions.
- Preserve backward compatibility unless the spec says otherwise.

## Rules

- Follow the existing project style before introducing a new pattern.
- Reuse current services and utilities before adding new helpers.
- Keep public APIs stable unless the spec explicitly approves a breaking change.
- Prefer extracting a small private helper instead of duplicating behavior.
- Do not mix unrelated refactors into a feature change.
- Validate edge cases that can be inferred from current behavior.
- Keep comments short and only where they reduce real ambiguity.

## Change Boundaries

- Touch the minimum number of files needed for the spec.
- Avoid changing runtime configuration unless the spec requires it.
- Avoid hidden side effects in constructors or global state.
