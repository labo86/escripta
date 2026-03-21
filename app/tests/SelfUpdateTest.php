<?php
declare(strict_types=1);

namespace labo86\escripta\tests;

use labo86\escripta\SelfUpdate;
use labo86\escripta\ReleaseMetadata;
use org\bovigo\vfs\vfsStream;
use org\bovigo\vfs\vfsStreamDirectory;
use PHPUnit\Framework\TestCase;
use RuntimeException;

class SelfUpdateTest extends TestCase
{
    protected vfsStreamDirectory $root;

    protected function setUp(): void
    {
        $this->root = vfsStream::setup();
        putenv('ESCRIPTA_SELF_UPDATE_URL');
        putenv('ESCRIPTA_SELF_UPDATE_SHA256_URL');
        putenv('ESCRIPTA_RELEASE_BASE_URL');
        putenv('ESCRIPTA_RELEASE_PHAR_FILENAME');
        putenv('ESCRIPTA_RELEASE_SHA256_FILENAME');
        putenv('ESCRIPTA_RELEASE_MANIFEST_FILENAME');
        putenv('ESCRIPTA_RELEASE_MANIFEST_URL');
    }

    public function testIsRequestedWithShortOption(): void
    {
        $this->assertTrue(SelfUpdate::isRequested(['escripta.phar', '-U']));
    }

    public function testResolveCurrentPharPathPrefersPharRuntimePath(): void
    {
        $pharPath = $this->root->url() . '/escripta.phar';
        file_put_contents($pharPath, 'phar');

        $resolved = SelfUpdate::resolveCurrentPharPath('/tmp/other.phar', $pharPath);

        $this->assertSame($pharPath, $resolved);
    }

    public function testResolveReleaseUrlsFromEnvironment(): void
    {
        putenv('ESCRIPTA_SELF_UPDATE_URL=https://example.test/escripta.phar');
        putenv('ESCRIPTA_SELF_UPDATE_SHA256_URL=https://example.test/escripta.phar.sha256');

        $this->assertSame(
            [
                'https://example.test/escripta.phar',
                'https://example.test/escripta.phar.sha256',
            ],
            SelfUpdate::resolveReleaseUrls()
        );
    }

    public function testResolveReleaseUrlsFromBaseMetadataEnvironment(): void
    {
        putenv('ESCRIPTA_RELEASE_BASE_URL=https://example.test/downloads');
        putenv('ESCRIPTA_RELEASE_PHAR_FILENAME=escripta.phar');
        putenv('ESCRIPTA_RELEASE_SHA256_FILENAME=escripta.phar.sha256');

        $this->assertSame(
            [
                'https://example.test/downloads/escripta.phar',
                'https://example.test/downloads/escripta.phar.sha256',
            ],
            ReleaseMetadata::resolveReleaseUrls()
        );
    }

    public function testResolveReleaseManifestUrlFromBaseMetadataEnvironment(): void
    {
        putenv('ESCRIPTA_RELEASE_BASE_URL=https://example.test/downloads');
        putenv('ESCRIPTA_RELEASE_MANIFEST_FILENAME=release.json');

        $this->assertSame(
            'https://example.test/downloads/release.json',
            ReleaseMetadata::resolveReleaseManifestUrl()
        );
    }

    public function testResolveReleaseUrlsFromManifest(): void
    {
        $manifest = rawurlencode(json_encode([
            'phar_url' => 'https://example.test/downloads/escripta.phar',
            'sha256_url' => 'https://example.test/downloads/escripta.phar.sha256',
        ]));

        $this->assertSame(
            [
                'https://example.test/downloads/escripta.phar',
                'https://example.test/downloads/escripta.phar.sha256',
            ],
            SelfUpdate::resolveReleaseUrlsFromManifest('data://text/plain,' . $manifest)
        );
    }

    public function testResolveReleaseUrlsFailsWhenEnvironmentIsMissing(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Faltan URLs de self-update');

        SelfUpdate::resolveReleaseUrls();
    }

    public function testAssertChecksumMatchesValidFile(): void
    {
        $path = $this->root->url() . '/escripta.phar.tmp';
        file_put_contents($path, 'contenido');

        SelfUpdate::assertChecksumMatches($path, hash('sha256', 'contenido'));
        $this->assertFileExists($path);
    }

    public function testAssertChecksumMatchesFailsForInvalidChecksum(): void
    {
        $path = $this->root->url() . '/escripta.phar.tmp';
        file_put_contents($path, 'contenido');

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('no coincide');

        SelfUpdate::assertChecksumMatches($path, hash('sha256', 'otro'));
    }

    public function testReplaceFileAtomicallySwapsFiles(): void
    {
        $target = $this->root->url() . '/escripta.phar';
        $temp = $this->root->url() . '/escripta.phar.tmp';
        file_put_contents($target, 'viejo');
        file_put_contents($temp, 'nuevo');

        SelfUpdate::replaceFileAtomically($target, $temp);

        $this->assertSame('nuevo', file_get_contents($target));
        $this->assertFileDoesNotExist($temp);
        $this->assertFileDoesNotExist($target . '.bak');
    }
}
