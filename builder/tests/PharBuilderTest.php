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
        ]);
        $this->assertFileExists($this->pharFile);
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
