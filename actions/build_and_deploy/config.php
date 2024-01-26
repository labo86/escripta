#!/usr/bin/php
<?php
declare(strict_types=1);

require_once(__DIR__ . '/include.php');

use labo86\action_scripts\OnePassword;

OnePassword::getConfig(
    "prod",
    PROJECT_NAME,
    LOCAL_CONFIG_DEPLOY_GITHUB,
    LOCAL_CONFIG_DIR
);


