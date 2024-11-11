<?php

require_once(__DIR__ . '/escripta.php');

use labo86\escripta\Escripta;
use labo86\escripta\Script;

$config = Escripta::loadConfig();
$configServer = $config['server'];

$projectName = Escripta::getProjectName();

$sshHost = $configServer['ssh_host'];
$sshRootUser = $configServer['ssh_root_user'];
$sshUser= $configServer['ssh_user'];
$sshPort = $configServer['ssh_port'];
$sshKeyFilename = $configServer["private_key"];
$publicKey = $configServer['public_key'];

$remoteIdentifier = "remote_admin";
$escriptaRemoteDir = Escripta::getFullActionName() . "_{$remoteIdentifier}_escripta";
$escriptaLocalDir = __DIR__. "/" . $remoteIdentifier . ".escripta";
$installDir = "/home/{$sshUser}/{$projectName}";

?>

<?php Escripta::callFunction("connect_to_$remoteIdentifier") ?>

<?php Script::unixCreateUser($remoteIdentifier, $sshUser, $publicKey, $escriptaRemoteDir) ?>
