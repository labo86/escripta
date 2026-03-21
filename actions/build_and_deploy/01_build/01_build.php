#!/usr/bin/php -d phar.readonly=0
<?php
declare(strict_types=1);

require_once(__DIR__ . '/../../../builder/vendor/autoload.php');

use labo86\builder\PharBuilder;

function requireEnv(string $name): string
{
    $value = getenv($name);
    if (!is_string($value) || trim($value) === '') {
        die("Falta la variable de entorno requerida [$name].");
    }

    return $value;
}

function gitOutput(string $repositoryDir, array $arguments, bool $required = true): string
{
    $escapedArguments = array_map('escapeshellarg', $arguments);
    $command = sprintf(
        'git -C %s %s 2>/dev/null',
        escapeshellarg($repositoryDir),
        implode(' ', $escapedArguments)
    );

    exec($command, $output, $exitCode);

    if ($exitCode !== 0) {
        if ($required) {
            die(sprintf('No se pudo obtener metadata git con [%s].', implode(' ', $arguments)));
        }

        return '';
    }

    return trim(implode("\n", $output));
}

$escripta_current_dir = getenv('ESCRIPTA_CURRENT_DIR') ?: die("No esta la version");
$repositoryDir = realpath(__DIR__ . '/../../..') ?: die("No se pudo resolver el directorio del repositorio.");
$build_dir = $escripta_current_dir . '/var/build';
$releaseBaseUrl = requireEnv('ESCRIPTA_RELEASE_BASE_URL');
$releasePharFilename = requireEnv('ESCRIPTA_RELEASE_PHAR_FILENAME');
$releaseSha256Filename = requireEnv('ESCRIPTA_RELEASE_SHA256_FILENAME');
$releaseManifestFilename = 'release.json';
$releaseCommit = gitOutput($repositoryDir, ['rev-parse', 'HEAD']);
$releaseTag = gitOutput($repositoryDir, ['describe', '--tags', '--exact-match', 'HEAD']);
$releaseVersion = $releaseTag;

if (!is_dir($build_dir) && !mkdir($build_dir, 0775, true) && !is_dir($build_dir)) {
    die("No se pudo crear el directorio de build");
}

$pharPath = $build_dir . '/' . $releasePharFilename;
$checksumPath = $build_dir . '/' . $releaseSha256Filename;
$manifestPath = $build_dir . '/' . $releaseManifestFilename;

PharBuilder::build($pharPath, $releaseVersion, [
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

$normalizedBaseUrl = rtrim($releaseBaseUrl, '/');
$manifest = [
    'version' => $releaseVersion,
    'tag' => $releaseTag,
    'commit' => $releaseCommit,
    'generated_at' => date(DATE_ATOM),
    'base_url' => $releaseBaseUrl,
    'phar_filename' => $releasePharFilename,
    'sha256_filename' => $releaseSha256Filename,
    'phar_url' => $normalizedBaseUrl === '' ? '' : $normalizedBaseUrl . '/' . ltrim($releasePharFilename, '/'),
    'sha256_url' => $normalizedBaseUrl === '' ? '' : $normalizedBaseUrl . '/' . ltrim($releaseSha256Filename, '/'),
    'sha256' => $checksum,
];

$manifestJson = json_encode($manifest, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
if (!is_string($manifestJson)) {
    die("No se pudo serializar el manifiesto del release");
}

if (file_put_contents($manifestPath, $manifestJson . PHP_EOL) === false) {
    die("No se pudo escribir el manifiesto del release");
}
