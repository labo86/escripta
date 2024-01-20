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
        $PARAMS = Escript::PARAMS;
        return [
            ['```bash escript', [$LANG => 'bash', $PARAMS => []]],
            ['```bash escript ', [$LANG => 'bash', $PARAMS => []]],
            ['```php escript ', [$LANG => 'php', $PARAMS => []]],
            ['```php escriptaas', false],
            ['```php escript name=hola', [$LANG => 'php', $PARAMS => ['name' => 'hola']]],
            ['```php escript name=hola ', [$LANG => 'php', $PARAMS => ['name' => 'hola']]],
            ['```php escript name=hola name2=chau', [$LANG => 'php', $PARAMS => ['name' => 'hola', 'name2' => 'chau']]],
            ['```php escript name=hola name2=chau ', [$LANG => 'php', $PARAMS => ['name' => 'hola', 'name2' => 'chau']]],
            ['```php escript name=hola name2=chau name3', [$LANG => 'php', $PARAMS => ['name' => 'hola', 'name2' => 'chau', 'name3' => true]]],
            ['```php escript name=hola name2=chau name3 ', [$LANG => 'php', $PARAMS => ['name' => 'hola', 'name2' => 'chau', 'name3' => true]]],
            ['```php escript name=hola name2=chau name3= ', [$LANG => 'php', $PARAMS => ['name' => 'hola', 'name2' => 'chau', 'name3' => '']]],
            ['```php escript name=hola name2=chau name3=  ', [$LANG => 'php', $PARAMS => ['name' => 'hola', 'name2' => 'chau', 'name3' => '']]],
            ['```php escript name=hola name2=chau name3=  ', [$LANG => 'php', $PARAMS => ['name' => 'hola', 'name2' => 'chau', 'name3' => '']]],
            ['```php escript name=hola name2=chau name3=  ', [$LANG => 'php', $PARAMS => ['name' => 'hola', 'name2' => 'chau', 'name3' => '']]],
            ['```php escript name=hola name2=chau name3=  ', [$LANG => 'php', $PARAMS => ['name' => 'hola', 'name2' => 'chau', 'name3' => '']]]
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


    public function testGetCodeBlockList1() {
        $text = <<<EOF
# Hola
    
```bash escript
echo "hola"
```

```php escript
echo "hola"
```

```php escript name=hola
EOF;

        $expected = [
            [
                Escript::LANG => 'bash',
                Escript::PARAMS => [],
                Escript::CONTENT => 'echo "hola"' . "\n"
            ],
            [
                Escript::LANG => 'php',
                Escript::PARAMS => [],
                Escript::CONTENT => 'echo "hola"' . "\n"
            ]
        ];

        $actual = Escript::getCodeBlockList($text);

        $this->assertEquals($expected, $actual);
    }

    public function testGetCodeBlockList2() {
        $text = <<<EOF
# Hola
    
```bash escript name=hola
echo "hola"
```

```php escript p1=v1 p2=v2 p3=v3
echo "hola2"
```

```php escript name=hola
EOF;

        $expected = [
            [
                Escript::LANG => 'bash',
                Escript::PARAMS => [ 'name' => 'hola'],
                Escript::CONTENT => 'echo "hola"' . "\n"
            ],
            [
                Escript::LANG => 'php',
                Escript::PARAMS => [ 'p1' => 'v1', 'p2' => 'v2', 'p3' => 'v3' ],
                Escript::CONTENT => 'echo "hola2"' . "\n"
            ]
        ];

        $actual = Escript::getCodeBlockList($text);

        $this->assertEquals($expected, $actual);
    }


}
