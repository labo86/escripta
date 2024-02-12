<?php
declare(strict_types=1);

namespace labo86\escripta;

use Exception;

class EscriptaInstance
{

    public function loadEscriptaConfigInDir(string $currentWorkingDir) : array {
        $file = Util::findFileBackwards($currentWorkingDir, '.escripta.json');
        if ( !$file )
            throw new Exception("No se encontro el archivo .escripta.json");
        return $this->loadInstanceConfig($file);
    }

    /**
     * @throws Exception
     */
    public function loadEscriptaConfig(string $fileName) : array {
        $data = json_decode(file_get_contents($fileName), true);
        if ( !$data )

            throw new Exception("No se pudo leer el archivo [$fileName]");

        $absoluteFileDir = dirname($fileName);
        $data['config_file_dir'] = $absoluteFileDir;

        return $data;
    }
}