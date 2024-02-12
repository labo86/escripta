<?php
declare(strict_types=1);

require_once(__DIR__ . '/include.php');

use labo86\action_scripts\Config;

$DEPLOY_APP_DIR =__DIR__ .  '/var/app';
$CONFIG_DEPLOY_GITHUB = LOCAL_CONFIG_DEPLOY_GITHUB;

$config = Config::load();


?>

## Clonar repositorio de despliegue

<?php

$targetRepo = $config['git_repo_url'];
$targetBranch = $config['git_repo_branch'];
$targetDir =  __DIR__ . '/var/repo';
$sshKeyFilename = $config[$CONFIG_DEPLOY_GITHUB . "_private_key"];

?>

```bash escript name=clone_deploy_repo

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

$sourceDir = __DIR__ . '/..';
$targetDir = __DIR__ . '/var/repo';

?>

```txt escript name=var dir=remoto file=true

SOURCE_DIR=<?=escapeshellarg($sourceDir)?> # PARAM
TARGET_DIR=<?=escapeshellarg($targetDir)?> # PARAM


cp -rf \
-v \
$SOURCE_DIR/action_scripts.phar \
$TARGET_DIR/action_scripts.phar
```

## Copiar archivos de despliegue al repositorio

<?php

$sourceDir = __DIR__ . '/..';
$targetDir = __DIR__ . '/var/repo';

?>

```bash escript name=copy_deploy_files_to_repo file=true
SOURCE_DIR=<?=escapeshellarg($sourceDir)?> # PARAM
TARGET_DIR=<?=escapeshellarg($targetDir)?> # PARAM


cp -rf \
-v \
$SOURCE_DIR/action_scripts.phar \
$TARGET_DIR/action_scripts.phar
```


## Copiar archivos de despliegue al repositorio

<?php

$sourceDir = __DIR__ . '/..';
$targetDir = __DIR__ . '/var/repo';

?>

```bash escript name=copy_deploy_files_to_repo dir=remoto

SOURCE_DIR=<?=escapeshellarg($sourceDir)?> # PARAM
TARGET_DIR=<?=escapeshellarg($targetDir)?> # PARAM


cp -rf \
  -v \
  $SOURCE_DIR/action_scripts.phar \
  $TARGET_DIR/action_scripts.phar
```

## Hacer commit y push

<?php

$targetDir = __DIR__ . '/var/repo';
$sshKeyFilename = $config[$CONFIG_DEPLOY_GITHUB . "_private_key"];

?>

```bash escript name=commit_and_push

TARGET_DIR=<?=escapeshellarg($targetDir)?> # PARAM
SSH_KEY_FILENAME=<?=$sshKeyFilename?> # PARAM

cd $TARGET_DIR;
GIT_SSH_COMMAND="ssh -i $SSH_KEY_FILENAME";
git add -A;
git commit -m "commit"
git push;
```

