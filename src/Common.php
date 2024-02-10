<?php
declare(strict_types=1);

namespace labo86\escripta;


class Common
{
    static function executeCommandAndGetStdOut(string $command, bool $captureStdout = true): string
    {
        if ($captureStdout) {
            ob_start();
        }
        passthru($command, $return);

        if ($captureStdout) {
            $strValue = ob_get_clean();
            return $strValue;
        } else {
            return "";
        }
    }

    static function arrayToIniFormat(array $data): string
    {
        $arrayString = [];
        foreach ($data as $key => $value) {
            $arrayString[] = join("=", [$key, $value]);
        }
        $stringData = join("\n", $arrayString);
        return $stringData;
    }

    static function loadConfigs(string $baseDir, array $configFiles): array
    {
        $config = [];
        foreach ($configFiles as $file) {
            $data = parse_ini_file($baseDir . '/' . $file, true, INI_SCANNER_RAW);
            if ($data) {
                $config = array_merge($config, $data);
            }
        }
        return $config;
    }

    static function runCommandInteractive(string $command): void
    {
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

    static function loadConfigsAndKeys(string $baseDir, array $configFiles): array
    {
        $config = [];
        $keys = [];
        foreach ($configFiles as $file) {
            $filePath = $baseDir . '/' . $file . '.ini';
            if ( file_exists($filePath) ) {

                $data = parse_ini_file($filePath, true, INI_SCANNER_RAW);
                if ($data) {
                    $config = array_merge($config, $data);
                }
            }

            $filePath = $baseDir . '/' . $file . '.key';
            if ( file_exists($filePath) ) {
                $keys[$file] = $filePath;
            }
        }

        return [$config, $keys];
    }
}