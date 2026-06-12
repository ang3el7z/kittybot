<?php

namespace KittyBot\Backups;

final class BackupPayloadCodec
{
    public const SCHEMA_VERSION = 2;

    /** @param array<string,mixed> $payload */
    public function encode(array $payload): string
    {
        $payload['schema_version'] = self::SCHEMA_VERSION;

        return json_encode($payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    }

    /** @return array<string,mixed>|null */
    public function decode(string $json): ?array
    {
        $decoded = json_decode($json, true);
        return is_array($decoded) ? $decoded : null;
    }

    public function supports(array $payload): bool
    {
        return ($payload['schema_version'] ?? null) === self::SCHEMA_VERSION;
    }
}
