#!/usr/bin/php
<?php
declare(strict_types=1);

require_once(__DIR__ . '/escripta.php');

use labo86\escripta\Escripta;

// cuando ejecutes este script usa eso para elegir AWS_PROFILE
//export AWS_PROFILE=prod

$key = "data";

$data = Escripta::getConfigAmazonSecrets($key);

var_dump($data);


