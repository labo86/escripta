<?php
declare(strict_types=1);

namespace labo86\escripta;


use Exception;

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

    /**
     * Load config from local
     * @return Config
     * @throws Exception
     */
    public static function loadConfig() : Config {

        self::initInstance();

        //currentWorkingDir
        $currentWorkingDir = self::$instance->getCwd();
        if ( !is_dir($currentWorkingDir) ) {
            throw new Exception("Carpeta de configuraciones no encontrada: [$currentWorkingDir]");
        }
        fwrite(STDERR, "Cargando configuraciones de la carpeta [$currentWorkingDir]\n");

        $config = new Config(Config::loadConfigsAndKeys($currentWorkingDir . "/config"));
        return $config;

    }

    public static function getConfigLocal($sourceConfigName) : array {
        self::initInstance();
        echo "Buscando [$sourceConfigName] en Local...\n\n";
        $localConfigDir = self::getEscriptaDir() . "/configs/$sourceConfigName";
        if ( is_dir($localConfigDir) ) {
            return Config::loadConfig($localConfigDir, "config");
        }
        return [];
    }

    public static function getConfigOnePassword($sourceConfigName) : array {
        self::initInstance();
        echo "Buscando [$sourceConfigName] en OnePassword...\n\n";
        $itemInfo = OnePassword::getItemRawInfo($sourceConfigName);

        return OnePassword::getItemInfo($itemInfo);
    }

    public static function saveConfig(array $targetConfigNameList, array $configList) {
        self::initInstance();
        $targetFolder = self::$instance->getCwd() .  "/config";
        $config = array_merge(...$configList);
        foreach ( $targetConfigNameList as $configName) {
            echo "Escribiendo configuración [$configName]...\n\n";
            ConfigWriter::write($targetFolder, $configName, $config);
            echo "Configuración [$configName] escrita.\n";
        }
    }

    /**
     * Reemplazo para pullConfig
     * @deprecated
     * @param string $sourceConfigName Nombre de la configuracion en 1password
     * @param string $targetConfigName Nombre de la configuracion que estara disponible en la variable de configuracion
     * @return void
     */
    public static function getConfig(string $sourceConfigName, string|array $targetConfigName) : void {
        self::initInstance();

        $targetProjectName = self::$instance->getProjectName();
        $targetFolder = self::$instance->getCwd() .  "/config";

        if ( is_string($targetConfigName) ) {
            $targetConfigName = [$targetConfigName];
        }


        if ( str_contains($sourceConfigName, '_config_') ) {
            $configName = $sourceConfigName;
        } else {
            $configName = "{$targetProjectName}_config_{$sourceConfigName}";
        }

        echo "Obteniendo configuración [$configName]...\n\n";

        $localConfigDir = self::getEscriptaDir() . "/configs/$sourceConfigName";
        if ( is_dir($localConfigDir) ) {
            echo "Encontrada en directorio local!\n\n";
            if (!is_dir($targetFolder)) {
                mkdir($targetFolder, 0755, true);
            }
            //copy all files in localConfigDir and replace the prefix config to targetConfigName
            foreach (scandir($localConfigDir) as $file) {
                if ( $file === '.' || $file === '..' )
                    continue;
                $sourceFile = "$localConfigDir/$file";

                foreach ($targetConfigName as $targetConfigNameItem) {
                    echo "Escribiendo configuración [$targetConfigNameItem]...\n\n";
                    $targetFile = "$targetFolder/$targetConfigNameItem." . str_replace("config.", "", basename($file));
                    copy($sourceFile, $targetFile);
                    if ( str_ends_with($targetFile, '.private_key') ) {
                        chmod($targetFile, 0600);
                    }
                    echo "Configuración [$targetConfigNameItem] escrita.\n";
                }
            }

        } else {
            echo "Buscando [$configName] en OnePassword...\n\n";
            $itemInfo = OnePassword::getItemRawInfo($configName);

            $itemInfo = OnePassword::getItemInfo($itemInfo);
            echo "Encontrada en OnePassword!\n\n";
            foreach ($targetConfigName as $targetConfigNameItem) {
                echo "Escribiendo configuración [$targetConfigNameItem]...\n\n";
                OnePassword::writeIniFile($targetFolder, $targetConfigNameItem, $itemInfo);
                OnePassword::writeMultilineFiles($targetFolder, $targetConfigNameItem, $itemInfo);
                OnePassword::writeKeyFile($targetFolder, $targetConfigNameItem, $itemInfo);
                echo "Configuración [$targetConfigNameItem] escrita.\n";
            }

        }
    }



    public static function getProjectName() : string {
        self::initInstance();
        return self::$instance->getProjectName();
    }

    public static function getActionName() : string {
        self::initInstance();
        $folder = self::$instance->getCwd();
        return basename($folder);
    }

    /**
     * Identificador para la acción del archivo actual.
     * Ideal para crear un identificador de archivo remoto
     * @return string
     */
    public static function getFullActionName() : string {
        $projectName = self::getProjectName();
        $actionName = self::getActionName();

        return "{$projectName}_{$actionName}";
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



}