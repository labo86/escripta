#!/usr/bin/env bash

set -euo pipefail

SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
REPOSITORY_DIR="$(cd "$SCRIPT_DIR/../.." && pwd)"

cd "$REPOSITORY_DIR"

# shellcheck disable=SC1091
source "$SCRIPT_DIR/release_config.sh"

php -d phar.readonly=0 actions/build_and_deploy/01_build/01_build.php
bash actions/build_and_deploy/01_build/02_test_build.sh

php app/vendor/bin/phpunit -c app/phpunit.xml.dist
php -d phar.readonly=0 builder/vendor/bin/phpunit -c builder/phpunit.xml.dist

php actions/build_and_deploy/02_github_deploy/01_publish_tagged_release.php
bash actions/build_and_deploy/02_github_deploy/02_upload_release_assets.sh

curl -fsS -L "$ESCRIPTA_RELEASE_BASE_URL/release.json" >/dev/null
curl -fsS -L "$ESCRIPTA_RELEASE_BASE_URL/ESCRIPTA_AGENTS.md" >/dev/null
