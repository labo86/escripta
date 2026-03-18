<?php
declare(strict_types=1);

namespace labo86\escripta\tests;

use labo86\escripta\Escripta;
use org\bovigo\vfs\vfsStream;
use org\bovigo\vfs\vfsStreamDirectory;
use PHPUnit\Framework\TestCase;

class EscriptaTest extends TestCase
{

    protected vfsStreamDirectory $root;
    public function setUp(): void
    {
        $this->root = vfsStream::setup();
    }

    public function testLoadNormal()
    {
        $path = $this->root->url();
        $path .= '/root';
        mkdir($path, 0777, true);
        mkdir($path . '/.escripta', 0777, true);

        file_put_contents($path . '/.escripta/config.json', json_encode([
            'project_name' => 'test',
            'base_dir' => '..'
        ]));

        $path .= '/action';
        mkdir($path, 0777, true);

        Escripta::$instance = null;
        Escripta::initInstance($path);

        $this->assertEquals('action', Escripta::getActionName());
        $this->assertEquals('vfs://root/root/.escripta', Escripta::getProjectConfigDir());

        $this->assertEquals('test', Escripta::getProjectName());
        $this->assertEquals('vfs://root/root/.escripta/..', Escripta::getProjectBaseDir());
        $this->assertEquals('test_action', Escripta::getFullActionName());

    }


}
