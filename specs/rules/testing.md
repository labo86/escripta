# Testing Rules

## Goals

- Every meaningful behavior change should have test coverage.
- Tests should describe behavior, not implementation trivia.

## Rules

- Add or update tests in the same change as the production code.
- Prefer unit tests for deterministic behavior.
- Add integration tests only when boundaries with external systems matter.
- Cover normal path, important edge cases, and regression cases.
- Keep fixtures small and local to the test when possible.
- Do not delete existing coverage unless behavior is intentionally removed.
- If a change is not practical to test automatically, document manual verification in the spec.

## Acceptance

- A spec is not complete until its test plan has been executed or explicitly deferred with reason.
