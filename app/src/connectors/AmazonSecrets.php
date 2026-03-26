<?php
declare(strict_types=1);
# version 1.1.0

namespace labo86\escripta\connectors;

class AmazonSecrets
{
    static function getSecretRawValue(string $secretName): ?string
    {
        $secretName = escapeshellarg($secretName);
        ob_start();
        passthru("aws secretsmanager get-secret-value --secret-id $secretName --output json", $return);
        $jsonString = ob_get_clean();

        if ($return !== 0) {
            return null;
        }

        $data = json_decode($jsonString, true);
        if (!is_array($data)) {
            return null;
        }

        if (!empty($data['SecretString'])) {
            return $data['SecretString'];
        }

        if (!empty($data['SecretBinary'])) {
            return base64_decode($data['SecretBinary']);
        }

        return null;
    }

    static function getSecretInfo(string $secretName): ?array
    {
        $secretString = self::getSecretRawValue($secretName);
        if ($secretString === null || $secretString === '') {
            return [];
        }

        $decoded = json_decode($secretString, true);
        if (is_array($decoded)) {
            return $decoded;
        }

        return ['value' => $secretString];
    }
}
