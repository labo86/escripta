#!/usr/bin/env bash

set -uo pipefail

# Parámetros
TARGET_REPO="${ESCRIPTA_GITHUB_PAGES_GIT_REPO_URL}"
TARGET_BRANCH="${ESCRIPTA_GITHUB_PAGES_GIT_REPO_BRANCH}"
TARGET_DIR="${ESCRIPTA_CURRENT_DIR}/var/repo"
PRIVATE_KEY_FILE="${ESCRIPTA_GITHUB_PAGES_PRIVATE_KEY_FILENAME}"


echo "Repositorio destino : [$TARGET_REPO]"
echo "Branch destino      : [$TARGET_BRANCH]"
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

if [ -d "$TARGET_DIR" ]; then
    rm -rf "$TARGET_DIR" || {
        echo "ERROR: No se pudo eliminar el directorio $TARGET_DIR"
        exit 1
    }
fi

echo "Directorio eliminado."

echo "Clonando branch [$TARGET_BRANCH] del repositorio [$TARGET_REPO] en [$TARGET_DIR]..."

if ! GIT_SSH_COMMAND="$SSH_COMMAND" git clone \
    "$TARGET_REPO" \
    --branch "$TARGET_BRANCH" \
    --single-branch \
    --depth 1 \
    "$TARGET_DIR"
then
    echo "ERROR: git clone falló."
    exit 2
fi

echo "Clonado correctamente en $TARGET_DIR"

exit 0