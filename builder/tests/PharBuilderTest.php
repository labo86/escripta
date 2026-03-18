<?php
declare(strict_types=1);

namespace tests;

use labo86\builder\PharBuilder;
use PHPUnit\Framework\TestCase;

class PharBuilderTest extends TestCase
{

    /**
     * @var false|string
     */
    private string $outputFolder;
    private string $pharFile;

    public function setUp() : void {
        $this->outputFolder = tempnam(__DIR__, 'demo_phar');
        $this->pharFile = $this->outputFolder . '.phar';

        unlink($this->outputFolder);
    }

    public function tearDown() : void {
        if ( file_exists($this->pharFile))
            unlink($this->pharFile);
        exec(sprintf('rm -rf %s', escapeshellarg($this->outputFolder)));
    }

    public function testBuild()
    {

       PharBuilder::build($this->pharFile);
       $this->assertFileExists($this->pharFile);

    }





}
