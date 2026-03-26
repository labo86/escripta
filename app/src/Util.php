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

    public static function removeFileRecursive(string $fileOrFolder) : void {
        if ( !file_exists($fileOrFolder) ) {
            return;
        } else if ( is_file($fileOrFolder) ) {
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
            if ($filename === '.' || $filename === '..') {
                continue;
            }

            if (fnmatch($pattern, $filename)) {
                yield $folder . '/' . $filename;
            }
        }
    }

    public static function executeCommandAndGetStdOut(string $command, bool $captureStdout = true): string
    {
        if ($captureStdout) {
            ob_start();
        }
        passthru($command, $return);

        if ($captureStdout) {
            $strValue = ob_get_clean();
            return $strValue;
        } else {
            return "";
        }
    }


    public static function findFileBackwards(string $filename, string $startFolder) : ?string {
        $folder = $startFolder;
        while ( true ) {
            $path = $folder . '/' . $filename;
            if ( file_exists($path) )
                return $path;

            $parent = dirname($folder);
            if ( $parent === $folder )
                return null;
            $folder = $parent;
        }
    }





}
