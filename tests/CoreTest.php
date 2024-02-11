<?php
declare(strict_types=1);

namespace labo86\escripta\tests;


use labo86\escripta\Core;
use org\bovigo\vfs\vfsStream;
use org\bovigo\vfs\vfsStreamDirectory;
use PHPUnit\Framework\TestCase;

class CoreTest extends TestCase
{

    protected vfsStreamDirectory $root;
    public function setUp(): void
    {
        $this->root = vfsStream::setup();
    }

    /**
     * @return array[]
     */
    public static function dataProviderIsLineCodeStart() : array {

        $LANG = Core::LANG;
        $PARAMS = Core::PARAMS;
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

        $actual = Core::isLineCodeStart($line);


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
        $actual = Core::isLineCodeEnd($line);
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
                Core::LANG => 'bash',
                Core::PARAMS => [],
                Core::CONTENT => 'echo "hola"' . "\n"
            ],
            [
                Core::LANG => 'php',
                Core::PARAMS => [],
                Core::CONTENT => 'echo "hola"' . "\n"
            ]
        ];

        $actual = Core::getCodeBlockList($text);

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
                Core::LANG => 'bash',
                Core::PARAMS => [ 'name' => 'hola'],
                Core::CONTENT => 'echo "hola"' . "\n"
            ],
            [
                Core::LANG => 'php',
                Core::PARAMS => [ 'p1' => 'v1', 'p2' => 'v2', 'p3' => 'v3' ],
                Core::CONTENT => 'echo "hola2"' . "\n"
            ]
        ];

        $actual = Core::getCodeBlockList($text);

        $this->assertEquals($expected, $actual);
    }

    public function testProcessBlock() {
        $block = [
            Core::LANG => 'bash',
            Core::PARAMS => [ 'name' => 'hola'],
            Core::CONTENT => 'echo "hola"'
        ];

        $expected = <<<EOF
#!/bin/bash
# ESCRIPTA, la desplegadora, generó este script
#
# name=hola

echo "hola"
EOF;

        $actual = Core::processBlock($block);
        $this->assertEquals($expected, $actual);
    }

    public function testProcessBlock2() {
        $block = [
            Core::LANG => 'php',
            Core::PARAMS => [ 'name' => 'hola'],
            Core::CONTENT => 'echo "hola"'
        ];

        $expected = <<<EOF
#!/usr/bin/php
<?php
declare(strict_types=1);

// ESCRIPTA, la desplegadora, generó este script
// name=hola

?>echo "hola"
EOF;

        $actual = Core::processBlock($block);
        $this->assertEquals($expected, $actual);
    }


    public function testTranslateMdPhpFile() {
        $path = $this->root->url();

        $md = <<<EOF
# Hola
        
```bash escripta name=hola
```

```bash escripta name=chao
```
    
EOF;

        file_put_contents($path . '/file.md.php', $md);

        $codeBlockList = Core::translateMdPhpFile($path . '/file.md.php');

        $this->assertEquals([
            [
                Core::LANG => 'bash',
                Core::PARAMS => [ 'name' => 'hola'],
                Core::CONTENT => ''
            ],
            [
                Core::LANG => 'bash',
                Core::PARAMS => [ 'name' => 'chao'],
                Core::CONTENT => ''
            ]
        ], $codeBlockList);

    }

    /**
     * @throws \Throwable
     */
    public function testTranslateMdPhpFileException() {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage("excep");

        $path = $this->root->url();

        $md = <<<EOF
# Hola
        
```bash escripta name=hola

```

<?php

throw new Exception("excep");
?>
```

```bash escripta name=chao
```
    
EOF;

        file_put_contents($path . '/file.md.php', $md);

        Core::translateMdPhpFile($path . '/file.md.php');

    }

    public function testTranslateMdPhpFileTriggerError()
    {
        $path = $this->root->url();
        $md = <<<EOF
# Hola

<?php

fwrite(STDERR, "hola");
?>
```

```bash escripta name=chao
```

EOF;
        file_put_contents($path . '/file.md.php', $md);

        $result = Core::translateMdPhpFile($path . '/file.md.php');
        $this->assertEquals([
            [
                Core::LANG => 'bash',
                Core::PARAMS => [ 'name' => 'chao'],
                Core::CONTENT => ''
            ]
        ], $result);

    }

    public function testTranslateMdPhpFiles()
    {
        $path = $this->root->url();
        file_put_contents($path . "/1.md.php", <<<EOF
```bash escripta name=hola
```
EOF
        );
        file_put_contents($path . "/2.md.php", <<<EOF
```bash escripta name=chao
```
EOF
        );

        file_put_contents($path . "/3.md", <<<EOF
```bash escripta name=ignored_1
```
EOF
        );

        file_put_contents($path . "/4.php", <<<EOF
```bash escripta name=ignored_2
```
EOF
        );

        $result = Core::translateMdPhpFolder($path);

        $this->assertEquals([
            [
                Core::LANG => 'bash',
                Core::PARAMS => ['name' => 'hola'],
                Core::CONTENT => ''
            ],
            [
                Core::LANG => 'bash',
                Core::PARAMS => ['name' => 'chao'],
                Core::CONTENT => ''
            ]
        ], $result);
    }
}
