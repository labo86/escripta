<?php

require_once(__DIR__ . '/escripta.php');

use labo86\escripta\Escripta;
use labo86\escripta\Script;

$config = Escripta::loadConfig();
$configServer = $config['server'];

$sshUser= $configServer['ssh_user'];
$publicKey = $configServer['public_key'];

$remoteIdentifier = "remote_admin";



Script::unixCreateUser($remoteIdentifier, $sshUser, $publicKey);
