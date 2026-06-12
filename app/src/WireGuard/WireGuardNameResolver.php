<?php

namespace KittyBot\WireGuard;

final class WireGuardNameResolver
{
    /** @param array<string,mixed> $peer */
    public function name(array $peer): string
    {
        $name = '';
        foreach ($peer as $key => $value) {
            if (preg_match('~^#.*name$~', (string) $key)) {
                $name = (string) $value;
            }
        }

        return $name !== '' ? $name : (string) ($peer['AllowedIPs'] ?? $peer['Address'] ?? '');
    }
}
