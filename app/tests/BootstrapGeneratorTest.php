<?php
declare(strict_types=1);


use labo86\escripta\BootstrapGenerator;
use PHPUnit\Framework\TestCase;

class BootstrapGeneratorTest extends TestCase
{
    private string $outputFolder;

    public function setUp() : void {
        $this->outputFolder = tempnam(__DIR__, 'demo_phar');
        unlink($this->outputFolder);
        mkdir($this->outputFolder);
    }

    public function tearDown() : void {
        exec(sprintf('rm -rf %s', escapeshellarg($this->outputFolder)));
    }


    public function testWriteInFiles()
    {
        $folder = $this->outputFolder . "/config";
        $output = $this->outputFolder . "/out";

        mkdir($folder);
        mkdir($output);
        file_put_contents($folder . '/a_hola', 'a');
        file_put_contents($folder . '/a_chao', 'a');
        file_put_contents($folder . '/a_chao', "a\nb\nc");

        $g = new BootstrapGenerator($folder, $output);

        $g->generate("other");

        $this->assertFileExists($output . '/escripta_env.sh');
        $this->assertFileExists($output . '/escripta_env_vars.md');
    }

    public function testGeneratedManifestMatchesExportedVariablesAndOmitsSensitiveValues(): void
    {
        $folder = $this->outputFolder . "/config";
        $output = $this->outputFolder . "/out";
        $projectDir = $this->outputFolder . '/project';

        mkdir($folder);
        mkdir($output);
        mkdir($projectDir);

        file_put_contents($folder . '/app_secret', 'super-secret-token');
        file_put_contents($folder . '/ssh_private_key', "line-1\nline-2\n");

        $g = new BootstrapGenerator($folder, $output);
        $g->generate($projectDir);

        $script = file_get_contents($output . '/escripta_env.sh');
        $manifest = file_get_contents($output . '/escripta_env_vars.md');

        $this->assertIsString($script);
        $this->assertIsString($manifest);

        preg_match_all('/^export ([A-Z0-9_]+)=/m', $script, $scriptMatches);
        preg_match_all('/^- `([A-Z0-9_]+)`$/m', $manifest, $manifestMatches);

        $scriptVars = $scriptMatches[1];
        $manifestVars = $manifestMatches[1];

        sort($scriptVars);
        sort($manifestVars);

        $this->assertSame($scriptVars, $manifestVars);
        $this->assertStringContainsString('## Value Variables', $manifest);
        $this->assertStringContainsString('## File Variables (`*_FILENAME`)', $manifest);
        $this->assertStringContainsString('- `ESCRIPTA_APP_SECRET`', $manifest);
        $this->assertStringContainsString('- `ESCRIPTA_SSH_PRIVATE_KEY_FILENAME`', $manifest);
        $this->assertStringContainsString('- `ESCRIPTA_CURRENT_DIR`', $manifest);
        $this->assertStringContainsString('- `ESCRIPTA_PROJECT_DIR`', $manifest);
        $this->assertStringNotContainsString('super-secret-token', $manifest);
        $this->assertStringNotContainsString('line-1', $manifest);
    }

    public function testGenerateCreatesGitignoreForGeneratedOutputs(): void
    {
        $output = $this->outputFolder . "/out";
        $folder = $output . "/config.gen";
        $projectDir = $this->outputFolder . '/project';

        mkdir($output);
        mkdir($folder);
        mkdir($projectDir);

        file_put_contents($folder . '/app_secret', 'super-secret-token');

        $g = new BootstrapGenerator($folder, $output);
        $g->generate($projectDir);

        $gitignore = file_get_contents($output . '/.gitignore');

        $this->assertIsString($gitignore);
        $this->assertStringContainsString('# BEGIN Escripta generated outputs', $gitignore);
        $this->assertStringContainsString('escripta_env.sh', $gitignore);
        $this->assertStringContainsString('config.gen/', $gitignore);
        $this->assertStringContainsString('# END Escripta generated outputs', $gitignore);
    }

    public function testGenerateUpdatesGitignoreIdempotently(): void
    {
        $output = $this->outputFolder . "/out";
        $folder = $output . "/config.gen";
        $projectDir = $this->outputFolder . '/project';

        mkdir($output);
        mkdir($folder);
        mkdir($projectDir);

        file_put_contents($folder . '/app_secret', 'super-secret-token');
        file_put_contents($output . '/.gitignore', "var/\nescripta_env.sh\nconfig.gen\n");

        $g = new BootstrapGenerator($folder, $output);
        $g->generate($projectDir);
        $firstRun = file_get_contents($output . '/.gitignore');

        $g->generate($projectDir);
        $secondRun = file_get_contents($output . '/.gitignore');

        $this->assertSame($firstRun, $secondRun);
        $this->assertSame(1, substr_count($secondRun, 'escripta_env.sh'));
        $this->assertSame(1, substr_count($secondRun, 'config.gen/'));
        $this->assertStringNotContainsString("\nconfig.gen\n", $secondRun);
        $this->assertStringContainsString("var/\n", $secondRun);
        $this->assertStringContainsString('# BEGIN Escripta generated outputs', $secondRun);
        $this->assertStringContainsString('# END Escripta generated outputs', $secondRun);
    }

    public function testRelativePathSameDirectory()
    {
        $from = '/a/b/c';
        $to   = '/a/b/c';

        $this->assertEquals('.', BootstrapGenerator::relativePath($from, $to));
    }

    public function testRelativePathChildPath()
    {
        $from = '/var/www/project/src';
        $to   = '/var/www/project/assets/img/logo.png';

        $this->assertEquals('../assets/img/logo.png', BootstrapGenerator::relativePath($from, $to));
    }

    public function testRelativePathParentPath()
    {
        $from = '/var/www/project/src/utils';
        $to   = '/var/www/project/tests/test.php';

        $this->assertEquals('../../tests/test.php', BootstrapGenerator::relativePath($from, $to));
    }

    public function testRelativePathSibling()
    {
        $from = '/a/b/c';
        $to   = '/a/b/d/file.txt';

        $this->assertEquals('../d/file.txt', BootstrapGenerator::relativePath($from, $to));
    }

    public function testRelativePathDeeperTarget()
    {
        $from = '/a/b';
        $to   = '/a/b/c/d/e.txt';

        $this->assertEquals('c/d/e.txt', BootstrapGenerator::relativePath($from, $to));
    }

    public function testRelativePathDifferentRoots()
    {
        $from = '/a/b/c';
        $to   = '/x/y/z';

        $this->assertEquals('../../../x/y/z', BootstrapGenerator::relativePath($from, $to));
    }

    public function testRelativePathHandlesTrailingSlashes()
    {
        $from = '/a/b/c/';
        $to   = '/a/b/d/file.txt';

        $this->assertEquals('../d/file.txt', BootstrapGenerator::relativePath($from, $to));
    }

    public function testRelativePathWindowsSeparators()
    {
        $from = 'C:\\project\\src';
        $to   = 'C:\\project\\assets\\img\\logo.png';

        $this->assertEquals('../assets/img/logo.png', BootstrapGenerator::relativePath($from, $to));
    }

    public function testRelativePathWindowsSeparators2()
    {
        $from = 'C:\\project\\src';
        $to   = 'C:\\project\\assets\\img\\logo\..';

        $this->assertEquals('../assets/img', BootstrapGenerator::relativePath($from, $to));
    }
}
