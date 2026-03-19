SERVICE_NAME="${SERVICE_NAME:-example-app}" # PARAM
sudo cp files/service_file_example-app /etc/systemd/system/$SERVICE_NAME.service
sudo chmod 644 /etc/systemd/system/$SERVICE_NAME.service
