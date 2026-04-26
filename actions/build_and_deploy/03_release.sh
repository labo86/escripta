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
  - Tener app/vendor y builder/vendor instalados.
  - Tener acceso a OnePassword para escripta_github_release.
  - Tener permisos para pushear tags a origin.
EOF
}

require_file() {
    local path="$1"

    if [ ! -f "$path" ]; then
        echo "Falta [$path]. Ejecuta primero el bootstrap de dependencias si corresponde." >&2
        exit 1
    fi
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

cleanup_generated_config() {
    rm -rf "$REPOSITORY_DIR/config.gen" \
        "$REPOSITORY_DIR/escripta_env.sh" \
        "$REPOSITORY_DIR/escripta_env_vars.md"
}

handle_error() {
    local exit_code="$?"

    cleanup_generated_config

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

require_file "app/vendor/bin/phpunit"
require_file "builder/vendor/bin/phpunit"
require_clean_worktree
require_tag_available "$RELEASE_TAG"

git tag "$RELEASE_TAG"
RELEASE_TAG_CREATED=1

php actions/build_and_deploy/config.php

# shellcheck disable=SC1091
source "$REPOSITORY_DIR/escripta_env.sh"

php -d phar.readonly=0 actions/build_and_deploy/01_build/01_build.php
bash actions/build_and_deploy/01_build/02_test_build.sh

php app/vendor/bin/phpunit -c app/phpunit.xml.dist
php -d phar.readonly=0 builder/vendor/bin/phpunit -c builder/phpunit.xml.dist

git push origin "$RELEASE_TAG"
RELEASE_TAG_PUSHED=1

php actions/build_and_deploy/02_github_deploy/01_publish_tagged_release.php
bash actions/build_and_deploy/02_github_deploy/02_upload_release_assets.sh

curl -fsS -L "$ESCRIPTA_RELEASE_BASE_URL/release.json" >/dev/null
curl -fsS -L "$ESCRIPTA_RELEASE_BASE_URL/ESCRIPTA_AGENTS.md" >/dev/null

cleanup_generated_config

echo "Release publicado y verificado: [$RELEASE_TAG]"
