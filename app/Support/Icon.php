<?php

namespace App\Support;

class Icon
{
    private static array $cache = [];

    /** SVG Material Symbols dari resources/icons, siap disisipkan inline. */
    public static function svg(string $name, bool $fill = false, string $class = 'h-6 w-6'): string
    {
        $file = $name.($fill ? '-fill' : '');
        if (! isset(self::$cache[$file])) {
            $path = resource_path("icons/{$file}.svg");
            if (! is_file($path)) {
                $path = resource_path("icons/{$name}.svg");
            }
            $svg = is_file($path) ? file_get_contents($path) : '<svg viewBox="0 -960 960 960"></svg>';
            self::$cache[$file] = preg_replace('/\s(width|height)="\d+"/', '', $svg);
        }

        return str_replace('<svg ', '<svg class="'.e($class).' shrink-0" aria-hidden="true" focusable="false" fill="currentColor" ', self::$cache[$file]);
    }
}
