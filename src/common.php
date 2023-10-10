<?php
declare(strict_types=1);
# version 1.1.0

function executeCommandAndGetStdOut(string $command, bool $captureStdout = true) : string
{
    if ($captureStdout) {
        ob_start();
    }
    passthru($command, $return);
    /** @noinspection PhpUnnecessaryLocalVariableInspection */
    if ($captureStdout) {
        $strValue = ob_get_clean();
        return $strValue;
    } else {
        return "";
    }
}

function arrayToIniFormat(array $data) : string {
    $arrayString = [];
    foreach ( $data  as $key => $value ) {
        $arrayString[] = join("=", [$key, $value]);
    }
    $stringData = join("\n", $arrayString);
    return $stringData;
}

function loadConfigs(string $baseDir, array $configFiles) : array {
    $config = [];
    foreach ( $configFiles as $file ) {
        $data = parse_ini_file($baseDir . '/' . $file, true, INI_SCANNER_RAW );
        if ( $data ) {
            $config = array_merge($config, $data);
        }
    }
    return $config;
}

function runCommandInteractive(string $command) : void {
    $descriptorSpec = array(
        0 => STDIN,
        1 => STDOUT,
        2 => STDERR
    );
    $process = proc_open($command, $descriptorSpec, $pipes);
    if (is_resource($process)) {
        proc_close($process);
    }
}