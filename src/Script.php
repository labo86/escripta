<?php
declare(strict_types=1);

namespace labo86\escripta;


use Exception;

class Script {

    public static function getSshCommandAsString(?string $sshKeyFilename = null, string $port = "22") : string {
        ob_start()?>
        ssh
        <?php if ( is_string($sshKeyFilename) ) : ?>
            -o IdentitiesOnly=yes
            -o IdentityFile=<?=escapeshellarg($sshKeyFilename)?>
            -o IdentityAgent=none
            -F /dev/null
        <?php endif ?>
        -o StrictHostKeyChecking=no
        -o UserKnownHostsFile=/dev/null
        -p <?=$port?>
        <?php
        return ob_get_clean();
    }

    public static function gitCloneRepoScript(string $targetRepo, string $targetBranch, string $targetDir, string $sshKeyFilename) : void {

        $gitCommand = self::getSshCommandAsString($sshKeyFilename);

        ?>
```bash escripta name=clone_deploy_repo

TARGET_REPO=<?=escapeshellarg($targetRepo)?> # PARAM
TARGET_BRANCH=<?=escapeshellarg($targetBranch)?> # PARAM
TARGET_DIR=<?=escapeshellarg($targetDir)?> # PARAM
SSH_COMMAND="<?=escapeshellcmd($gitCommand)?>"

echo "Eliminando directorio [$TARGET_DIR]..."

rm $TARGET_DIR -rf;

echo "HECHO"


echo "Clonando branch [$TARGET_BRANCH] del repositorio [$TARGET_REPO] en el directorio [$TARGET_DIR]..."

GIT_SSH_COMMAND="$SSH_COMMAND" \
git clone \
$TARGET_REPO \
--branch $TARGET_BRANCH \
--single-branch \
--depth 1 \
$TARGET_DIR

echo "HECHO"


```

<?php

    }


    public static function gitCommitAndPushScript(string $targetDir, ?string $sshKeyFilename, string $message) : void {
        $gitCommand = self::getSshCommandAsString($sshKeyFilename);

        ?>

## Hacer commit y push


```bash escripta name=commit_and_push

TARGET_DIR=<?=escapeshellarg($targetDir)?> # PARAM
MESSAGE=<?=escapeshellarg($message)?> # PARAM
SSH_COMMAND="<?=escapeshellcmd($gitCommand)?>"

cd $TARGET_DIR;

echo "Actualizando repositorio...\n"

GIT_SSH_COMMAND="$SSH_COMMAND" \
git add -A;
git commit -m $MESSAGE;
git push;

echo "HECHO"

```


<?php

    }

    public static function executeUsingSshCommand(string $sshHost, string $sshPort, string $sshUser, ?string $sshKeyFilename, string $command) {
?>
SERVER_HOST=<?=escapeshellarg($sshHost)?> # PARAM
SERVER_USER=<?=escapeshellarg($sshUser)?> # PARAM
COMMAND="<?=$command?>" #PARAM

<?=escapeshellcmd(self::getSshCommandAsString($sshKeyFilename, $sshPort))?> \
-t \
$SERVER_USER@$SERVER_HOST \
"$COMMAND"
<?php

    }

    public static function installRsyncUsingSshCommand(string $sshHost, string $sshPort, string $sshUser) {
        self::executeUsingSshCommand($sshHost, $sshPort, $sshUser, null, "sudo apt-get install -y rsync");
    }


    public static function uploadUsingRsyncCommand(string $sshHost, string $sshPort, string $sshUser, string $localSource, string $remoteTarget, ?string $sshKeyFilename = null) {

        $gitCommand = self::getSshCommandAsString($sshKeyFilename, $sshPort);
?>
LOCAL_SOURCE=<?=escapeshellarg($localSource)?> # PARAM
SSH_HOST=<?=escapeshellarg($sshHost)?> # PARAM
SSH_USER=<?=escapeshellarg($sshUser)?> # PARAM
SSH_PORT=<?=escapeshellarg($sshPort)?> # PARAM
REMOTE_TARGET=<?=escapeshellarg($remoteTarget)?> # PARAM

SSH_COMMAND="<?=escapeshellcmd($gitCommand)?>"

echo "Subiendo scripts de despliegue a [SSH_HOST] en [$REMOTE_TARGET]"

rsync \
--recursive \
--links \
--perms \
--times \
--devices \
--specials \
--verbose \
--compress \
--delete \
--exclude='.git' \
-e "$SSH_COMMAND" \
$LOCAL_SOURCE \
$SSH_USER@$SSH_HOST:$REMOTE_TARGET

<?php

    }

    public static function vBoxAddPortScript(string $serviceName, string $ruleName, string $hostPort, string $guestPort) { ?>

## Configurar puertos vm

```bash escripta name=configure_vm_ports_<?=$ruleName?> dir=bootstrap

VM_NAME=<?=escapeshellarg($serviceName)?> # PARAM
HOST_PORT=<?=escapeshellarg($hostPort)?> # PARAM
GUEST_PORT=<?=escapeshellarg($guestPort)?> # PARAM

vboxmanage modifyvm $VM_NAME --natpf1="<?=$ruleName?>,tcp,,$HOST_PORT,,$GUEST_PORT"

```


## Configurar puertos vm

```bash escripta name=show_vm_ports_<?=$ruleName?> dir=bootstrap
VM_NAME=<?=escapeshellarg($serviceName)?> # PARAM

vboxmanage showvminfo $VM_NAME | grep "guestapi"
```
<?php
    }

    public static function vboxBootstrapScriptList(string $serviceName, string $sshPort) { ?>
## Probar version de vbox

```bash escripta name=check_vbox_version dir=bootstrap
cat <<'EOF'
versiones probadas:
- 6.1.48_Ubuntur159471
- 7.0.20r163906

EOF

vboxmanage --version
```

## Probar crear maquina virtual


```bash escripta name=create_vm dir=bootstrap
VM_NAME=<?=escapeshellarg($serviceName)?> # PARAM
OVA_FILE=$1 # PARAM
REAL_RUN=$2 # PARAM

if [ -z "$OVA_FILE" ]; then
    echo "Falta el archivo ova"
    exit 1
fi

COMMAND="vboxmanage import $OVA_FILE --vsys 0 --vmname $VM_NAME --memory 4096"

# si $REAL_RUN es diferente de --run entonces es un dry run
if [ "$REAL_RUN" != "--run" ]; then
    $COMMAND --dry-run
    echo "Para correr el comando agregar --run"
else
    $COMMAND --memory 1024
fi

```


## Verificar vm

```bash escripta name=verify_vm dir=bootstrap
VM_NAME=<?=escapeshellarg($serviceName)?> # PARAM

vboxmanage showvminfo $VM_NAME
```


<?php self::vBoxAddPortScript($serviceName, "guestssh", $sshPort, "22");



    }

    public static function vboxCommandsScriptList(string $serviceName, string $sshHost, string $sshPort, string $sshUser) { ?>
## Comenzar vm

```bash escripta name=start_vm numbered=false dir=command
VM_NAME=<?=escapeshellarg($serviceName)?> # PARAM

vboxmanage startvm $VM_NAME
```





## Connect vm

```bash escripta name=connect_vm numbered=false dir=command

USER=<?=escapeshellarg($sshUser)?> # PARAM
HOST=<?=escapeshellarg($sshHost)?> # PARAM

<?=escapeshellcmd(Script::getSshCommandAsString(null, $sshPort))?> \
$USER@$HOST

```

## Detener vm

```bash escripta name=stop_vm numbered=false numbered=false dir=command
VM_NAME=<?=escapeshellarg($serviceName)?> # PARAM

vboxmanage controlvm $VM_NAME acpipowerbutton
```





## Eliminar vm

```bash escripta name=delete_vm numbered=false dir=command
VM_NAME=<?=escapeshellarg($serviceName)?> # PARAM

vboxmanage unregistervm $VM_NAME --delete
```

<?php
    }

    public static function unixCreateUserScriptList(string $sshUserToCreate, string $publicKey) {

$identifier = Escripta::getFullActionName() . "_{$sshUserToCreate}_escripta";
        ?>

```bash escripta name=check_if_app_user_exists_<?=$sshUserToCreate?>

USERNAME=<?=escapeshellarg($sshUserToCreate)?> # PARAM

if id $USERNAME >/dev/null 2>&1; then
    echo 'user found'
else
    echo 'user not found'
fi
```








## Crear usuario de la aplicación

```bash escripta name=create_app_user_<?=$sshUserToCreate?>

USERNAME=<?=escapeshellarg($sshUserToCreate)?> # PARAM

sudo useradd --create-home --shell /bin/bash --user-group $USERNAME
sudo passwd --delete $USERNAME
```








## Agregar llave autorizada de ssh


```bash escripta name=add_authorized_key_for_user_<?=$sshUserToCreate?>

SSH_USER=<?=escapeshellarg($sshUserToCreate)?> # PARAM
IDENTIFIER=<?=escapeshellarg($identifier)?> # PARAM
PUBLIC_KEY=<?=escapeshellarg($publicKey)?> # PARAM

sudo mkdir -p /home/$SSH_USER/.ssh
echo "$PUBLIC_KEY $IDENTIFIER" | sudo tee --append /home/$SSH_USER/.ssh/authorized_keys
sudo chmod 700 /home/$SSH_USER/.ssh
sudo chown -R $SSH_USER:$SSH_USER /home/$SSH_USER/.ssh
```
<?php }



    public static function nginxProxyPassScriptList(string $publicHost, string $privateHost, string $privatePort) { ?>


## Archivo de configuracion de sitio

```txt escripta name=vhost_conf_<?=$publicHost?> file=true
server {
    listen 80;
    listen [::]:80;

    server_name <?=$publicHost?>;

    location / {
        proxy_pass http://<?=$privateHost?>:<?=$privatePort?>/;
    }
}

```






## Registar configuracion de sitio

```bash escripta name=available_site_<?=$publicHost?>

PUBLIC_HOST=<?=$publicHost?> # PARAM

sudo cp files/vhost_conf_PUBLIC_HOST /etc/nginx/sites-available/$PUBLIC_HOST
```










## Habilitar sitio

```bash escripta name=enable_site_<?=$publicHost?>

PUBLIC_HOST=<?=$publicHost?> # PARAM


sudo ln -s /etc/nginx/sites-available/$PUBLIC_HOST /etc/nginx/sites-enabled/
sudo systemctl restart nginx
```

<?php
    }

    public static function systemDSetupServiceScriptList(string $serviceName, string $sshUser, string $installDir) {

        $fileName = "sudoers_service_$serviceName";
        ?>

## Permisos de sudo

```txt escripta name=<?=$fileName?> file=true
<?=$sshUser?> ALL=(ALL) NOPASSWD: /usr/bin/systemctl start <?=$serviceName?>.service
<?=$sshUser?> ALL=(ALL) NOPASSWD: /usr/bin/systemctl stop <?=$serviceName?>.service
<?=$sshUser?> ALL=(ALL) NOPASSWD: /usr/bin/systemctl enable <?=$serviceName?>.service --now
<?=$sshUser?> ALL=(ALL) NOPASSWD: /usr/bin/systemctl disable <?=$serviceName?>.service --now
<?=$sshUser?> ALL=(ALL) NOPASSWD: /usr/bin/systemctl restart <?=$serviceName?>.service
<?=$sshUser?> ALL=(ALL) NOPASSWD: /usr/bin/systemctl status <?=$serviceName?>.service
```






## Agregar usuario a grupo sudo

```bash escripta name=add_user_to_<?=$sshUser?>_sudo_group

SERVICE_USERNAME=<?=escapeshellarg($sshUser)?> # PARAM
SUDO_FILE="files/<?=$fileName?>"
cat $SUDO_FILE | sudo EDITOR='tee --append' visudo /etc/sudoers.d/$SERVICE_USERNAME
```






## Crear datos dummy

```bash escripta name=launch_<?=$serviceName?> file=true
#!/bin/bash

# while loop with echo sleep
while true; do
    echo "Service running"
    sleep 10
done
```




## Crear archivo de ejecucion de servicio

```bash escripta name=create_launch_file_<?=$serviceName?>

SERVICE_DIR=<?=$installDir?> # PARAM
SSH_USER=<?=escapeshellarg($sshUser)?> # PARAM

sudo mkdir -p $SERVICE_DIR/launch
sudo mkdir -p $SERVICE_DIR/var/logs

sudo cp files/launch_<?=$serviceName?> $SERVICE_DIR/launch/launch.sh
sudo chmod +x $SERVICE_DIR/launch/
sudo chown -R $SSH_USER:$SSH_USER $SERVICE_DIR

```








## Archivo de servicio


```txt escripta name=service_file_<?=$serviceName?> file=true
[Unit]
Description=<?=$serviceName?>

After=network.target

[Service]
Type=simple
User=<?=$sshUser?>

Group=<?=$sshUser?>

LimitNOFILE=65536

Restart=on-failure
RestartSec=5

WorkingDirectory=<?=$installDir?>/launch
ExecStart=<?=$installDir?>/launch/launch.sh

StandardOutput=append:<?=$installDir?>/var/logs/stdout.log
StandardError=append:<?=$installDir?>/var/logs/stderr.log
SyslogIdentifier=<?=$sshUser?>


[Install]
WantedBy=multi-user.target
```










## Crear archivo de servicio

```bash escripta name=create_service_file_<?=$serviceName?>

SERVICE_NAME=<?=escapeshellarg($serviceName)?> # PARAM

sudo cp files/service_file_<?=$serviceName?> /etc/systemd/system/$SERVICE_NAME.service
sudo chmod 644 /etc/systemd/system/$SERVICE_NAME.service
```











## Checkear archivo de servicio


```bash escripta name=check_service_file_<?=$serviceName?>

SERVICE_NAME=<?=escapeshellarg($serviceName)?> # PARAM

sudo systemd-analyze verify $SERVICE_NAME.service
```

## Habilitar servicio












```bash escripta name=enable_service_<?=$serviceName?>

SERVICE_NAME=<?=escapeshellarg($serviceName)?> # PARAM

sudo systemctl enable $SERVICE_NAME.service --now
```







## Verificar estado de servicio


```bash escripta name=status_service_<?=$serviceName?>

SERVICE_NAME=<?=escapeshellarg($serviceName)?> # PARAM

sudo systemctl status $SERVICE_NAME.service
```




## Check journalctl status messages


```bash escripta name=journalctl_<?=$serviceName?>

SERVICE_NAME=<?=$serviceName?> # PARAM

journalctl | grep <?=$serviceName?>.service | grep systemd
```










<?php
}

public static function sshRemoteAdminScriptList(string $sshHost, string $sshUser, string $sshPort, string $remoteIdentifier, string $localDir) {

$remoteDir =  Escripta::getFullActionName() . "_{$remoteIdentifier}_escripta";

Escripta::registerFunction("connect_to_$remoteIdentifier", function() use ($remoteIdentifier, $sshHost, $sshUser, $sshPort, $remoteDir) { ?>
## Conectarse al servidor

```bash escripta name=connect_to_<?=$remoteIdentifier?>

at_exit
trap - EXIT

<?php

$target_dir=escapeshellarg($remoteDir);
Script::executeUsingSshCommand($sshHost, $sshPort, $sshUser, null, "cd $target_dir; bash") ?>

```

```escripta escripta command=set_dir dir=<?=$remoteIdentifier?>

```

<?php
});

?>






## Instalar rsync mediante ssh

```bash escripta name=install_rsync_in_<?=$remoteIdentifier?>

<?php Script::installRsyncUsingSshCommand($sshHost, $sshPort, $sshUser)?>

```












## Subir scripts de despliegue a servidor

```bash escripta name=upload_scripts_to_<?=$remoteIdentifier?>

<?php Script::uploadUsingRsyncCommand($sshHost, $sshPort, $sshUser, $localDir . "/", $remoteDir . "/", null) ?>

```


<?php
}

public static function sshRemoteScriptList(string $sshHost, string $sshUser, string $sshPort, string $remoteIdentifier, string $localDir, string $sshKeyFilename) {

        $remoteDir =  Escripta::getFullActionName() . "_{$remoteIdentifier}_escripta";

        Escripta::registerFunction("connect_to_$remoteIdentifier", function() use ($remoteIdentifier, $sshHost, $sshUser, $sshPort, $remoteDir, $sshKeyFilename) { ?>
## Conectarse al servidor

```bash escripta name=connect_to_<?=$remoteIdentifier?>

at_exit
trap - EXIT

<?php

$target_dir=escapeshellarg($remoteDir);
Script::executeUsingSshCommand($sshHost, $sshPort, $sshUser, $sshKeyFilename, "cd $target_dir; bash") ?>

```

```escripta escripta command=set_dir dir=<?=$remoteIdentifier?>

```
            <?php
        });

        ?>



## Subir scripts de despliegue a servidor

```bash escripta name=upload_scripts_to_<?=$remoteIdentifier?>

<?php Script::uploadUsingRsyncCommand($sshHost, $sshPort, $sshUser, $localDir . "/", $remoteDir . "/", $sshKeyFilename) ?>

```


        <?php
    }




    public static function returnToLocalScript() {?>
## Volver al cliente

```bash escripta name=return_to_local

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

```

```escripta escripta command=unset_dir
```

        <?php
    }

    public static function cleanDirScript(string $dir) {?>

```bash escripta name=clean_tmp_dir
TARGET_DIR=<?=escapeshellarg($dir)?> # PARAM

rm -rf $TARGET_DIR

if [ -d $TARGET_DIR ]; then
    echo "Error: $TARGET_DIR no se pudo eliminar"
    exit 1
fi

mkdir -p $TARGET_DIR
```
<?php
    }

}