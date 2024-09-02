<?php
declare(strict_types=1);
# version 1.1.0

namespace labo86\escripta;

class OnePassword
{

    static function getValue(string $itemName): string
    {

        $itemName = escapeshellarg($itemName);
        ob_start();
        passthru("op read --cache $itemName", $return);
        /** @noinspection PhpUnnecessaryLocalVariableInspection */
        $strValue = ob_get_clean();
        return $strValue;
    }

    static function getItemRawInfo(string $itemName): ?array
    {
        $itemName = escapeshellarg($itemName);
        ob_start();
        passthru("op item get --cache $itemName --format json", $return);

        $jsonString = ob_get_clean();

        if ($return !== 0) return null;

        /** @noinspection PhpUnnecessaryLocalVariableInspection */
        $arrayData = json_decode($jsonString, true);
        return $arrayData;
    }

    static function getItemInfo(array $itemRawInfo): array
    {
        $itemList = [];
        foreach ($itemRawInfo["fields"] as $field) {
            $label = $field["label"];
            $type = $field["type"];
            $value = $field["value"] ?? null;

            //ignorar descripcion
            if ($field['id'] === 'notesPlain')
                continue;

            if ($value === null) {
                continue;
            }

            if ($type === "SSHKEY") {
                $itemList["private_key"] = $field["ssh_formats"]["openssh"]["reference"];
            } else if (!in_array($type, ["CONCEALED", "STRING"])) {
                continue;
            } else if ( $field['id'] === "public_key") {
                $itemList[$field['id']] = $value;
            } else {
                $itemList[$label] = $value;
            }
        }
        return $itemList;
    }

    static function writeIniFile(string $targetFolder, string $targetConfigName, array $itemInfo) : string
    {
        $iniFormatString = Util::arrayToIniFormat($itemInfo);
        if (!is_dir($targetFolder)) {
            mkdir($targetFolder, 0755, true);
        }
        file_put_contents("$targetFolder/$targetConfigName.ini", $iniFormatString);

        return $iniFormatString;
    }

    static function writeKeyFile(string $targetFolder, string $targetConfigName, array $itemInfo) : void {
        if (isset($itemInfo['private_key'])) {
            $privateKeyRef = $itemInfo['private_key'];
            $privateKey = self::getValue($privateKeyRef);
            file_put_contents("$targetFolder/$targetConfigName.private_key", str_ireplace("\r", "", $privateKey));
            chmod("$targetFolder/$targetConfigName.private_key", 0600);
        }
    }

    static function writeMultilineFiles(string $targetFolder, string $targetConfigName,array $itemInfo) : void {
        foreach ($itemInfo as $key => $value) {
            if ( $key === 'private_key' )
                continue;
            if (Util::isStringMultiline($value)) {
                file_put_contents("$targetFolder/$targetConfigName.$key", $value);
            }
        }
    }

}