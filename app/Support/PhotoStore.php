<?php

namespace App\Support;

use App\Models\Photo;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\Response;

/**
 * Tempat foto quest disimpan.
 *
 * Di laptop foto masuk ke disk lokal (storage/app/private). Di hosting yang
 * disknya terhapus setiap kali server tidur (Render paket gratis), set
 * KQ_PHOTO_STORE=database supaya foto ikut tersimpan di database.
 * Saat dibaca, database dicek dulu lalu disk, jadi foto lama tetap tampil
 * kalau mode penyimpanannya diganti.
 */
class PhotoStore
{
    private const HEADERS = [
        'Cache-Control' => 'private, max-age=86400',
        'X-Content-Type-Options' => 'nosniff',
        'Content-Security-Policy' => "default-src 'none'; style-src 'unsafe-inline'",
    ];

    public static function usesDatabase(): bool
    {
        return config('kampusquest.photo_store') === 'database';
    }

    public static function store(UploadedFile $file): string
    {
        if (! self::usesDatabase()) {
            return $file->store('quests', 'local');
        }

        $path = 'quests/'.$file->hashName();
        self::put($path, $file->get(), $file->getMimeType() ?: 'application/octet-stream');

        return $path;
    }

    public static function put(string $path, string $contents, string $mime): void
    {
        if (! self::usesDatabase()) {
            Storage::disk('local')->put($path, $contents);

            return;
        }

        Photo::updateOrCreate(
            ['path' => $path],
            ['mime' => $mime, 'size' => strlen($contents), 'data' => base64_encode($contents)],
        );
    }

    public static function exists(string $path): bool
    {
        return Photo::where('path', $path)->exists() || Storage::disk('local')->exists($path);
    }

    public static function response(string $path): ?Response
    {
        $photo = Photo::where('path', $path)->first();
        if ($photo) {
            return response(base64_decode($photo->data), 200, self::HEADERS + [
                'Content-Type' => $photo->mime,
                'Content-Length' => (string) $photo->size,
            ]);
        }

        if (Storage::disk('local')->exists($path)) {
            return Storage::disk('local')->response($path, null, self::HEADERS);
        }

        return null;
    }
}
