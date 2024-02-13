<?php
declare(strict_types=1);

namespace labo86\escripta;


use Exception;

class Escripta {


    public static ?EscriptaInstance $instance = null;
    public static function initInstance()
    {
        if (!self::$instance)
            self::$instance = new EscriptaInstance();
            self::$instance->loadEscriptaConfigInDir(getcwd());
    }

    /**
     * Load config from local
     * @return Config
     * @throws Exception
     */
    public static function loadConfig() : Config {

        self::initInstance();

        //currentWorkingDir
        $currentWorkingDir = getcwd();
        if ( !is_dir($currentWorkingDir) ) {
            throw new Exception("Carpeta de configuraciones no encontrada: [$currentWorkingDir]");
        }
        fwrite(STDERR, "Cargando configuraciones de la carpeta [$currentWorkingDir]\n");

        $config = new Config(Config::loadConfigsAndKeys($currentWorkingDir . "/config"));
        return $config;

    }

    public static function pullConfig(string $targetConfigName, string $environment)  {
        self::initInstance();

        $targetProjectName = self::$instance->getProjectName();
        $targetFolder = getcwd() .  "/config";

        $configName = "{$targetProjectName}_config_{$targetConfigName}_{$environment}";
        echo "Obteniendo configuración [$configName]...\n\n";

        $itemInfo = OnePassword::getItemRawInfo($configName);
        $itemInfo = OnePassword::getItemInfo($itemInfo);
        $iniData = OnePassword::writeIniFile($targetFolder, $targetConfigName, $itemInfo);
        OnePassword::writeKeyFile($targetFolder, $targetConfigName, $itemInfo);
        echo $iniData , "\n\n";
    }

    public static function getProjectName() : string {
        self::initInstance();
        return self::$instance->getProjectName();
    }

    public static function getActionName() : string {
        self::initInstance();
        $folder = getcwd();
        return basename($folder);
    }

    public static function getFullActionFolderName() : string {
        $projectName = self::getProjectName();
        $actionName = self::getActionName();

        return "$projectName\_$actionName.escripta";
    }



}