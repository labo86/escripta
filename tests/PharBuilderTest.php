<?php
declare(strict_types=1);

namespace labo86\escripta\tests;

use labo86\escripta\PharBuilder;
use PHPUnit\Framework\TestCase;

class PharBuilderTest extends TestCase
{

    public function testBuild()
    {

        //delete dir var if exists
        $varDir = __DIR__ . '/var';

        if ( is_dir($varDir) ) {
            exec("rm -rf $varDir");
        }

        //create dir var
        mkdir($varDir);


        $buildCreatorScript = __DIR__ . '/files/scripts/build.php';
        //execute

        $output = [];
        $return_var = 0;
        exec("php -d phar.readonly=0 $buildCreatorScript", $output, $return_var);

        $pharPath = $varDir . '/escripta.phar';

        $this->assertFileExists($pharPath);

        ob_start();
        passthru($pharPath);
        $output = ob_get_clean();

        $this->assertStringContainsString('Usage:', $output);


        exec("rm -rf $varDir");



    }





}
