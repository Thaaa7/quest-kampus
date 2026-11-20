<?php

namespace App\Models;

use App\Support\Tiers;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['user_id', 'tier', 'serial', 'xp_at_issue', 'valid_reports', 'issued_at'])]
class Certificate extends Model
{
    protected function casts(): array
    {
        return [
            'issued_at' => 'datetime',
            'xp_at_issue' => 'integer',
            'valid_reports' => 'integer',
        ];
    }

    public function getRouteKeyName(): string
    {
        return 'serial';
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function info(): array
    {
        return Tiers::certificate($this->tier);
    }

    public function verifyUrl(): string
    {
        return route('verify.show', $this->serial);
    }
}
