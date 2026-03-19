<?php

require_once(__DIR__ . '/escripta.php');

use labo86\escripta\Escripta;
use labo86\escripta\Script;

$config = Escripta::loadConfig();
$configServer = $config['server'];

$sshHost = $configServer['ssh_host'];
$sshUser = $configServer['ssh_user'];
$sshPort = $configServer['ssh_port'];
$sshKeyFilename = $configServer->getAsKeyFile('private_key');

$remoteIdentifier = "remote";
$escriptaLocalDir = __DIR__ . "_03010.escripta_remote.md.php/" . $remoteIdentifier . ".escripta";


Script::sshRemoteScriptList($sshHost, $sshUser, $sshPort, $remoteIdentifier, $escriptaLocalDir, $sshKeyFilename);


