<?php

namespace KittyBot\Storage;

use PDO;

final class ServiceStateRepository
{
    public function __construct(private PDO $pdo)
    {
    }

    public function seed(array $services): void
    {
        $stmt = $this->pdo->prepare('INSERT OR IGNORE INTO service_states(service, enabled) VALUES (?, 1)');
        foreach ($services as $service) {
            $stmt->execute([$service]);
        }
    }

    public function setEnabled(string $service, bool $enabled): void
    {
        $stmt = $this->pdo->prepare(
            'INSERT INTO service_states(service, enabled, updated_at) VALUES (?, ?, CURRENT_TIMESTAMP)
             ON CONFLICT(service) DO UPDATE SET enabled = excluded.enabled, updated_at = CURRENT_TIMESTAMP'
        );
        $stmt->execute([$service, $enabled ? 1 : 0]);
    }

    public function isEnabled(string $service): bool
    {
        $stmt = $this->pdo->prepare('SELECT enabled FROM service_states WHERE service = ?');
        $stmt->execute([$service]);
        $value = $stmt->fetchColumn();
        return $value === false ? true : (bool) $value;
    }

    /** @return array<string,bool> */
    public function all(): array
    {
        $rows = $this->pdo->query('SELECT service, enabled FROM service_states ORDER BY service')->fetchAll();
        $states = [];
        foreach ($rows as $row) {
            $states[$row['service']] = (bool) $row['enabled'];
        }
        return $states;
    }
}
