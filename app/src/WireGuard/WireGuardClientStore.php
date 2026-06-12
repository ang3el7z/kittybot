<?php

namespace KittyBot\WireGuard;

use KittyBot\Storage\ClientsRepository;
use Throwable;

final class WireGuardClientStore
{
    public function __construct(
        private ClientsRepository $clients,
        private string $primaryPath,
        private string $secondaryPath
    ) {
    }

    /** @return list<array<string,mixed>> */
    public function read(string $scope): array
    {
        $fallback = $this->readLegacyFile($scope);

        try {
            $this->clients->seed($scope, $fallback);
            return $this->clients->all($scope);
        } catch (Throwable) {
            return $fallback;
        }
    }

    /** @param list<array<string,mixed>> $clients */
    public function save(string $scope, array $clients, string $endpoint): void
    {
        foreach ($clients as $key => $client) {
            $clients[$key]['peers'][0]['Endpoint'] = $endpoint;
        }
        $clients = array_values($clients);

        try {
            $this->clients->setAll($scope, $clients);
        } catch (Throwable) {
        }

        file_put_contents(
            $this->legacyPath($scope),
            json_encode($clients, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)
        );
    }

    /** @return list<array<string,mixed>> */
    private function readLegacyFile(string $scope): array
    {
        $path = $this->legacyPath($scope);
        if (!file_exists($path)) {
            return [];
        }

        $clients = json_decode(file_get_contents($path), true);
        return is_array($clients) ? $clients : [];
    }

    private function legacyPath(string $scope): string
    {
        return $scope === 'wg1' ? $this->secondaryPath : $this->primaryPath;
    }
}
