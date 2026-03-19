
USER="${USER:-root}" # PARAM
HOST="${HOST:-127.0.0.1}" # PARAM
        ssh\
                -o StrictHostKeyChecking=no\
        -o UserKnownHostsFile=/dev/null\
        -p 2222         \
$USER@$HOST
