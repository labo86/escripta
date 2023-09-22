<?php
declare(strict_types=1);
# version 1.0.0

require_once(__DIR__ . '/common.php');

function executeCommandInRemoteServer(string $hostIp, string $hostUser, int $hostPort, string $command ): string
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
    $strValue = executeCommandAndGetStdOut($sshCommand);
    return $strValue;
}

/**
 * @throws Exception
 */
function executeComplexCommandInRemoteServer(string $hostIp, string $hostUser, int $hostPort, string $command ): string {
    //generate temp file with command as a content
    $tempFile = tempnam(sys_get_temp_dir(), 'tmp_cmd_');
    file_put_contents($tempFile, $command);

    uploadFilesToRemoteServer($hostIp, $hostUser, $hostPort, [$tempFile]);


    $executionCommand = sprintf("bash %s", escapeshellarg(basename($tempFile)));

    $strValue = executeCommandInRemoteServer($hostIp, $hostUser, $hostPort, $executionCommand);
    return $strValue;

}

/**
 * @throws Exception
 */
function uploadFilesToRemoteServer(string $hostIp, string $hostUser, int $hostPort, array $files) {

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
