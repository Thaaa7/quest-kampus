<?php

namespace App\Models;

use App\Support\Categories;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['room_id', 'reporter_id', 'category', 'description', 'photo_before'])]
class Quest extends Model
{
    public const ACTIVE = 'active';

    public const IN_PROGRESS = 'in_progress';

    public const RESOLVED = 'resolved';

    public const REJECTED = 'rejected';

    protected function casts(): array
    {
        return [
            'is_raid_boss' => 'boolean',
            'raid_boss_at' => 'datetime',
            'started_at' => 'datetime',
            'resolved_at' => 'datetime',
            'flagged_at' => 'datetime',
            'moderated_at' => 'datetime',
        ];
    }

    public function getRouteKeyName(): string
    {
        return 'number';
    }

    public function room(): BelongsTo
    {
        return $this->belongsTo(Room::class);
    }

    public function reporter(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reporter_id');
    }

    public function technician(): BelongsTo
    {
        return $this->belongsTo(User::class, 'technician_id');
    }

    public function confirmations(): HasMany
    {
        return $this->hasMany(QuestConfirmation::class);
    }

    /** Quest yang belum selesai dan belum ditolak. */
    public function scopeOpen(Builder $query): Builder
    {
        return $query->whereIn('status', [self::ACTIVE, self::IN_PROGRESS]);
    }

    public function isOpen(): bool
    {
        return in_array($this->status, [self::ACTIVE, self::IN_PROGRESS], true);
    }

    /**
     * Kunci cap status yang dipakai komponen <x-stamp>.
     * Raid Boss yang sudah dikerjakan tampil sebagai DIKERJAKAN.
     */
    public function stamp(): string
    {
        return match (true) {
            $this->status === self::REJECTED => 'rejected',
            $this->status === self::RESOLVED => 'resolved',
            $this->status === self::IN_PROGRESS => 'in_progress',
            $this->is_raid_boss => 'raid',
            default => 'active',
        };
    }

    public function categoryLabel(): string
    {
        return Categories::label($this->category);
    }

    public function categoryShort(): string
    {
        return Categories::short($this->category);
    }

    public function categoryIcon(): string
    {
        return Categories::icon($this->category);
    }

    public function partyCount(): int
    {
        return $this->confirmations_count ?? $this->confirmations()->count();
    }

    /** Jumlah konfirmasi dalam jendela waktu Raid Boss yang sedang berjalan. */
    public function recentConfirmations(): int
    {
        $window = config('kampusquest.raid_boss.window_minutes');

        return $this->confirmations()->where('created_at', '>=', now()->subMinutes($window))->count();
    }

    /** Durasi kerusakan dalam detik: dari naik Raid Boss sampai selesai (atau sekarang). */
    public function raidSeconds(): int
    {
        if (! $this->raid_boss_at) {
            return 0;
        }

        return (int) $this->raid_boss_at->diffInSeconds($this->resolved_at ?? now(), true);
    }

    /** Lama dari laporan dibuat sampai selesai (atau sekarang), dalam detik. */
    public function ageSeconds(): int
    {
        return (int) $this->created_at->diffInSeconds($this->resolved_at ?? now(), true);
    }

    public function isConfirmedBy(?User $user): bool
    {
        if (! $user) {
            return false;
        }

        return $this->confirmations()->where('user_id', $user->id)->exists();
    }

    public function roleOf(User $user): ?string
    {
        if ($this->reporter_id === $user->id) {
            return 'First Discoverer';
        }

        return $this->isConfirmedBy($user) ? 'Party' : null;
    }
}
