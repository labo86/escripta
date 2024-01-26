#!/usr/bin/php
<?php
declare(strict_types=1);

require_once(__DIR__ . '/include.php');

OnePassword::getConfigEnvironmentByCommandLine(
    PROJECT_NAME,
    LOCAL_CONFIG_LIST,
    LOCAL_CONFIG_DIR
);


