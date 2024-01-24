<?php
declare(strict_types=1);

namespace labo86\action_scripts;

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
        $addFile('Escript.php');
        $phar->setStub(<<<'EOF'
<?php

$PHAR_NAME = 'action_scripts.phar';

Phar::mapPhar($PHAR_NAME);

require_once("phar://${PHAR_NAME}/src/Common.php");
require_once("phar://${PHAR_NAME}/src/OnePassword.php");
require_once("phar://${PHAR_NAME}/src/Escript.php");

__HALT_COMPILER();
EOF);

        $phar->stopBuffering();


    }










}