<?php

namespace KittyBot\Storage;

use KittyBot\Backups\BackupPayloadCodec;
use PDO;

final class BackupRepository
{
    public function __construct(
        private PDO $pdo,
        private ?BackupPayloadCodec $codec = null,
    )
    {
    }

    public function add(string $name, string $payload): int
    {
        $decoded = $this->codec()->decode($payload);
        if ($decoded === null || !$this->codec()->supports($decoded)) {
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

    private function codec(): BackupPayloadCodec
    {
        return $this->codec ??= new BackupPayloadCodec();
    }
}
