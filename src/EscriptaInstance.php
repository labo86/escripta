<?php
declare(strict_types=1);

namespace labo86\escripta;

use Exception;

class EscriptaInstance
{

    public array $projectConfig = [];

    public function loadEscriptaConfigInDir(string $currentWorkingDir) : void {
        $file = Util::findFileBackwards('.escripta.json', $currentWorkingDir );
        if ( !$file )
            throw new Exception("No se encontro el archivo .escripta.json");
        $this->projectConfig = $this->loadEscriptaConfig($file);
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

    public function getProjectName() : string {
        return $this->projectConfig['project_name'];
    }
}