<?php
declare(strict_types=1);

namespace labo86\escripta\tests;

use Exception;
use labo86\escripta\Util;
use org\bovigo\vfs\vfsStream;
use org\bovigo\vfs\vfsStreamDirectory;
use PHPUnit\Framework\TestCase;
use Throwable;

class UtilTest extends TestCase
{
    protected vfsStreamDirectory $root;
    public function setUp(): void
    {
        $this->root = vfsStream::setup();
    }

    /**
     * @throws Throwable
     */
    public function testOutputBufferSafe()
    {
        $output = Util::outputBufferSafe(function () {
            echo "hello";
        });
        $this->assertEquals('hello', $output);

    }

    /**
     * @throws Throwable
     */
    public function testOutputBufferSafeException()
    {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage("hello exception");
        Util::outputBufferSafe(function () {
            throw new Exception("hello exception");
        });

    }

    public function testRemoveFileRecursiveDir() {
        $path = $this->root->url();
        mkdir($path . '/a', 0777, true);
        mkdir($path . '/a/b', 0777, true);
        file_put_contents($path . '/a/b/c.txt', 'hello');
        Util::removeFileRecursive($path . '/a');
        $this->assertDirectoryDoesNotExist($path . '/a');
    }

    public function testRemoveFileRecursiveFile() {
        $path = $this->root->url();
        file_put_contents($path . '/a', 'hello');
        Util::removeFileRecursive($path . '/a');
        $this->assertDirectoryDoesNotExist($path . '/a');
    }


    public function testGlob() {
        $path = $this->root->url();
        mkdir($path . '/a', 0777, true);
        file_put_contents($path . '/a/b.txt', 'hello');
        file_put_contents($path . '/a/c.txt', 'hello');
        file_put_contents($path . '/a/c.stxt', 'hello');
        file_put_contents($path . '/a/d.txt', 'hello');
        $files = iterator_to_array(Util::glob($path . '/a', '*.txt'));
        $this->assertEquals([$path . '/a/b.txt', $path . '/a/c.txt', $path . '/a/d.txt'], $files);
    }

    public function testFindFileBackwards() {
        $path = $this->root->url();
        mkdir($path . '/a', 0777, true);
        mkdir($path . '/a/b', 0777, true);
        file_put_contents($path . '/a3.txt', 'hello');
        file_put_contents($path . '/a/a1.txt', 'hello');
        file_put_contents($path . '/a/a2.txt', 'hello');
        file_put_contents($path . '/a/b/c.txt', 'hello');
        file_put_contents($path . '/a/b/d.txt', 'hello');
        file_put_contents($path . '/a/b/e.txt', 'hello');
        $this->assertEquals($path . '/a/b/c.txt', Util::findFileBackwards('c.txt', $path . '/a/b'));
        $this->assertEquals($path . '/a/b/d.txt', Util::findFileBackwards('d.txt', $path . '/a/b'));
        $this->assertEquals($path . '/a/b/e.txt', Util::findFileBackwards('e.txt', $path . '/a/b'));
        $this->assertEquals($path . '/a/a1.txt', Util::findFileBackwards('a1.txt', $path . '/a/b'));
        $this->assertEquals($path . '/a/a2.txt', Util::findFileBackwards('a2.txt', $path . '/a/b'));
        $this->assertEquals($path . '/a3.txt', Util::findFileBackwards('a3.txt', $path . '/a/b'));
        $this->assertEquals(null, Util::findFileBackwards('escripta.txt', $path . '/a/b'));
        $this->assertEquals($path . '/a/b', Util::findFileBackwards('b', $path . '/a/b'));

    }
}
