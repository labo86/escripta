
VM_NAME="${VM_NAME:-example-vm}" # PARAM
HOST_PORT="${HOST_PORT:-2222}" # PARAM
GUEST_PORT="${GUEST_PORT:-22}" # PARAM
RULE_NAME="${RULE_NAME:-guestssh}" # PARAM
vboxmanage modifyvm $VM_NAME --natpf1="$RULE_NAME,tcp,,$HOST_PORT,,$GUEST_PORT"
