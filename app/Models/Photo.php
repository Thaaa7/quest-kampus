<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Photo extends Model
{
    protected $fillable = ['path', 'mime', 'size', 'data'];

    protected $hidden = ['data'];
}
