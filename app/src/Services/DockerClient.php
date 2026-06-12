<?php

namespace KittyBot\Services;

final class DockerClient
{
    public function __construct(private string $socket = '/var/run/docker.sock')
    {
    }

    /** @return array<string,mixed> */
    public function request(string $url, string $method = 'GET', array $data = []): array
    {
        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_CUSTOMREQUEST => $method,
            CURLOPT_POSTFIELDS => $data ? json_encode($data) : null,
            CURLOPT_URL => 'http://localhost' . $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_UNIX_SOCKET_PATH => $this->socket,
            CURLOPT_HTTPHEADER => ['Content-Type: application/json'],
        ]);

        $raw = curl_exec($ch);
        if ($raw === false) {
            $error = curl_error($ch);
            curl_close($ch);
            throw new \RuntimeException('Docker API error: ' . $error);
        }

        $code = curl_getinfo($ch, CURLINFO_RESPONSE_CODE);
        curl_close($ch);
        if ($code >= 400) {
            throw new \RuntimeException("Docker API HTTP $code: $raw");
        }

        if ($raw === '' || $raw === 'null') {
            return [];
        }

        return json_decode($raw, true) ?: [];
    }

    /** @return array<string,mixed>|null */
    public function findComposeContainer(string $service): ?array
    {
        $containers = $this->request('/containers/json?all=1');
        foreach ($containers as $container) {
            if (($container['Labels']['com.docker.compose.service'] ?? null) === $service) {
                return $container;
            }
        }
        return null;
    }

    public function action(string $service, string $action): void
    {
        $container = $this->findComposeContainer($service);
        if (!$container) {
            throw new \RuntimeException("Container for service '$service' not found");
        }

        $id = $container['Id'];
        $this->request("/containers/$id/$action", 'POST');
    }
}
