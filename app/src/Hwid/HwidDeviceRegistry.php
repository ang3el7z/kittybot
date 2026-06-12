<?php

namespace KittyBot\Hwid;

final class HwidDeviceRegistry
{
    public function __construct(private HwidStore $store)
    {
    }

    /** @return array<string,array<string,mixed>> */
    public function devicesByUser(string $userId): array
    {
        $storage = $this->store->all();
        return $storage[$userId] ?? [];
    }

    /** @param array<string,mixed> $info */
    public function setDevice(string $userId, string $hwid, array $info): void
    {
        $storage = $this->store->all();
        $storage[$userId][$hwid] = $info;
        $this->store->setAll($storage);
    }

    public function deleteDevice(string $userId, string $hwid): void
    {
        $storage = $this->store->all();
        if (!isset($storage[$userId][$hwid])) {
            return;
        }

        unset($storage[$userId][$hwid]);
        if (empty($storage[$userId])) {
            unset($storage[$userId]);
        }

        $this->store->setAll($storage);
    }

    public function deleteUser(string $userId): void
    {
        $storage = $this->store->all();
        if (!isset($storage[$userId])) {
            return;
        }

        unset($storage[$userId]);
        $this->store->setAll($storage);
    }
}
