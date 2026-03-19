<?php
declare(strict_types=1);

namespace labo86\escripta\tests\integration;

use labo86\escripta\connectors\OnePassword;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\TestCase;

#[Group('integration')]
class OnePasswordIntegrationTest extends TestCase
{
    public function testGetItemInfoAgainstRealOnePasswordItem(): void
    {
        $itemName = getenv('ESCRIPTA_TEST_OP_ITEM_NAME') ?: '';
        $expectedJson = getenv('ESCRIPTA_TEST_OP_ITEM_EXPECTED_JSON') ?: '';

        if ($itemName === '' || $expectedJson === '') {
            self::markTestSkipped('Definir ESCRIPTA_TEST_OP_ITEM_NAME y ESCRIPTA_TEST_OP_ITEM_EXPECTED_JSON para correr este test.');
        }

        $expected = json_decode($expectedJson, true);
        self::assertIsArray($expected, 'ESCRIPTA_TEST_OP_ITEM_EXPECTED_JSON debe ser un JSON object valido.');

        $rawItem = OnePassword::getItemRawInfo($itemName);
        self::assertIsArray($rawItem, 'No se pudo leer el item real de OnePassword.');

        $actual = OnePassword::getItemInfo($rawItem);

        self::assertSame($expected, $actual);
    }
}
