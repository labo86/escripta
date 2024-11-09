#!/usr/bin/php
<?php
declare(strict_types=1);

require_once(__DIR__ . '/escripta.php');

use labo86\escripta\Escripta;

Escripta::saveConfig(['github_pages', 'holi'],
    [
            Escripta::getConfigLocal('example')
    ]

);

Escripta::processCurrentFolder();


