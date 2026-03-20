#!/usr/bin/php -d phar.readonly=0
<?php
declare(strict_types=1);

require_once(__DIR__ . '/../../../builder/vendor/autoload.php');

use labo86\builder\PharBuilder;

$build_version = getenv('ESCRIPTA_APP_BUILD_VERSION') ?: die("No esta la version");
$escripta_current_dir = getenv('ESCRIPTA_CURRENT_DIR') ?: die("No esta la version");
$build_dir = $escripta_current_dir . '/var/build';
$releaseBaseUrl = getenv('ESCRIPTA_RELEASE_BASE_URL') ?: '';
$releasePharFilename = getenv('ESCRIPTA_RELEASE_PHAR_FILENAME') ?: 'escripta.phar';
$releaseSha256Filename = getenv('ESCRIPTA_RELEASE_SHA256_FILENAME') ?: 'escripta.phar.sha256';

if (!is_dir($build_dir) && !mkdir($build_dir, 0775, true) && !is_dir($build_dir)) {
    die("No se pudo crear el directorio de build");
}

$pharPath = $build_dir . '/' . $releasePharFilename;
$checksumPath = $build_dir . '/' . $releaseSha256Filename;

PharBuilder::build($pharPath, $build_version, [
    'base_url' => $releaseBaseUrl,
    'phar_filename' => $releasePharFilename,
    'sha256_filename' => $releaseSha256Filename,
]);

$checksum = hash_file('sha256', $pharPath);
if ($checksum === false) {
    die("No se pudo calcular el checksum del phar");
}

if (file_put_contents($checksumPath, $checksum . PHP_EOL) === false) {
    die("No se pudo escribir el checksum del phar");
}
