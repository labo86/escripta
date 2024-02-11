<?php
declare(strict_types=1);

namespace labo86\escripta\tests;

use Exception;
use labo86\escripta\BlockListProcessor;
use PHPUnit\Framework\TestCase;
use Throwable;

class BlockListProcessorTest extends TestCase
{

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

}
