<?php
declare(strict_types=1);


namespace labo86\escripta;


use DirectoryIterator;
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

    public static function iterateFilesThatEndsWith(string $folder, string $extension) : \Generator {
        foreach (new DirectoryIterator($folder) as $file) {
            if ( $file->isFile() && str_ends_with($file->getFilename(), $extension))
                yield $file->getPathname();
        }
    }
}