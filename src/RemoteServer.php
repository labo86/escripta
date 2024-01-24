<?php
declare(strict_types=1);
# version 1.0.0

namespace labo86\action_scripts;

class RemoteServer
{
    function executeCommandInRemoteServer(string $hostIp, string $hostUser, int $hostPort, string $command, bool $captureStdout = true): string
    {
        $escapedCommandArg = escapeshellarg($command);

        $sshCommand = <<<EOF
ssh \
-p $hostPort \
-t \
$hostUser@$hostIp \
$escapedCommandArg
EOF;

        printf("Executing command in remote server\n$sshCommand\n");

        /** @noinspection PhpUnnecessaryLocalVariableInspection */
        $strValue = executeCommandAndGetStdOut($sshCommand, $captureStdout);
        return $strValue;
    }

    function executeCommandWithKeyInRemoteServer(string $hostIp, string $hostUser, int $hostPort, string $identityFile, string $command, bool $captureStdout = true): string
    {
        $escapedCommandArg = escapeshellarg($command);

        $sshCommand = <<<EOF
ssh \
-i $identityFile \
-p $hostPort \
-t \
$hostUser@$hostIp \
$escapedCommandArg
EOF;

        printf("Executing command in remote server\n$sshCommand\n");

        /** @noinspection PhpUnnecessaryLocalVariableInspection */
        $strValue = executeCommandAndGetStdOut($sshCommand, $captureStdout);
        return $strValue;
    }

    /**
     * @throws Exception
     */
    function executeComplexCommandInRemoteServer(string $hostIp, string $hostUser, int $hostPort, string $command, bool $captureStdout = true): string
    {
        //generate temp file with command as a content
        $tempFile = tempnam(sys_get_temp_dir(), 'tmp_cmd_');
        file_put_contents($tempFile, $command);

        uploadFilesToRemoteServer($hostIp, $hostUser, $hostPort, [$tempFile]);


        $executionCommand = sprintf("bash %s", escapeshellarg(basename($tempFile)));

        $strValue = executeCommandInRemoteServer($hostIp, $hostUser, $hostPort, $executionCommand, $captureStdout);
        return $strValue;

    }


    /**
     * @throws Exception
     */
    function rsyncToRemoteServer(string $host, string $hostUser, int $hostPort, string $sshKeyFile, string $sourceDir, string $targetDir): string
    {

        $sshKeyFile = realpath($sshKeyFile);
        $sshCommand = <<<EOF
ssh -i {$sshKeyFile} -o StrictHostKeyChecking=no -o UserKnownHostsFile=/dev/null -p {$hostPort}
EOF;
        $sshCommand = escapeshellarg($sshCommand);
        $sourceDir = realpath($sourceDir) . "/";


        $command = <<<EOF
rsync \
    --recursive \
    --links \
    --perms \
    --times \
    --devices \
    --specials \
    --verbose \
    --compress \
    --delete \
    --exclude='.git' \
    -e {$sshCommand} \
    {$sourceDir} \
    {$hostUser}@{$host}:{$targetDir}
EOF;

        printf("Uploading files to remote server with command\n$command\n");

        $strValue = executeCommandAndGetStdOut($command);
        return $strValue;
    }

}