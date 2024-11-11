<?php

require_once(__DIR__ . '/escripta.php');

use labo86\escripta\Escripta;
use labo86\escripta\Script;

$config = Escripta::loadConfig();
$configServer = $config['server'];

$sshHost = $configServer['ssh_host'];
$sshRootUser = $configServer['ssh_root_user'];
$sshPort = $configServer['ssh_port'];
$sshKeyFilename = $configServer["private_key"];
$publicKey = $configServer['public_key'];

$remoteIdentifier = "remote_admin";
$escriptaRemoteDir = Escripta::getFullActionName() . "_{$remoteIdentifier}_escripta";
$escriptaLocalDir = __DIR__. "/" . $remoteIdentifier . ".escripta";











Escripta::registerFunction("connect_to_$remoteIdentifier", function() use ($remoteIdentifier, $sshHost, $sshRootUser, $sshPort, $escriptaRemoteDir) { ?>
## Conectarse al servidor

```bash escripta name=connect_to_<?=$remoteIdentifier?>

<?php

$target_dir=escapeshellarg($escriptaRemoteDir);
Script::executeUsingSsh($sshHost, $sshPort, $sshRootUser, null, "cd $target_dir; bash") ?>

```
<?php
});








Escripta::registerFunction("return_to_local", function() use ($remoteIdentifier) {?>
## Volver al cliente

```bash escripta name=return_to_local dir=<?=$remoteIdentifier?>


echo "Debes volver al cliente"
echo "Escribe 'exit' para salir"

```
<?php
})

?>











## Instalar rsync mediante ssh

```bash escripta name=install_rsync_in_<?=$remoteIdentifier?>

<?php Script::installRsyncUsingSsh($sshHost, $sshPort, $sshRootUser)?>

```












## Subir scripts de despliegue a servidor

```bash escripta name=upload_scripts_to_<?=$remoteIdentifier?>

<?php Script::uploadUsingRsync($sshHost, $sshPort, $sshRootUser, $escriptaLocalDir . "/", $escriptaRemoteDir . "/", null) ?>

```

