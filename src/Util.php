<?php
declare(strict_types=1);


namespace labo86\escripta;


use Throwable;

class Util
{
    /**
     * @param callable $callable
     * @return false|string
     * @throws Throwable
     */
    public static function outputBufferSafe(callable $callable)
    {
        $level = ob_get_level();
        try {
            ob_start();
            $callable();
            return ob_get_clean();

        } catch (Throwable $exception) {
            while (ob_get_level() > $level) ob_get_clean();
            throw $exception;
        }
    }
}