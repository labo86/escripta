<?php
declare(strict_types=1);

namespace labo86\escripta;

use Exception;

class BlockListProcessor
{

    public array $folderList = [];

    /**
     * @throws Exception
     */
    public function process(array $codeBlockList) : array {
        $this->folderList = [];

        $blockProcessor = new BlockProcessor();
        foreach ($codeBlockList as $block) {


            $targetFolder = $blockProcessor->getTargetFolder($block);

            if ($block[Core::PARAMS]['file'] ?? false) {
                $targetFolder .= "/files";
                $fileName = $block[Core::PARAMS]['name'];
                $this->folderList[$targetFolder][] = [
                    'fileName' => $fileName,
                    'content' => $block[Core::CONTENT]
                ];
            } else if ( $block[Core::LANG] == "escripta" ) {
                $blockProcessor->processCommandBlockData($block);
            } else {
                $this->folderList[$targetFolder][] = $blockProcessor->generateBlockData($block);

            }
        }

        return $this->folderList;
    }





    public function save(string $targetFolder) : int {
        $fileCount = 0;
        foreach ($this->folderList as $folder => $fileList) {

            $folder = $folder === "." ? $targetFolder : "$targetFolder/$folder";
            if (!is_dir($folder)) {
                mkdir($folder, 0777, true);
            }
            foreach ($fileList as $file) {

                $filePath = "$folder/{$file['fileName']}";
                echo "Guardando [$filePath]...";


                file_put_contents($filePath, $file['content']);
                chmod($filePath, 0755);
                echo "HECHO\n";
                $fileCount++;
            }
        }
        return $fileCount;
    }
}