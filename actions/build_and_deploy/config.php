#!/usr/bin/php
<?php
declare(strict_types=1);

require_once(__DIR__ . '/escripta.php');

use labo86\escripta\Escripta;

Escripta::saveConfig(['github_pages'],
    [
            Escripta::getConfigOnePassword('escripta_github_pages_prod'),
            Escripta::getConfigLocal('github_pages'),
    ]
);

Escripta::processCurrentFolder();

