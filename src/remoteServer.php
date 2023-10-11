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

function executeCommandWithKeyInRemoteServer(string $hostIp, string $hostUser, int $hostPort, string $identityFile, string $command , bool $captureStdout = true): string
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

function createFileOnRemoteServer(string $hostIp, string $hostUser, int $hostPort, string $filePath, string $fileContent, bool $sudo = true) : string {
    $escapedFilePath = escapeshellarg($filePath);
    $escapedFileContent = escapeshellarg($fileContent);

    if ($sudo) {
        $escapedFilePath = "sudo tee {$escapedFilePath}";
    } else {
        $escapedFilePath = "tee {$escapedFilePath}";
    }
    $command = <<<EOF
echo {$escapedFileContent} | {$escapedFilePath}
EOF;

    $strValue = executeCommandInRemoteServer($hostIp, $hostUser, $hostPort, $command, false);
    return $strValue;
}

function createSudoersServiceFileInRemoteServer(string $hostIp, string $hostUser, int $hostPort, string $serviceUser, string $serviceName) : string
{
    $command = <<<EOF
{$serviceUser} ALL=(ALL) NOPASSWD:/usr/bin/systemctl start {$serviceName}.service, /usr/bin/systemctl stop {$serviceName}.service, /usr/bin/systemctl restart {$serviceName}.service, /usr/bin/systemctl status {$serviceName}.service
EOF;

    $filePath = "/etc/sudoers.d/{$serviceName}_sudoers";

    createFileOnRemoteServer($hostIp, $hostUser, $hostPort, $filePath , $command);

    $strValue = executeCommandInRemoteServer($hostIp, $hostUser, $hostPort, "sudo chmod 440 {$filePath}", false);
    return $strValue;
}

/**
 * Create a service files on /etc/systemd/system/
 * @param string $hostIp
 * @param string $hostUser
 * @param int $hostPort
 * @param string $serviceName
 * @param string $user
 * @param string $execStart
 * @return string
 */
function createSystemdServiceOnRemoteServer(string $hostIp, string $hostUser, int $hostPort, string $serviceName, string $user, string $execStart) : string
{
    $content = createSystemdServiceFileContent($serviceName, $user, $user, $serviceName, $execStart);
    $strValue = createFileOnRemoteServer($hostIp, $hostUser, $hostPort, "/etc/systemd/system/{$serviceName}.service", $content);
    return $strValue;

}

function systemCtlOnRemoteServer(string $hostIp, string $hostUser, int $hostPort, string $sshKeyFile, string $serviceName, string $serviceCommand) {
    $sshKeyFile = realpath($sshKeyFile);
    $command = <<<EOF
sudo systemctl {$serviceCommand} {$serviceName}.service; systemctl status {$serviceName}.service
EOF;

    $strValue = executeCommandWithKeyInRemoteServer($hostIp, $hostUser, $hostPort, $sshKeyFile, $command, true);
    return $strValue;


}