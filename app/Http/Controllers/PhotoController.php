<?php

namespace App\Http\Controllers;

use App\Models\Quest;
use Illuminate\Support\Facades\Storage;

class PhotoController extends Controller
{
    public function __invoke(Quest $quest, string $which)
    {
        $path = $which === 'after' ? $quest->photo_after : $quest->photo_before;

        abort_if(! $path || ! Storage::disk('local')->exists($path), 404);

        return Storage::disk('local')->response($path, null, [
            'Cache-Control' => 'private, max-age=86400',
        ]);
    }
}
