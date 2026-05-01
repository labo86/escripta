#!/usr/bin/env bash

set -euo pipefail

usage() {
    cat >&2 <<'EOF'
Uso:
  actions/build_and_deploy/03_release.sh <tag>

Ejemplo:
  actions/build_and_deploy/03_release.sh 4.1.2

Requisitos:
  - Ejecutar desde cualquier ruta dentro del repo.
  - Tener permisos para pushear tags a origin.

Este comando local solo crea y pushea el tag. GitHub Actions construye,
testea y publica los assets del release.
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

RELEASE_TAG="$1"
RELEASE_TAG_CREATED=0
RELEASE_TAG_PUSHED=0
SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
REPOSITORY_DIR="$(cd "$SCRIPT_DIR/../.." && pwd)"

cd "$REPOSITORY_DIR"

trap handle_error ERR

require_clean_worktree
require_tag_available "$RELEASE_TAG"

git tag "$RELEASE_TAG"
RELEASE_TAG_CREATED=1

git push origin "$RELEASE_TAG"
RELEASE_TAG_PUSHED=1

echo "Tag publicado: [$RELEASE_TAG]"
echo "GitHub Actions publicara el release desde ese tag."
