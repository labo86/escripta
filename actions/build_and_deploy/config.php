#!/usr/bin/php
<?php
declare(strict_types=1);

require_once(__DIR__ . '/../../app/vendor/autoload.php');

use labo86\escripta\Escripta;

Escripta::fetchConfig([
        'release' =>
                [
                        Escripta::getConfigLocal('release'),
                        Escripta::getConfigOnePassword('escripta_github_release'),
                ]
]);
