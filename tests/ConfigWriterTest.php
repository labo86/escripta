<?php
declare(strict_types=1);

namespace labo86\escripta\tests;

use labo86\escripta\ConfigWriter;
use org\bovigo\vfs\vfsStream;
use org\bovigo\vfs\vfsStreamDirectory;
use PHPUnit\Framework\TestCase;
use Throwable;

class ConfigWriterTest extends TestCase
{
    protected vfsStreamDirectory $root;
    public function setUp(): void
    {
        $this->root = vfsStream::setup();
    }



    /**
     * @throws Throwable
     */

    public function testWriteInitFile()
    {
        $actual = [
            'public_key' => 'ssh-ed25519 adfasdfasdfadsfasdf',
            'fingerprint' => 'SHA256:adsfasdfasdfasdf',
            'private_key' => 'op://Test/test/private key?ssh-format=openssh',
            'key type' => 'ed25519',
            'device_name' => 'alba_rpi',
            'device_raspbian_version' => 'bullseye',
            'device_screen_orientation' => 'normal'
        ];

        //mock getItemRawInfo
        $output = ConfigWriter::writeIniFile($this->root->url(), "test",  $actual);
        $this->assertEquals(<<<EOF
public_key=ssh-ed25519 adfasdfasdfadsfasdf
fingerprint=SHA256:adsfasdfasdfasdf
private_key=op://Test/test/private key?ssh-format=openssh
key type=ed25519
device_name=alba_rpi
device_raspbian_version=bullseye
device_screen_orientation=normal
EOF
, $output);

        $this->assertFileExists($this->root->url() . '/test.ini');
    }


    public function testWriteFiles()
    {

        $actual =    $expected = [
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




        //mock getItemRawInfo
        ConfigWriter::write($this->root->url(), "test",  $actual);
        $this->assertFileExists($this->root->url() . '/test.ini');
        $this->assertFileExists($this->root->url() . '/test.private_key');
        $this->assertFileExists($this->root->url() . '/test.device_name');
        $this->assertFileExists($this->root->url() . '/test.device_raspbian_version');
        $this->assertEquals(<<<EOF
public_key=ssh-ed25519 adfasdfasdfadsfasdf
fingerprint=SHA256:adsfasdfasdfasdf
key type=ed25519
device_screen_orientation=normal
EOF
            , file_get_contents($this->root->url() . '/test.ini'));
        $this->assertEquals(<<<EOF
SOME SOME
LA LA LA
CO OC OC
EOF
            , file_get_contents($this->root->url() . '/test.private_key'));
        $this->assertEquals(<<<EOF
alba_rpi
hola
mundo
EOF
            , file_get_contents($this->root->url() . '/test.device_name'));
        $this->assertEquals(<<<EOF
bullseye
some
asdfasdf
sdfsdfsdf
EOF
            , file_get_contents($this->root->url() . '/test.device_raspbian_version'));

    }
}
