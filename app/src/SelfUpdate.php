<?php
declare(strict_types=1);

namespace labo86\escripta;

use RuntimeException;

class SelfUpdate
{
    private const PHAR_URL_ENV = 'ESCRIPTA_SELF_UPDATE_URL';
    private const CHECKSUM_URL_ENV = 'ESCRIPTA_SELF_UPDATE_SHA256_URL';

    public static function isRequested(array $argv): bool
    {
        if (count($argv) !== 2) {
            return false;
        }

        return in_array($argv[1], ['-U', '--self-update'], true);
    }

    public static function run(array $argv): void
    {
        $currentPharPath = self::resolveCurrentPharPath($argv[0] ?? null, \Phar::running(false));
        if ($currentPharPath === null) {
            throw new RuntimeException('No se pudo determinar la ruta del phar actual.');
        }

        [$pharUrl, $checksumUrl] = self::resolveReleaseUrls();

        echo "Actualizando Escripta...\n";
        $expectedChecksum = trim(self::downloadString($checksumUrl));
        if ($expectedChecksum === '') {
            throw new RuntimeException('El checksum remoto esta vacio.');
        }

        $tempPath = $currentPharPath . '.tmp';
        self::downloadFile($pharUrl, $tempPath);
        self::assertChecksumMatches($tempPath, $expectedChecksum);
        self::replaceFileAtomically($currentPharPath, $tempPath);

        echo "Escripta actualizada correctamente.\n";
    }

    public static function resolveCurrentPharPath(?string $argv0, ?string $pharRunning): ?string
    {
        if (is_string($pharRunning) && $pharRunning !== '') {
            $resolved = realpath($pharRunning);
            if ($resolved !== false) {
                return $resolved;
            }

            return $pharRunning;
        }

        if (is_string($argv0) && $argv0 !== '') {
            $resolved = realpath($argv0);
            if ($resolved !== false && is_file($resolved)) {
                return $resolved;
            }
        }

        return null;
    }

    public static function resolveReleaseUrls(): array
    {
        $pharUrl = getenv(self::PHAR_URL_ENV) ?: '';
        $checksumUrl = getenv(self::CHECKSUM_URL_ENV) ?: '';

        if ($pharUrl === '' || $checksumUrl === '') {
            throw new RuntimeException(
                'Faltan URLs de self-update. Define ESCRIPTA_SELF_UPDATE_URL y ESCRIPTA_SELF_UPDATE_SHA256_URL.'
            );
        }

        return [$pharUrl, $checksumUrl];
    }

    public static function assertChecksumMatches(string $filePath, string $expectedChecksum): void
    {
        $actualChecksum = hash_file('sha256', $filePath);
        if ($actualChecksum === false) {
            throw new RuntimeException('No se pudo calcular el checksum del archivo descargado.');
        }

        if (!hash_equals(trim($expectedChecksum), $actualChecksum)) {
            throw new RuntimeException('El checksum del archivo descargado no coincide.');
        }
    }

    public static function replaceFileAtomically(string $targetPath, string $tempPath): void
    {
        $backupPath = $targetPath . '.bak';
        @unlink($backupPath);

        if (is_file($targetPath) && !rename($targetPath, $backupPath)) {
            throw new RuntimeException('No se pudo mover el phar actual a un archivo de respaldo.');
        }

        if (!rename($tempPath, $targetPath)) {
            if (is_file($backupPath)) {
                rename($backupPath, $targetPath);
            }

            throw new RuntimeException('No se pudo reemplazar el phar actual.');
        }

        chmod($targetPath, 0755);
        @unlink($backupPath);
    }

    private static function downloadString(string $url): string
    {
        $content = file_get_contents($url);
        if ($content === false) {
            throw new RuntimeException("No se pudo descargar [$url].");
        }

        return $content;
    }

    private static function downloadFile(string $url, string $destinationPath): void
    {
        $content = self::downloadString($url);
        if (file_put_contents($destinationPath, $content) === false) {
            throw new RuntimeException("No se pudo escribir el archivo temporal [$destinationPath].");
        }
    }
}
