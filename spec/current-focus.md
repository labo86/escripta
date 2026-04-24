# Current Focus

Last Updated At: 2026-04-24 19:41:00

## Active Task

- `spec/done/release_installs_agent_guide.md`

## State

- status: done
- phase: delivered

## Next Step

- No immediate follow-up required for `release_installs_agent_guide`.

## Notes

- New idea captured: release/target install support for `ESCRIPTA_AGENTS.md` plus an agent-readable hint for `AGENTS.md` integration.
- User selected `.escripta/ESCRIPTA_AGENTS.md` as the target location.
- Implemented `--install-agent-guide`, `.escripta/AGENTS_HINT.md`, PHAR resource packaging, and release asset publication.
- Validation passed with app PHPUnit and builder PHPUnit using `phar.readonly=0`.
- User requested generated files include the Escripta version number.
- Generated guide and hint files now include the Escripta version; the release guide asset includes the release version.
- Final validation passed with app PHPUnit and builder PHPUnit using `phar.readonly=0`.
