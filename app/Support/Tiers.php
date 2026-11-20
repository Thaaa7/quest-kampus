<?php

namespace App\Support;

class Tiers
{
    public static function all(): array
    {
        return config('kampusquest.tiers');
    }

    /** Tier tertinggi yang sudah dicapai, atau null bila belum ada. */
    public static function current(int $xp): ?array
    {
        $reached = null;
        foreach (self::all() as $tier) {
            if ($xp >= $tier['xp']) {
                $reached = $tier;
            }
        }

        return $reached;
    }

    public static function next(int $xp): ?array
    {
        foreach (self::all() as $tier) {
            if ($xp < $tier['xp']) {
                return $tier;
            }
        }

        return null;
    }

    public static function byLevel(int $level): ?array
    {
        foreach (self::all() as $tier) {
            if ($tier['level'] === $level) {
                return $tier;
            }
        }

        return null;
    }

    public static function leaderboardXp(): int
    {
        return self::byLevel(1)['xp'];
    }

    public static function certificate(string $key): ?array
    {
        $cert = config("kampusquest.certificates.$key");
        if (! $cert) {
            return null;
        }
        $tier = self::byLevel($cert['tier']);

        return $cert + ['key' => $key, 'xp' => $tier['xp'], 'tier_name' => $tier['name']];
    }
}
