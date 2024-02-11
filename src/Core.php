<?php
declare(strict_types=1);

namespace labo86\escripta;


use DirectoryIterator;
use Exception;

class Core {

  const LANG = 'lang';
  const PARAMS = 'params';
  const CONTENT = 'content';

/**
 * si la linea hace match con una linea que empieza con tres backtics y despues solo espacios
 * @param string $line
 * @return bool
 */
    static function isLineCodeEnd(string $line): bool
    {
        return preg_match('/^```\s*$/', $line) === 1;
    }


    /**
     * si la linea coincides con tres backticks seguida por una palabra despues la palabra escripts y despues parametros de la forma name=value separador por espacios
     * @param string $line
     * @return array{lang: string, params : array }|bool
     */
    static function isLineCodeStart(string $line): array|bool
    {
        if ( preg_match('/^```([a-zA-Z0-9_]+)\s+escripta(\s+.*)?$/', $line, $matches) === 1 ) {

            $parameters = $matches[2];
            if ( is_null($parameters) ) {
                $parameters = '';
            } else {
                $parameters = trim($parameters);
            }
            if ( $parameters === '' ) {
                $parameters = [];
            } else {
                $parameters = explode(' ', $parameters);
                $parameters = array_map(function($item) {
                    $item = explode('=', $item);
                    if ( count($item) === 1 ) {
                        $item = [$item[0], true];
                    }
                    return $item;
                }, $parameters);
                $parameters = array_combine(array_column($parameters, 0), array_column($parameters, 1));
            }

            return [
                self::LANG => $matches[1],
                self::PARAMS => $parameters
            ];
        } else {
            return false;
        }
    }

    /**
     * Returns and object that is the param of processBlock
     * @param string $text
     * @return array{lang: string, params : array, content: string}[]

     */
    static function getCodeBlockList(string $text) : array {
        $lines = explode("\n", $text);
        $codeBlockList = [];
        $currentCodeBlock = null;
        foreach ( $lines as $line ) {
            if ( is_null($currentCodeBlock) ) {
                if ( ($newCodeBlock = self::isLineCodeStart($line)) !== false ) {
                    $newCodeBlock[self::CONTENT] = '';
                    $currentCodeBlock = $newCodeBlock;
                }
            } else {
                if ( self::isLineCodeEnd($line) ) {
                    $codeBlockList[] = $currentCodeBlock;
                    $currentCodeBlock = null;
                } else {
                    $currentCodeBlock[self::CONTENT] .= $line . "\n";
                }
            }
        }
        return $codeBlockList;
    }

    /**
     * @param array{lang: string, params : array, content: string} $block
     * @return string
     * @throws \Exception
     */
    static function processBlock(array $block) : string {
        if ( $block['lang'] === 'bash' ) {
            $content = <<<EOF
#!/bin/bash
# ESCRIPTA, la desplegadora, generó este script
#
EOF;
            foreach ( $block['params'] as $key => $value ) {
                $content .= "\n# $key=$value";
            }

            $content .= "\n\n" . $block['content'];

        } else if ( $block['lang'] === 'php' ) {
            $content = <<<EOF
#!/usr/bin/php
<?php
declare(strict_types=1);

// ESCRIPTA, la desplegadora, generó este script
EOF;

            foreach ( $block['params'] as $key => $value ) {
                $content .= "\n// $key=$value";
            }

            $content .= "\n\n?>" . $block['content'];
        } else {
            $content = <<<EOF
#!/usr/bin/cat
EOF;

            $content .= $block['content'];


        }
        return $content;
    }

    /**
     * @param array $blockParams
     * @return string
     */
    static function generateFileName(array $block) : string {

        $blockParams = $block['params'];
        $name = $blockParams['name'] ?? $blockParams['original'] ?? 'script';
        $name = preg_replace('/[^a-zA-Z0-9_]/', '_', $name);
        $name .= '.escripta';

        if ( $block['lang'] === 'php' ) {
            $name .= '.php';
        } else if ( $block['lang'] === 'bash' ) {
            $name .= '.sh';
        } else {
            $name .= '.txt';
        }
        return $name;

    }

    static function mergeBlock(array $block, array $refBlock) : array {
        $mergedBlock = array_merge($block, $refBlock);
        $mergedBlock['content'] = $block['content'];
        return $mergedBlock;
    }

    /**
     * @throws \Throwable
     */
    static function translateMdPhpFolder(string $folder) {
        $codeBlockList = [];
        {
            //but not using glob


            foreach ( Util::iterateFilesThatEndsWith($folder, ".md.php") as $file) {

                //if filename ends with .md.php
                echo "Traduciendo $file...\n";
                $newCodeBlockList = self::translateMdPhpFile($file);

                array_push($codeBlockList, ...$newCodeBlockList);
            }
        }
        return $codeBlockList;
    }

    /**
     * @throws \Throwable
     */
    static function translateMdPhpFile(string $file): array
    {
        $markdown = Util::outputBufferSafe(function() use($file) {
            include  $file;
        });

        return Core::getCodeBlockList($markdown);
    }

    static function processFolderByCommandLine() {

        global $argv;
        if ( count($argv) !== 2 ) {
            echo "Usage: ./escripta.phar <folder>\n";


            $phar = debug_backtrace()[0]['file'];
            $dir = dirname($phar);
            $dir = realpath($dir);

            $configFile = "$dir/.escripta.json";
            if ( file_exists($configFile) ) {
                $contents = file_get_contents($configFile);
                $config = json_decode($contents, true);
                if ( $config === null ) {
                    throw new Exception("Error de formato de $configFile");
                } else {
                    echo "Using config file $configFile\n";
                    echo "$contents\n";
                }
            }
            else {
                echo "Archivo de configuración [$configFile] no encontrado\n";
            }

        }

        $folder = $argv[1];

        if ( !is_dir($folder) ) {
            throw new Exception( "La carpeta [$folder] no existe");
        }

        {
            echo "Removiendo archivos viejos...\n";

            //delete all files that match the pattern
            foreach (glob("$folder/*.escripta.*") as $file) {
                unlink($file);
            }

            passthru("rm $folder/*.escripta -rf");
            passthru("rm $folder/files -rf");
            passthru("rm $folder/*.md -rf");
        }

        echo "Traduciendo archivos md.php...\n";
        $codeBlockList = self::translateMdPhpFolder($folder);

        {
            $storedBlockList = [];


            $i = 1;
            foreach ($codeBlockList as $block) {

                if ( $block['params']['id'] ?? false ) {
                    $id = $block['params']['id'];
                    $storedBlockList[$id] = $block;
                }

                $targetFolder = $folder;
                if (isset($block['params']['dir'])) {
                    $dir = $block['params']['dir'] . ".escripta";
                    $targetFolder = "$folder/$dir";
                    if (!is_dir($targetFolder)) {
                        mkdir($targetFolder);
                    }
                }

                if ($block['params']['file'] ?? false) {
                    $targetFolder = "$targetFolder/files";
                    if (!is_dir($targetFolder)) {
                        mkdir($targetFolder);
                    }
                    $fileName = $block['params']['name'];
                    $filePath = "$targetFolder/$fileName";
                    file_put_contents($filePath, $block['content']);
                    echo "Copiando archivo $fileName\n";

                } else {

                    if ( isset($block['params']['ref']) ) {
                        $ref = $block['params']['ref'];
                        if ( !isset($storedBlockList[$ref]) ) {
                            echo "Referencia $ref no encontrada\n";
                        }
                        $refBlock = $storedBlockList[$ref];
                        $block = Core::mergeBlock($block, $refBlock);
                    }

                    $numberPrefix = str_pad((string)$i, 2, '0', STR_PAD_LEFT);
                    $scriptName = Core::generateFileName($block);
                    $scriptContent = Core::processBlock($block);

                    echo "Generando archivo $numberPrefix.$scriptName...\n";
                    $fileName = "$numberPrefix.$scriptName";
                    $filePath = "$targetFolder/$fileName";

                    file_put_contents($filePath, $scriptContent);
                    chmod($filePath, 0755);
                    $i++;
                }
            }
        }
    }
}