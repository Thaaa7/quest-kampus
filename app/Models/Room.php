<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

#[Fillable(['building_id', 'code', 'name', 'floor'])]
class Room extends Model
{
    protected function casts(): array
    {
        return [
            'floor' => 'integer',
            'qr_printed_at' => 'datetime',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (Room $room) {
            $room->qr_token ??= self::newToken();
        });
    }

    public static function newToken(): string
    {
        return Str::lower(Str::random(24));
    }

    public function building(): BelongsTo
    {
        return $this->belongsTo(Building::class);
    }

    public function quests(): HasMany
    {
        return $this->hasMany(Quest::class);
    }

    public function openQuests(): HasMany
    {
        return $this->hasMany(Quest::class)->open();
    }

    public function scanUrl(): string
    {
        return route('room.show', $this->qr_token);
    }

    public function location(): string
    {
        return $this->building->name.', Lantai '.$this->floor;
    }

    /**
     * HP dihitung dari quest yang masih terbuka, jadi tidak pernah selisih
     * dengan data quest. Quest normal -20 HP, Raid Boss -80 HP.
     */
    public function hp(): int
    {
        $config = config('kampusquest.hp');
        $quests = $this->relationLoaded('openQuests') ? $this->openQuests : $this->openQuests()->get();
        $penalty = $quests->sum(fn (Quest $q) => $q->is_raid_boss ? $config['raid_boss'] : $config['per_quest']);

        return max(0, $config['max'] - $penalty);
    }

    public static function zoneFor(int $hp): string
    {
        $zones = config('kampusquest.hp.zones');
        if ($hp >= $zones['sehat']) {
            return 'sehat';
        }

        return $hp >= $zones['perhatian'] ? 'perhatian' : 'kritis';
    }
}
