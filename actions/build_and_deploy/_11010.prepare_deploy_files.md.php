<?php
declare(strict_types=1);

use labo86\escripta\Escripta;

$config = Escripta::loadConfig();
$configGithubPages = $config['github_pages'];

$sourceDir = Escripta::getEscriptaDir();
$targetDir = __DIR__ . '/var/repo';


?>


## Copiar archivos de despliegue al repositorio

```bash escripta name=copy_deploy_files_to_repo

SOURCE_DIR=<?=escapeshellarg($sourceDir)?> # PARAM
TARGET_DIR=<?=escapeshellarg($targetDir)?> # PARAM


cp -rf \
   -v \
   $SOURCE_DIR/escripta.phar \
   $TARGET_DIR/escripta.phar
```


