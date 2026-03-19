TARGET_DIR="${TARGET_DIR:-/tmp/example-workdir}" # PARAM
rm -rf $TARGET_DIR

if [ -d $TARGET_DIR ]; then
    echo "Error: $TARGET_DIR no se pudo eliminar"
    exit 1
fi

mkdir -p $TARGET_DIR

echo "Directorio limpiado [$TARGET_DIR]"
