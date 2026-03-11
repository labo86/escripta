#!/usr/bin/php
<?php
declare(strict_types=1);

require_once(__DIR__ . '/escripta.php');

use labo86\escripta\Escripta;

Escripta::saveConfig(['server'],
    [Escripta::getConfigLocal('ssh_admin'),
     Escripta::getConfigLocal('app_private')]
);

Escripta::processCurrentFolder();

