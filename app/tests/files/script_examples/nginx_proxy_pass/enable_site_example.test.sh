PUBLIC_HOST="${PUBLIC_HOST:-example.test}" # PARAM
sudo ln -s /etc/nginx/sites-available/$PUBLIC_HOST /etc/nginx/sites-enabled/
sudo systemctl restart nginx
