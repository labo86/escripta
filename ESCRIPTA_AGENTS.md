# Escripta Guide for Agents

This file is the short operational guide for agents writing or reviewing scripts that consume Escripta configuration.

## Core Flow

1. A repository stores source config under `.escripta/configs/`.
2. A PHP action calls `Escripta::fetchConfig([...])`.
3. Escripta writes flattened config files into `config.gen/` in the action directory.
4. Escripta writes `escripta_env.sh` in the same action directory.
5. Escripta writes `escripta_env_vars.md`, a value-free manifest of generated variables.
6. Shell scripts should `source ./escripta_env.sh` before reading `ESCRIPTA_*` variables.

## Naming Contract

The most reliable way to infer a variable is:

```text
ESCRIPTA_ + UPPERCASE(<fetchConfig block> + "_" + <flattened config key>)
```

Example:

```php
Escripta::fetchConfig([
    'release' => [
        Escripta::getConfigLocal('release'),
    ],
]);
```

If `.escripta/configs/release.ini` contains:

```ini
base_url=https://example.test/releases/latest/download
phar_filename=escripta.phar
```

Expected variables:

```text
ESCRIPTA_RELEASE_BASE_URL
ESCRIPTA_RELEASE_PHAR_FILENAME
```

## Flattening Rules

- The top-level key in `fetchConfig` becomes the final generated-file prefix.
- `.ini` sections become extra key segments joined with `_`.
- Spaces in generated file names become `_`.
- Environment variable names remove non-alphanumeric runs, collapse them to `_`, trim extra `_`, uppercase the result, and add `ESCRIPTA_`.
- If the generated name starts with a number, Escripta prefixes it with `N_`.
- A file or directory segment that starts with `_` does not contribute that segment as a prefix.
- `_.ini` places keys at the current directory level.

## Multiline Values

If a generated config file contains a newline, Escripta does not export the content directly. It exports a path variable ending in `_FILENAME`.

Example:

```text
config.gen/app_private_key
```

If that file is multiline:

```text
ESCRIPTA_APP_PRIVATE_KEY_FILENAME
```

Use this pattern for private keys, certificates, SSH identities, and other multiline secrets:

```bash
source ./escripta_env.sh
ssh -i "$ESCRIPTA_APP_PRIVATE_KEY_FILENAME" user@host
```

## Generated Artifacts

After `fetchConfig`, expect these artifacts in the action directory:

- `config.gen/`: generated config files, usually sensitive and not meant to be read manually unless debugging.
- `escripta_env.sh`: source this file from shell scripts.
- `escripta_env_vars.md`: generated manifest listing available variables without values.

`escripta_env.sh` also exports:

- `ESCRIPTA_CURRENT_DIR`: directory where `escripta_env.sh` lives.
- `ESCRIPTA_PROJECT_DIR`: project base path relative to `ESCRIPTA_CURRENT_DIR`.

## Script Checklist

- Source `escripta_env.sh` before using `ESCRIPTA_*`.
- Prefer `ESCRIPTA_*` variables over hardcoded paths, hosts, users, or release filenames.
- Check `escripta_env_vars.md` first when unsure which variables exist.
- Use `*_FILENAME` for keys, certificates, SSH identities, and any multiline value.
- Derive names from `fetchConfig` block plus flattened key, not from guesses in script names.
- Remember that `_`-prefixed files or folders are organizational and do not add name segments.

For fuller examples and integration notes, see `docs/integracion_escripta_para_otro_repo.md`.
