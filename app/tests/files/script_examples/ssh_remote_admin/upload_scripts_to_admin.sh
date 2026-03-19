LOCAL_SOURCE="${LOCAL_SOURCE:-/tmp/local-admin/}" # PARAM
SSH_HOST="${SSH_HOST:-127.0.0.1}" # PARAM
SSH_USER="${SSH_USER:-root}" # PARAM
SSH_PORT="${SSH_PORT:-2222}" # PARAM
REMOTE_TARGET="${REMOTE_TARGET:-escripta_escripta_admin_escripta/}" # PARAM
SSH_COMMAND="        ssh\
                -o StrictHostKeyChecking=no\
        -o UserKnownHostsFile=/dev/null\
        -p 2222        "

echo "Subiendo scripts de despliegue a [$SSH_HOST] en [$REMOTE_TARGET]"

rsync \
--recursive \
--links \
--perms \
--times \
--devices \
--specials \
--verbose \
--compress \
--delete \
--exclude='.git' \
-e "$SSH_COMMAND" \
$LOCAL_SOURCE \
$SSH_USER@$SSH_HOST:$REMOTE_TARGET
