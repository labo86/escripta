<?php

namespace labo86\escripta;
use RuntimeException;

class BootstrapGenerator
{
    private const MANIFEST_FILENAME = 'escripta_env_vars.md';
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

        $entries = [];
        $exports = [];

        foreach ($files as $file) {
            $baseEnv = $this->buildEnvName($file);
            $path = realpath($this->inputDir . DIRECTORY_SEPARATOR . $file);

            $content = file_get_contents($path);

            $relativePath = self::relativePath($this->outputDir, $path);

            if ($this->isMultiline($content)) {
                $env = "{$baseEnv}_FILENAME";
                $exports[] =
                    "export {$env}=\"\$ESCRIPTA_CURRENT_DIR/{$relativePath}\"";
                $entries[] = [
                    'name' => $env,
                    'kind' => 'filename',
                ];

            } else {
                $exports[] =
                    "export {$baseEnv}=\"\$(cat \"\$ESCRIPTA_CURRENT_DIR/{$relativePath}\")\"";
                $entries[] = [
                    'name' => $baseEnv,
                    'kind' => 'value',
                ];
            }
        }

        {
            $env = "ESCRIPTA_CURRENT_DIR";
            $exports[] =
                "export {$env}=\"\$ESCRIPTA_CURRENT_DIR\"";
            $entries[] = [
                'name' => $env,
                'kind' => 'value',
            ];
        }

        {
            $relativePath = self::relativePath($this->outputDir, $projectDir);
            $env = "ESCRIPTA_PROJECT_DIR";
            $exports[] =
                "export {$env}=\"\$ESCRIPTA_CURRENT_DIR/{$relativePath}\"";
            $entries[] = [
                'name' => $env,
                'kind' => 'value',
            ];
        }


        $this->writeLoadScript($exports);
        $this->writeManifest($entries);
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

    private function writeManifest(array $entries): void
    {
        $valueVariables = [];
        $filenameVariables = [];

        foreach ($entries as $entry) {
            if ($entry['kind'] === 'filename') {
                $filenameVariables[] = $entry['name'];
                continue;
            }

            $valueVariables[] = $entry['name'];
        }

        $manifest = [
            '# Escripta Environment Variables Manifest',
            '',
            'Archivo generado automaticamente por Escripta. No incluye valores sensibles.',
            '',
            '## Value Variables',
        ];

        foreach ($valueVariables as $name) {
            $manifest[] = "- `{$name}`";
        }

        $manifest[] = '';
        $manifest[] = '## File Variables (`*_FILENAME`)';

        foreach ($filenameVariables as $name) {
            $manifest[] = "- `{$name}`";
        }

        $manifest[] = '';

        file_put_contents(
            $this->outputDir . '/' . self::MANIFEST_FILENAME,
            implode("\n", $manifest)
        );
    }
}
