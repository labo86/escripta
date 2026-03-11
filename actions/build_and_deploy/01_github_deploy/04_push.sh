#!/usr/bin/env bash

set -uo pipefail

# Parámetros
TARGET_DIR="${ESCRIPTA_CURRENT_DIR}/var/repo"
PRIVATE_KEY_FILE="${ESCRIPTA_GITHUB_PAGES_PRIVATE_KEY_FILENAME}"


echo "Directorio destino  : [$TARGET_DIR]"
echo "Clave privada       : [$PRIVATE_KEY_FILE]"

SSH_COMMAND="ssh \
  -o IdentitiesOnly=yes \
  -o IdentityFile=$PRIVATE_KEY_FILE \
  -o IdentityAgent=none \
  -F /dev/null \
  -o StrictHostKeyChecking=no \
  -o UserKnownHostsFile=/dev/null \
  -p 22"

cd $TARGET_DIR;

echo "Push a repositorio..."


GIT_SSH_COMMAND="$SSH_COMMAND" git push || {
    echo "ERROR: git push falló."
    exit 4
}

echo "HECHO"
exit 0