<?php

namespace KittyBot\Backups;

final class BackupApplyService
{
    public function __construct(private BackupRestoreService $restore)
    {
    }

    /** @param array<string,mixed> $json
     *  @param array<string,callable> $ops
     */
    public function apply(array $json, array $ops, callable $progress): void
    {
        $switchAmnezia = 0;
        $switchWg1Amnezia = 0;

        if (!empty($json['ssl'])) {
            $progress('update certificates');
            $this->restore->applySsl($json['ssl']);
        }

        if (!empty($json['pac'])) {
            $progress('update pac');
            $pacRestore = $this->restore->applyPac(
                $json['pac'],
                $ops['getPacConf'],
                $ops['setPacConf'],
                $ops['restartNaive'],
                $ops['pacUpdate'],
            );
            $switchAmnezia = $pacRestore['switch_amnezia'];
            $switchWg1Amnezia = $pacRestore['switch_wg1amnezia'];
            $progress('update naiveproxy');
        }

        if (!empty($json['wg'])) {
            $progress('update wireguard');
            $ops['setWgScope'](0);
            $this->restore->applyWireguardInstance(
                $json['wg'],
                $switchAmnezia,
                $ops['saveClients'],
                $ops['createConfig'],
                $ops['restartWG'],
                $ops['iptablesWG'],
            );
        }

        if (!empty($json['wg1'])) {
            $progress('update wireguard 1');
            $ops['setWgScope'](1);
            $this->restore->applyWireguardInstance(
                $json['wg1'],
                $switchWg1Amnezia,
                $ops['saveClients'],
                $ops['createConfig'],
                $ops['restartWG'],
                $ops['iptablesWG'],
            );
        }

        if (!empty($json['ad'])) {
            $progress('update adguard');
            $this->restore->applyAdguard($json['ad'], $ops['adguardPath'](), $ops['stopAd'], $ops['startAd']);
        }

        if (!empty($json['ss'])) {
            $progress('update shadowsocks server');
            $this->restore->applyShadowsocksServer($json['ss'], $ops['ssh']);
        }

        if (!empty($json['sl'])) {
            $progress('update shadowsocks proxy');
            $this->restore->applyShadowsocksProxy($json['sl'], $ops['ssh']);
        }

        if (!empty($json['mtproto'])) {
            $progress('update mtproto');
            $this->restore->applyMtproto($json['mtproto'], $json['mtprotodomain'] ?? '', $ops['restartTG']);
        }

        if (array_key_exists('hwid', $json)) {
            $progress('update hwid devices');
            $ops['setHwidStorage']($this->restore->normalizeHwid($json['hwid']));
        }

        if (!empty($json['xray'])) {
            $progress('update xray');
            $this->restore->applyXray(
                $json['xray'],
                $json['pac'] ?? [],
                $ops['restartXray'],
                $ops['adguardXrayClients'],
                $ops['setUpstreamDomain'],
            );
        }

        if (!empty($json['xraystats'])) {
            $progress('update xray stats');
            $this->restore->applyXrayStats($json['xraystats'], $ops['setXrayStats']);
        }

        if (!empty($json['oc'])) {
            $progress('update ocserv');
            $this->restore->applyOcserv($json['oc'], $json['ocu'], $ops['restartOcserv']);
        }

        if (!empty($json['hy'])) {
            $progress('update hysteria');
            $this->restore->applyHysteria($json['hy'], $ops['restartHysteria']);
        }

        if (!empty($json['pac']['domain'])) {
            $ops['setUpstreamDomainOcserv']($json['pac']['domain']);
            $ops['setUpstreamDomainNaive']($json['pac']['domain']);
        }

        if (!empty($json['dnstt'])) {
            $progress('update dnstt certificates');
            $this->restore->applyDnstt($json['dnstt']);
        }

        if (!empty($json['service_states']) && is_array($json['service_states'])) {
            $progress('update service states');
            foreach ($this->restore->applyServiceStates($json['service_states'], $ops['containers']()) as $error) {
                $progress($error);
            }
        }

        $progress('reset nginx');
        $ops['cloakNginx']();
    }
}
