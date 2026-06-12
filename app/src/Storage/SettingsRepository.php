<?php

namespace KittyBot\Storage;

use PDO;

final class SettingsRepository
{
    public function __construct(private PDO $pdo)
    {
    }

    /** @return array<string,mixed> */
    public function getJson(string $key, array $default = []): array
    {
        $stmt = $this->pdo->prepare('SELECT value FROM settings WHERE key = ?');
        $stmt->execute([$key]);
        $value = $stmt->fetchColumn();
        if ($value === false || $value === '') {
            return $default;
        }

        $decoded = json_decode((string) $value, true);
        return is_array($decoded) ? $decoded : $default;
    }

    /** @param array<string,mixed> $value */
    public function setJson(string $key, array $value): void
    {
        $stmt = $this->pdo->prepare(
            'INSERT INTO settings(key, value, updated_at) VALUES (?, ?, CURRENT_TIMESTAMP)
             ON CONFLICT(key) DO UPDATE SET value = excluded.value, updated_at = CURRENT_TIMESTAMP'
        );
        $stmt->execute([
            $key,
            json_encode($value, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
        ]);
    }

    /** @param array<string,mixed> $value */
    public function seedJson(string $key, array $value): void
    {
        $stmt = $this->pdo->prepare('INSERT OR IGNORE INTO settings(key, value) VALUES (?, ?)');
        $stmt->execute([
            $key,
            json_encode($value, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
        ]);
    }
}
