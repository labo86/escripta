#!/usr/bin/php
<?php
declare(strict_types=1);

require_once(__DIR__ . '/include.php');

use labo86\action_scripts\OnePassword;

OnePassword::getConfigEnvironmentByCommandLine(
    'action_scripts',
    CONFIG_LIST,
    CONFIG_DIR
);


