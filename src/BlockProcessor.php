<?php
declare(strict_types=1);

namespace labo86\escripta;

class BlockProcessor
{

    public int $index;
    public string $targetFolder;
    public function __construct() {
        $this->index = 1;
        $this->targetFolder = ".";
    }

    public function getTargetFolder(array $block) : string {
        if (isset($block['params']['dir'])) {
            $dir = $block['params']['dir'] . ".escripta";
            return "$dir";
        } else if ( $this->targetFolder !== "." ) {
            $dir = $this->targetFolder . ".escripta";
            return "$dir";
        } else {
            return $this->targetFolder;
        }
    }

    public function generateBlockData(array $block) : array {
        $numberPrefix = str_pad((string)$this->index, 2, '0', STR_PAD_LEFT);
        $scriptName = Core::generateFileName($block);
        $scriptContent = Core::processBlock($block);

        $fileName = self::isBlockNumbered($block) ? "$numberPrefix.$scriptName" : $scriptName;
        if ( self::isBlockNumbered($block) ) {
            $this->index++;
        }

        return [
            'fileName' => $fileName,
            'content' => $scriptContent
        ];
    }

    public function processCommandBlockData(array $block) : void {
        $command = $block[Core::PARAMS]['command'] ?? "";

        if ( $command == "reset_numbers" ) {
            $this->index = 1;
        } else if ( $command == "set_dir" ) {
            $dir = $block[Core::PARAMS]['dir'] ?? ".";
            $this->targetFolder = $dir;
        } else if ( $command === "unset_dir" ) {
            $this->targetFolder = ".";
        } else {

        }
    }

    public static function isBlockNumbered(array $block) : bool {
        return filter_var($block['params']['numbered'] ?? true, FILTER_VALIDATE_BOOLEAN);
    }
}