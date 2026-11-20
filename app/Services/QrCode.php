<?php

namespace App\Services;

use BaconQrCode\Renderer\ImageRenderer;
use BaconQrCode\Renderer\Image\SvgImageBackEnd;
use BaconQrCode\Renderer\RendererStyle\RendererStyle;
use BaconQrCode\Writer;

class QrCode
{
    public static function svg(string $text, int $size = 240): string
    {
        $writer = new Writer(new ImageRenderer(new RendererStyle($size, 0), new SvgImageBackEnd));
        $svg = $writer->writeString($text);

        // Buang deklarasi XML agar bisa disisipkan langsung ke HTML.
        return preg_replace('/^<\?xml[^>]*>\s*/', '', $svg);
    }

    public static function dataUri(string $text, int $size = 240): string
    {
        return 'data:image/svg+xml;base64,'.base64_encode(self::svg($text, $size));
    }
}
