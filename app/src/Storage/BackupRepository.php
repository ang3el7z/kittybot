<?php

namespace KittyBot\Storage;

use PDO;

final class BackupRepository
{
    public function __construct(private PDO $pdo)
    {
    }

    public function add(string $name, string $payload): int
    {
        $decoded = json_decode($payload, true);
        if (!is_array($decoded) || ($decoded['schema_version'] ?? null) !== 2) {
            throw new \InvalidArgumentException('Unsupported backup payload');
        }

        $stmt = $this->pdo->prepare('INSERT INTO backups(name, payload) VALUES (?, ?)');
        $stmt->execute([$name, $payload]);

        return (int) $this->pdo->lastInsertId();
    }

    /** @return list<array{id:int,name:string,created_at:string}> */
    public function list(int $limit = 10): array
    {
        $stmt = $this->pdo->prepare('SELECT id, name, created_at FROM backups ORDER BY id DESC LIMIT ?');
        $stmt->bindValue(1, max(1, $limit), PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll();
    }

    public function payload(int $id): ?string
    {
        $stmt = $this->pdo->prepare('SELECT payload FROM backups WHERE id = ?');
        $stmt->execute([$id]);
        $payload = $stmt->fetchColumn();

        return $payload === false ? null : (string) $payload;
    }
}
