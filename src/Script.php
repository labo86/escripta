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

    public static function gitCloneRepo(string $targetRepo, string $targetBranch, string $targetDir, string $sshKeyFilename) : void {

        $gitCommand = self::getSshCommandAsString($sshKeyFilename);

        ?>
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

<?php

    }


    public static function gitCommitAndPush(string $targetDir, ?string $sshKeyFilename, string $message) : void {
        $gitCommand = self::getSshCommandAsString($sshKeyFilename);

        ?>
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
<?php

    }

    public static function executeUsingSsh(string $sshHost, string $sshPort, string $sshUser, ?string $sshKeyFilename, string $command) {
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

    public static function installRsyncUsingSsh(string $sshHost, string $sshPort, string $sshUser) {
        self::executeUsingSsh($sshHost, $sshPort, $sshUser, null, "sudo apt-get install -y rsync");
    }


    public static function uploadUsingRsync(string $sshHost, string $sshPort, string $sshUser, string $localSource, string $remoteTarget, ?string $sshKeyFilename = null) {

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

    public static function vBoxAddPort(string $serviceName, string $ruleName, string $hostPort, string $guestPort) { ?>
VM_NAME=<?=escapeshellarg($serviceName)?> # PARAM
HOST_PORT=<?=escapeshellarg($hostPort)?> # PARAM
GUEST_PORT=<?=escapeshellarg($guestPort)?> # PARAM

vboxmanage modifyvm $VM_NAME --natpf1="<?=$ruleName?>,tcp,,$HOST_PORT,,$GUEST_PORT"
<?php
    }

    public static function vboxBootstrap(string $serviceName, string $sshPort) { ?>
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

## Configurar puertos vm

```bash escripta name=configure_vm_ports dir=bootstrap
<?php self::vBoxAddPort($serviceName, "guestssh", $sshPort, "22") ?>

```
<?php
    }

    public static function vboxCommands(string $serviceName, string $sshHost, string $sshPort, string $sshUser) { ?>
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

    public static function unixCreateUser(string $scriptDir, string $sshUserToCreate, string $publicKey, string $identifier) {?>

```bash escripta name=check_if_app_user_exists_<?=$sshUserToCreate?> dir=<?=$scriptDir?>

USERNAME=<?=escapeshellarg($sshUserToCreate)?> # PARAM

if id $USERNAME >/dev/null 2>&1; then
    echo 'user found'
else
    echo 'user not found'
fi
```








## Crear usuario de la aplicación

```bash escripta name=create_app_user_<?=$sshUserToCreate?> dir=<?=$scriptDir?>

USERNAME=<?=escapeshellarg($sshUserToCreate)?> # PARAM

sudo useradd --create-home --shell /bin/bash --user-group $USERNAME
sudo passwd --delete $USERNAME
```








## Agregar llave autorizada de ssh


```bash escripta name=add_authorized_key_for_user_<?=$sshUserToCreate?> dir=<?=$scriptDir?>

SSH_USER=<?=escapeshellarg($sshUserToCreate)?> # PARAM
IDENTIFIER=<?=escapeshellarg($identifier)?> # PARAM
PUBLIC_KEY=<?=escapeshellarg($publicKey)?> # PARAM

sudo mkdir -p /home/$SSH_USER/.ssh
echo "$PUBLIC_KEY $IDENTIFIER" | sudo tee --append /home/$SSH_USER/.ssh/authorized_keys
sudo chmod 700 /home/$SSH_USER/.ssh
sudo chown -R $SSH_USER:$SSH_USER /home/$SSH_USER/.ssh
```
<?php }

}