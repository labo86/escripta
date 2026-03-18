<?php
declare(strict_types=1);

namespace labo86\escripta\tests\connectors;

use labo86\escripta\connectors\OnePassword;
use org\bovigo\vfs\vfsStream;
use org\bovigo\vfs\vfsStreamDirectory;
use PHPUnit\Framework\TestCase;
use Throwable;

class OpenPasswordTest extends TestCase
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

        $input = json_decode(file_get_contents(__DIR__ . '/../files/op_item_get_output.json'), true);
        //mock getItemRawInfo
        $actual = OnePassword::getItemInfo($input, function ($v) { return $v; } );

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

    public function testWriteInitFile2()
    {
        $input = json_decode(file_get_contents(__DIR__ . '/../files/op_item_get_output_2.json'), true);

        $actual = OnePassword::getItemInfo($input, function ($v) { return $v; } );

        $expected = [
            "public_key" => "ssh-ed25519 adfasdfasdfadsfasdf",
    "fingerprint" => "SHA256:adsfasdfasdfasdf",
    "private_key" => "op://Test/test/private key?ssh-format=openssh",
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

       $this->assertEquals($actual, $expected);

    }

}
