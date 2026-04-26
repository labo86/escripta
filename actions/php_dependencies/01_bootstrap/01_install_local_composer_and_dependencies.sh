#!/usr/bin/env bash
set -euo pipefail

ROOT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")/../../.." && pwd)"
COMPOSER_PHAR="$ROOT_DIR/composer.phar"

download_composer() {
    local installer expected_signature actual_signature installer_path

    installer_path="$(mktemp)"

    echo "Downloading Composer installer..."
    curl -fsSL https://getcomposer.org/installer -o "$installer_path"

    expected_signature="$(curl -fsSL https://composer.github.io/installer.sig)"
    actual_signature="$(php -r "echo hash_file('sha384', '$installer_path');")"

    if [[ "$expected_signature" != "$actual_signature" ]]; then
        rm -f "$installer_path"
        echo "Composer installer signature mismatch." >&2
        exit 1
    fi

    php "$installer_path" --install-dir="$ROOT_DIR" --filename="$(basename "$COMPOSER_PHAR")"
    rm -f "$installer_path"
}

ensure_composer() {
    if [[ -f "$COMPOSER_PHAR" ]]; then
        echo "Using existing local Composer at $COMPOSER_PHAR"
        return
    fi

    download_composer
}

run_install() {
    local target="$1"

    echo
    echo "Installing dependencies in $target"
    php "$COMPOSER_PHAR" install \
        --working-dir="$ROOT_DIR/$target" \
        --no-interaction \
        --prefer-dist
}

ensure_composer
php "$COMPOSER_PHAR" --version
run_install "app"
run_install "builder"
