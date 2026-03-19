TARGET_BRANCH="${TARGET_BRANCH:-main}" # PARAM
TARGET_DIR="${TARGET_DIR:-/tmp/example-empty}" # PARAM
MESSAGE="${MESSAGE:-Initial import}" # PARAM
SSH_COMMAND="        ssh\
                    -o IdentitiesOnly=yes\
            -o IdentityFile='/tmp/keys/example_empty'            -o IdentityAgent=none\
            -F /dev/null\
                -o StrictHostKeyChecking=no\
        -o UserKnownHostsFile=/dev/null\
        -p 22        "

cd $TARGET_DIR;

echo "Comiteando branch [$TARGET_BRANCH] por primera vez..."

git add -A;
git commit -m "$MESSAGE";
GIT_SSH_COMMAND="$SSH_COMMAND" \
git push -u origin $TARGET_BRANCH;

echo "HECHO"
