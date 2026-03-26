SERVICE_NAME="${SERVICE_NAME:-example-app}" # PARAM
journalctl | grep example-app.service | grep systemd
