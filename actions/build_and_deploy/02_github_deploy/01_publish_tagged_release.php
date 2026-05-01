#!/usr/bin/php
<?php
declare(strict_types=1);

function requireEnv(string $name): string
{
    $value = getenv($name);
    if (!is_string($value) || trim($value) === '') {
        fwrite(STDERR, "Falta la variable de entorno requerida [$name].\n");
        exit(1);
    }

    return $value;
}

function requireGithubToken(): string
{
    $token = getenv('GITHUB_TOKEN');
    if (is_string($token) && trim($token) !== '') {
        return $token;
    }

    $token = getenv('ESCRIPTA_RELEASE_GITHUB_TOKEN');
    if (is_string($token) && trim($token) !== '') {
        return $token;
    }

    exec('gh auth token 2>/dev/null', $output, $exitCode);
    if ($exitCode === 0) {
        $token = trim(implode("\n", $output));
        if ($token !== '') {
            return $token;
        }
    }

    fwrite(STDERR, "Falta un token GitHub. Define GITHUB_TOKEN o autentica gh auth.\n");
    exit(1);
}

function parseGithubRepository(string $repository): array
{
    if (preg_match('#^[^/]+/[^/]+$#', $repository) === 1) {
        return explode('/', $repository, 2);
    }

    throw new RuntimeException("No se pudo extraer owner/repo desde [$repository].");
}

function githubRequest(string $method, string $url, string $token, ?string $body = null, string $contentType = 'application/json'): array
{
    $headers = [
        'Accept: application/vnd.github+json',
        'Authorization: Bearer ' . $token,
        'User-Agent: escripta-build-and-deploy',
        'X-GitHub-Api-Version: 2022-11-28',
    ];

    if ($body !== null) {
        $headers[] = 'Content-Type: ' . $contentType;
    }

    $curl = curl_init($url);
    curl_setopt_array($curl, [
        CURLOPT_CUSTOMREQUEST => $method,
        CURLOPT_HTTPHEADER => $headers,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_HEADER => true,
        CURLOPT_POSTFIELDS => $body,
    ]);

    $rawResponse = curl_exec($curl);
    if ($rawResponse === false) {
        throw new RuntimeException('GitHub API fallo: ' . curl_error($curl));
    }

    $statusCode = curl_getinfo($curl, CURLINFO_RESPONSE_CODE);
    $headerSize = curl_getinfo($curl, CURLINFO_HEADER_SIZE);
    curl_close($curl);

    $responseBody = substr($rawResponse, $headerSize);
    $decodedBody = $responseBody === '' ? null : json_decode($responseBody, true);

    return [
        'status' => $statusCode,
        'body' => $decodedBody,
        'raw_body' => $responseBody,
    ];
}

function requireManifestValue(array $manifest, string $key): string
{
    $value = $manifest[$key] ?? null;
    if (!is_string($value) || trim($value) === '') {
        throw new RuntimeException("Falta [$key] en release.json.");
    }

    return $value;
}

function loadReleaseManifest(string $manifestPath): array
{
    $rawManifest = file_get_contents($manifestPath);
    if ($rawManifest === false) {
        throw new RuntimeException("No se pudo leer [$manifestPath].");
    }

    $manifest = json_decode($rawManifest, true);
    if (!is_array($manifest)) {
        throw new RuntimeException("No se pudo parsear [$manifestPath].");
    }

    return $manifest;
}

function shellQuote(string $value): string
{
    return "'" . str_replace("'", "'\"'\"'", $value) . "'";
}

function createOrGetRelease(string $owner, string $repo, string $tag, string $commit, string $token): array
{
    $releaseUrl = sprintf('https://api.github.com/repos/%s/%s/releases/tags/%s', $owner, $repo, rawurlencode($tag));
    $existingRelease = githubRequest('GET', $releaseUrl, $token);
    if ($existingRelease['status'] === 200 && is_array($existingRelease['body'])) {
        return $existingRelease['body'];
    }

    if ($existingRelease['status'] !== 404) {
        throw new RuntimeException("GitHub devolvio [{$existingRelease['status']}] al consultar el release [$tag].");
    }

    $createReleaseUrl = sprintf('https://api.github.com/repos/%s/%s/releases', $owner, $repo);
    $payload = json_encode([
        'tag_name' => $tag,
        'target_commitish' => $commit,
        'name' => $tag,
        'draft' => false,
        'prerelease' => false,
    ], JSON_UNESCAPED_SLASHES);

    if (!is_string($payload)) {
        throw new RuntimeException('No se pudo serializar el payload del release.');
    }

    $createdRelease = githubRequest('POST', $createReleaseUrl, $token, $payload);
    if ($createdRelease['status'] !== 201 || !is_array($createdRelease['body'])) {
        throw new RuntimeException("No se pudo crear el release [$tag].");
    }

    return $createdRelease['body'];
}

function deleteExistingAssetIfPresent(array $release, string $assetName, string $owner, string $repo, string $token): void
{
    $assets = $release['assets'] ?? null;
    if (!is_array($assets)) {
        return;
    }

    foreach ($assets as $asset) {
        if (!is_array($asset) || ($asset['name'] ?? null) !== $assetName) {
            continue;
        }

        $assetId = $asset['id'] ?? null;
        if (!is_int($assetId)) {
            continue;
        }

        $deleteUrl = sprintf('https://api.github.com/repos/%s/%s/releases/assets/%d', $owner, $repo, $assetId);
        $response = githubRequest('DELETE', $deleteUrl, $token);
        if ($response['status'] !== 204) {
            throw new RuntimeException("No se pudo borrar el asset existente [$assetName].");
        }
    }
}

function uploadAsset(array $release, string $assetPath, string $token): void
{
    if (!is_file($assetPath)) {
        throw new RuntimeException("No existe el asset [$assetPath].");
    }
}

$token = requireGithubToken();
$repository = requireEnv('ESCRIPTA_RELEASE_GITHUB_REPOSITORY');
$currentDir = requireEnv('ESCRIPTA_CURRENT_DIR');
$pharFilename = requireEnv('ESCRIPTA_RELEASE_PHAR_FILENAME');
$sha256Filename = requireEnv('ESCRIPTA_RELEASE_SHA256_FILENAME');

[$owner, $repo] = parseGithubRepository($repository);
$buildDir = $currentDir . '/var/build';
$manifestPath = $buildDir . '/release.json';
$manifest = loadReleaseManifest($manifestPath);
$tag = requireManifestValue($manifest, 'tag');
$commit = requireManifestValue($manifest, 'commit');

$release = createOrGetRelease($owner, $repo, $tag, $commit, $token);

$assets = [
    $buildDir . '/' . $pharFilename,
    $buildDir . '/' . $sha256Filename,
    $manifestPath,
    $buildDir . '/ESCRIPTA_AGENTS.md',
];

foreach ($assets as $assetPath) {
    $assetName = basename($assetPath);
    deleteExistingAssetIfPresent($release, $assetName, $owner, $repo, $token);
    uploadAsset($release, $assetPath, $token);
}

$uploadUrl = $release['upload_url'] ?? null;
if (!is_string($uploadUrl) || $uploadUrl === '') {
    throw new RuntimeException('El release no expone upload_url.');
}

$uploadUrl = preg_replace('/\{\?name,label\}$/', '', $uploadUrl);
if (!is_string($uploadUrl) || $uploadUrl === '') {
    throw new RuntimeException('No se pudo normalizar upload_url.');
}

$contextPath = $buildDir . '/github_release.env';
$context = [
    'ESCRIPTA_GITHUB_RELEASE_TAG' => $tag,
    'ESCRIPTA_GITHUB_RELEASE_COMMIT' => $commit,
    'ESCRIPTA_GITHUB_RELEASE_UPLOAD_URL' => $uploadUrl,
    'ESCRIPTA_GITHUB_RELEASE_ASSET_PHAR' => $buildDir . '/' . $pharFilename,
    'ESCRIPTA_GITHUB_RELEASE_ASSET_SHA256' => $buildDir . '/' . $sha256Filename,
    'ESCRIPTA_GITHUB_RELEASE_ASSET_MANIFEST' => $manifestPath,
    'ESCRIPTA_GITHUB_RELEASE_ASSET_AGENT_GUIDE' => $buildDir . '/ESCRIPTA_AGENTS.md',
];

$lines = ["#!/usr/bin/env bash"];
foreach ($context as $name => $value) {
    $lines[] = 'export ' . $name . '=' . shellQuote($value);
}

$written = file_put_contents($contextPath, implode("\n", $lines) . "\n");
if ($written === false) {
    throw new RuntimeException("No se pudo escribir [$contextPath].");
}

chmod($contextPath, 0755);

echo "Release preparado: [$tag]\n";
echo "Contexto generado: [$contextPath]\n";
