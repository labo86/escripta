#!/usr/bin/php
<?php
declare(strict_types=1);

require_once(__DIR__ . '/../../../app/vendor/autoload.php');

use labo86\escripta\Escripta;

Escripta::fetchConfig(['server' =>
        [
                Escripta::getConfigLocal('ssh_admin'),
        ],
        'app' =>[
                        Escripta::getConfigLocal('app_private'),
        ]
]);

