<?php
declare(strict_types=1);
# version 1.1.0

namespace labo86\escripta;

class ConfigWriter
{
    /** @deprecated */
    static function write(string $targetFolder, string $targetConfigName, array $itemInfo) {
        self::writeIniFile($targetFolder, $targetConfigName, $itemInfo);
        self::writeMultilineFiles($targetFolder, $targetConfigName, $itemInfo);
    }

    static function writeInFiles(string $targetFolder, string $targetConfigName, array $itemInfo) {
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
    /** @deprecated */
    static function writeIniFile(string $targetFolder, string $targetConfigName, array $itemInfo) : string
    {
        $iniFormatString = Util::arrayToIniFormat($itemInfo);
        if (!is_dir($targetFolder)) {
            mkdir($targetFolder, 0755, true);
        }
        file_put_contents("$targetFolder/$targetConfigName.ini", $iniFormatString);

        return $iniFormatString;
    }

    /** @deprecated */
    static function writeMultilineFiles(string $targetFolder, string $targetConfigName,array $itemInfo) : void {
        foreach ($itemInfo as $key => $value) {

            if (Util::isStringMultiline($value)) {
                file_put_contents("$targetFolder/$targetConfigName.$key", $value);
                if ( $key === 'private_key' )
                    chmod("$targetFolder/$targetConfigName.$key", 0600);
            }

        }
    }

}