<?php

namespace KittyBot\Storage;

use PDO;

final class ClientsRepository
{
    public function __construct(private PDO $pdo)
    {
    }

    /** @param list<array<string,mixed>> $clients */
    public function seed(string $scope, array $clients): void
    {
        $stmt = $this->pdo->prepare('SELECT COUNT(*) FROM clients WHERE scope = ?');
        $stmt->execute([$scope]);
        if ((int) $stmt->fetchColumn() > 0) {
            return;
        }

        $this->setAll($scope, $clients);
    }

    /** @return list<array<string,mixed>> */
    public function all(string $scope): array
    {
        $stmt = $this->pdo->prepare('SELECT payload FROM clients WHERE scope = ? ORDER BY id');
        $stmt->execute([$scope]);

        $clients = [];
        foreach ($stmt->fetchAll() as $row) {
            $client = json_decode($row['payload'], true);
            if (is_array($client)) {
                $clients[] = $client;
            }
        }

        return $clients;
    }

    /** @param list<array<string,mixed>> $clients */
    public function setAll(string $scope, array $clients): void
    {
        $this->pdo->beginTransaction();
        try {
            $delete = $this->pdo->prepare('DELETE FROM clients WHERE scope = ?');
            $delete->execute([$scope]);

            $insert = $this->pdo->prepare(
                'INSERT INTO clients(scope, name, payload, updated_at) VALUES (?, ?, ?, CURRENT_TIMESTAMP)'
            );
            foreach (array_values($clients) as $client) {
                $insert->execute([
                    $scope,
                    $this->clientName($client),
                    json_encode($client, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
                ]);
            }

            $this->pdo->commit();
        } catch (\Throwable $e) {
            $this->pdo->rollBack();
            throw $e;
        }
    }

    /** @param array<string,mixed> $client */
    private function clientName(array $client): ?string
    {
        return $client['interface']['## name']
            ?? $client['email']
            ?? $client['name']
            ?? null;
    }
}
