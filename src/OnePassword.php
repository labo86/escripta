<?php
declare(strict_types=1);
# version 1.1.0

namespace labo86\action_scripts;

class OnePassword
{

    static function getValue(string $itemName): string
    {
        ob_start();
        passthru("op read --cache " . escapeshellarg($itemName), $return);
        /** @noinspection PhpUnnecessaryLocalVariableInspection */
        $strValue = ob_get_clean();
        return $strValue;
    }

    static function getItemRawInfo(string $itemName): array
    {
        ob_start();
        passthru("op item get --cache $itemName --format json", $return);

        $jsonString = ob_get_clean();
        /** @noinspection PhpUnnecessaryLocalVariableInspection */
        $arrayData = json_decode($jsonString, true);
        return $arrayData;
    }

    static function getItemInfo(string $itemName): array
    {
        $itemRawInfo = self::getItemRawInfo($itemName);
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
            } else {
                $itemList[$label] = $value;
            }
        }
        return $itemList;
    }

    static function getItemListByTags(string ...$tags)
    {
        $tagString = join(",", $tags);
        $command = "op item list --tags $tagString --cache --format json";
        $jsonString = Common::executeCommandAndGetStdOut($command);

        /** @noinspection PhpUnnecessaryLocalVariableInspection */
        $arrayData = json_decode($jsonString, true);
        return $arrayData;
    }

    static function getConfigEnvironmentList(string $targetProjectName, string $targetConfigName): array
    {
        $itemList = self::getItemListByTags($targetProjectName, "config");
        $environments = [];
        $prefix = "{$targetProjectName}_config_{$targetConfigName}_";
        foreach ($itemList as $item) {
            $title = $item['title'];
            //remove the prefix of a string
            if (!str_starts_with($title, $prefix))
                continue;
            $environment = substr($title, strlen($prefix));

            $environments[$environment] = [
                'id' => $item["id"],
                'title' => $item['title']
            ];
        }
        return $environments;
    }

    static function getConfigEnvironmentByCommandLine(string $targetProjectName, array $configNames, string $targetFolder): void
    {
        global $argv;

        if (count($argv) < 2) {
            echo "Usage: config.php <config> <environment>\n\n";
            echo "Available configs:\n";
            foreach ($configNames as $configName) {
                echo $configName . "\n";
            }

            exit(1);
        }

        $targetConfigName = $argv[1];


        if (count($argv) < 3) {
            $environmentList = self::getConfigEnvironmentList($targetProjectName, $targetConfigName);
            echo "Usage: config.php $targetConfigName <environment>\n\n";
            echo "Available environments:\n";
            foreach ($environmentList as $envName => $envData) {
                echo $envName . "\n";
            }
            exit(1);
        }

        self::getConfig($argv[2], $targetProjectName, $targetConfigName, $targetFolder);


    }

    static function getConfig(string $environment, string $targetProjectName, string $targetConfigName, string $targetFolder) {
        {
            $configName = "{$targetProjectName}_config_{$targetConfigName}_{$environment}";
            echo "Retrieving Information [$configName]:\n\n";
            $itemInfo = self::getItemInfo($configName);
            $iniFormatString = Common::arrayToIniFormat($itemInfo);
            echo $iniFormatString . "\n\n";
            if (!is_dir($targetFolder)) {
                mkdir($targetFolder, 0755, true);
            }
            file_put_contents("$targetFolder/$targetConfigName.ini", $iniFormatString);

            if (isset($itemInfo['private_key'])) {
                $privateKeyRef = $itemInfo['private_key'];
                $privateKey = self::getValue($privateKeyRef);
                file_put_contents("$targetFolder/$targetConfigName.key", str_ireplace("\r", "", $privateKey));
                chmod("$targetFolder/$targetConfigName.key", 0600);
            }

        }
    }
}