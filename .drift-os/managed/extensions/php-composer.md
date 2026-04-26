# PHP Composer

This managed file defines shared `drift-os` guidance for repositories that use PHP and Composer.

## Rules

- If the repository uses Composer-based validation, prefer a reusable operational workflow over ad hoc dependency commands.
- If `composer` is not available globally, use the repository's defined bootstrap flow for local Composer and dependency installation before declaring Composer-based validation blocked.
- Repository-specific bootstrap paths or scripts should be documented locally, but the fallback pattern should stay consistent across PHP repositories.
- If Composer validation is still blocked after the repository's bootstrap flow is attempted, document the blocker explicitly in the active task spec or equivalent task record.
