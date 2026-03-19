VM_NAME="${VM_NAME:-example-vm}" # PARAM
RULE_NAME="${RULE_NAME:-guestssh}" # PARAM
vboxmanage showvminfo $VM_NAME | grep "$RULE_NAME"
