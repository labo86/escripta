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
            if (file_exists($escriptaDir . '/config.json')) {
                $this->projectConfig = $this->loadEscriptaConfig($escriptaDir . '/config.json');
            }
        }
    }

    /**
     * @throws Exception
     */
    public function loadEscriptaConfig(string $fileName): array
    {
        $data = json_decode(file_get_contents($fileName), true);
        if (!$data)

            throw new Exception("No se pudo leer el archivo [$fileName]");

        $absoluteFileDir = dirname($fileName);
        $data['escripta_dir'] = $absoluteFileDir;

        return $data;
    }

    public function getCwd(): string
    {
        return $this->currentWorkingDir;
    }

    public function getProjectName(): string
    {
        return $this->projectConfig['project_name'];
    }

    public function setCurrentFile(string $fileName): void
    {
        $this->currentFile = $fileName;
    }

    public function getCurrentFile(): string
    {
        return $this->currentFile;
    }

    public function getProjectConfig(): array
    {
        return $this->projectConfig;
    }

    /**
     * Use getEscriptaDir instead
     * @return string
     * @deprecated
     */
    public function getProjectConfigDir(): string
    {
        return $this->projectConfig['escripta_dir'];
    }

    public function getProjectBaseDir(): string
    {
        if (isset($this->projectConfig['base_dir']))
            return $this->projectConfig['escripta_dir'] . '/' . $this->projectConfig['base_dir'];
        else
            return $this->projectConfig['escripta_dir'] . '/..';
    }

    public function getEscriptaDir(): string
    {
        return $this->projectConfig['escripta_dir'];
    }
}