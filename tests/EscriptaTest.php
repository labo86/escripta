<?php
declare(strict_types=1);

namespace labo86\escripta\tests;

use labo86\escripta\Escripta;
use PHPUnit\Framework\TestCase;

class EscriptaTest extends TestCase
{


    /**
     * @return array[]
     */
    public static function dataProviderIsLineCodeStart() : array {

        $LANG = Escripta::LANG;
        $PARAMS = Escripta::PARAMS;
        return [
            ['```bash escripta', [$LANG => 'bash', $PARAMS => []]],
            ['```bash escripta ', [$LANG => 'bash', $PARAMS => []]],
            ['```php escripta ', [$LANG => 'php', $PARAMS => []]],
            ['```php escriptaas', false],
            ['```php escripta name=hola', [$LANG => 'php', $PARAMS => ['name' => 'hola']]],
            ['```php escripta name=hola ', [$LANG => 'php', $PARAMS => ['name' => 'hola']]],
            ['```php escripta name=hola name2=chau', [$LANG => 'php', $PARAMS => ['name' => 'hola', 'name2' => 'chau']]],
            ['```php escripta name=hola name2=chau ', [$LANG => 'php', $PARAMS => ['name' => 'hola', 'name2' => 'chau']]],
            ['```php escripta name=hola name2=chau name3', [$LANG => 'php', $PARAMS => ['name' => 'hola', 'name2' => 'chau', 'name3' => true]]],
            ['```php escripta name=hola name2=chau name3 ', [$LANG => 'php', $PARAMS => ['name' => 'hola', 'name2' => 'chau', 'name3' => true]]],
            ['```php escripta name=hola name2=chau name3= ', [$LANG => 'php', $PARAMS => ['name' => 'hola', 'name2' => 'chau', 'name3' => '']]],
            ['```php escripta name=hola name2=chau name3=  ', [$LANG => 'php', $PARAMS => ['name' => 'hola', 'name2' => 'chau', 'name3' => '']]],
            ['```php escripta name=hola name2=chau name3=  ', [$LANG => 'php', $PARAMS => ['name' => 'hola', 'name2' => 'chau', 'name3' => '']]],
            ['```php escripta name=hola name2=chau name3=  ', [$LANG => 'php', $PARAMS => ['name' => 'hola', 'name2' => 'chau', 'name3' => '']]],
            ['```php escripta name=hola name2=chau name3=  ', [$LANG => 'php', $PARAMS => ['name' => 'hola', 'name2' => 'chau', 'name3' => '']]]
        ];
    }

    /**
     * @dataProvider dataProviderIsLineCodeStart
     */
    public function testIsLineCodeStart($line, $expected)
    {

        $actual = Escripta::isLineCodeStart($line);


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
        $actual = Escripta::isLineCodeEnd($line);
        $this->assertEquals($expected, $actual);
    }


    public function testGetCodeBlockList1() {
        $text = <<<EOF
# Hola
    
```bash escripta
echo "hola"
```

```php escripta
echo "hola"
```

```php escripta name=hola
EOF;

        $expected = [
            [
                Escripta::LANG => 'bash',
                Escripta::PARAMS => [],
                Escripta::CONTENT => 'echo "hola"' . "\n"
            ],
            [
                Escripta::LANG => 'php',
                Escripta::PARAMS => [],
                Escripta::CONTENT => 'echo "hola"' . "\n"
            ]
        ];

        $actual = Escripta::getCodeBlockList($text);

        $this->assertEquals($expected, $actual);
    }

    public function testGetCodeBlockList2() {
        $text = <<<EOF
# Hola
    
```bash escripta name=hola
echo "hola"
```

```php escripta p1=v1 p2=v2 p3=v3
echo "hola2"
```

```php escripta name=hola
EOF;

        $expected = [
            [
                Escripta::LANG => 'bash',
                Escripta::PARAMS => [ 'name' => 'hola'],
                Escripta::CONTENT => 'echo "hola"' . "\n"
            ],
            [
                Escripta::LANG => 'php',
                Escripta::PARAMS => [ 'p1' => 'v1', 'p2' => 'v2', 'p3' => 'v3' ],
                Escripta::CONTENT => 'echo "hola2"' . "\n"
            ]
        ];

        $actual = Escripta::getCodeBlockList($text);

        $this->assertEquals($expected, $actual);
    }

    public function testProcessBlock() {
        $block = [
            Escripta::LANG => 'bash',
            Escripta::PARAMS => [ 'name' => 'hola'],
            Escripta::CONTENT => 'echo "hola"'
        ];

        $expected = <<<EOF
#!/bin/bash
# ESCRIPTA, la desplegadora, generó este script
#
# name=hola

echo "hola"
EOF;

        $actual = Escripta::processBlock($block);
        $this->assertEquals($expected, $actual);
    }

    public function testProcessBlock2() {
        $block = [
            Escripta::LANG => 'php',
            Escripta::PARAMS => [ 'name' => 'hola'],
            Escripta::CONTENT => 'echo "hola"'
        ];

        $expected = <<<EOF
#!/usr/bin/php
<?php
declare(strict_types=1);

// ESCRIPTA, la desplegadora, generó este script
// name=hola

?>echo "hola"
EOF;

        $actual = Escripta::processBlock($block);
        $this->assertEquals($expected, $actual);
    }




}
