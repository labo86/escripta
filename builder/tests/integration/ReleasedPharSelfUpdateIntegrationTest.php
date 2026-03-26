<?php
declare(strict_types=1);

namespace labo\builder\tests\integration;

use PHPUnit\Framework\TestCase;

class ReleasedPharSelfUpdateIntegrationTest extends TestCase
{
    private string $workDir;
    private string $downloadedPharPath;

    protected function setUp(): void
    {
        if (getenv('ESCRIPTA_RUN_RELEASE_INTEGRATION') !== '1') {
            self::markTestSkipped('Definir ESCRIPTA_RUN_RELEASE_INTEGRATION=1 para correr este test contra GitHub Releases.');
        }

        $this->workDir = sys_get_temp_dir() . '/escripta-release-integration-' . bin2hex(random_bytes(8));
        $this->downloadedPharPath = $this->workDir . '/escripta.phar';

        mkdir($this->workDir, 0775, true);
    }

    protected function tearDown(): void
    {
        if (isset($this->workDir) && $this->workDir !== '') {
            exec(sprintf('rm -rf %s', escapeshellarg($this->workDir)));
        }
    }

    public function testReleasedPharUpdatesFromOldTagToLatest(): void
    {
        $repository = getenv('ESCRIPTA_TEST_RELEASE_REPOSITORY') ?: 'labo86/escripta';
        $oldTag = getenv('ESCRIPTA_TEST_RELEASE_OLD_TAG') ?: '4.0.1';
        $pharFilename = getenv('ESCRIPTA_TEST_RELEASE_PHAR_FILENAME') ?: 'escripta.phar';
        $releasesBaseUrl = getenv('ESCRIPTA_TEST_RELEASES_BASE_URL') ?: ('https://github.com/' . $repository . '/releases');

        $oldPharUrl = sprintf('%s/download/%s/%s', rtrim($releasesBaseUrl, '/'), rawurlencode($oldTag), rawurlencode($pharFilename));
        $latestManifestUrl = rtrim($releasesBaseUrl, '/') . '/latest/download/release.json';

        $this->downloadFile($oldPharUrl, $this->downloadedPharPath);
        chmod($this->downloadedPharPath, 0755);

        $beforeUpdate = $this->runPhpCommand([$this->downloadedPharPath, '--version']);
        $this->assertStringContainsString($oldTag, $beforeUpdate, 'El phar descargado no corresponde al tag antiguo esperado.');

        $manifest = $this->downloadJson($latestManifestUrl);
        $expectedLatestVersion = $manifest['version'] ?? $manifest['tag'] ?? null;
        $this->assertIsString($expectedLatestVersion);
        $this->assertNotSame('', trim($expectedLatestVersion));

        $updateOutput = $this->runPhpCommand([$this->downloadedPharPath, '-U']);
        $this->assertStringContainsString('Actualizando Escripta...', $updateOutput);
        $this->assertStringContainsString('Escripta actualizada correctamente.', $updateOutput);

        $afterUpdate = $this->runPhpCommand([$this->downloadedPharPath, '--version']);
        $this->assertStringContainsString($expectedLatestVersion, $afterUpdate, 'El phar no quedó actualizado a la versión latest publicada.');
        $this->assertStringNotContainsString($oldTag, $afterUpdate, 'El phar siguió reportando la versión antigua tras el self-update.');
    }

    private function runPhpCommand(array $arguments): string
    {
        $command = array_merge(['php'], $arguments);
        $escapedCommand = implode(' ', array_map('escapeshellarg', $command));

        exec($escapedCommand . ' 2>&1', $output, $exitCode);

        $this->assertSame(0, $exitCode, implode("\n", $output));

        return implode("\n", $output);
    }

    private function downloadJson(string $url): array
    {
        $rawJson = $this->downloadString($url);
        $decoded = json_decode($rawJson, true);

        $this->assertIsArray($decoded, "No se pudo parsear JSON desde [$url].");

        return $decoded;
    }

    private function downloadFile(string $url, string $destinationPath): void
    {
        $content = $this->downloadString($url);

        $written = file_put_contents($destinationPath, $content);
        $this->assertNotFalse($written, "No se pudo escribir [$destinationPath].");
    }

    private function downloadString(string $url): string
    {
        $curl = curl_init($url);
        curl_setopt_array($curl, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_FAILONERROR => true,
            CURLOPT_USERAGENT => 'escripta-builder-integration-test',
        ]);

        $content = curl_exec($curl);
        $error = curl_error($curl);
        $statusCode = curl_getinfo($curl, CURLINFO_RESPONSE_CODE);
        curl_close($curl);

        $this->assertNotFalse($content, "No se pudo descargar [$url]. Error: [$error] Status: [$statusCode]");

        return (string) $content;
    }
}
