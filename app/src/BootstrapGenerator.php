<?php

namespace labo86\escripta;
use RuntimeException;

class BootstrapGenerator
{
    private string $inputDir;
    private string $outputDir;

    public function __construct(string $inputDir, string $outputDir)
    {
        $this->inputDir = realpath($inputDir);
        $this->outputDir = realpath($outputDir);

        if (!$this->inputDir || !is_dir($this->inputDir)) {
            throw new RuntimeException("Input directory inválido");
        }

        if (!$this->outputDir || !is_dir($this->outputDir)) {
            throw new RuntimeException("Output directory inválido");
        }
    }

    public function generate(string $projectDir): void
    {
        $files = $this->getFiles();

        $exports = [];

        foreach ($files as $file) {

            $env = $this->buildEnvName($file);
            $path = realpath($this->inputDir . DIRECTORY_SEPARATOR . $file);

            $content = file_get_contents($path);

            $relativePath = self::relativePath($this->outputDir, $path);

            if ($this->isMultiline($content)) {
                $env = "{$env}_FILENAME";
                $exports[] =
                    "export {$env}=\"\$ESCRIPTA_CURRENT_DIR/{$relativePath}\"";


            } else {

                $exports[] =
                    "export {$env}=\"\$(cat \"\$ESCRIPTA_CURRENT_DIR/{$relativePath}\")\"";
            }
        }

        {
            $env = "ESCRIPTA_CURRENT_DIR";
            $exports[] =
                "export {$env}=\"\$ESCRIPTA_CURRENT_DIR\"";
        }

        {
            $relativePath = self::relativePath($this->outputDir, $projectDir);
            $env = "ESCRIPTA_PROJECT_DIR";
            $exports[] =
                "export {$env}=\"\$ESCRIPTA_CURRENT_DIR/{$relativePath}\"";
        }


        $this->writeLoadScript($exports);
    }

    private function getFiles(): array
    {
        $files = [];

        foreach (scandir($this->inputDir) as $f) {

            if ($f === '.' || $f === '..') {
                continue;
            }

            if (is_file($this->inputDir . DIRECTORY_SEPARATOR . $f)) {
                $files[] = $f;
            }
        }

        sort($files);

        return $files;
    }

    private function buildEnvName(string $filename): string
    {
        $name = pathinfo($filename, PATHINFO_FILENAME);

        $name = preg_replace('/[^a-zA-Z0-9]+/', '_', $name);
        $name = preg_replace('/_+/', '_', $name);
        $name = trim($name, '_');

        if ($name === '') {
            $name = 'EMPTY_NAME';
        }

        if (preg_match('/^[0-9]/', $name)) {
            $name = 'N_' . $name;
        }

        return 'ESCRIPTA_' . strtoupper($name);
    }

    private function isMultiline(string $content): bool
    {
        return str_contains($content, "\n") || str_contains($content, "\r");
    }

    public static function relativePath(string $from, string $to): string
    {
        $normalize = function ($path) {
            $path = str_replace('\\', '/', $path);
            $parts = explode('/', $path);
            $result = [];

            foreach ($parts as $part) {
                if ($part === '' || $part === '.') {
                    continue;
                }

                if ($part === '..') {
                    array_pop($result);
                    continue;
                }

                $result[] = $part;
            }

            return $result;
        };

        $fromParts = $normalize($from);
        $toParts   = $normalize($to);

        $length = min(count($fromParts), count($toParts));
        $common = 0;

        for ($i = 0; $i < $length; $i++) {
            if ($fromParts[$i] !== $toParts[$i]) {
                break;
            }
            $common++;
        }

        $up   = array_fill(0, count($fromParts) - $common, '..');
        $down = array_slice($toParts, $common);

        $relative = array_merge($up, $down);

        return $relative ? implode('/', $relative) : '.';
    }

    private function writeLoadScript(array $exports): void
    {
        $script = "#!/usr/bin/env bash\n";
        $script .= "# generated automatically\n\n";

        $script .= <<<'BASH'
if [[ "${BASH_SOURCE[0]}" == "${0}" ]]; then
    echo "Este script debe ejecutarse con: source $0"
    exit 1
fi


BASH;

        $script .= 'ESCRIPTA_CURRENT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"' . "\n\n";

        $script .= implode("\n", $exports) . "\n";

        file_put_contents(
            $this->outputDir . '/escripta_env.sh',
            $script
        );

        chmod($this->outputDir . '/escripta_env.sh', 0755);
    }
}