at_exit
trap - EXIT

SERVER_HOST="${SERVER_HOST:-127.0.0.1}" # PARAM
SERVER_USER="${SERVER_USER:-deploy}" # PARAM
COMMAND="${COMMAND:-cd 'escripta_escripta_app_escripta'; bash}" # PARAM
        ssh\
                    -o IdentitiesOnly=yes\
            -o IdentityFile='/tmp/keys/example_app'            -o IdentityAgent=none\
            -F /dev/null\
                -o StrictHostKeyChecking=no\
        -o UserKnownHostsFile=/dev/null\
        -p 2222         \
-t \
$SERVER_USER@$SERVER_HOST \
"$COMMAND"
