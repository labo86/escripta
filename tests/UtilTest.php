<?php
declare(strict_types=1);

namespace labo86\escripta\tests;

use Exception;
use labo86\escripta\Util;
use PHPUnit\Framework\TestCase;
use Throwable;

class UtilTest extends TestCase
{

    /**
     * @throws Throwable
     */
    public function testOutputBufferSafe()
    {
        $output = Util::outputBufferSafe(function () {
            echo "hello";
        });
        $this->assertEquals('hello', $output);

    }

    /**
     * @throws Throwable
     */
    public function testOutputBufferSafeException()
    {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage("hello exception");
        Util::outputBufferSafe(function () {
            throw new Exception("hello exception");
        });

    }
}
