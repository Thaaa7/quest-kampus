<?php

namespace App\Support;

/**
 * Kategori kerusakan fasilitas. Ikon memakai nama Material Symbols yang
 * file SVG-nya ada di resources/icons.
 */
class Categories
{
    public const ALL = [
        'ac' => ['label' => 'AC / Pendingin', 'short' => 'AC', 'icon' => 'ac_unit'],
        'listrik' => ['label' => 'Listrik & Lampu', 'short' => 'Listrik', 'icon' => 'lightbulb'],
        'proyektor' => ['label' => 'Proyektor & Layar', 'short' => 'Proyektor', 'icon' => 'slideshow'],
        'furnitur' => ['label' => 'Meja & Kursi', 'short' => 'Meja atau kursi', 'icon' => 'chair'],
        'air' => ['label' => 'Air & Toilet', 'short' => 'Air atau toilet', 'icon' => 'water_drop'],
        'internet' => ['label' => 'Internet / Wi-Fi', 'short' => 'Internet', 'icon' => 'wifi'],
        'pintu' => ['label' => 'Pintu & Jendela', 'short' => 'Pintu atau jendela', 'icon' => 'door_front'],
        'lainnya' => ['label' => 'Lainnya', 'short' => 'Kerusakan ini', 'icon' => 'more_horiz'],
    ];

    public static function keys(): array
    {
        return array_keys(self::ALL);
    }

    public static function label(string $key): string
    {
        return self::ALL[$key]['label'] ?? $key;
    }

    public static function short(string $key): string
    {
        return self::ALL[$key]['short'] ?? $key;
    }

    public static function icon(string $key): string
    {
        return self::ALL[$key]['icon'] ?? 'more_horiz';
    }
}
