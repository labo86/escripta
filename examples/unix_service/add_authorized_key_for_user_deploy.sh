SSH_USER="${SSH_USER:-deploy}" # PARAM
IDENTIFIER="${IDENTIFIER:-escripta_escripta_deploy_escripta}" # PARAM
PUBLIC_KEY="${PUBLIC_KEY:-ssh-ed25519 AAAAexample deploy@example}" # PARAM
sudo mkdir -p /home/$SSH_USER/.ssh
echo "$PUBLIC_KEY $IDENTIFIER" | sudo tee --append /home/$SSH_USER/.ssh/authorized_keys
sudo chmod 700 /home/$SSH_USER/.ssh
sudo chown -R $SSH_USER:$SSH_USER /home/$SSH_USER/.ssh
