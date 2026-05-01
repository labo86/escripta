#!/usr/bin/env bash

set -euo pipefail

usage() {
    cat >&2 <<'EOF'
Uso:
  actions/build_and_deploy/00_release.sh <tag|patch|minor|major>

Ejemplo:
  actions/build_and_deploy/00_release.sh 4.1.2
  actions/build_and_deploy/00_release.sh patch

Requisitos:
  - Ejecutar desde cualquier ruta dentro del repo.
  - Tener permisos para pushear tags a origin.

Si recibe patch, minor o major, calcula el proximo tag desde el ultimo tag
semver X.Y.Z local o remoto. Luego crea y pushea el tag. GitHub Actions
construye, testea y publica los assets del release.
EOF
}

require_clean_worktree() {
    if [ -n "$(git status --porcelain)" ]; then
        echo "El arbol de trabajo tiene cambios. Publica o limpia esos cambios antes del release." >&2
        git status --short >&2
        exit 1
    fi
}

require_tag_available() {
    local tag="$1"

    if git rev-parse -q --verify "refs/tags/$tag" >/dev/null; then
        echo "El tag local [$tag] ya existe." >&2
        exit 1
    fi

    if git ls-remote --exit-code --tags origin "$tag" >/dev/null 2>&1; then
        echo "El tag remoto [$tag] ya existe en origin." >&2
        exit 1
    fi
}

latest_semver_tag() {
    {
        git tag --list
        git ls-remote --tags origin 2>/dev/null | sed -E 's#^[[:xdigit:]]+[[:space:]]+refs/tags/##; s#\\^\\{\\}$##'
    } | php -r '
        $latest = [0, 0, 0];
        while (($line = fgets(STDIN)) !== false) {
            $tag = trim($line);
            if (!preg_match("/^([0-9]+)\\.([0-9]+)\\.([0-9]+)$/", $tag, $matches)) {
                continue;
            }

            $version = [(int) $matches[1], (int) $matches[2], (int) $matches[3]];
            if ($version > $latest) {
                $latest = $version;
            }
        }

        echo implode(".", $latest) . PHP_EOL;
    '
}

bump_semver_tag() {
    local tag="$1"
    local bump="$2"

    php -r '
        $tag = $argv[1];
        $bump = $argv[2];

        if (!preg_match("/^([0-9]+)\\.([0-9]+)\\.([0-9]+)$/", $tag, $matches)) {
            fwrite(STDERR, "No se pudo interpretar el tag semver [$tag].\n");
            exit(1);
        }

        $major = (int) $matches[1];
        $minor = (int) $matches[2];
        $patch = (int) $matches[3];

        if ($bump === "major") {
            $major++;
            $minor = 0;
            $patch = 0;
        } elseif ($bump === "minor") {
            $minor++;
            $patch = 0;
        } elseif ($bump === "patch") {
            $patch++;
        } else {
            fwrite(STDERR, "Tipo de version invalido [$bump]. Usa patch, minor o major.\n");
            exit(1);
        }

        echo "$major.$minor.$patch" . PHP_EOL;
    ' "$tag" "$bump"
}

resolve_release_tag() {
    local requested="$1"
    local latest_tag

    case "$requested" in
        patch|minor|major)
            latest_tag="$(latest_semver_tag)"
            bump_semver_tag "$latest_tag" "$requested"
            ;;
        *)
            printf '%s\n' "$requested"
            ;;
    esac
}

handle_error() {
    local exit_code="$?"

    if [ "$RELEASE_TAG_CREATED" = "1" ] && [ "$RELEASE_TAG_PUSHED" = "0" ]; then
        git tag -d "$RELEASE_TAG" >/dev/null 2>&1 || true
        echo "Tag local eliminado tras fallo: [$RELEASE_TAG]" >&2
    fi

    exit "$exit_code"
}

if [ "${1:-}" = "" ] || [ "${1:-}" = "-h" ] || [ "${1:-}" = "--help" ]; then
    usage
    exit 1
fi

RELEASE_TAG_CREATED=0
RELEASE_TAG_PUSHED=0
SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
REPOSITORY_DIR="$(cd "$SCRIPT_DIR/../.." && pwd)"

cd "$REPOSITORY_DIR"

RELEASE_TAG="$(resolve_release_tag "$1")"

trap handle_error ERR

require_clean_worktree
require_tag_available "$RELEASE_TAG"

git tag "$RELEASE_TAG"
RELEASE_TAG_CREATED=1

git push origin "$RELEASE_TAG"
RELEASE_TAG_PUSHED=1

echo "Tag publicado: [$RELEASE_TAG]"
echo "GitHub Actions publicara el release desde ese tag."
