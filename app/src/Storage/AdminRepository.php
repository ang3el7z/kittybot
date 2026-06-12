<?php

namespace KittyBot\Storage;

use PDO;

final class AdminRepository
{
    public function __construct(private PDO $pdo)
    {
    }

    /** @param list<string|int> $admins */
    public function seed(array $admins): void
    {
        $stmt = $this->pdo->prepare('INSERT OR IGNORE INTO admins(telegram_id) VALUES (?)');
        foreach ($admins as $admin) {
            if ($admin !== '' && $admin !== null) {
                $stmt->execute([(string) $admin]);
            }
        }
    }

    /** @return list<string> */
    public function all(): array
    {
        $rows = $this->pdo->query('SELECT telegram_id FROM admins ORDER BY created_at, telegram_id')->fetchAll();
        return array_map(static fn(array $row): string => (string) $row['telegram_id'], $rows);
    }

    public function add(string|int $telegramId): void
    {
        $stmt = $this->pdo->prepare('INSERT OR IGNORE INTO admins(telegram_id) VALUES (?)');
        $stmt->execute([(string) $telegramId]);
    }

    public function delete(string|int $telegramId): void
    {
        $stmt = $this->pdo->prepare('DELETE FROM admins WHERE telegram_id = ?');
        $stmt->execute([(string) $telegramId]);
    }
}
