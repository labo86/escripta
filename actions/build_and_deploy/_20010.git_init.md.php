<?php
declare(strict_types=1);

use labo86\escripta\Escripta;
use labo86\escripta\Script;

$config = Escripta::loadConfig();
$configGithubPages = $config['github_pages'];

$targetRepo = $configGithubPages['git_repo_url'];
$targetBranch = $configGithubPages['git_repo_branch'];
$sshKeyFilename = $configGithubPages->getAsKeyFile('private_key');

$targetDir = __DIR__ . '/var/repo';
$message = "First commit";


Script::gitInitScriptList($targetRepo, $targetBranch, $targetDir, $sshKeyFilename, $message);