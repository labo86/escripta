USERNAME="${USERNAME:-deploy}" # PARAM
if id $USERNAME >/dev/null 2>&1; then
    echo 'user found'
else
    echo 'user not found'
fi
