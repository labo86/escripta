#!/usr/bin/env php
<?php
declare(strict_types=1);

use labo86\escripta\Escripta;
use labo86\escripta\Script;

require_once __DIR__ . '/../../vendor/autoload.php';

$projectRoot = dirname(__DIR__, 3);
$outputDir = $projectRoot . '/app/tests/files/script_examples';

Escripta::$instance = null;
Escripta::initInstance($projectRoot);

removeDir($outputDir);
mkdir($outputDir, 0775, true);

$examples = [
    'git_clone_repo' => static fn() => Script::gitCloneRepoScript(
        'git@github.com:example/deploy.git',
        'main',
        '/tmp/example-deploy',
        '/tmp/keys/example_deploy'
    ),
    'git_clone_repo_deep' => static fn() => Script::gitCloneRepoDeepScript(
        'git@github.com:example/deploy.git',
        'main',
        '/tmp/example-deploy',
        '/tmp/keys/example_deploy'
    ),
    'git_commit_and_push' => static fn() => Script::gitCommitAndPushScript(
        '/tmp/example-deploy',
        '/tmp/keys/example_deploy',
        'Update generated files'
    ),
    'vbox_add_port' => static fn() => Script::vBoxAddPortScript('example-vm', 'guestssh', '2222', '22'),
    'vbox_bootstrap' => static fn() => Script::vboxBootstrapScriptList('example-vm', '2222'),
    'vbox_commands' => static fn() => Script::vboxCommandsScriptList('example-vm', '127.0.0.1', '2222', 'root'),
    'unix_create_user' => static fn() => Script::unixCreateUserScriptList('deploy', 'ssh-ed25519 AAAAexample deploy@example'),
    'nginx_proxy_pass' => static fn() => Script::nginxProxyPassScriptList('example.test', '127.0.0.1', '8080'),
    'systemd_setup_service' => static fn() => Script::systemDSetupServiceScriptList('example-app', 'deploy', '/opt/example-app'),
    'ssh_remote_admin' => static function (): void {
        Script::sshRemoteAdminScriptList('127.0.0.1', 'root', '2222', 'admin', '/tmp/local-admin');
        Escripta::callFunction('connect_to_admin');
    },
    'ssh_remote' => static function (): void {
        Script::sshRemoteScriptList('127.0.0.1', 'deploy', '2222', 'app', '/tmp/local-app', '/tmp/keys/example_app');
        Escripta::callFunction('connect_to_app');
    },
    'return_to_local' => static fn() => Script::returnToLocalScript(),
    'clean_dir' => static fn() => Script::cleanDirScript('/tmp/example-workdir'),
    'git_init_repo' => static fn() => Script::gitInitRepoScript(
        'git@github.com:example/empty.git',
        'main',
        '/tmp/example-empty'
    ),
    'git_commit_and_push_first_time' => static fn() => Script::gitCommitAndPushFirstTimeScript(
        'main',
        '/tmp/example-empty',
        '/tmp/keys/example_empty',
        'Initial import'
    ),
    'git_init_script_list' => static fn() => Script::gitInitScriptList(
        'git@github.com:example/empty.git',
        'main',
        '/tmp/example-empty',
        '/tmp/keys/example_empty',
        'Initial import'
    ),
];

foreach ($examples as $folderName => $generator) {
    $content = capture($generator);
    $targetFolder = $outputDir . '/' . $folderName;
    mkdir($targetFolder, 0775, true);
    writeBlocks($content, $targetFolder);
}

echo "Generated script examples in [$outputDir]\n";

function capture(callable $callable): string
{
    ob_start();
    $callable();
    return (string) ob_get_clean();
}

function writeBlocks(string $content, string $targetFolder): void
{
    preg_match_all('/```([^\n]*)\n(.*?)```/s', $content, $matches, PREG_SET_ORDER);

    foreach ($matches as $match) {
        $info = trim($match[1]);
        $body = normalizeParams(rtrim($match[2]) . "\n");

        if (!preg_match('/\bname=([^\s]+)/', $info, $nameMatch)) {
            continue;
        }

        $language = strtok($info, ' ') ?: 'txt';
        $name = $nameMatch[1];
        $extension = match ($language) {
            'bash' => 'sh',
            'escripta' => 'escripta',
            'txt' => 'txt',
            default => 'txt',
        };

        file_put_contents($targetFolder . '/' . $name . '.' . $extension, $body);
    }
}

function normalizeParams(string $body): string
{
    return preg_replace_callback(
        '/^([A-Z_]+)=(.+?)\s+#\s*PARAM\s*$/m',
        static function (array $matches): string {
            $varName = $matches[1];
            $rawValue = trim($matches[2]);
            $defaultValue = unquote($rawValue);
            $defaultValue = str_replace(['\\', '"', '$'], ['\\\\', '\\"', '\\$'], $defaultValue);

            return sprintf('%s="${%s:-%s}" # PARAM', $varName, $varName, $defaultValue);
        },
        $body
    ) ?? $body;
}

function unquote(string $value): string
{
    $length = strlen($value);
    if ($length >= 2) {
        $first = $value[0];
        $last = $value[$length - 1];
        if (($first === "'" && $last === "'") || ($first === '"' && $last === '"')) {
            return substr($value, 1, -1);
        }
    }

    return $value;
}

function removeDir(string $dir): void
{
    if (!is_dir($dir)) {
        return;
    }

    $iterator = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($dir, FilesystemIterator::SKIP_DOTS),
        RecursiveIteratorIterator::CHILD_FIRST
    );

    foreach ($iterator as $fileInfo) {
        if ($fileInfo->isDir()) {
            rmdir($fileInfo->getPathname());
        } else {
            unlink($fileInfo->getPathname());
        }
    }

    rmdir($dir);
}
