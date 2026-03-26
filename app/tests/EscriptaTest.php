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

        $this->assertEquals('vfs://root/root/.escripta/..', Escripta::getProjectBaseDir());

    }

    public function testGetConfigLocalLoadsIniFileFromConfigsDirectory(): void
    {
        $actionPath = $this->createEscriptaProject();
        file_put_contents($this->root->url() . '/root/.escripta/configs/app.ini', "host=localhost\n[db]\nport=3306");

        Escripta::$instance = null;
        Escripta::initInstance($actionPath);

        $config = Escripta::getConfigLocal('app');

        $this->assertSame(
            [
                'host' => 'localhost',
                'db_port' => '3306',
            ],
            $config
        );
    }

    public function testGetConfigLocalLoadsDirectoryFromConfigsDirectory(): void
    {
        $actionPath = $this->createEscriptaProject();
        mkdir($this->root->url() . '/root/.escripta/configs/service/_private', 0777, true);
        file_put_contents($this->root->url() . '/root/.escripta/configs/service/app.ini', "name=api");
        file_put_contents($this->root->url() . '/root/.escripta/configs/service/_private/db.ini', "[conn]\nhost=localhost");

        Escripta::$instance = null;
        Escripta::initInstance($actionPath);

        $config = Escripta::getConfigLocal('service');

        $this->assertCount(2, $config);
        $this->assertSame('api', $config['app_name']);
        $this->assertSame('localhost', $config['db_conn_host']);
    }

    public function testGetConfigLocalByPathLoadsIniFileUsingAbsolutePath(): void
    {
        $actionPath = $this->createEscriptaProject();
        $filename = $this->root->url() . '/root/custom.ini';
        file_put_contents($filename, "host=localhost\n[db]\nport=3306");

        Escripta::$instance = null;
        Escripta::initInstance($actionPath);

        $config = Escripta::getConfigLocalByPath($filename);

        $this->assertSame(
            [
                'host' => 'localhost',
                'db_port' => '3306',
            ],
            $config
        );
    }

    public function testGetConfigLocalByPathLoadsIniFileUsingRelativePath(): void
    {
        $actionPath = $this->createEscriptaProject();
        file_put_contents($actionPath . '/custom.ini', "host=localhost\n[db]\nport=3306");

        Escripta::$instance = null;
        Escripta::initInstance($actionPath);

        $config = Escripta::getConfigLocalByPath('custom.ini');

        $this->assertSame(
            [
                'host' => 'localhost',
                'db_port' => '3306',
            ],
            $config
        );
    }

    private function createEscriptaProject(): string
    {
        $path = $this->root->url() . '/root';
        mkdir($path . '/.escripta/configs', 0777, true);

        file_put_contents($path . '/.escripta/config.json', json_encode([
            'project_name' => 'test',
            'base_dir' => '..'
        ]));

        $actionPath = $path . '/action';
        mkdir($actionPath, 0777, true);

        return $actionPath;
    }

}
