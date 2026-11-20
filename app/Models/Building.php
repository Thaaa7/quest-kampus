<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['code', 'name'])]
class Building extends Model
{
    public function rooms(): HasMany
    {
        return $this->hasMany(Room::class);
    }
}
