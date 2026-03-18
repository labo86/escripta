#!/usr/bin/php -d phar.readonly=0
<?php
declare(strict_types=1);

require_once(__DIR__ . '/../../../builder/vendor/autoload.php');

use labo86\builder\PharBuilder;

$build_version = getenv('ESCRIPTA_APP_BUILD_VERSION') ?: die("No esta la version");
$escripta_current_dir = getenv('ESCRIPTA_CURRENT_DIR') ?: die("No esta la version");
$build_dir = $escripta_current_dir . '/var/build';

if (!is_dir($build_dir) && !mkdir($build_dir, 0775, true) && !is_dir($build_dir)) {
    die("No se pudo crear el directorio de build");
}

PharBuilder::build($build_dir . '/escripta.phar', $build_version);
