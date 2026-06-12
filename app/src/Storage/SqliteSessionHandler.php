<?php

namespace KittyBot\Storage;

use PDO;
use SessionHandlerInterface;

final class SqliteSessionHandler implements SessionHandlerInterface
{
    public function __construct(private PDO $pdo)
    {
    }

    public function open(string $path, string $name): bool
    {
        return true;
    }

    public function close(): bool
    {
        return true;
    }

    public function read(string $id): string|false
    {
        $stmt = $this->pdo->prepare('SELECT payload FROM bot_sessions WHERE user_id = ?');
        $stmt->execute([$id]);
        $payload = $stmt->fetchColumn();

        return $payload === false ? '' : (string) $payload;
    }

    public function write(string $id, string $data): bool
    {
        $stmt = $this->pdo->prepare(
            'INSERT INTO bot_sessions(user_id, payload, updated_at) VALUES (?, ?, CURRENT_TIMESTAMP)
             ON CONFLICT(user_id) DO UPDATE SET payload = excluded.payload, updated_at = CURRENT_TIMESTAMP'
        );

        return $stmt->execute([$id, $data]);
    }

    public function destroy(string $id): bool
    {
        $stmt = $this->pdo->prepare('DELETE FROM bot_sessions WHERE user_id = ?');
        return $stmt->execute([$id]);
    }

    public function gc(int $max_lifetime): int|false
    {
        $cutoff = gmdate('Y-m-d H:i:s', time() - $max_lifetime);
        $stmt = $this->pdo->prepare('DELETE FROM bot_sessions WHERE updated_at < ?');
        $stmt->execute([$cutoff]);

        return $stmt->rowCount();
    }
}
