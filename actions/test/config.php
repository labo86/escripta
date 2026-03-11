#!/usr/bin/php
<?php
declare(strict_types=1);

require_once(__DIR__ . '/escripta.php');

use labo86\escripta\Escripta;

// cuando ejecutes este script usa eso para elegir AWS_PROFILE
//export AWS_PROFILE=prod

$key = "data";


Escripta::saveConfig(['public'],
        [Escripta::getConfigAmazonSecrets($key)]
);



Escripta::processCurrentFolder();


$config = Escripta::loadConfig();
$configServer = $config['public'];

$sshUser= $configServer->getAsKeyFile('private_key');



