<?php
declare(strict_types=1);

namespace labo86\escripta;

use ArrayAccess;
use Exception;

class Config
{
    static function loadConfigFile($filename) {
        $extension = pathinfo($filename, PATHINFO_EXTENSION);
        $basename = basename($filename);
        fwrite(STDERR, " - $filename\n");

        if ( $extension === 'ini' ) {
            $data = parse_ini_file($filename, true, INI_SCANNER_RAW);

            if ($data === false) {
                return [];
            }

            $baseNameWithoutExtension = pathinfo($basename, PATHINFO_FILENAME);
            $prefix = str_starts_with($baseNameWithoutExtension, '_') ? '' : $baseNameWithoutExtension;

            return self::flattenIniConfig($data, $prefix);
        } else {
            return [
                $basename => file_get_contents($filename)
            ];
        }
    }

    static function loadConfigDir(string $baseDir): array
    {
        $configs = [];

        foreach ( Util::glob($baseDir,  "*") as $file ) {
            if ( is_dir($file) )
            {
                $configs[] = self::loadConfigDir($file);
            } else {
                $configs[] = self::loadConfigFile($file);
            }
        }


        return array_merge(...$configs);
    }

    public static function writeInFiles(string $targetFolder, string $targetConfigName, array $itemInfo)
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

}
