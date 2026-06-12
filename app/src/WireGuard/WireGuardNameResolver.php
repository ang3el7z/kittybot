<?php

namespace KittyBot\WireGuard;

final class WireGuardNameResolver
{
    /** @param array<string,mixed> $peer */
    public function name(array $peer): string
    {
        foreach ($peer as $key => $value) {
            if (preg_match('~^#.*name$~', (string) $key)) {
                return (string) $value;
            }
        }

        return (string) ($peer['AllowedIPs'] ?? $peer['Address'] ?? '');
    }
}
