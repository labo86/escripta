SERVICE_NAME="${SERVICE_NAME:-example-app}" # PARAM
sudo systemd-analyze verify $SERVICE_NAME.service
