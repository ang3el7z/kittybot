<?php

namespace KittyBot\WireGuard;

final class WireGuardConfigCodec
{
    /** @return array{interface:array<string,mixed>,peers:list<array<string,mixed>>} */
    public function parse(string $config): array
    {
        $sections = [];
        $section = 0;

        foreach (array_filter(explode(PHP_EOL, $config)) as $line) {
            if (preg_match('~\[(.+)\]~', $line, $match)) {
                $section++;
                $sections[$section]['type'] = $match[1] === 'Interface' ? 'interface' : 'peer';
                continue;
            }

            $parts = explode('=', $line, 2);
            if (count($parts) !== 2 || $section === 0) {
                continue;
            }

            $sections[$section][trim($parts[0])] = trim($parts[1]);
        }

        $data = [
            'interface' => [],
            'peers' => [],
        ];

        foreach ($sections as $sectionData) {
            $type = $sectionData['type'];
            unset($sectionData['type']);

            if ($type === 'interface') {
                $data['interface'] = $sectionData;
            } else {
                $data['peers'][] = $sectionData;
            }
        }

        return $data;
    }

    /**
     * @param array{interface:array<string,mixed>,peers?:list<array<string,mixed>>} $data
     */
    public function render(array $data, string $dns, string $mtu, string $endpointHost, string $endpointPort): string
    {
        $data['interface'] ??= [];
        $conf = ['[Interface]'];

        if (empty($data['interface']['ListenPort'])) {
            if (empty($data['interface']['DNS'])) {
                $data['interface']['DNS'] = $dns;
            }
            if (empty($data['interface']['MTU'])) {
                $data['interface']['MTU'] = $mtu;
            }
        }

        foreach ($data['interface'] as $key => $value) {
            $conf[] = "$key = $value";
        }

        foreach ($data['peers'] ?? [] as $peer) {
            $conf[] = '';
            $conf[] = !empty($peer['# PublicKey']) ? '# [Peer]' : '[Peer]';

            if (!empty($peer['Endpoint'])) {
                $peer['Endpoint'] = "$endpointHost:$endpointPort";
            }

            foreach ($peer as $key => $value) {
                $conf[] = "$key = $value";
            }
        }

        return implode(PHP_EOL, $conf);
    }
}
