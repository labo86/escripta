<?php
declare(strict_types=1);

namespace labo86\escripta;

class Log {

    static public array $log = [];

    static function error(string $message): void
    {
        fprintf(STDERR, "%s\n", $message);
        self::$log[] = $message;
    }

    static function printErrorList(): void
    {
        foreach (self::$log as $message) {
            fprintf(STDERR, "%s\n", $message);
        }
    }
}
