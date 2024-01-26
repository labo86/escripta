#!/usr/bin/php
<?php
declare(strict_types=1);

require_once(__DIR__ . '/action_scripts.php');

use labo86\action_scripts\OnePassword;

OnePassword::getConfigEnvironmentByCommandLine(
    PROJECT_NAME,
    CONFIG_LIST,
    CONFIG_DIR
);


