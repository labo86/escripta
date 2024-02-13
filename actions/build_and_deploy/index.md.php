<?php
declare(strict_types=1);

use labo86\escripta\Escripta;

$DEPLOY_APP_DIR =__DIR__ .  '/var/app';
$CONFIG_DEPLOY_GITHUB = 'deploy_github';

$config = Escripta::loadConfig();


?>

## Clonar repositorio de despliegue

<?php

$targetRepo = $config['deploy_github']['git_repo_url'];
$targetBranch = $config['deploy_github']['git_repo_branch'];
$targetDir =  __DIR__ . '/var/repo';
$sshKeyFilename = $config['deploy_github']['private_key'];

?>

```bash escripta name=clone_deploy_repo

TARGET_REPO=<?=escapeshellarg($targetRepo)?> # PARAM
TARGET_BRANCH=<?=escapeshellarg($targetBranch)?> # PARAM
TARGET_DIR=<?=escapeshellarg($targetDir)?> # PARAM
SSH_KEY_FILENAME=<?=$sshKeyFilename?> # PARAM

rm $TARGET_DIR -rf;

GIT_SSH_COMMAND="ssh -i $SSH_KEY_FILENAME" \
git clone \
$TARGET_REPO \
--branch $TARGET_BRANCH \
$TARGET_DIR
```

## Copiar archivos de despliegue al repositorio

<?php

$sourceDir = Escripta::getProjectConfigDir();
$targetDir = __DIR__ . '/var/repo';

?>

```bash escripta name=copy_deploy_files_to_repo

SOURCE_DIR=<?=escapeshellarg($sourceDir)?> # PARAM
TARGET_DIR=<?=escapeshellarg($targetDir)?> # PARAM


cp -rf \
  -v \
  $SOURCE_DIR/escripta.phar \
  $TARGET_DIR/escripta.phar
```

## Hacer commit y push

<?php

$targetDir = __DIR__ . '/var/repo';
$sshKeyFilename = $config['deploy_github']['private_key'];
$message = Escripta::getFullActionName();

?>

```bash escripta name=commit_and_push

TARGET_DIR=<?=escapeshellarg($targetDir)?> # PARAM
SSH_KEY_FILENAME=<?=$sshKeyFilename?> # PARAM
MESSAGE=<?=escapeshellarg($message)?> # PARAM

cd $TARGET_DIR;
GIT_SSH_COMMAND="ssh -i $SSH_KEY_FILENAME";
git add -A;
git commit -m $MESSAGE;
git push;
```

