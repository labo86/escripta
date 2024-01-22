#!/usr/bin/php
<?php
declare(strict_types=1);

require_once(__DIR__ . '/../src/Escript.php');

use labo86\action_scripts\Escript;

if ( count($argv) !== 2 ) {
    echo "Usage: php escript.php <folder>\n";
    exit(1);
}

$folder = $argv[1];

if ( !is_dir($folder) ) {
    echo "Folder $folder does not exist\n";
    exit(1);
}

if ( !file_exists("$folder/index.md.php") ) {
    echo "File $folder/index.md.php does not exist\n";
    exit(1);
}

passthru("php $folder/index.md.php > $folder/index.md");

$markdown = file_get_contents("$folder/index.md");

$codeBlockList = Escript::getCodeBlockList($markdown);
$i = 1;
foreach ( $codeBlockList as $block ) {
    $numberPrefix = str_pad((string)$i, 2, '0', STR_PAD_LEFT);

    $scriptName = Escript::generateFileName($block);
    $scriptContent = Escript::processBlock($block);
    $filePath = "$folder/$numberPrefix.$scriptName";
    file_put_contents($filePath, $scriptContent);
    chmod($filePath, 0755);
    $i++;
}


