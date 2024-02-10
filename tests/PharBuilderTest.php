<?php
declare(strict_types=1);

namespace labo86\escripta\tests;

use labo86\escripta\PharBuilder;
use PHPUnit\Framework\TestCase;

class PharBuilderTest extends TestCase
{

    public function testBuild()
    {

        $buildCreatorScript = __DIR__ . '/../actions/build_phar/build.php';
        //execute

        $output = [];
        $return_var = 0;
        exec("php -d phar.readonly=0 $buildCreatorScript", $output, $return_var);

        $pharPath = __DIR__ . '/../actions/escripta.phar';

        $this->assertFileExists($pharPath);
    }





}
