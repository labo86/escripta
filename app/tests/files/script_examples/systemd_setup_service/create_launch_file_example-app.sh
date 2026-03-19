SERVICE_DIR="${SERVICE_DIR:-/opt/example-app}" # PARAM
SSH_USER="${SSH_USER:-deploy}" # PARAM
sudo mkdir -p $SERVICE_DIR/launch
sudo mkdir -p $SERVICE_DIR/var/logs

sudo cp files/launch_example-app $SERVICE_DIR/launch/launch.sh
sudo chmod +x $SERVICE_DIR/launch/
sudo chown -R $SSH_USER:$SSH_USER $SERVICE_DIR
