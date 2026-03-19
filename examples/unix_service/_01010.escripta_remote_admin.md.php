<?php

require_once(__DIR__ . '/escripta.php');

use labo86\escripta\Escripta;
use labo86\escripta\Script;

$config = Escripta::loadConfig();
$configServer = $config['server'];

$sshHost = $configServer['ssh_host'];
$sshRootUser = $configServer['ssh_root_user'];
$sshPort = $configServer['ssh_port'];

$remoteIdentifier = "remote_admin";
$escriptaLocalDir = __DIR__ . "_01010.escripta_remote_admin.md.php/" . $remoteIdentifier . ".escripta";


Script::sshRemoteAdminScriptList($sshHost, $sshRootUser, $sshPort, $remoteIdentifier, $escriptaLocalDir);


