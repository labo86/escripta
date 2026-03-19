<?php

require_once(__DIR__ . '/escripta.php');

use labo86\escripta\Escripta;
use labo86\escripta\Script;

$config = Escripta::loadConfig();
$configServer = $config['server'];

$projectName = Escripta::getProjectName();

$sshUser= $configServer['ssh_user'];

$installDir = "/home/{$sshUser}/{$projectName}";

Script::systemDSetupServiceScriptList($projectName, $sshUser, $installDir);
