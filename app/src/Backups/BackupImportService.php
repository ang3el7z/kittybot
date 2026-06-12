<?php

namespace KittyBot\Backups;

final class BackupImportService
{
    public function __construct(private BackupPayloadCodec $codec)
    {
    }

    /** @return array<string,mixed> */
    public function fromPath(string $path): array
    {
        return $this->fromRaw((string) file_get_contents($path));
    }

    /** @return array<string,mixed> */
    public function fromUrl(string $url): array
    {
        return $this->fromRaw((string) file_get_contents($url));
    }

    /** @return array<string,mixed> */
    public function fromRaw(string $raw): array
    {
        $payload = $this->codec->decode($raw);
        if ($payload === null) {
            throw new \InvalidArgumentException('error');
        }
        if (!$this->codec->supports($payload)) {
            throw new \InvalidArgumentException('unsupported backup format');
        }

        return $payload;
    }
}
