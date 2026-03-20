<?php
declare(strict_types=1);

namespace labo86\escripta;

class ReleaseMetadata
{
    public static function resolveReleaseUrls(): array
    {
        $explicitPharUrl = getenv('ESCRIPTA_SELF_UPDATE_URL') ?: '';
        $explicitChecksumUrl = getenv('ESCRIPTA_SELF_UPDATE_SHA256_URL') ?: '';
        if ($explicitPharUrl !== '' && $explicitChecksumUrl !== '') {
            return [$explicitPharUrl, $explicitChecksumUrl];
        }

        $baseUrl = getenv('ESCRIPTA_RELEASE_BASE_URL') ?: self::readGlobalString('escriptaReleaseBaseUrl');
        $pharFilename = getenv('ESCRIPTA_RELEASE_PHAR_FILENAME') ?: self::readGlobalString('escriptaReleasePharFilename');
        $checksumFilename = getenv('ESCRIPTA_RELEASE_SHA256_FILENAME') ?: self::readGlobalString('escriptaReleaseSha256Filename');

        if ($baseUrl === '' || $pharFilename === '' || $checksumFilename === '') {
            return ['', ''];
        }

        $baseUrl = rtrim($baseUrl, '/');

        return [
            $baseUrl . '/' . ltrim($pharFilename, '/'),
            $baseUrl . '/' . ltrim($checksumFilename, '/'),
        ];
    }

    private static function readGlobalString(string $name): string
    {
        global ${$name};

        $value = ${$name} ?? null;
        return is_string($value) ? $value : '';
    }
}
