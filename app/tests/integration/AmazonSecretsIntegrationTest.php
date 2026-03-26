<?php
declare(strict_types=1);

namespace labo86\escripta\tests\integration;

use labo86\escripta\connectors\AmazonSecrets;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\TestCase;

#[Group('integration')]
class AmazonSecretsIntegrationTest extends TestCase
{
    public function testGetSecretInfoAgainstRealAwsSecret(): void
    {
        $secretName = getenv('ESCRIPTA_TEST_AWS_SECRET_NAME') ?: '';
        $expectedJson = getenv('ESCRIPTA_TEST_AWS_SECRET_EXPECTED_JSON') ?: '';

        if ($secretName === '' || $expectedJson === '') {
            self::markTestSkipped('Definir ESCRIPTA_TEST_AWS_SECRET_NAME y ESCRIPTA_TEST_AWS_SECRET_EXPECTED_JSON para correr este test.');
        }

        $expected = json_decode($expectedJson, true);
        self::assertIsArray($expected, 'ESCRIPTA_TEST_AWS_SECRET_EXPECTED_JSON debe ser un JSON object valido.');

        $actual = AmazonSecrets::getSecretInfo($secretName);

        self::assertSame($expected, $actual);
    }
}
