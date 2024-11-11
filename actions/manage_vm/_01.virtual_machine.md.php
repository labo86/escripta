<?php

require_once(__DIR__ . '/escripta.php');

use labo86\escripta\Escripta;
use labo86\escripta\Script;

$config = Escripta::loadConfig();
$configServer = $config['server'];

$serviceName = $configServer['service_name'];
$sshPort = $configServer['ssh_port'];
$serverHost = $configServer['ssh_host'];
$sshRootUser = $configServer['ssh_root_user'];

Script::vboxBootstrap($serviceName, $sshPort);

Script::vboxCommands($serviceName, $serverHost, $sshPort, $sshRootUser);
