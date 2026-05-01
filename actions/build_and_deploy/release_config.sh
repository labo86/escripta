#!/usr/bin/env bash

RELEASE_CONFIG_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"

export ESCRIPTA_CURRENT_DIR="$RELEASE_CONFIG_DIR"
export ESCRIPTA_PROJECT_DIR="$(cd "$RELEASE_CONFIG_DIR/../.." && pwd)"
export ESCRIPTA_RELEASE_BASE_URL="https://github.com/labo86/escripta/releases/latest/download"
export ESCRIPTA_RELEASE_GITHUB_REPOSITORY="labo86/escripta"
export ESCRIPTA_RELEASE_PHAR_FILENAME="escripta.phar"
export ESCRIPTA_RELEASE_SHA256_FILENAME="escripta.phar.sha256"
