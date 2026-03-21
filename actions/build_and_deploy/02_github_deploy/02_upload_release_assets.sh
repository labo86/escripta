#!/usr/bin/env bash

set -euo pipefail

require_env() {
    local name="$1"
    local value="${!name:-}"

    if [ -z "$value" ]; then
        echo "Falta la variable de entorno requerida [$name]." >&2
        exit 1
    fi

    printf '%s\n' "$value"
}

urlencode() {
    php -r 'echo rawurlencode($argv[1]);' "$1"
}

upload_asset() {
    local upload_url="$1"
    local asset_path="$2"
    local token="$3"
    local asset_name

    if [ ! -f "$asset_path" ]; then
        echo "No existe el asset [$asset_path]." >&2
        exit 1
    fi

    asset_name="$(basename "$asset_path")"

    curl -fsS \
        -X POST \
        -H "Accept: application/vnd.github+json" \
        -H "Authorization: Bearer $token" \
        -H "User-Agent: escripta-build-and-deploy" \
        -H "X-GitHub-Api-Version: 2022-11-28" \
        -H "Content-Type: application/octet-stream" \
        --data-binary @"$asset_path" \
        "${upload_url}?name=$(urlencode "$asset_name")" \
        >/dev/null

    echo "Asset publicado: [$asset_name]"
}

CURRENT_DIR="$(require_env ESCRIPTA_CURRENT_DIR)"
TOKEN="$(require_env ESCRIPTA_GITHUB_PAGES_TOKEN)"
CONTEXT_PATH="$CURRENT_DIR/var/build/github_release.env"

if [ ! -f "$CONTEXT_PATH" ]; then
    echo "No existe el contexto de release [$CONTEXT_PATH]." >&2
    exit 1
fi

# shellcheck disable=SC1090
source "$CONTEXT_PATH"

UPLOAD_URL="$(require_env ESCRIPTA_GITHUB_RELEASE_UPLOAD_URL)"
ASSET_PHAR="$(require_env ESCRIPTA_GITHUB_RELEASE_ASSET_PHAR)"
ASSET_SHA256="$(require_env ESCRIPTA_GITHUB_RELEASE_ASSET_SHA256)"
ASSET_MANIFEST="$(require_env ESCRIPTA_GITHUB_RELEASE_ASSET_MANIFEST)"
RELEASE_TAG="$(require_env ESCRIPTA_GITHUB_RELEASE_TAG)"

upload_asset "$UPLOAD_URL" "$ASSET_PHAR" "$TOKEN"
upload_asset "$UPLOAD_URL" "$ASSET_SHA256" "$TOKEN"
upload_asset "$UPLOAD_URL" "$ASSET_MANIFEST" "$TOKEN"

echo "Release publicado: [$RELEASE_TAG]"
