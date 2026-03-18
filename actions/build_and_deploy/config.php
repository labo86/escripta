#!/usr/bin/php
<?php
declare(strict_types=1);

require_once(__DIR__ . '/../../app/vendor/autoload.php');

use labo86\escripta\Escripta;

Escripta::fetchConfig(['github_pages' =>
    [
            Escripta::getConfigOnePassword('escripta_github_pages_prod'),
            Escripta::getConfigLocal('github_pages'),
    ],
        'app' =>
                [
                        Escripta::getConfigLocal('app_public'),
                ]
]);



