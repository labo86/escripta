
SERVICE_USERNAME="${SERVICE_USERNAME:-deploy}" # PARAM
SUDO_FILE="files/sudoers_service_example-app"
cat $SUDO_FILE | sudo EDITOR='tee --append' visudo /etc/sudoers.d/$SERVICE_USERNAME
