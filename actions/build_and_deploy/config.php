#!/usr/bin/php
<?php
declare(strict_types=1);

require_once(__DIR__ . '/../../.escripta/escripta.phar');

use labo86\escripta\Escripta;

Escripta::saveConfig(['github_pages'],
    [
            Escripta::getConfigOnePassword('escripta_config_github_pages_prod')
    ]
);

Escripta::processCurrentFolder();

