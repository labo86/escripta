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

    static function listConfigs(string $baseDir): array {
        $configList = [];

        foreach ( Util::glob($baseDir,  '*.*') as $file )  {
            //if file is dir
            if ( is_dir($file) )
                continue;

            $pathName = $file;
            //get extension
            $extension = pathinfo($pathName, PATHINFO_EXTENSION);


            $fileName = basename($pathName, ".$extension");
            $configList[] = $fileName; 
        }

        return array_unique($configList);
    }

    static function loadConfig(string $baseDir, string $configName): array
    {
        $config = [];

        foreach ( Util::glob($baseDir,  "$configName.ini") as $file ) {
            $pathName = $file;
            fwrite(STDERR, " - $pathName\n");
            $data = parse_ini_file($pathName, true, INI_SCANNER_RAW);

            if ($data) {
                $config = $data;
            }
        }

        foreach ( Util::glob($baseDir,  "$configName.*") as $file )  {
            //if file is dir
            if ( is_dir($file) )
                continue;

            $pathName = $file;
            //get extension
            $extension = pathinfo($pathName, PATHINFO_EXTENSION);
            if ( $extension === 'ini' )
                continue;

            fwrite(STDERR," - $pathName\n");
            if ( $extension === 'private_key' )
                $config[$extension] = $pathName;
            else
                $config[$extension] = file_get_contents($pathName);
        }

        return $config;
    }

    static function loadConfigsAndKeys(string $baseDir): array
    {
        $config = [];
        $configList = self::listConfigs($baseDir);
        foreach ( $configList as $configName ) {
            fwrite(STDERR," - $configName\n");
            $config[$configName] = self::loadConfig($baseDir, $configName);
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
        $errorMessage = $this->processDebugBacktrace(debug_backtrace());

        Log::error($errorMessage);
        return "[[$name]]";
    }


    public function processDebugBacktrace(array $backtrace): string
    {

        //find in backtrace where function is 'hola'
        $translateMdPhpFileTrace = array_values(array_filter($backtrace, function ($trace) {
            return $trace['function'] === 'translateMdPhpFile';
        }));

        $file = $translateMdPhpFileTrace[0]['args'][0];

        $offsetGetTrace = array_values(array_filter($backtrace, function ($trace) {
            return $trace['function'] === 'offsetGet';
        }));

        $value = $offsetGetTrace[0]['args'][0];
        $line = $offsetGetTrace[0]['line'];

        $message = "No se puede encontrar [$value] en [$file:$line].";
        return $message;
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
