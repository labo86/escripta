<?php
declare(strict_types=1);

namespace labo86\escripta\tests;

use labo86\escripta\Config;
use org\bovigo\vfs\vfsStream;
use org\bovigo\vfs\vfsStreamDirectory;
use PHPUnit\Framework\TestCase;

class ConfigTest extends TestCase
{

    protected vfsStreamDirectory $root;
    public function setUp(): void
    {
        $this->root = vfsStream::setup();
    }

    public function testLoadNormal()
    {
        $path = $this->root->url();

        mkdir($path, 0777, true);

        file_put_contents($path . '/a.ini', 'a=1');
        file_put_contents($path . '/b.ini', 'b=2');
        file_put_contents($path . '/c.key', 'some_key');

        $config = Config::loadConfigsAndKeys($path);

        $this->assertEquals(['a' => ['a' =>'1'], 'b' => ['b' =>'2'], 'c' => ['private_key' => $path . '/c.key']], $config);

        $this->assertEquals('1', $config['a']['a']);
        $this->assertEquals('2', $config['b']['b']);
        $this->assertEquals($path . '/c.key', $config['c']['private_key']);

    }

    public function testLoadOverwriteVariable()
    {
        $path = $this->root->url();

        mkdir($path, 0777, true);

        file_put_contents($path . '/a.ini', "a=1\nb=4");
        file_put_contents($path . '/b.ini', "a=2\nc=3");
        file_put_contents($path . '/c.key', 'some_key');

        $config = Config::loadConfigsAndKeys($path);

        $this->assertEquals(['a' => ['a' =>'1', 'b'=> '4'], 'b' => ['a' => '2', 'c' => '3'], 'c' => ['private_key' => $path . '/c.key']], $config);

        $this->assertEquals('2', $config['b']['a']);
        $this->assertEquals('4', $config['a']['b']);
        $this->assertEquals('3', $config['b']['c']);
        $this->assertEquals($path . '/c.key', $config['c']['private_key']);
    }

    public function testLoadConfigObject()
    {
        $path = $this->root->url();

        mkdir($path, 0777, true);

        file_put_contents($path . '/a.ini', "a=1\nb=4");
        file_put_contents($path . '/b.ini', "a=2\nc=3");
        file_put_contents($path . '/c.key', 'some_key');

        $config = new Config(Config::loadConfigsAndKeys($path));

        $this->assertEquals(['a' => ['a' =>'1', 'b'=> '4'], 'b' => ['a' => '2', 'c' => '3'], 'c' => ['private_key' => $path . '/c.key']], $config->data);

        $this->assertEquals('2', $config['b']['a']);
        $this->assertEquals('4', $config['a']['b']);
        $this->assertEquals('3', $config['b']['c']);
        $this->assertEquals($path . '/c.key', $config['c']['private_key']);
    }





}
