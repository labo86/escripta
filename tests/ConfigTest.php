<?php
declare(strict_types=1);

namespace labo86\escripta\tests;

use labo86\escripta\Config;
use PHPUnit\Framework\TestCase;

class ConfigTest extends TestCase
{

    public function setUp() : void {
        $this->outputFolder = tempnam(__DIR__, 'demo_config');

        unlink($this->outputFolder);
    }

    public function tearDown() : void {
        exec(sprintf('rm -rf %s', escapeshellarg($this->outputFolder)));
    }

    public function testLoadNormal()
    {
        $path = $this->outputFolder . '/config';

        mkdir($path, 0777, true);

        file_put_contents($path . '/a.ini', 'a=1');
        file_put_contents($path . '/b.ini', 'b=2');
        file_put_contents($path . '/c.key', 'some_key');

        $config = Config::loadConfigsAndKeys($path);

        $this->assertEquals(['a' => '1', 'b' => '2', 'c_private_key' => $path . '/c.key'], $config);

        $this->assertEquals('1', $config['a']);
        $this->assertEquals('2', $config['b']);
        $this->assertEquals($path . '/c.key', $config['c_private_key']);

    }

    public function testLoadOverwriteVariable()
    {
        $path = $this->outputFolder . '/config';

        mkdir($path, 0777, true);

        file_put_contents($path . '/a.ini', "a=1\nb=4");
        file_put_contents($path . '/b.ini', "a=2\nc=3");
        file_put_contents($path . '/c.key', 'some_key');

        $config = Config::loadConfigsAndKeys($path);

        $this->assertEquals(['a' => '2', 'b' => '4', 'c' => '3', 'c_private_key' => $path . '/c.key'], $config);

        $this->assertEquals('2', $config['a']);
        $this->assertEquals('4', $config['b']);
        $this->assertEquals('3', $config['c']);
        $this->assertEquals($path . '/c.key', $config['c_private_key']);
    }





}
