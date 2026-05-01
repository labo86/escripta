<?php
declare(strict_types=1);

namespace labo\builder\tests;

use labo86\builder\PharBuilder;
use PHPUnit\Framework\TestCase;
use RuntimeException;

class PharBuilderTest extends TestCase
{

    /**
     * @var false|string
     */
    private string $outputFolder;
    private string $pharFile;
    private string $releaseDir;

    public function setUp() : void {
        $this->outputFolder = tempnam(__DIR__, 'demo_phar');
        $this->pharFile = $this->outputFolder . '.phar';
        $this->releaseDir = $this->outputFolder . '_release';

        unlink($this->outputFolder);
        mkdir($this->releaseDir, 0775, true);
    }

    public function tearDown() : void {
        if ( file_exists($this->pharFile))
            unlink($this->pharFile);
        exec(sprintf('rm -rf %s', escapeshellarg($this->releaseDir)));
        exec(sprintf('rm -rf %s', escapeshellarg($this->outputFolder)));
        exec(sprintf('rm -rf %s', escapeshellarg($this->outputFolder . '_target')));
    }

    public function testBuild()
    {
        PharBuilder::build($this->pharFile, 'test-version', [
            'base_url' => 'https://example.test/releases',
            'phar_filename' => 'escripta.phar',
            'sha256_filename' => 'escripta.phar.sha256',
            'github_repository' => 'owner/private-repo',
        ]);
        $this->assertFileExists($this->pharFile);

        $phar = new \Phar($this->pharFile);
        $globals = $phar['globals.php']->getContent();

        $this->assertStringContainsString("\$escriptaGithubRepository = 'owner/private-repo';", $globals);
        $this->assertTrue(isset($phar['resources/ESCRIPTA_AGENTS.md']));
    }

    public function testBuiltPharEmbedsReleaseMetadataContract(): void
    {
        PharBuilder::build($this->pharFile, 'test-version', [
            'base_url' => 'https://example.test/releases',
            'phar_filename' => 'escripta.phar',
            'sha256_filename' => 'escripta.phar.sha256',
            'github_repository' => 'owner/private-repo',
        ]);

        $phar = new \Phar($this->pharFile);
        $globalsPath = $phar['globals.php']->getPathName();

        unset(
            $GLOBALS['escriptaReleaseBaseUrl'],
            $GLOBALS['escriptaReleasePharFilename'],
            $GLOBALS['escriptaReleaseSha256Filename'],
            $GLOBALS['escriptaGithubRepository']
        );

        require $globalsPath;

        $this->assertSame('https://example.test/releases', $GLOBALS['escriptaReleaseBaseUrl'] ?? null);
        $this->assertSame('escripta.phar', $GLOBALS['escriptaReleasePharFilename'] ?? null);
        $this->assertSame('escripta.phar.sha256', $GLOBALS['escriptaReleaseSha256Filename'] ?? null);
        $this->assertSame('owner/private-repo', $GLOBALS['escriptaGithubRepository'] ?? null);
    }

    public function testBuildFailsWhenReleaseMetadataIsMissing(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Falta releaseMetadata[sha256_filename]');

        PharBuilder::build($this->pharFile, 'test-version', [
            'base_url' => 'https://example.test/releases',
            'phar_filename' => 'escripta.phar',
        ]);
    }

    public function testBuiltPharCanSelfUpdateToLatestReleaseFromLocalManifest(): void
    {
        $baseUrl = 'file://' . $this->releaseDir;
        $releasePhar = $this->releaseDir . '/escripta.phar';
        $releaseChecksum = $this->releaseDir . '/escripta.phar.sha256';
        $manifestPath = $this->releaseDir . '/release.json';

        PharBuilder::build($this->pharFile, 'v1.0.0', [
            'base_url' => $baseUrl,
            'phar_filename' => 'escripta.phar',
            'sha256_filename' => 'escripta.phar.sha256',
            'github_repository' => '',
        ]);

        PharBuilder::build($releasePhar, 'v2.0.0', [
            'base_url' => $baseUrl,
            'phar_filename' => 'escripta.phar',
            'sha256_filename' => 'escripta.phar.sha256',
            'github_repository' => '',
        ]);

        $checksum = hash_file('sha256', $releasePhar);
        $this->assertIsString($checksum);
        file_put_contents($releaseChecksum, $checksum . PHP_EOL);
        file_put_contents($manifestPath, json_encode([
            'phar_url' => $baseUrl . '/escripta.phar',
            'sha256_url' => $baseUrl . '/escripta.phar.sha256',
        ], JSON_UNESCAPED_SLASHES));

        $this->clearReleaseEnvironment();

        $beforeUpdate = $this->runPhpCommand([$this->pharFile, '--version']);
        $this->assertStringContainsString('Versión: v1.0.0', $beforeUpdate);

        $updateOutput = $this->runPhpCommand([$this->pharFile, '-U']);
        $this->assertStringContainsString('Actualizando Escripta...', $updateOutput);
        $this->assertStringContainsString('Escripta actualizada correctamente.', $updateOutput);

        $afterUpdate = $this->runPhpCommand([$this->pharFile, '--version']);
        $this->assertStringContainsString('Versión: v2.0.0', $afterUpdate);
    }

    public function testBuiltPharInstallsAgentGuide(): void
    {
        $targetDir = $this->outputFolder . '_target';
        mkdir($targetDir);

        PharBuilder::build($this->pharFile, 'test-version', [
            'base_url' => 'https://example.test/releases',
            'phar_filename' => 'escripta.phar',
            'sha256_filename' => 'escripta.phar.sha256',
            'github_repository' => '',
        ]);

        $output = $this->runPhpCommand([$this->pharFile, '--install-agent-guide'], $targetDir);

        $this->assertStringContainsString('Guia de agentes instalada:', $output);
        $this->assertFileExists($targetDir . '/.escripta/ESCRIPTA_AGENTS.md');
        $this->assertFileExists($targetDir . '/.escripta/AGENTS_HINT.md');
        $this->assertStringContainsString(
            'Read `.escripta/ESCRIPTA_AGENTS.md`',
            file_get_contents($targetDir . '/.escripta/AGENTS_HINT.md')
        );
        $this->assertStringContainsString(
            'Generated by Escripta version: test-version',
            file_get_contents($targetDir . '/.escripta/ESCRIPTA_AGENTS.md')
        );
    }

    private function runPhpCommand(array $arguments, ?string $workingDir = null): string
    {
        $command = array_merge(['php'], $arguments);
        $escapedCommand = implode(' ', array_map('escapeshellarg', $command));

        if ($workingDir !== null) {
            $escapedCommand = 'cd ' . escapeshellarg($workingDir) . ' && ' . $escapedCommand;
        }

        exec($escapedCommand . ' 2>&1', $output, $exitCode);

        $this->assertSame(0, $exitCode, implode("\n", $output));

        return implode("\n", $output);
    }

    private function clearReleaseEnvironment(): void
    {
        foreach ([
            'ESCRIPTA_RELEASE_BASE_URL',
            'ESCRIPTA_RELEASE_PHAR_FILENAME',
            'ESCRIPTA_RELEASE_SHA256_FILENAME',
            'ESCRIPTA_RELEASE_MANIFEST_URL',
            'ESCRIPTA_SELF_UPDATE_URL',
            'ESCRIPTA_SELF_UPDATE_SHA256_URL',
        ] as $name) {
            putenv($name);
        }
    }





}
