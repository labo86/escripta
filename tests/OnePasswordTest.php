<?php
declare(strict_types=1);

namespace labo86\escripta\tests;

use Exception;
use labo86\escripta\BlockProcessor;
use labo86\escripta\OnePassword;
use org\bovigo\vfs\vfsStream;
use org\bovigo\vfs\vfsStreamDirectory;
use PHPUnit\Framework\TestCase;
use Throwable;

class OnePasswordTest extends TestCase
{
    protected vfsStreamDirectory $root;
    public function setUp(): void
    {
        $this->root = vfsStream::setup();
    }



    /**
     * @throws Throwable
     */
    public function testGetTargetFolder()
    {

        $input = json_decode(file_get_contents(__DIR__ . '/files/op_item_get_output.json'), true);
        //mock getItemRawInfo
        $actual = OnePassword::getItemInfo($input);

        $expected = [
            'public_key' => 'ssh-ed25519 adfasdfasdfadsfasdf',
    'fingerprint' => 'SHA256:adsfasdfasdfasdf',
    'private_key' => 'op://Test/test/private key?ssh-format=openssh',
    'key type' => 'ed25519',
    'device_name' => 'alba_rpi',
    'device_raspbian_version' => 'bullseye',
    'device_screen_orientation' => 'normal'
        ];

        $this->assertEquals($expected, $actual);

    }

    public function testGetConfigEnvironmentList()
    {
        $input = json_decode(file_get_contents(__DIR__ . '/files/op_item_list_output.json'), true);
        //mock getItemRawInfo
        $actual = OnePassword::getConfigEnvironmentList($input, "test", "dev");

        $expected = [
            'local' => [
                'id' => 'i365hthdgfh6y',
        'title' => 'test_config_dev_local'
                ],
            'proc' => [
        'id' => 'i365hthddgfhdgfhdfghgfh6y',
        'title' => 'test_config_dev_proc'
        ]];

        $this->assertEquals($expected, $actual);
    }

    public function testWriteInitFile()
    {
        $input = json_decode(file_get_contents(__DIR__ . '/files/op_item_get_output.json'), true);

        $actual = OnePassword::getItemInfo($input);

        //mock getItemRawInfo
        $output = OnePassword::writeIniFile($this->root->url(), "test",  $actual);
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

    public function testWriteInitFile2()
    {
        $input = json_decode(file_get_contents(__DIR__ . '/files/op_item_get_output_2.json'), true);

        $actual = OnePassword::getItemInfo($input);

        //mock getItemRawInfo
        $output = OnePassword::writeIniFile($this->root->url(), "test",  $actual);
        $this->assertEquals(<<<EOF
public_key=ssh-ed25519 adfasdfasdfadsfasdf
fingerprint=SHA256:adsfasdfasdfasdf
private_key=op://Test/test/private key?ssh-format=openssh
key type=ed25519
device_screen_orientation=normal
EOF
            , $output);

        $this->assertFileExists($this->root->url() . '/test.ini');
    }

    public function testWriteFiles()
    {
        $input = json_decode(file_get_contents(__DIR__ . '/files/op_item_get_output_2.json'), true);

        $actual = OnePassword::getItemInfo($input);

        //mock getItemRawInfo
        OnePassword::writeMultilineFiles($this->root->url(), "test",  $actual);
        $this->assertFileExists($this->root->url() . '/test.device_name');
        $this->assertFileExists($this->root->url() . '/test.device_raspbian_version');
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
