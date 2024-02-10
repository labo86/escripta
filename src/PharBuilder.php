<?php
declare(strict_types=1);

namespace labo86\escripta;

use Phar;

class PharBuilder {

    static public function build(string $filePath) {
        $phar = new Phar($filePath);

        $phar->startBuffering();

        $addFile = function(string $file) use ($phar) {
            $phar->addFile(__DIR__ . '/' . $file, 'src/' . $file);
        };

        $addFile('Common.php');
        $addFile('OnePassword.php');
        $addFile('Escripta.php');
        $addFile('Config.php');
        $phar->setStub(<<<'EOF'
#!/usr/bin/php
<?php

$PHAR_NAME = 'escript.phar';

Phar::mapPhar($PHAR_NAME);

require_once("phar://${PHAR_NAME}/src/Common.php");
require_once("phar://${PHAR_NAME}/src/OnePassword.php");
require_once("phar://${PHAR_NAME}/src/Escripta.php");
require_once("phar://${PHAR_NAME}/src/Config.php");

if (isset($argv[0])) {
    $scriptName = realpath($argv[0]);
    $currentPhar = __FILE__;
    if ( $scriptName === $currentPhar ) {
        \labo86\action_scripts\Escripta::processFolderByCommandLine();
    }
}

__HALT_COMPILER();
EOF);

        $phar->stopBuffering();


    }










}