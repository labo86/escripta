#!/usr/bin/php
<?php
declare(strict_types=1);

require_once(__DIR__ . '/../escripta.phar');

use labo86\escripta\Escripta;

Escripta::pullConfig(
    'deploy_github_prod'
);


