<?php
declare(strict_types=1);

$DEPLOY_APP_DIR =__DIR__ .  '/var/app';

?>
## Limpiar directorio de despliegue

<?php

$targetDir = $DEPLOY_APP_DIR;

?>


```bash escript name=clean_deploy_dir
TARGET_DIR=<?=escapeshellarg($targetDir)?> # PARAM

sudo rm -rf $TARGET_DIR

if [ -d $TARGET_DIR ]; then
  echo "Error: $TARGET_DIR no se pudo eliminar"
  exit 1
fi
mkdir -p $TARGET_DIR
```


## Preparar información para el despliegue en GitHub

<?php

$sourceDir = __DIR__ . '/../..';
$targetDir = $DEPLOY_APP_DIR;

?>

```bash escript name=copy_source_to_deploy_dir
SOURCE_DIR=<?=escapeshellarg($sourceDir)?> # PARAM
TARGET_DIR=<?=escapeshellarg($targetDir)?> # PARAM

cp -rf \
  -v \
  $SOURCE_DIR/src \
  $SOURCE_DIR/composer.json \
  $SOURCE_DIR/scripts \
  $TARGET_DIR
```

## Instalar dependencias en modo producción

<?php

$targetDir = $DEPLOY_APP_DIR;

?>

```bash escript name=install_dependencies
SOURCE_DIR=<?=escapeshellarg($targetDir)?> # PARAM

docker run \
--rm \
--interactive \
--tty \
--volume $SOURCE_DIR:/app \
composer:2 \
composer install --no-dev --no-interaction --no-progress --no-suggest --optimize-autoloader --ignore-platform-reqs

sudo chown -R $(whoami) $SOURCE_DIR
```

## Clonar repositorio de despliegue

<?php

$targetRepo = 'git@github.com:labo86/action_scripts.git';
$targetBranch = 'latest_release';
$targetDir =  __DIR__ . '/var/repo';

?>

```bash escript name=clone_deploy_repo

TARGET_REPO=<?=escapeshellarg($targetRepo)?> # PARAM
TARGET_BRANCH=<?=escapeshellarg($targetBranch)?> # PARAM
TARGET_DIR=<?=escapeshellarg($targetDir)?> # PARAM

rm $TARGET_DIR -rf;

GIT_SSH_COMMAND="ssh -i {$sshKeyFile}" \
git clone \
$TARGET_REPO \
--branch $TARGET_BRANCH \
$TARGET_DIR
```
