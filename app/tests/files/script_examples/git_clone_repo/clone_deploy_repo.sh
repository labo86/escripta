
TARGET_REPO="${TARGET_REPO:-git@github.com:example/deploy.git}" # PARAM
TARGET_BRANCH="${TARGET_BRANCH:-main}" # PARAM
TARGET_DIR="${TARGET_DIR:-/tmp/example-deploy}" # PARAM
SSH_COMMAND="        ssh\
                    -o IdentitiesOnly=yes\
            -o IdentityFile='/tmp/keys/example_deploy'            -o IdentityAgent=none\
            -F /dev/null\
                -o StrictHostKeyChecking=no\
        -o UserKnownHostsFile=/dev/null\
        -p 22        "

echo "Eliminando directorio [$TARGET_DIR]..."

rm -rf $TARGET_DIR;

echo "HECHO"


echo "Clonando branch [$TARGET_BRANCH] del repositorio [$TARGET_REPO] en el directorio [$TARGET_DIR]..."

GIT_SSH_COMMAND="$SSH_COMMAND" \
git clone \
$TARGET_REPO \
--branch $TARGET_BRANCH \
--single-branch \
--depth 1 \
$TARGET_DIR

echo "HECHO"
