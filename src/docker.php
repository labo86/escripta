<?php
declare(strict_types=1);

function dockerCheckContainerExists(string $containerName): bool
{
    $command = "docker ps -a -format '{{.Names}}' | grep {$containerName}";
    $output = shell_exec($command);
    return !empty($output);
}

function dockerRemoveContainer(string $containerName): void
{
    $command = "docker rm -f {$containerName}";
    shell_exec($command);
}

function dockerRunMySqlContainer(string $containerName, string $mysqlDataDir, int $port, string $rootPassword ) {
    $command = <<<EOF
docker run \
--name {$containerName} \
--volume {$mysqlDataDir}:/var/lib/mysql \
--publish {$port}:3306 \
--env MYSQL_ROOT_PASSWORD={$rootPassword} \
mysql
EOF;
    passthru($command);
}

function dockerStopContainer(string $containerName): void
{
    $command = "docker stop {$containerName}";
    passthru($command);
}

function dockerStartContainer(string $containerName): void
{
    $command = "docker start {$containerName}";
    passthru($command);
}