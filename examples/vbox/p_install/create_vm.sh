VM_NAME="${VM_NAME:-example-vm}" # PARAM
OVA_FILE="${OVA_FILE:-\$1}" # PARAM
REAL_RUN="${REAL_RUN:-\$2}" # PARAM
if [ -z "$OVA_FILE" ]; then
    echo "Falta el archivo ova"
    exit 1
fi

COMMAND="vboxmanage import $OVA_FILE --vsys 0 --vmname $VM_NAME --memory 4096"

# si $REAL_RUN es diferente de --run entonces es un dry run
if [ "$REAL_RUN" != "--run" ]; then
    $COMMAND --dry-run
    echo "Para correr el comando agregar --run"
else
    $COMMAND --memory 1024
fi
