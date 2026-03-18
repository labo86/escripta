<?php
declare(strict_types=1);

namespace labo86\escripta\tests;

use labo86\escripta\EscriptaInstance;
use org\bovigo\vfs\vfsStream;
use org\bovigo\vfs\vfsStreamDirectory;
use PHPUnit\Framework\TestCase;

class EscriptaInstanceTest extends TestCase
{

    protected vfsStreamDirectory $root;
    public function setUp(): void
    {
        $this->root = vfsStream::setup();
    }

    public function testLoadNormal()
    {
        $path = $this->root->url();

        file_put_contents($path . '/.escripta.json', json_encode([
            'project_name' => 'test'
        ]));

        $instance = new EscriptaInstance();
        $config = $instance->loadEscriptaConfig($path . '/.escripta.json');

        $this->assertEquals(['project_name' => 'test', 'escripta_dir' => 'vfs://root'], $config);

    }


}
