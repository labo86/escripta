<?php
declare(strict_types=1);
# version 1.1.0

require_once(__DIR__ . '/common.php');

function get1PasswordValue(string $itemName) : string {
    ob_start();
    passthru("op read --cache " . escapeshellarg($itemName), $return);
    /** @noinspection PhpUnnecessaryLocalVariableInspection */
    $strValue = ob_get_clean();
    return $strValue;
}

function get1PasswordItemRawInfo(string $itemName) : array {
    ob_start();
    passthru("op item get --cache $itemName --format json", $return);

    $jsonString = ob_get_clean();
    /** @noinspection PhpUnnecessaryLocalVariableInspection */
    $arrayData = json_decode($jsonString, true);
    return $arrayData;
}

function get1PasswordItemInfo(string $itemName) : array {
    $itemRawInfo = get1PasswordItemRawInfo($itemName);
    $itemList = [];
    foreach ( $itemRawInfo["fields"] as $field ) {
        $label = $field["label"];
        $type = $field["type"];
        $value = $field["value"] ?? null;

        //ignorar descripcion
        if ( $field['id'] === 'notesPlain' )
            continue;

        if ( $value === null ) {
            continue;
        }

        if ( $type === "SSHKEY") {
            $itemList["private_key"] = $field["ssh_formats"]["openssh"]["reference"];
        } else if ( ! in_array($type, ["CONCEALED", "STRING"]) ) {
            continue;
        } else {
            $itemList[$label] = $value;
        }
    }
    return $itemList;
}

function get1PasswordItemListByTags(string ... $tags) {
    $tagString = join(",", $tags);
    $command = "op item list --tags $tagString --cache --format json";
    $jsonString = executeCommandAndGetStdOut($command);

    /** @noinspection PhpUnnecessaryLocalVariableInspection */
    $arrayData = json_decode($jsonString, true);
    return $arrayData;
}

function get1PasswordConfigEnvironmentList(string $targetProjectName, string $targetConfigName) : array {
    $itemList = get1PasswordItemListByTags($targetProjectName, "config");
    $environments = [];
    $prefix = "{$targetProjectName}_config_{$targetConfigName}_";
    foreach ( $itemList as $item ) {
        $title = $item['title'];
        //remove the prefix of a string
        if ( !str_starts_with($title, $prefix) )
            continue;
        $environment = substr($title, strlen($prefix));

        $environments[$environment] = [
            'id' => $item["id"],
            'title' => $item['title']
        ];
    }
    return $environments;
}

function get1PasswordConfigEnvironmentByCommandLine(string $targetProjectName, string $targetConfigName, string $targetFolder) : void {
    global $argv;

    if ( count($argv) < 2 ) {
        $environmentList = get1PasswordConfigEnvironmentList($targetProjectName, $targetConfigName);
        echo "Available environments:\n";
        foreach ( $environmentList as $envName => $envData ) {
            echo $envName . "\n";
        }
    } else {
        echo "Retrieving Information:\n\n";
        $environment = $argv[1];
        $itemInfo = get1PasswordItemInfo("{$targetProjectName}_config_{$targetConfigName}_{$environment}");
        $iniFormatString = arrayToIniFormat($itemInfo);
        echo $iniFormatString . "\n\n";
        file_put_contents("$targetFolder/$targetConfigName.ini", $iniFormatString);

        if ( isset($itemInfo['private_key']) ) {
            $privateKeyRef = $itemInfo['private_key'];
            $privateKey = get1PasswordValue($privateKeyRef);
            file_put_contents("$targetFolder/$targetConfigName.key", $privateKey);
            chmod("$targetFolder/$targetConfigName.key", 0600);
        }

    }


}