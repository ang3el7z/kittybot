<?php

namespace KittyBot\Storage;

use PDO;

final class HwidRepository
{
    public function __construct(private PDO $pdo)
    {
    }

    /** @param array<string,array<string,array<string,mixed>>> $storage */
    public function seed(array $storage): void
    {
        $count = (int) $this->pdo->query('SELECT COUNT(*) FROM hwid_devices')->fetchColumn();
        if ($count > 0) {
            return;
        }

        $this->setAll($storage);
    }

    /** @return array<string,array<string,array<string,mixed>>> */
    public function all(): array
    {
        $rows = $this->pdo->query('SELECT user_id, hwid, payload FROM hwid_devices ORDER BY user_id, id')->fetchAll();
        $storage = [];
        foreach ($rows as $row) {
            $payload = json_decode($row['payload'], true);
            $storage[$row['user_id']][$row['hwid']] = is_array($payload) ? $payload : [];
        }

        return $storage;
    }

    /** @param array<string,array<string,array<string,mixed>>> $storage */
    public function setAll(array $storage): void
    {
        $this->pdo->beginTransaction();
        try {
            $this->pdo->exec('DELETE FROM hwid_devices');
            foreach ($storage as $userId => $devices) {
                foreach ((array) $devices as $hwid => $info) {
                    $this->setDevice((string) $userId, (string) $hwid, is_array($info) ? $info : []);
                }
            }
            $this->pdo->commit();
        } catch (\Throwable $e) {
            $this->pdo->rollBack();
            throw $e;
        }
    }

    /** @param array<string,mixed> $info */
    public function setDevice(string $userId, string $hwid, array $info): void
    {
        $stmt = $this->pdo->prepare(
            'INSERT INTO hwid_devices(user_id, hwid, payload, updated_at) VALUES (?, ?, ?, CURRENT_TIMESTAMP)
             ON CONFLICT(user_id, hwid) DO UPDATE SET payload = excluded.payload, updated_at = CURRENT_TIMESTAMP'
        );
        $stmt->execute([
            $userId,
            $hwid,
            json_encode($info, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
        ]);
    }
}
