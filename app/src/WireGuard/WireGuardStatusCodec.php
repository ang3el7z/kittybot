<?php

namespace KittyBot\WireGuard;

final class WireGuardStatusCodec
{
    /** @return array{interface:array<string,mixed>,peers:list<array<string,mixed>>} */
    public function parse(string $status): array
    {
        $sections = [];
        $section = 0;

        foreach (array_filter(explode(PHP_EOL, $status)) as $line) {
            if (preg_match('~^(interface|peer):~', $line, $match)) {
                $section++;
                $sections[$section]['type'] = $match[1] === 'interface' ? 'interface' : 'peer';
            }

            $parts = explode(':', $line, 2);
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

    /** @param list<array<string,mixed>> $peers */
    public function findPeer(string $publicKey, array $peers): ?array
    {
        foreach ($peers as $peer) {
            if (($peer['peer'] ?? null) === $publicKey) {
                return $peer;
            }
        }

        return null;
    }
}
