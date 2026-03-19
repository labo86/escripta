<?php
declare(strict_types=1);

namespace labo86\escripta;


use labo86\escripta\connectors\AmazonSecrets;
use labo86\escripta\connectors\Config;
use labo86\escripta\connectors\OnePassword;

class Escripta {


    public static ?EscriptaInstance $instance = null;
    public static function initInstance(string $currentWorkingDir = null) {
        if (!self::$instance) {
            if ( !$currentWorkingDir )
                $currentWorkingDir = getcwd();
            self::$instance = new EscriptaInstance();
            self::$instance->loadEscriptaConfigInDir($currentWorkingDir);
        }
    }


    public static function getConfigLocal($sourceConfigName) : array {
        self::initInstance();
        echo "Buscando [$sourceConfigName] en Local...\n\n";
        $configBaseDir = self::getEscriptaDir() . '/configs';
        $localConfigIni = $configBaseDir . "/$sourceConfigName.ini";
        $localConfigDir = $configBaseDir . "/$sourceConfigName";

        return self::loadLocalConfigPath($localConfigIni, $localConfigDir);
    }

    public static function getConfigLocalByPath(string $sourceConfigPath) : array {
        self::initInstance();
        echo "Buscando [$sourceConfigPath] en Local por ruta...\n\n";

        $resolvedPath = self::resolveLocalPath($sourceConfigPath);

        return self::loadLocalConfigPath($resolvedPath);
    }

    public static function getConfigOnePassword($sourceConfigName) : array {
        self::initInstance();
        echo "Buscando [$sourceConfigName] en OnePassword...\n\n";
        $itemInfo = OnePassword::getItemRawInfo($sourceConfigName);

        if ($itemInfo === null) {
            return [];
        }

        return OnePassword::getItemInfo($itemInfo);
    }

    public static function getConfigAmazonSecrets($sourceConfigName) : array {
        self::initInstance();
        echo "Buscando [$sourceConfigName] en Amazon Secrets Manager...\n\n";
        $itemInfo = AmazonSecrets::getSecretInfo($sourceConfigName);

        return $itemInfo ?? [];
    }

    public static function fetchConfig(array $configList) {
        self::initInstance();
        $targetFolder = self::$instance->getCwd() .  "/config.gen";
        Util::removeFileRecursive($targetFolder);
        foreach ( $configList as $configName => $configValuesList) {
            echo "Escribiendo configuración [$configName]...\n\n";
            $configValues = array_merge(...$configValuesList);
            Config::writeInFiles($targetFolder, $configName, $configValues);
            echo "Configuración [$configName] escrita.\n";
        }

        $g = new BootstrapGenerator($targetFolder, self::$instance->getCwd());
        $g->generate(self::$instance->getProjectBaseDir());
    }




    public static function getActionName() : string {
        self::initInstance();
        $folder = self::$instance->getCwd();
        return basename($folder);
    }


    public static function setCurrentFile(string $file) {
        self::initInstance();
        self::$instance->setCurrentFile($file);
    }

    /**
     * La ruta completa del archivo md.php que se procesa actualmente
     * @return string
     */
    public static function getCurrentFile() : string {
        self::initInstance();
        return self::$instance->getCurrentFile();
    }

    /**
     * El nombre del actual del archivo md.php sin extensiones
     * @return string
     */
    public static function getCurrentFileBaseName() : string {
        return basename(self::getCurrentFile(), '.md.php');
    }

    /**
     * @deprecated
     * Usar getEscriptaDir instead
     * Es directorio en donde esta el archivo de configuración .escripta.json.
     * Esta función busca el archivo .escripta.json más cercano hacia arriba en el árbol de directorios.
     * @return array
     */
    public static function getProjectConfigDir() : string {
        return self::getEscriptaDir();
    }

    public static function getEscriptaDir() : string {
        self::initInstance();
        return self::$instance->getEscriptaDir();
    }

    /**
     * Es el directorio base como 'base_dir' especificado en el archivo de configuración .escripta.json.
     * El valor especificado siempre es relativo a la posición del archivo .escripta.json.
     *
     * @return string
     */
    public static function getProjectBaseDir() : string {
        self::initInstance();
        return self::$instance->getProjectBaseDir();
    }


    public static function processCurrentFolder() : void {
        Core::processFolderByCommandLine();
    }

    public static function makeExecutable() {
        global $argv;
        if (isset($argv[0])) {
            $scriptName = realpath($argv[0]);
            $currentPhar = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 1)[0]['file'];
            if ( $scriptName === $currentPhar ) {
                Core::processFolderByCommandLine();
            }
        }
    }

    private static function loadLocalConfigPath(string ...$paths) : array {
        foreach ($paths as $path) {
            if (is_file($path)) {
                return Config::loadConfigFile($path, '', false);
            }

            if (is_dir($path)) {
                return Config::loadConfigDir($path, '', false);
            }
        }

        return [];
    }

    private static function resolveLocalPath(string $path): string
    {
        if ($path === '') {
            return $path;
        }

        if (
            $path[0] === '/'
            || preg_match('/^[A-Za-z]:[\\\\\\/]/', $path) === 1
            || preg_match('/^[A-Za-z][A-Za-z0-9+.-]*:\/\//', $path) === 1
        ) {
            return $path;
        }

        return self::$instance->getCwd() . '/' . $path;
    }


}
