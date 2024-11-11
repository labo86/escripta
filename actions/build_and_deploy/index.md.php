<?php
declare(strict_types=1);

use labo86\escripta\Escripta;use labo86\escripta\Script;

$config = Escripta::loadConfig();
$configGithubPages = $config['github_pages'];

$targetRepo = $configGithubPages['git_repo_url'];
$targetBranch = $configGithubPages['git_repo_branch'];
$sshKeyFilename = $configGithubPages->getAsKeyFile('private_key');

$sourceDir = Escripta::getEscriptaDir();
$targetDir = __DIR__ . '/var/repo';
$message = Escripta::getFullActionName();






?>

## Clonar repositorio de despliegue

```bash escripta name=clone_deploy_repo
<?php Script::gitCloneRepo($targetRepo, $targetBranch, $targetDir, $sshKeyFilename) ?>

```











## Copiar archivos de despliegue al repositorio

```bash escripta name=copy_deploy_files_to_repo

SOURCE_DIR=<?=escapeshellarg($sourceDir)?> # PARAM
TARGET_DIR=<?=escapeshellarg($targetDir)?> # PARAM


cp -rf \
   -v \
   $SOURCE_DIR/escripta.phar \
   $TARGET_DIR/escripta.phar
```














## Hacer commit y push


```bash escripta name=commit_and_push
<?php Script::gitCommitAndPush($targetDir, $sshKeyFilename, $message) ?>

```

