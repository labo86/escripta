<?php
declare(strict_types=1);

namespace labo86\action_scripts\tests;

use labo86\action_scripts\Escript;
use PHPUnit\Framework\TestCase;

class EscriptTest extends TestCase
{


    /**
     * @return array[]
     */
    public static function dataProviderIsLineCodeStart() : array {

        $LANG = Escript::LANG;
        $PARAM = Escript::PARAM;
        return [
            ['```bash escript', [$LANG => 'bash', $PARAM => []]],
            ['```bash escript ', [$LANG => 'bash', $PARAM => []]],
            ['```php escript ', [$LANG => 'php', $PARAM => []]],
            ['```php escriptaas', false],
            ['```php escript name=hola', [$LANG => 'php', $PARAM => ['name' => 'hola']]],
            ['```php escript name=hola ', [$LANG => 'php', $PARAM => ['name' => 'hola']]],
            ['```php escript name=hola name2=chau', [$LANG => 'php', $PARAM => ['name' => 'hola', 'name2' => 'chau']]],
            ['```php escript name=hola name2=chau ', [$LANG => 'php', $PARAM => ['name' => 'hola', 'name2' => 'chau']]],
            ['```php escript name=hola name2=chau name3', [$LANG => 'php', $PARAM => ['name' => 'hola', 'name2' => 'chau', 'name3' => true]]],
            ['```php escript name=hola name2=chau name3 ', [$LANG => 'php', $PARAM => ['name' => 'hola', 'name2' => 'chau', 'name3' => true]]],
            ['```php escript name=hola name2=chau name3= ', [$LANG => 'php', $PARAM => ['name' => 'hola', 'name2' => 'chau', 'name3' => '']]],
            ['```php escript name=hola name2=chau name3=  ', [$LANG => 'php', $PARAM => ['name' => 'hola', 'name2' => 'chau', 'name3' => '']]],
            ['```php escript name=hola name2=chau name3=  ', [$LANG => 'php', $PARAM => ['name' => 'hola', 'name2' => 'chau', 'name3' => '']]],
            ['```php escript name=hola name2=chau name3=  ', [$LANG => 'php', $PARAM => ['name' => 'hola', 'name2' => 'chau', 'name3' => '']]],
            ['```php escript name=hola name2=chau name3=  ', [$LANG => 'php', $PARAM => ['name' => 'hola', 'name2' => 'chau', 'name3' => '']]]
        ];
    }

    /**
     * @dataProvider dataProviderIsLineCodeStart
     */
    public function testIsLineCodeStart($line, $expected)
    {

        $actual = Escript::isLineCodeStart($line);


        $this->assertEquals($expected, $actual);
    }

    public static function dataProviderIsLineCodeEnd() : array {


        return [
            ['```', true],
            ['``` ', true],
            ['```bash', false],
            ['```bash ', false],
            ['```bash escript', false],
            ['```bash escript ', false],
            ['```php escript ', false],
            ['```', true],
            ['``` ', true],
            ['```bash', false],
            ['```bash ', false],
            ['```bash escript', false],
            ['```bash escript ', false],
            ['```php escript ', false],


        ];
    }

    /**
     * @dataProvider dataProviderIsLineCodeEnd
     */
    public function testIsLineCodeEnd($line, $expected) {
        $actual = Escript::isLineCodeEnd($line);
        $this->assertEquals($expected, $actual);
    }
}
