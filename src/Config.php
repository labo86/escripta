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

    public function addConfigs(array $config) : void
    {
        $this->data = array_merge($this->data, $config);
    }


    static function loadConfigsAndKeys(string $baseDir): array
    {
        $config = [];

        foreach ( Util::glob($baseDir,  '*.ini') as $file ) {
            $pathName = $file;
            fwrite(STDERR, " - $pathName\n");
            $data = parse_ini_file($pathName, true, INI_SCANNER_RAW);
            if ($data) {
                $config = array_merge($config, $data);
            }
        }

        foreach ( Util::glob($baseDir,  '*.key') as $file )  {
            $pathName = $file;
            fwrite(STDERR," - $pathName\n");
            $key = basename($pathName, '.key') . "_private_key";
            $config[$key] = $pathName;
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