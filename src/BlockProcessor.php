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
}