<?php
declare(strict_types=1);

namespace labo86\escripta\tests;

use Exception;
use labo86\escripta\BlockProcessor;
use labo86\escripta\OnePassword;
use PHPUnit\Framework\TestCase;
use Throwable;

class OnePasswordTest extends TestCase
{

    /**
     * @throws Throwable
     */
    public function testGetTargetFolder()
    {

        $input = json_decode(file_get_contents(__DIR__ . '/files/op_item_get_output.json'), true);
        //mock getItemRawInfo
        $driver = new OnePassword();
        $actual = $driver->getItemInfo($input);

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
        $driver = new OnePassword();
        $actual = $driver->getConfigEnvironmentList($input, "test", "dev");

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

}
