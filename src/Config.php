<?php
declare(strict_types=1);

namespace labo86\action_scripts;

use ArrayAccess;

class Config implements ArrayAccess
{

    public array $data = [];

    public function __construct(array $data)
    {
        $this->data = $data;
    }

    static function getCallerDirectory() : string
    {
        $backtrace = debug_backtrace();

        return dirname($backtrace[1]['file']);
    }

    static function load() : Config {
        $dir = self::getCallerDirectory();
        $dir .= '/config';
        if ( !is_dir($dir) ) {
            trigger_error("Config directory not found: [$dir]", E_USER_ERROR);
        }
        fwrite(STDERR, "Loading configs from directory: [$dir]\n");

        return new Config(self::loadConfigsAndKeys($dir));
    }

    static function loadConfigsAndKeys(string $baseDir): array
    {
        $config = [];

        foreach ( glob($baseDir . '/*.ini') as $file ) {
            fwrite(STDERR, " - $file\n");
            $data = parse_ini_file($file, true, INI_SCANNER_RAW);
            if ($data) {
                $config = array_merge($config, $data);
            }
        }

        foreach ( glob($baseDir . '/*.key') as $file ) {
            fwrite(STDERR," - $file\n");
            $key = basename($file, '.key') . "_private_key";
            $config[$key] = $file;
        }

        return $config;
    }

    public function offsetExists(mixed $offset): bool
    {
        return array_key_exists($offset, $this->data);
    }

    public function offsetGet(mixed $offset): mixed
    {
        if (array_key_exists($offset, $this->data)) {
            return $this->data[$offset];
        }

        trigger_error(
            "Config [$offset] not found. Putting a placeholder",
            E_USER_NOTICE);
        return "[[$offset]]";
    }

    public function offsetSet(mixed $offset, mixed $value): void
    {
        $this->data[$offset] = $value;
    }

    public function offsetUnset(mixed $offset): void
    {
        unset($this->data[$offset]);
    }
}