<?php
declare(strict_types=1);

namespace labo86\escripta\tests;

use Exception;
use labo86\escripta\BlockProcessor;
use PHPUnit\Framework\TestCase;
use Throwable;

class BlockProcessorTest extends TestCase
{

    /**
     * @throws Throwable
     */
    public function testGetTargetFolder()
    {
        $block = [
            'params' => [
                'dir' => 'a'
            ]
        ];
        $actual = (new BlockProcessor())->getTargetFolder($block);
        $this->assertEquals('a.escripta', $actual);

    }

    /**
     * @throws Throwable
     */
    public function testGetTargetFolderNoDir()
    {
        $block = [
            'params' => []
        ];
        $actual = (new BlockProcessor())->getTargetFolder($block);
        $this->assertEquals('.', $actual);

    }

    public function testGenerateBlockData() {
        $block = [
            'params' => [
                'name' => 'script_1',
                'dir' => 'a'
            ],
            'content' => 'hello'
        ];
        $actual = (new BlockProcessor())->generateBlockData($block);
        $this->assertEquals([
            'fileName' => '01.script_1.escripta.txt',
            'content' => 'hello'
        ], $actual);
    }

}
