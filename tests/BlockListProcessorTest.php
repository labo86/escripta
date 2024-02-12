<?php
declare(strict_types=1);

namespace labo86\escripta\tests;

use Exception;
use labo86\escripta\BlockListProcessor;
use org\bovigo\vfs\vfsStream;
use org\bovigo\vfs\vfsStreamDirectory;
use PHPUnit\Framework\TestCase;
use Throwable;

class BlockListProcessorTest extends TestCase
{

    protected vfsStreamDirectory $root;
    public function setUp(): void
    {
        $this->root = vfsStream::setup();
    }

    /**
     * @throws Throwable
     */
    public function testProcess()
    {
         $blockList = [
                [
                 'params' => [
                      'dir' => 'a',
                     'name' => 'script_1',
                 ],

                 'content' => 'hello'
                ],
             [
                 'params' => [
                     'dir' => 'a',
                     'name' => 'script_2',
                 ],

                 'content' => 'hello'
             ],
                [
                 'params' => [
                      'dir' => 'b',
                     'name' => 'script_3',
                 ],
                 'content' => 'world'
                ],
             [
                 'params' => [
                     'name' => 'script_4',
                 ],
                 'content' => 'world'
             ],
             [
                 'params' => [
                     'name' => 'script_5',
                     'file' => true,
                 ],
                 'content' => 'world 2'
             ],
             [
                 'params' => [
                     'name' => 'script_6',
                     'file' => true,
                     'dir' => 'b',
                 ],
                 'content' => 'world 3'
             ]
          ];
          $processor = new BlockListProcessor();
          $actual = $processor->process($blockList);
          $this->assertEquals([
                'a.escripta' => [
                    [
                      'content' => 'hello',
                        'fileName' => '01.script_1.escripta.txt'
                    ],
[
                        'content' => 'hello',
                        'fileName' => '02.script_2.escripta.txt'
                    ]
                ],
                'b.escripta' => [
                    [
                        'content' => 'world',
            'fileName' => '03.script_3.escripta.txt'
                    ]
                ],
                'b.escripta/files' => [
                    [
                        'fileName' => 'script_6',
                        'content' => 'world 3'
                    ]
                ],
                '.' => [
                    [
                        'fileName' => '04.script_4.escripta.txt',
                        'content' => 'world'
                    ]
                ],
                './files' => [
                    [
                        'fileName' => 'script_5',
                        'content' => 'world 2'
                    ]
                ]
          ], $actual);

     }

     public function testGetReferencedBlock() {
            $referencedBlock =
                [
                    'params' => [
                        'id' => 'a',
                        'name' => 'script_1',
                        'hidden' => 'true'
                    ],
                    'lang' => 'bash',
                    'content' => 'original'
                ];
            $referedBlock = [
                    'params' => [
                        'ref' => 'a',
                        'name' => 'script_2',
                    ],
                    'lang' => 'php',
                    'content' => 'ignored'
                ];
            $processor = new BlockListProcessor();
            $processor->storeReferencedBlock($referencedBlock);
            $actual = $processor->getReferencedBlock($referedBlock);
            $this->assertEquals( [
                        'content' => 'original',
                        'lang' => 'bash',
                        'params' => [
                            'name' => 'script_2'
                        ]
            ], $actual);
     }

     public function testSave() {
    $processor = new BlockListProcessor();
            $processor->folderList = [
                'a.escripta' => [
                    [
                        'content' => 'hello',
                        'fileName' => '01.script_1.escripta.txt'
                    ],
                    [
                        'content' => 'hello',
                        'fileName' => '02.script_2.escripta.txt'
                    ]
                ],
                'b.escripta' => [
                    [
                        'content' => 'world',
                        'fileName' => '03.script_3.escripta.txt'
                    ]
                ],
                'b.escripta/files' => [
                    [
                        'fileName' => 'script_6',
                        'content' => 'world 3'
                    ]
                ],
                '.' => [
                    [
                        'fileName' => '04.script_4.escripta.txt',
                        'content' => 'world'
                    ]
                ],
                './files' => [
                    [
                        'fileName' => 'script_5',
                        'content' => 'world 2'
                    ]
                ]
            ];
            $path = $this->root->url();
            $filesCreatedCount = $processor->save($path);
            $this->assertEquals(6, $filesCreatedCount);

            $this->assertFileExists($path . '/a.escripta/01.script_1.escripta.txt');
            $this->assertFileExists($path . '/a.escripta/02.script_2.escripta.txt');
            $this->assertFileExists($path . '/b.escripta/03.script_3.escripta.txt');
            $this->assertFileExists($path . '/b.escripta/files/script_6');
            $this->assertFileExists($path . '/04.script_4.escripta.txt');
            $this->assertFileExists($path . '/files/script_5');
     }

}
