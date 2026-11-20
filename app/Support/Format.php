<?php

namespace App\Support;

use Carbon\CarbonInterface;

class Format
{
    public static function number(int|float $value): string
    {
        return number_format($value, 0, ',', '.');
    }

    public static function xp(int $value, bool $signed = false): string
    {
        $sign = $signed && $value > 0 ? '+' : '';

        return $sign.self::number($value)."\u{00A0}XP";
    }

    /** 01:24:37 */
    public static function clock(int $seconds): string
    {
        $seconds = max(0, $seconds);

        return sprintf('%02d:%02d:%02d', intdiv($seconds, 3600), intdiv($seconds % 3600, 60), $seconds % 60);
    }

    /** "3 jam 12 menit", "22 menit", "kurang dari 1 menit" */
    public static function duration(int $seconds): string
    {
        $hours = intdiv($seconds, 3600);
        $minutes = intdiv($seconds % 3600, 60);
        if ($hours === 0 && $minutes === 0) {
            return 'kurang dari 1 menit';
        }
        if ($hours >= 24) {
            $days = intdiv($hours, 24);
            $hours = $hours % 24;

            return $days.' hari'.($hours ? ' '.$hours.' jam' : '');
        }

        return trim(($hours ? $hours.' jam ' : '').($minutes ? $minutes.' menit' : ''));
    }

    public static function time(?CarbonInterface $at): string
    {
        return $at ? $at->format('H.i') : '';
    }

    /** 14 Nov 2026 */
    public static function date(?CarbonInterface $at): string
    {
        return $at ? $at->translatedFormat('j M Y') : '';
    }

    /** 20 November 2026 */
    public static function longDate(?CarbonInterface $at): string
    {
        return $at ? $at->translatedFormat('j F Y') : '';
    }

    public static function initials(string $name): string
    {
        $clean = trim(preg_replace('/[^\pL\s]/u', ' ', $name));
        $parts = preg_split('/\s+/', $clean, -1, PREG_SPLIT_NO_EMPTY);
        if (! $parts) {
            return '?';
        }
        $first = mb_substr($parts[0], 0, 1);
        $last = count($parts) > 1 ? mb_substr(end($parts), 0, 1) : '';

        return mb_strtoupper($first.$last);
    }
}
