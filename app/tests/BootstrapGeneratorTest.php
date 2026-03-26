<?php
declare(strict_types=1);


use labo86\escripta\BootstrapGenerator;
use PHPUnit\Framework\TestCase;

class BootstrapGeneratorTest extends TestCase
{
    public function setUp() : void {
        $this->outputFolder = tempnam(__DIR__, 'demo_phar');
        unlink($this->outputFolder);
        mkdir($this->outputFolder);
    }

    public function tearDown() : void {
        exec(sprintf('rm -rf %s', escapeshellarg($this->outputFolder)));
    }


    public function testWriteInFiles()
    {
        $folder = $this->outputFolder . "/config";
        $output = $this->outputFolder . "/out";

        mkdir($folder);
        mkdir($output);
        file_put_contents($folder . '/a_hola', 'a');
        file_put_contents($folder . '/a_chao', 'a');
        file_put_contents($folder . '/a_chao', "a\nb\nc");

        $g = new BootstrapGenerator($folder, $output);

        $g->generate("other");

        $this->assertFileExists($output . '/escripta_env.sh');

        //$this->assertEquals("a", file_get_contents($output. '/escripta_env.sh'));


    }

    public function testRelativePathSameDirectory()
    {
        $from = '/a/b/c';
        $to   = '/a/b/c';

        $this->assertEquals('.', BootstrapGenerator::relativePath($from, $to));
    }

    public function testRelativePathChildPath()
    {
        $from = '/var/www/project/src';
        $to   = '/var/www/project/assets/img/logo.png';

        $this->assertEquals('../assets/img/logo.png', BootstrapGenerator::relativePath($from, $to));
    }

    public function testRelativePathParentPath()
    {
        $from = '/var/www/project/src/utils';
        $to   = '/var/www/project/tests/test.php';

        $this->assertEquals('../../tests/test.php', BootstrapGenerator::relativePath($from, $to));
    }

    public function testRelativePathSibling()
    {
        $from = '/a/b/c';
        $to   = '/a/b/d/file.txt';

        $this->assertEquals('../d/file.txt', BootstrapGenerator::relativePath($from, $to));
    }

    public function testRelativePathDeeperTarget()
    {
        $from = '/a/b';
        $to   = '/a/b/c/d/e.txt';

        $this->assertEquals('c/d/e.txt', BootstrapGenerator::relativePath($from, $to));
    }

    public function testRelativePathDifferentRoots()
    {
        $from = '/a/b/c';
        $to   = '/x/y/z';

        $this->assertEquals('../../../x/y/z', BootstrapGenerator::relativePath($from, $to));
    }

    public function testRelativePathHandlesTrailingSlashes()
    {
        $from = '/a/b/c/';
        $to   = '/a/b/d/file.txt';

        $this->assertEquals('../d/file.txt', BootstrapGenerator::relativePath($from, $to));
    }

    public function testRelativePathWindowsSeparators()
    {
        $from = 'C:\\project\\src';
        $to   = 'C:\\project\\assets\\img\\logo.png';

        $this->assertEquals('../assets/img/logo.png', BootstrapGenerator::relativePath($from, $to));
    }

    public function testRelativePathWindowsSeparators2()
    {
        $from = 'C:\\project\\src';
        $to   = 'C:\\project\\assets\\img\\logo\..';

        $this->assertEquals('../assets/img', BootstrapGenerator::relativePath($from, $to));
    }
}
