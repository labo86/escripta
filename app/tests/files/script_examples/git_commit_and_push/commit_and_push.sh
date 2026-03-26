
TARGET_DIR="${TARGET_DIR:-/tmp/example-deploy}" # PARAM
MESSAGE="${MESSAGE:-Update generated files}" # PARAM
SSH_COMMAND="        ssh\
                    -o IdentitiesOnly=yes\
            -o IdentityFile='/tmp/keys/example_deploy'            -o IdentityAgent=none\
            -F /dev/null\
                -o StrictHostKeyChecking=no\
        -o UserKnownHostsFile=/dev/null\
        -p 22        "

cd $TARGET_DIR;

echo "Actualizando repositorio...\n"

GIT_SSH_COMMAND="$SSH_COMMAND" \
git add -A;
git commit -m $MESSAGE;
git push;

echo "HECHO"
