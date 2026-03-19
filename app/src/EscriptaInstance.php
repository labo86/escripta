<?php
declare(strict_types=1);

namespace labo86\escripta;

use Exception;

class EscriptaInstance
{

    public array $projectConfig = [];
    public string $currentFile = '';
    public string $currentWorkingDir = '';


    public function findEscriptaDir(string $currentWorkingDir): ?string
    {
        $this->currentWorkingDir = $currentWorkingDir;
        return Util::findFileBackwards('.escripta', $currentWorkingDir);
    }

    public function loadEscriptaConfigInDir(string $currentWorkingDir): void
    {
        $this->currentWorkingDir = $currentWorkingDir;
        $escriptaDir = $this->findEscriptaDir($currentWorkingDir);
        if ($escriptaDir) {
            $this->projectConfig = ['escripta_dir' => $escriptaDir ];
        } else {
            throw new Exception("No se pudo encontrar la carpeta escripta");
        }
    }

    public function getCwd(): string
    {
        return $this->currentWorkingDir;
    }


    public function getProjectBaseDir(): string
    {

        return $this->projectConfig['escripta_dir'] . '/..';
    }

    public function getEscriptaDir(): string
    {
        return $this->projectConfig['escripta_dir'];
    }
}