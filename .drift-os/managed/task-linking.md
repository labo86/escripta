# Task Linking

This managed file defines how a local task may point to related work in another `code-repo`.

## Use

- Use task linking when local work depends on, blocks, parallels, or hands off to work in another repository.
- Keep one source of truth for the local task and one source of truth for the external task.

## Recommended Fields

- `related_repo`
- `related_repo_path`
- `related_repo_role`
- `related_task_ref`
- `relationship_type`
- `external_status`
- `sync_note`
- `last_sync_at`

## Expectations

- Summarize only the external detail needed to continue local work safely.
- Do not duplicate the full external decision log or validation trail.
- Update the local task when the external task changes materially enough to affect local assumptions.
