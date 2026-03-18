<?php
declare(strict_types=1);

namespace labo86\escripta\tests;


use labo86\escripta\BlockListProcessor;
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

        $actual = Core::processBlock($block);
        $expected = <<<EOF
#!/bin/bash
# ESCRIPTA, la desplegadora, generó este script
#
EOF;
        $this->assertStringContainsString($expected, $actual);

        $expected = <<<EOF
# name=hola

echo "hola"
EOF;
        $this->assertStringContainsString($expected, $actual);

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

        $md = <<<'EOF'
<?php
use labo86\escripta\Escripta;

$config = Escripta::getCurrentFileBaseName();

?>
# Hola
        

        
```bash escripta name=hola
<?=$config?> #PARAM
```

```bash escripta name=chao
asd1
```

```bash escripta name=chao
123
```
    
EOF;

        file_put_contents($path . '/file.md.php', $md);

        $codeBlockList = Core::translateMdPhpFile($path . '/file.md.php');

        $this->assertEquals([
            [
                Core::LANG => 'bash',
                Core::PARAMS => [ 'name' => 'hola'],
                Core::CONTENT => "file #PARAM\n"
            ],
            [
                Core::LANG => 'bash',
                Core::PARAMS => [ 'name' => 'chao'],
                Core::CONTENT => "asd1\n"
            ],
            [
                Core::LANG => 'bash',
                Core::PARAMS => [ 'name' => 'chao'],
                Core::CONTENT => "123\n"
            ]
        ], $codeBlockList);

    }

    public function testTranslateMdPhpFile2() {
        $path = $this->root->url();

        $md = <<<EOF

## Clonar repositorio de despliegue


```txt escripta name=clone_deploy_repo
```

## Copiar archivos de despliegue al repositorio


```txt escripta name=var dir=remoto file=true
```

## Copiar archivos de despliegue al repositorio


```txt escripta name=copy_deploy_files_to_repo file=true
```


## Copiar archivos de despliegue al repositorio


```txt escripta name=copy_deploy_files_to_repo dir=remoto
```

## Hacer commit y push


```txt escripta name=commit_and_push
```


## Clonar repositorio de despliegue


```txt escripta name=clone_deploy_repo
```

## Copiar archivos de despliegue al repositorio


```txt escripta name=var dir=remoto file=true
```

## Copiar archivos de despliegue al repositorio


```txt escripta name=copy_deploy_files_to_repo file=true
```


## Copiar archivos de despliegue al repositorio


```txt escripta name=copy_deploy_files_to_repo dir=remoto
```

## Hacer commit y push


```txt escripta name=commit_and_push
```



EOF;

        file_put_contents($path . '/file.md.php', $md);

        $codeBlockList = Core::translateMdPhpFile($path . '/file.md.php');

        $processor = new BlockListProcessor();
        $blockList = $processor->process($codeBlockList);

        $expected =  Array (
            '.' => Array (
                0 => Array (
                    'fileName' => '01.clone_deploy_repo.escripta.txt',
                    'content' => ''
                ),
                1 => Array (
                    'fileName' => '03.commit_and_push.escripta.txt',
                    'content' => ''
                ),
                2 => Array (
                    'fileName' => '04.clone_deploy_repo.escripta.txt',
                    'content' => ''
                ),
                3 => Array (
                    'fileName' => '06.commit_and_push.escripta.txt',
                    'content' => ''
                ),
            ),
            'remoto.escripta/files' => Array (
                    0 => Array (
                        'fileName' => 'var',
                    'content' => ''
                ),
                1 => Array (
                    'fileName' => 'var',
                    'content' => ''
                )
            ),
            './files' => Array (
                    0 => Array (
                        'fileName' => 'copy_deploy_files_to_repo',
                    'content' => ''
                ),
                1 => Array (
                    'fileName' => 'copy_deploy_files_to_repo',
                    'content' => ''
                )
            ),
            'remoto.escripta' => Array (
                    0 => Array (
                        'fileName' => '02.copy_deploy_files_to_repo.escripta.txt',
                    'content' => ''
                ),
                1 => Array (
                    'fileName' => '05.copy_deploy_files_to_repo.escripta.txt',
                    'content' => ''
                )
            )
        );

        $this->assertEquals($expected, $blockList);

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

    public function testCleanGeneratedFiles() {
        $path = $this->root->url();

        $deletableFiles = [
            "/3.md",
            "/7.escripta",
            "/8.escripta.php",
            "/8.escripta.sh"
        ];

        $deletableFolders = [
            "/files",
            "/a.escripta"
        ];

        $files = [
            "/1.md.php",
            "/2.md.php",
            "/4.php",
            "/5.md.php",
            "/6.md.php"
        ];

        $folders = [
            "/a",
            "/config"
        ];

        foreach ($deletableFiles as $file) {
            file_put_contents($path . $file, "");
        }

        foreach ($deletableFolders as $folder) {
            mkdir($path . $folder);
        }

        foreach ($files as $file) {
            file_put_contents($path . $file, "");
        }

        foreach ($folders as $folder) {
            mkdir($path . $folder);
        }

        Core::cleanGeneratedFiles($path);

        foreach ($deletableFiles as $file) {
            $this->assertFileDoesNotExist($path . $file, "");
        }

        foreach ($deletableFolders as $folder) {
            $this->assertFileDoesNotExist($path . $folder);
        }

        foreach ($files as $file) {
            $this->assertFileExists($path . $file, "");
        }

        foreach ($folders as $folder) {
            $this->assertFileExists($path . $folder);
        }
    }

    public function testCleanGeneratedFiles2() {
        $path = $this->root->url();

        $deletableFiles = [
            "/3.md",
            "/7.escripta",
            "/8.escripta.php",
            "/8.escripta.sh"
        ];

        $deletableFolders = [
            "/files",
            "/a.escripta"
        ];

        $files = [
            "/1.md.php",
            "/2.md.php",
            "/4.php",
            "/5.md.php",
            "/6.md.php"
        ];

        $folders = [
            "/a",
            "/config"
        ];


        Core::cleanGeneratedFiles($path);

        foreach ($deletableFiles as $file) {
            $this->assertFileDoesNotExist($path . $file, "");
        }

        foreach ($deletableFolders as $folder) {
            $this->assertFileDoesNotExist($path . $folder);
        }

        foreach ($files as $file) {
            $this->assertFileDoesNotExist($path . $file, "");
        }

        foreach ($folders as $folder) {
            $this->assertFileDoesNotExist($path . $folder);
        }
    }

    public function testSave2()
    {
        $path = $this->root->url();

        $processor = new BlockListProcessor();
        $processor->folderList =         $expected =  Array (
            '.' => Array (
                0 => Array (
                    'fileName' => '01.clone_deploy_repo.escripta.txt',
                    'content' => ''
                ),
                1 => Array (
                    'fileName' => '03.commit_and_push.escripta.txt',
                    'content' => ''
                ),
                2 => Array (
                    'fileName' => '04.clone_deploy_repo.escripta.txt',
                    'content' => ''
                ),
                3 => Array (
                    'fileName' => '06.commit_and_push.escripta.txt',
                    'content' => ''
                ),
            ),
            'remoto.escripta/files' => Array (
                0 => Array (
                    'fileName' => 'var',
                    'content' => ''
                ),
                1 => Array (
                    'fileName' => 'var',
                    'content' => ''
                )
            ),
            './files' => Array (
                0 => Array (
                    'fileName' => 'copy_deploy_files_to_repo',
                    'content' => ''
                ),
                1 => Array (
                    'fileName' => 'copy_deploy_files_to_repo',
                    'content' => ''
                )
            ),
            'remoto.escripta' => Array (
                0 => Array (
                    'fileName' => '02.copy_deploy_files_to_repo.escripta.txt',
                    'content' => ''
                ),
                1 => Array (
                    'fileName' => '05.copy_deploy_files_to_repo.escripta.txt',
                    'content' => ''
                )
            )
        );

        $processor->save($path);

        $this->assertFileExists($path . '/01.clone_deploy_repo.escripta.txt');

    }
}
