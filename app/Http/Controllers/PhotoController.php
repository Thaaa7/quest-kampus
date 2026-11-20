<?php

namespace App\Http\Controllers;

use App\Models\Quest;
use App\Support\PhotoStore;

class PhotoController extends Controller
{
    public function __invoke(Quest $quest, string $which)
    {
        $path = $which === 'after' ? $quest->photo_after : $quest->photo_before;

        abort_if(! $path, 404);

        return PhotoStore::response($path) ?? abort(404);
    }
}
