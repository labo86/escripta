#!/usr/bin/php
<?php
declare(strict_types=1);

require_once(__DIR__ . '/../../.escripta/escripta.phar');

use labo86\escripta\Escripta;

Escripta::getConfig(
    'github_pages_prod',
    "github_pages"
);

Escripta::processCurrentFolder();

