<?php
declare(strict_types=1);

namespace labo86\escripta;

use ArrayAccess;
use Exception;

class Config implements ArrayAccess
{

    public array $data = [];

    public function __construct(array $data)
    {
        $this->data = $data;
    }


    static function load($configDir) : Config {
        if ( !is_dir($configDir) ) {
            throw new Exception("Carpeta de configuraciones no encontrada: [$configDir]");
        }
        fwrite(STDERR, "Cargando configuraciones de la carpeta [$configDir]\n");

        return new Config(self::loadConfigsAndKeys($configDir));
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
            "Configuración [$offset] no encontrada.",
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