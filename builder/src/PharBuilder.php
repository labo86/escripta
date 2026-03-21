<?php
declare(strict_types=1);

namespace labo86\builder;

use FilesystemIterator;
use Phar;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;

class PharBuilder {

    /**
     * El script que contenga esta llamada debe tener configurada la variable {@see https://www.php.net/manual/es/phar.configuration.php#ini.phar.readonly phar.readonly} en <strong>On</strong>
     * Eso se puede hacer modificando el archivo {@see https://www.php.net/manual/en/configuration.file.php php.ini} o llamando el script con <code>php -d phar.readonly=Off</code>.
     * El primer argumento que captura es el nombre de phar de salida.
     */
    static public function build(string $filePath, $version = 'unknown', array $releaseMetadata = []) {
        $date = date('Y-m-d H:i:s');
        $phar = new Phar($filePath);
        $basename = basename($filePath);
        $releaseBaseUrl = self::exportPhpString($releaseMetadata['base_url'] ?? '');
        $releasePharFilename = self::exportPhpString($releaseMetadata['phar_filename'] ?? $basename);
        $releaseSha256Filename = self::exportPhpString($releaseMetadata['sha256_filename'] ?? ($basename . '.sha256'));
        $releaseManifestFilename = self::exportReleaseManifestFilename($releaseMetadata, $basename);

        $phar->startBuffering();

        $phar->buildFromIterator(
            self::createFileIterator(__DIR__ . '/../../app/src'),
            __DIR__ . '/../../app'
        );

        $phar->buildFromIterator(
            self::createFileIterator(__DIR__ . '/../../app/vendor'),
            __DIR__ . '/../../app'
        );

        $phar->addFromString('globals.php', <<<EOF
<?php
declare(strict_types=1);
global \$escriptaVersion;
global \$escriptaDate;
global \$escriptaReleaseBaseUrl;
global \$escriptaReleasePharFilename;
global \$escriptaReleaseSha256Filename;
global \$escriptaReleaseManifestFilename;

\$escriptaVersion = '$version';
\$escriptaDate = '$date';
\$escriptaReleaseBaseUrl = $releaseBaseUrl;
\$escriptaReleasePharFilename = $releasePharFilename;
\$escriptaReleaseSha256Filename = $releaseSha256Filename;
\$escriptaReleaseManifestFilename = $releaseManifestFilename;
EOF
);
        $phar->setStub(<<<EOF
#!/usr/bin/php
<?php

\$PHAR_NAME = '$basename';

Phar::mapPhar(\$PHAR_NAME);

require_once("phar://{\$PHAR_NAME}/globals.php");
require_once("phar://{\$PHAR_NAME}/vendor/autoload.php");


\\labo86\\escripta\\Escripta::makeExecutable();

__HALT_COMPILER();
EOF);

        $phar->stopBuffering();

        chmod($filePath, 0755);


    }

    private static function exportPhpString(string $value): string
    {
        return var_export($value, true);
    }

    private static function exportReleaseManifestFilename(array $releaseMetadata, string $basename): string
    {
        $defaultManifestFilename = preg_replace('/\.phar$/', '.json', $basename);
        if (!is_string($defaultManifestFilename) || $defaultManifestFilename === '') {
            $defaultManifestFilename = 'release.json';
        }

        return self::exportPhpString($releaseMetadata['manifest_filename'] ?? $defaultManifestFilename);
    }

    private static function createFileIterator(string $path): RecursiveIteratorIterator
    {
        return new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($path, FilesystemIterator::SKIP_DOTS),
            RecursiveIteratorIterator::LEAVES_ONLY
        );
    }










}
