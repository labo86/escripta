<?php
declare(strict_types=1);

namespace labo86\escripta\connectors;

use labo86\escripta\Util;

class Config
{
    public static function loadConfigFile($filename, string $prefix = '', bool $includeOwnNameAsPrefix = true): array
    {
        $extension = pathinfo($filename, PATHINFO_EXTENSION);
        $basename = basename($filename);
        fwrite(STDERR, " - $filename\n");

        if ($extension === 'ini') {
            $data = parse_ini_file($filename, true, INI_SCANNER_RAW);

            if ($data === false) {
                return [];
            }

            $baseNameWithoutExtension = pathinfo($basename, PATHINFO_FILENAME);
            $filePrefix = self::composePrefix(
                $prefix,
                self::shouldPrefixName($baseNameWithoutExtension, $includeOwnNameAsPrefix) ? $baseNameWithoutExtension : ''
            );

            return self::flattenIniConfig($data, $filePrefix);
        }

        return [
            $basename => file_get_contents($filename)
        ];
    }

    public static function loadConfigDir(string $baseDir, string $prefix = '', bool $includeOwnNameAsPrefix = true): array
    {
        $configs = [];
        $baseDirPrefix = self::composePrefix(
            $prefix,
            self::shouldPrefixName(basename($baseDir), $includeOwnNameAsPrefix) ? basename($baseDir) : ''
        );

        foreach (Util::glob($baseDir,  "*") as $file) {
            if (is_dir($file)) {
                $dirPrefix = self::composePrefix(
                    $baseDirPrefix,
                    str_starts_with(basename($file), '_') ? '' : basename($file)
                );
                $configs[] = self::loadConfigDir($file, $dirPrefix, false);
            } else {
                $configs[] = self::loadConfigFile($file, $baseDirPrefix);
            }
        }

        return $configs === [] ? [] : array_merge(...$configs);
    }

    public static function writeInFiles(string $targetFolder, string $targetConfigName, array $itemInfo): void
    {
        if (!is_dir($targetFolder)) {
            mkdir($targetFolder, 0755, true);
        }

        foreach ($itemInfo as $key => $value) {
            $configKey = preg_replace('/\s+/', '_', "{$targetConfigName}_{$key}");

            $filename = "$targetFolder/$configKey";
            file_put_contents($filename, $value);
            chmod($filename, 0600);
        }
    }

    private static function flattenIniConfig(array $config, string $prefix = ''): array
    {
        $flattened = [];

        foreach ($config as $key => $value) {
            $composedKey = $prefix === '' ? (string) $key : $prefix . '_' . $key;

            if (is_array($value)) {
                $flattened = array_merge($flattened, self::flattenIniConfig($value, $composedKey));
                continue;
            }

            $flattened[$composedKey] = (string) $value;
        }

        return $flattened;
    }

    private static function composePrefix(string $prefix, string $segment): string
    {
        if ($prefix === '') {
            return $segment;
        }

        if ($segment === '') {
            return $prefix;
        }

        return $prefix . '_' . $segment;
    }

    private static function shouldPrefixName(string $name, bool $includeOwnNameAsPrefix): bool
    {
        if (!$includeOwnNameAsPrefix) {
            return false;
        }

        return !str_starts_with($name, '_');
    }
}
