#!/usr/bin/php
<?php
declare(strict_types=1);

require_once(__DIR__ . '/../vendor/autoload.php');

use labo86\action_scripts\OnePassword;

OnePassword::getConfigEnvironmentByCommandLine(
    'action_scripts',
    ['lib'],
    __DIR__ . '/config'
);


