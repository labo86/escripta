SERVER_HOST="${SERVER_HOST:-127.0.0.1}" # PARAM
SERVER_USER="${SERVER_USER:-root}" # PARAM
COMMAND="${COMMAND:-sudo apt-get install -y rsync}" # PARAM
        ssh\
                -o StrictHostKeyChecking=no\
        -o UserKnownHostsFile=/dev/null\
        -p 2222         \
-t \
$SERVER_USER@$SERVER_HOST \
"$COMMAND"
