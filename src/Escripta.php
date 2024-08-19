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

    /**
     * @deprecated
     * @param string $targetConfigName
     * @param string|null $environment
     * @return void
     */
    public static function pullConfig(string $targetConfigName, string $environment = null) : void {
        self::initInstance();

        $targetProjectName = self::$instance->getProjectName();
        $targetFolder = self::$instance->getCwd() .  "/config";

        $suffix = $targetConfigName;

        if ( !is_null($environment)) {
            $suffix .= "_$environment";
        }

        $configName = "{$targetProjectName}_config_{$suffix}";
        echo "Obteniendo configuración [$configName]...\n\n";



        $itemInfo = OnePassword::getItemRawInfo($configName);
        $itemInfo = OnePassword::getItemInfo($itemInfo);
        $iniData = OnePassword::writeIniFile($targetFolder, $targetConfigName, $itemInfo);
        OnePassword::writeMultilineFiles($targetFolder, $targetConfigName, $itemInfo);
        OnePassword::writeKeyFile($targetFolder, $targetConfigName, $itemInfo);
        echo $iniData , "\n\n";
    }

    /**
     * Reemplazo para pullConfig
     * @param string $sourceConfigName Nombre de la configuracion en 1password
     * @param string $targetConfigName Nombre de la configuracion que estara disponible en la variable de configuracion
     * @return void
     */
    public static function getConfig(string $sourceConfigName, string $targetConfigName) : void {
        self::initInstance();

        $targetProjectName = self::$instance->getProjectName();
        $targetFolder = self::$instance->getCwd() .  "/config";


        $configName = "{$targetProjectName}_config_{$sourceConfigName}";
        echo "Obteniendo configuración [$configName]...\n\n";

        $localConfigDir = self::getEscriptaDir() . "/configs/$configName";
        if ( is_dir($localConfigDir) ) {
            if (!is_dir($targetFolder)) {
                mkdir($targetFolder, 0755, true);
            }
            //copy all files in localConfigDir and replace the prefix config to targetConfigName
            foreach (scandir($localConfigDir) as $file) {
                if ( $file === '.' || $file === '..' )
                    continue;
                $sourceFile = "$localConfigDir/$file";
                $targetFile = "$targetFolder/$targetConfigName." . str_replace("config.", "", basename($file));
                copy($sourceFile, $targetFile);
            }

        } else {

            $itemInfo = OnePassword::getItemRawInfo($configName);
            $itemInfo = OnePassword::getItemInfo($itemInfo);

            echo "Escribiendo configuración [$targetConfigName]...\n\n";
            OnePassword::writeIniFile($targetFolder, $targetConfigName, $itemInfo);
            OnePassword::writeMultilineFiles($targetFolder, $targetConfigName, $itemInfo);
            OnePassword::writeKeyFile($targetFolder, $targetConfigName, $itemInfo);
        }
        echo "Configuración [$targetConfigName] escrita.\n";
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
        self::initInstance();
        Core::processFolderByCommandLine();
    }



}