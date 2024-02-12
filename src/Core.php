<?php
declare(strict_types=1);

namespace labo86\escripta;


use Exception;
use Throwable;

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
            $content = $block['content'];


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
        $mergedBlock = array_merge($refBlock, $block);
        $mergedBlock['content'] = $refBlock['content'];
        $mergedBlock['lang'] = $refBlock['lang'];
        unset($mergedBlock['params']['id']);
        unset($mergedBlock['params']['ref']);
        unset($mergedBlock['params']['hidden']);
        return $mergedBlock;
    }

    /**
     * @throws Throwable
     */
    static function translateMdPhpFolder(string $folder): array
    {
        $codeBlockList = [];
        {
            //but not using glob


            foreach ( Util::glob($folder, "*.md.php") as $file) {
                $pathName = $file;

                //if filename ends with .md.php
                echo "Traduciendo $pathName...\n";
                $newCodeBlockList = self::translateMdPhpFile($pathName);
                $count = count($newCodeBlockList);
                echo " [$count] bloques encontrados\n";

                array_push($codeBlockList, ...$newCodeBlockList);
            }
        }
        return $codeBlockList;
    }

    static function cleanGeneratedFiles(string $folder) {
        foreach (Util::glob("$folder", "*.escripta.*") as $file) {
            Util::removeFileRecursive($file);
        }

        foreach (Util::glob("$folder", "*.escripta") as $file) {
            Util::removeFileRecursive($file);
        }

        foreach (Util::glob("$folder", "*.md") as $file) {
            Util::removeFileRecursive($file);
        }

        Util::removeFileRecursive("$folder/files");
    }

    /**
     * @throws Throwable
     */
    static function translateMdPhpFile(string $file): array
    {
        $markdown = Util::outputBufferSafe(function() use($file) {
            include  $file;
        });

        return Core::getCodeBlockList($markdown);
    }

    /**
     * @throws Throwable
     */
    static function processFolderByCommandLine() {


        global $argv;

        //get version
        if ( count($argv) === 2 && $argv[1] === '--version' ) {
            global $escriptaVersion;
            global $escriptaDate;
            echo <<<EOF
Escripta, la desplegadora
=========================
Version: $escriptaVersion
Fecha de compilación: $escriptaDate

Hora de la ciudad de La Concepción de María Purísima del Nuevo Extremo, Reino de Chile, fundada el 5 de octubre de 1550 por el gobernador Don Pedro de Valdivia.
Fecha desde la primera venida de nuestro señor Jesuscristo. 

EOF;
            return;
        }


        $folder = getcwd();



        echo "Removiendo archivos viejos...\n";
        self::cleanGeneratedFiles($folder);


        echo "Traduciendo archivos md.php...\n";
        $codeBlockList = self::translateMdPhpFolder($folder);
        $count = count($codeBlockList);
        echo "Carpeta: [$folder] con [$count] bloques encontrados:\n";

        echo "Procesando bloques...\n";
        $blockListProcessor = new BlockListProcessor();
        $blocks = $blockListProcessor->process($codeBlockList);
        $count = count($blocks);
        echo "Carpeta: [$folder] con [$count] bloques a generar:\n";

        //print processed blocks
        foreach ( $blocks as $subFolder => $blockList ) {

            foreach ( $blockList as $block ) {
                echo "  - " . $subFolder . "/" . $block['fileName'] . "\n";
            }
        }

        echo "Guardando archivos...\n";
        $blockListProcessor->save($folder);
    }
}