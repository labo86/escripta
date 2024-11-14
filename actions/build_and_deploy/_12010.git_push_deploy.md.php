<?php
declare(strict_types=1);

use labo86\escripta\Escripta;
use labo86\escripta\Script;

$config = Escripta::loadConfig();
$configGithubPages = $config['github_pages'];

$sshKeyFilename = $configGithubPages->getAsKeyFile('private_key');

$targetDir = __DIR__ . '/var/repo';
$message = Escripta::getFullActionName();


Script::gitCommitAndPushScript($targetDir, $sshKeyFilename, $message);

