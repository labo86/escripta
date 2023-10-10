<?php
declare(strict_types=1);
# version 1.0.0

require_once(__DIR__ . '/common.php');

function executeCommandInRemoteServer(string $hostIp, string $hostUser, int $hostPort, string $command , bool $captureStdout = true): string
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

/**
 * @throws Exception
 */
function executeComplexCommandInRemoteServer(string $hostIp, string $hostUser, int $hostPort, string $command, bool $captureStdout = true): string {
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
function uploadFilesToRemoteServer(string $hostIp, string $hostUser, int $hostPort, array $files) : string {

    $escapedFiles = [];

    foreach ( $files as $file ) {
        if ( !file_exists($file) ) {
            throw new Exception("File $file does not exists");
        }
        $escapedFiles[] = escapeshellarg($file);
    }

    $escapedFilesString = join(" \\\n" , $escapedFiles);

    $sshCommand = <<<EOF
scp \
-P $hostPort \
-o StrictHostKeyChecking=no \
$escapedFilesString \
$hostUser@$hostIp:
EOF;

    printf("Uploading files to remote server with command\n$sshCommand\n");

    $strValue = executeCommandAndGetStdOut($sshCommand);
    return $strValue;
}

/**
 * @throws Exception
 */
function rsyncToRemoteServer(string $host, string $hostUser, int $hostPort, string $sshKeyFile, string $sourceDir, string $targetDir) : string {

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

function createUserWithNoPasswordInRemoteServer(string $hostIp, string $hostUser, int $hostPort, string $userName) : string {
    $command = <<<EOF
sudo useradd --create-home --shell /bin/bash --user-group {$userName}
sudo passwd --delete {$userName}
EOF;

    $strValue = executeCommandInRemoteServer($hostIp, $hostUser, $hostPort, $command, false);
    return $strValue;

}

function addAuthorizedKeyToUserInRemoteServer(string $hostIp, string $hostUser, int $hostPort, string $userName, string $publicKey) : string {
    $command = <<<EOF
mkdir -p /home/{$userName}/.ssh
chown -R {$userName}:{$userName} /home/{$userName}/.ssh
echo "{$publicKey}" >> /home/{$userName}/.ssh/authorized_keys
chmod 700 /home/{$userName}/.ssh
EOF;
    $command = escapeshellarg($command);
    $command = escapeshellarg("bash -c {$command}");
    $sucommand = <<<EOF
sudo su -c {$command} - {$userName}
EOF;


    $strValue = executeCommandInRemoteServer($hostIp, $hostUser, $hostPort, $sucommand, false);
    return $strValue;
}

