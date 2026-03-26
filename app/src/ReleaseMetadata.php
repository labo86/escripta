<?php
declare(strict_types=1);

namespace labo86\escripta;

class ReleaseMetadata
{
    public static function resolveReleaseManifestUrl(): string
    {
        $explicitManifestUrl = getenv('ESCRIPTA_RELEASE_MANIFEST_URL') ?: '';
        if ($explicitManifestUrl !== '') {
            return $explicitManifestUrl;
        }

        $baseUrl = self::resolveReleaseBaseUrl();
        if ($baseUrl === '') {
            return '';
        }

        return rtrim($baseUrl, '/') . '/release.json';
    }

    public static function resolveReleaseUrls(): array
    {
        $explicitPharUrl = getenv('ESCRIPTA_SELF_UPDATE_URL') ?: '';
        $explicitChecksumUrl = getenv('ESCRIPTA_SELF_UPDATE_SHA256_URL') ?: '';
        if ($explicitPharUrl !== '' && $explicitChecksumUrl !== '') {
            return [$explicitPharUrl, $explicitChecksumUrl];
        }

        $baseUrl = self::resolveReleaseBaseUrl();
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

    public static function resolveReleaseBaseUrl(): string
    {
        return getenv('ESCRIPTA_RELEASE_BASE_URL') ?: self::readGlobalString('escriptaReleaseBaseUrl');
    }

    private static function readGlobalString(string $name): string
    {
        global ${$name};

        $value = ${$name} ?? null;
        return is_string($value) ? $value : '';
    }
}
