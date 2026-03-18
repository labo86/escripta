<?php
declare(strict_types=1);
# version 1.1.0

namespace labo86\escripta\connectors;

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

    static function getItemInfo(array $itemRawInfo, callable $getValueFunction = null): array
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
                $reference = $field["ssh_formats"]["openssh"]["reference"];
                if ( is_null($getValueFunction) ) {
                    $reference = self::getValue($reference);
                } else {
                    $reference = $getValueFunction($reference);
                }
                $itemList["private_key"] = str_ireplace("\r", "", $reference );

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

}