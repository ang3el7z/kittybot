<?php

namespace KittyBot\Backups;

use KittyBot\Storage\BackupRepository;

final class BackupHistoryService
{
    public function __construct(
        private BackupRepository $repository,
        private ?BackupPayloadCodec $codec = null,
    )
    {
    }

    public function storeExport(string $botName, string $payload): string
    {
        $safeBot = preg_replace('~[\W]~iu', '_', $botName) ?: 'kittybot';
        $name = "{$safeBot}_export_" . date('d_m_Y_H_i') . '.json';
        $this->repository->add($name, $payload);

        return $name;
    }

    /** @return list<array{id:int,name:string,created_at:string}> */
    public function recent(int $limit = 10): array
    {
        return $this->repository->list($limit);
    }

    public function payload(int $id): ?string
    {
        return $this->repository->payload($id);
    }

    public function filename(int $id): string
    {
        return "kittybot_backup_$id.json";
    }

    public function codec(): BackupPayloadCodec
    {
        return $this->codec ??= new BackupPayloadCodec();
    }
}
