USERNAME="${USERNAME:-deploy}" # PARAM
sudo useradd --create-home --shell /bin/bash --user-group $USERNAME
sudo passwd --delete $USERNAME
