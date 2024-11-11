<?php
declare(strict_types=1);

namespace labo86\escripta;

class BlockProcessor
{

    public static function getTargetFolder(array $block) : string {
        $targetFolder = ".";
        if (isset($block['params']['dir'])) {
            $dir = $block['params']['dir'] . ".escripta";
            $targetFolder = "$dir";
        }
        return $targetFolder;
    }

    public static function generateBlockData(int $index, array $block) : array {
        $numberPrefix = str_pad((string)$index, 2, '0', STR_PAD_LEFT);
        $scriptName = Core::generateFileName($block);
        $scriptContent = Core::processBlock($block);

        $fileName = self::isBlockNumbered($block) ? "$numberPrefix.$scriptName" : $scriptName;

        return [
            'fileName' => $fileName,
            'content' => $scriptContent
        ];
    }

    public static function isBlockNumbered(array $block) : bool {
        return filter_var($block['params']['numbered'] ?? true, FILTER_VALIDATE_BOOLEAN);
    }
}