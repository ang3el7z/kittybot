<?php

namespace KittyBot\Backups;

use KittyBot\Services\ContainerService;
use KittyBot\Services\ServiceCatalog;

final class BackupRestoreService
{
    /** @param array{private:string,public:string} $ssl */
    public function applySsl(array $ssl): void
    {
        file_put_contents('/certs/cert_private', $ssl['private']);
        file_put_contents('/certs/cert_public', $ssl['public']);
    }

    /** @param array{private:string,public:string} $dnstt */
    public function applyDnstt(array $dnstt): void
    {
        file_put_contents('/config/dnstt/server.key', $dnstt['private']);
        file_put_contents('/config/dnstt/server.pub', $dnstt['public']);
    }

    /** @param array<string,mixed> $config */
    public function applyAdguard(array $config, string $path, callable $stop, callable $start): void
    {
        $stop();
        yaml_emit_file($path, $config);
        $start();
    }

    public function applyMtproto(string $secret, ?string $domain, callable $restart): void
    {
        file_put_contents('/config/mtprotosecret', $secret);
        file_put_contents('/config/mtprotodomain', $domain ?: '');
        $restart();
    }

    public function applyOcserv(string $config, string $passwd, callable $restart): void
    {
        file_put_contents('/config/ocserv.passwd', $passwd);
        $restart($config);
    }

    /** @param array<string,mixed> $config */
    public function applyHysteria(array $config, callable $restart): void
    {
        yaml_emit_file('/config/hysteria.yaml', $config);
        $restart();
    }

    /** @return array<string,mixed> */
    public function normalizeHwid(mixed $hwid): array
    {
        return is_array($hwid) ? $hwid : [];
    }

    /** @param array<string,mixed> $states
     *  @return list<string>
     */
    public function applyServiceStates(array $states, ContainerService $containers): array
    {
        $errors = [];
        foreach ($states as $service => $enabled) {
            if (!ServiceCatalog::isOptional($service)) {
                continue;
            }

            try {
                if ($enabled) {
                    $containers->enable($service);
                } else {
                    $containers->disable($service);
                }
            } catch (\Throwable $e) {
                $errors[] = "service $service: " . $e->getMessage();
            }
        }

        return $errors;
    }
}
