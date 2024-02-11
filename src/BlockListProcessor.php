<?php
declare(strict_types=1);

namespace labo86\escripta;

use Exception;

class BlockListProcessor
{

    public array $storedBlockList = [];
    public array $folderList = [];

    /**
     * @throws Exception
     */
    public function process(array $codeBlockList) : array {
        $this->storedBlockList = [];
        $this->folderList = [];


        $i = 1;
        foreach ($codeBlockList as $block) {

            $this->storeReferencedBlock($block);

            $targetFolder = BlockProcessor::getTargetFolder($block);

            if ($block['params']['file'] ?? false) {
                $targetFolder .= "/files";
                $fileName = $block['params']['name'];
                $this->folderList[$targetFolder][] = [
                    'fileName' => $fileName,
                    'content' => $block['content']
                ];

            } else {

                $block = $this->getReferencedBlock($block);

                $this->folderList[$targetFolder][] = BlockProcessor::generateBlockData($i, $block);

                $i++;
            }
        }

        return $this->folderList;
    }

    public function storeReferencedBlock(array $block) : void {
        if ( $block['params']['id'] ?? false ) {
            $id = $block['params']['id'];
            $this->storedBlockList[$id] = $block;
        }
    }

    /**
     * Retorna un bloque referenciado si es referencia, en caso contrario retorna el bloque original
     * @throws Exception
     */
    public function getReferencedBlock(array $block) : array {
        if ( isset($block['params']['ref']) ) {
            $ref = $block['params']['ref'];
            if ( !isset($this->storedBlockList[$ref]) ) {
                throw new Exception("Referencia [$ref] no encontrada en bloque [{$block['params']['name']}]");
            }
            $refBlock = $this->storedBlockList[$ref];
            return Core::mergeBlock($block, $refBlock);
        } else {
            return $block;
        }
    }



    public function save(string $targetFolder) : void {
        foreach ($this->folderList as $folder => $file) {
            $folder = "$targetFolder/$folder";
            $filePath = "$folder/{$file['fileName']}";
            echo "Guardando [$filePath]...";
            if (!is_dir($folder)) {
                mkdir($folder, 0777, true);
            }

            file_put_contents($filePath, $file['content']);
            chmod($filePath, 0755);
            echo "HECHO\n";
        }
    }
}