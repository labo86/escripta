<?php
declare(strict_types=1);
# version 1.0.0

function yarnInstall($repoDir) : void {

    $containerRepoDir = '/var/app/repo';
    $command = <<<EOF
docker run \
    --volume {$repoDir}:{$containerRepoDir} \
    --rm \
    --workdir {$containerRepoDir} \
    --env NODE_ENV='production' \
    node:18 \
    yarn install --immutable --immutable-cache --check-cache
EOF;

    passthru($command);

    passthru("sudo chown -R $(whoami):$(whoami) {$repoDir}");
}

function yarnBuild($repoDir) : void {
    $containerRepoDir = '/var/app/repo';
    $command = <<<EOF
docker run \
    --volume {$repoDir}:{$containerRepoDir} \
    --rm \
    --workdir {$containerRepoDir} \
    --env NODE_ENV='production' \
    node:18 \
    yarn run build
EOF;

    passthru($command);

    passthru("sudo chown -R $(whoami):$(whoami) {$repoDir}");
}




