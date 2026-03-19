
echo "
######  ##    ## ######  ######
##       ##  ##    ##      ##
######     ##      ##      ##
##       ##  ##    ##      ##
######  ##    ## ######    ##
"


function command_at_exit {
    at_exit
    kill -SIGHUP $PPID
}

trap command_at_exit EXIT
