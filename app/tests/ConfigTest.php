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

    public function testLoadConfigFileParsesIniFiles(): void
    {
        $filename = $this->root->url() . '/config.ini';
        file_put_contents($filename, "a=1\n[b]\nc=2\n");

        $config = Config::loadConfigFile($filename);

        $this->assertSame(
            [
                'a' => '1',
                'b' => [
                    'c' => '2',
                ],
            ],
            $config
        );
    }

    public function testLoadConfigFileReturnsRawContentForNonIniFiles(): void
    {
        $filename = $this->root->url() . '/private_key';
        file_put_contents($filename, "line 1\nline 2");

        $config = Config::loadConfigFile($filename);

        $this->assertSame(
            [
                $filename => "line 1\nline 2",
            ],
            $config
        );
    }

    public function testLoadConfigDirMergesFilesRecursively(): void
    {
        $path = $this->root->url();
        mkdir($path . '/nested', 0777, true);

        file_put_contents($path . '/a.ini', 'a=1');
        file_put_contents($path . '/private_key', 'some_key');
        file_put_contents($path . '/nested/b.ini', "b=2\n[section]\nc=3");

        $config = Config::loadConfigDir($path);

        $this->assertCount(4, $config);
        $this->assertSame('1', $config['a']);
        $this->assertSame('2', $config['b']);
        $this->assertSame(['c' => '3'], $config['section']);
        $this->assertSame('some_key', $config[$path . '/private_key']);
    }

    public function testWriteInFilesCreatesTargetDirectoryAndSanitizesKeys(): void
    {
        $actual =  [
            "public_key" => "ssh-ed25519 adfasdfasdfadsfasdf",
            "fingerprint" => "SHA256:adsfasdfasdfasdf",
            "private_key" => <<<EOF
SOME SOME
LA LA LA
CO OC OC
EOF,
            "key type" => "ed25519",
            "device_name" => <<<EOF
alba_rpi
hola
mundo
EOF,
            "device_raspbian_version" => <<<EOF
bullseye
some
asdfasdf
sdfsdfsdf
EOF,
            "device_screen_orientation" => "normal"
        ];

        $targetFolder = $this->root->url() . '/config.gen';

        Config::writeInFiles($targetFolder, "test", $actual);

        $this->assertDirectoryExists($targetFolder);
        $this->assertFileExists($targetFolder . '/test_public_key');
        $this->assertFileExists($targetFolder . '/test_fingerprint');
        $this->assertFileExists($targetFolder . '/test_key_type');
        $this->assertFileExists($targetFolder . '/test_private_key');
        $this->assertFileExists($targetFolder . '/test_device_name');
        $this->assertFileExists($targetFolder . '/test_device_raspbian_version');
        $this->assertFileExists($targetFolder . '/test_device_screen_orientation');
        $this->assertSame("ssh-ed25519 adfasdfasdfadsfasdf", file_get_contents($targetFolder . '/test_public_key'));
        $this->assertSame("SHA256:adsfasdfasdfasdf", file_get_contents($targetFolder . '/test_fingerprint'));
        $this->assertSame("ed25519", file_get_contents($targetFolder . '/test_key_type'));
        $this->assertSame(<<<EOF
SOME SOME
LA LA LA
CO OC OC
EOF
            , file_get_contents($targetFolder . '/test_private_key'));
        $this->assertSame(<<<EOF
alba_rpi
hola
mundo
EOF
            , file_get_contents($targetFolder . '/test_device_name'));
        $this->assertSame(<<<EOF
bullseye
some
asdfasdf
sdfsdfsdf
EOF
            , file_get_contents($targetFolder . '/test_device_raspbian_version'));
        $this->assertSame("normal", file_get_contents($targetFolder . '/test_device_screen_orientation'));
    }
}
