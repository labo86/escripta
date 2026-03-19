#!/usr/bin/php
<?php
declare(strict_types=1);

require_once(__DIR__ . '/../../app/vendor/autoload.php');

use labo86\escripta\Escripta;

Escripta::fetchConfig([
    'git' => [
        Escripta::getConfigLocal('git'),
    ],
    'server_admin' => [
        Escripta::getConfigLocal('server_admin'),
    ],
    'server_app' => [
        Escripta::getConfigLocal('server_app'),
    ],
    'service' => [
        Escripta::getConfigLocal('service'),
    ],
]);
