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

    public static function removeFileRecursive(string $fileOrFolder) : void {
        if ( !is_dir($fileOrFolder) ) {
            unlink($fileOrFolder);
            return;
        }

        $folder = $fileOrFolder;


        $iterator = new \RecursiveDirectoryIterator($folder, \FilesystemIterator::SKIP_DOTS);
        $files = new \RecursiveIteratorIterator($iterator, \RecursiveIteratorIterator::CHILD_FIRST);
        foreach ($files as $file) {
            if ($file->isDir()) {
                rmdir($file->getPathname());
            } else {
                unlink($file->getPathname());
            }
        }
        rmdir($folder);

    }

    public static function glob(string $folder, string $pattern) : \Generator {
        $files = scandir($folder);

        foreach ($files as $filename) {
            if (fnmatch($pattern, $filename)) {
                yield $folder . '/' . $filename;
            }
        }
    }
}