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

    public function setUp() : void {
        $this->outputFolder = tempnam(__DIR__, 'demo_phar');
        $this->pharFile = $this->outputFolder . '.phar';

        unlink($this->outputFolder);
    }

    public function tearDown() : void {
        if ( file_exists($this->pharFile))
            unlink($this->pharFile);
        exec(sprintf('rm -rf %s', escapeshellarg($this->outputFolder)));
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





}
