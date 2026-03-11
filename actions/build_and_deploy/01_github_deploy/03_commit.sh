#!/usr/bin/env bash

set -uo pipefail

# Parámetros
TARGET_DIR="${ESCRIPTA_CURRENT_DIR}/var/repo"
PRIVATE_KEY_FILE="${ESCRIPTA_GITHUB_PAGES_PRIVATE_KEY_FILENAME}"
MESSAGE="escripta automatic commit"


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

echo "Actualizando repositorio..."

GIT_SSH_COMMAND="$SSH_COMMAND" git add -A || {
    echo "ERROR: git add falló."
    exit 2
}

if git diff --cached --quiet; then
    echo "No hay cambios para commit."
    exit 0
fi

GIT_SSH_COMMAND="$SSH_COMMAND" git commit -m "$MESSAGE" || {
    echo "ERROR: git commit falló."
    exit 3
}

echo "HECHO"
exit 0