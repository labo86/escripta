# Operational Processes

This managed file defines how a downstream `code-repo` should represent reusable operational processes.

## Intent

- Prefer durable, inspectable process definitions over one-off command sequences.
- Make recurring operational work readable by both agents and scripts.
- Avoid tying the convention to a single process type such as build or deploy.

## When To Use

- Use this convention when a repository has a repeatable workflow such as build, deploy, bootstrap, dependency installation, release publishing, reporting, or maintenance.
- Do not force a formal process folder for trivial one-line tasks that do not benefit from reuse or tracking.

## Recommended Shape

- A process should have its own folder named after the action it performs.
- A process may contain one or more named steps.
- Step folders may use numeric prefixes when execution order matters.
- A step may contain one or more scripts or documents.
- Supporting configuration or environment reference files may live inside the process folder when they belong to that process.

## Example Pattern

```text
<process_root>/
  <process_name>/
    config.<ext>
    <env_reference>.md
    01_<step_name>/
      01_<script>.<ext>
      02_<script>.<ext>
    02_<step_name>/
      01_<script>.<ext>
```

## Guidance

- Prefer descriptive process names over vague buckets.
- Prefer explicit step boundaries when the workflow has multiple phases.
- Prefer numbered step and script prefixes when order should be unambiguous to agents and scripts.
- Document inputs, outputs, required environment, and validation expectations close to the process when that context is not already obvious.
- If the repository already has an established root for reusable operational processes, continue using it consistently instead of inventing parallel roots.

## Script Design

- Prefer simple scripts, especially `sh` or `bash`, when shell is an appropriate fit for the task.
- Prefer scripts that can be followed top to bottom without forcing the reader to jump across many helper functions.
- If a script contains dense or non-obvious commands, document the command or the surrounding step so the intent stays clear.
- Avoid overgrown scripts. If a script becomes too long or takes on multiple responsibilities, split it into smaller step scripts or separate process steps.
- Prefer resolving support paths relative to the script location when working with temporary files, local config, or process-owned artifacts.
- Prefer portable shell patterns and minimize platform-specific assumptions so the process can be adapted across Windows, Linux, and macOS as much as practical.
- If compatibility is known to be limited or poor on some platforms, document that limitation explicitly near the process or script.
- Use standard exit codes and clear error output so supervisor scripts or higher-level process runners can reliably detect failure.
- Failures should be explicit and machine-detectable rather than hidden behind silent fallbacks.
