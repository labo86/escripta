<?php
declare(strict_types=1);

namespace labo86\escripta\tests\connectors;

use labo86\escripta\connectors\Config;
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
                'config_a' => '1',
                'config_b_c' => '2',
            ],
            $config
        );
    }

    public function testLoadConfigFileDoesNotPrefixIniKeysWhenNameStartsWithUnderscore(): void
    {
        $filename = $this->root->url() . '/_config.ini';
        file_put_contents($filename, "a=1\n[b]\nc=2\n");

        $config = Config::loadConfigFile($filename);

        $this->assertSame(
            [
                'a' => '1',
                'b_c' => '2',
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
                'private_key' => "line 1\nline 2",
            ],
            $config
        );
    }

    public function testLoadConfigDirMergesFilesRecursively(): void
    {
        $path = $this->root->url();
        mkdir($path . '/nested', 0777, true);
        mkdir($path . '/nested/_private', 0777, true);

        file_put_contents($path . '/a.ini', 'a=1');
        file_put_contents($path . '/private_key', 'some_key');
        file_put_contents($path . '/nested/b.ini', "b=2\n[section]\nc=3");
        file_put_contents($path . '/nested/_raw.ini', "d=4\n[inner]\ne=5");
        file_put_contents($path . '/nested/_private/c.ini', "f=6\n[group]\ng=7");

        $config = Config::loadConfigDir($path);

        $this->assertCount(8, $config);
        $this->assertSame('1', $config['a_a']);
        $this->assertSame('2', $config['nested_b_b']);
        $this->assertSame('3', $config['nested_b_section_c']);
        $this->assertSame('4', $config['nested_d']);
        $this->assertSame('5', $config['nested_inner_e']);
        $this->assertSame('6', $config['nested_c_f']);
        $this->assertSame('7', $config['nested_c_group_g']);
        $this->assertSame('some_key', $config['private_key']);
    }

    public function testLoadConfigDirDoesNotPrefixUsingUnderscoreDirectories(): void
    {
        $path = $this->root->url();
        mkdir($path . '/_shared/service', 0777, true);

        file_put_contents($path . '/_shared/service/app.ini', "key=value\n[db]\nhost=localhost");

        $config = Config::loadConfigDir($path . '/_shared');

        $this->assertSame(
            [
                'service_app_key' => 'value',
                'service_app_db_host' => 'localhost',
            ],
            $config
        );
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
