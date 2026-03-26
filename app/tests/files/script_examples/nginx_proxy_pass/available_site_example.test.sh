PUBLIC_HOST="${PUBLIC_HOST:-example.test}" # PARAM
sudo cp files/vhost_conf_$PUBLIC_HOST /etc/nginx/sites-available/$PUBLIC_HOST
