<?php
declare(strict_types=1);
# version 1.0.0


function composerInstall($repoDir) : void {

    $containerRepoDir = '/app';
    $command = <<<EOF
docker run \
    --rm \
    --interactive \
    --tty \
    --volume {$repoDir}:{$containerRepoDir} \
    composer:2 \
    composer install --no-dev --no-interaction --no-progress --no-suggest --optimize-autoloader --ignore-platform-reqs
EOF;

    passthru($command);

    passthru("sudo chown -R $(whoami):$(whoami) {$repoDir}");
}
