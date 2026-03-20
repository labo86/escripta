#!/usr/bin/env bash

set -uo pipefail

# Parámetros
SOURCE_DIR="${ESCRIPTA_CURRENT_DIR}/var/build"
TARGET_DIR="${ESCRIPTA_CURRENT_DIR}/var/repo"
PHAR_FILENAME="${ESCRIPTA_RELEASE_PHAR_FILENAME:-escripta.phar}"
SHA256_FILENAME="${ESCRIPTA_RELEASE_SHA256_FILENAME:-escripta.phar.sha256}"


echo "Branch destino      : [$SOURCE_DIR]"
echo "Directorio destino  : [$TARGET_DIR]"


cp -rf \
   -v \
   "$SOURCE_DIR/$PHAR_FILENAME" \
   "$TARGET_DIR/$PHAR_FILENAME"

cp -rf \
   -v \
   "$SOURCE_DIR/$SHA256_FILENAME" \
   "$TARGET_DIR/$SHA256_FILENAME"
