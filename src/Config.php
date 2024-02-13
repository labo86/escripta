<?php
declare(strict_types=1);

namespace labo86\escripta;

use ArrayAccess;
use Exception;

class Config implements ArrayAccess
{

    public array $data = [];
    public ?Config $parent = null;

    public function __construct(array $data, Config $parent = null)
    {
        $this->data = $data;
        $this->parent = $parent;
    }

    static function loadConfigsAndKeys(string $baseDir): array
    {
        $config = [];

        foreach ( Util::glob($baseDir,  '*.ini') as $file ) {
            $pathName = $file;
            $fileName = basename($pathName, '.ini');
            fwrite(STDERR, " - $pathName\n");
            $data = parse_ini_file($pathName, true, INI_SCANNER_RAW);

            if ($data) {
                $config[$fileName] = $data;
            }
        }

        foreach ( Util::glob($baseDir,  '*.key') as $file )  {
            $pathName = $file;
            $fileName = basename($pathName, '.key');
            fwrite(STDERR," - $pathName\n");
            $config[$fileName]["private_key"] = $pathName;
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
            if (is_array($this->data[$offset]))
                return new Config($this->data[$offset], $this);
            else
                return $this->data[$offset];
        }

        $name = $this->fullScopeKeyName($offset);

        trigger_error(
            "Configuración [$name] no encontrada.",
            E_USER_NOTICE);
        return "[[$name]]";
    }

    public function fullScopeKeyName(string $key): string
    {
        if ($this->parent) {
            return $this->parent->fullScopeKeyName($key) . "." . $key;
        } else {
            return $key;
        }
    }

    public function offsetSet(mixed $offset, mixed $value): void
    {
        $name = $this->fullScopeKeyName($offset);
        trigger_error("No se puede eliminar la configuración [$name]", E_USER_NOTICE);
    }

    public function offsetUnset(mixed $offset): void
    {
        $name = $this->fullScopeKeyName($offset);
        trigger_error("No se puede eliminar la configuración [$name]", E_USER_NOTICE);
    }
}