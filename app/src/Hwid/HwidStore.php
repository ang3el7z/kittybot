<?php

namespace KittyBot\Hwid;

use KittyBot\Storage\HwidRepository;
use Throwable;

final class HwidStore
{
    public function __construct(
        private HwidRepository $storage,
        private string $legacyPath
    ) {
    }

    /** @return array<string,array<string,array<string,mixed>>> */
    public function all(): array
    {
        $fallback = $this->readLegacyFile();

        try {
            $this->storage->seed($fallback);
            return $this->storage->all();
        } catch (Throwable) {
            return $fallback;
        }
    }

    /** @param array<string,array<string,array<string,mixed>>> $storage */
    public function setAll(array $storage): void
    {
        try {
            $this->storage->setAll($storage);
        } catch (Throwable) {
        }

        file_put_contents(
            $this->legacyPath,
            json_encode($storage, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)
        );
    }

    /** @return array<string,array<string,array<string,mixed>>> */
    private function readLegacyFile(): array
    {
        if (!file_exists($this->legacyPath)) {
            return [];
        }

        $storage = json_decode(file_get_contents($this->legacyPath), true);
        return is_array($storage) ? $storage : [];
    }
}
