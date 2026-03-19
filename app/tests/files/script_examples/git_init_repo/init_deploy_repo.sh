
TARGET_REPO="${TARGET_REPO:-git@github.com:example/empty.git}" # PARAM
TARGET_BRANCH="${TARGET_BRANCH:-main}" # PARAM
TARGET_DIR="${TARGET_DIR:-/tmp/example-empty}" # PARAM
echo "Eliminando directorio [$TARGET_DIR]..."

rm -rf $TARGET_DIR;

echo "HECHO"

echo "Clonando branch [$TARGET_BRANCH] del repositorio [$TARGET_REPO] en el directorio [$TARGET_DIR]..."

mkdir -p $TARGET_DIR
cd $TARGET_DIR
git init -b $TARGET_BRANCH
git remote add origin $TARGET_REPO;

echo "HECHO"
