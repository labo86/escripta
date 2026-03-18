#!/usr/bin/env bash

set -uo pipefail

# Parámetros
SOURCE_DIR="${ESCRIPTA_CURRENT_DIR}/var/build"
TARGET_DIR="${ESCRIPTA_CURRENT_DIR}/var/repo"


echo "Branch destino      : [$SOURCE_DIR]"
echo "Directorio destino  : [$TARGET_DIR]"


cp -rf \
   -v \
   $SOURCE_DIR/escripta.phar \
   $TARGET_DIR/escripta.phar
