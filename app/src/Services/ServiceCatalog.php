<?php

namespace KittyBot\Services;

final class ServiceCatalog
{
    public const CORE = ['php', 'service', 'ng', 'up'];

    public const OPTIONAL = [
        'wg',
        'wg1',
        'xr',
        'ad',
        'tg',
        'oc',
        'np',
        'wp',
        'proxy',
        'ss',
        'dnstt',
        'hy',
    ];

    public static function isCore(string $service): bool
    {
        return in_array($service, self::CORE, true);
    }

    public static function isOptional(string $service): bool
    {
        return in_array($service, self::OPTIONAL, true);
    }

    public static function isKnown(string $service): bool
    {
        return self::isCore($service) || self::isOptional($service);
    }

    /** @return list<string> */
    public static function all(): array
    {
        return array_merge(self::CORE, self::OPTIONAL);
    }
}
