<?php

namespace App\Models;

use App\Support\Format;
use App\Support\Tiers;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;

#[Fillable(['name', 'identifier', 'role', 'faculty', 'password'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory;

    public const MAHASISWA = 'mahasiswa';

    public const TEKNISI = 'teknisi';

    public const ADMIN = 'admin';

    protected $attributes = ['xp' => 0];

    protected function casts(): array
    {
        return [
            'password' => 'hashed',
            'xp' => 'integer',
        ];
    }

    public function reportedQuests(): HasMany
    {
        return $this->hasMany(Quest::class, 'reporter_id');
    }

    public function confirmations(): HasMany
    {
        return $this->hasMany(QuestConfirmation::class);
    }

    public function xpTransactions(): HasMany
    {
        return $this->hasMany(XpTransaction::class);
    }

    public function certificates(): HasMany
    {
        return $this->hasMany(Certificate::class);
    }

    public function hasRole(string $role): bool
    {
        return $this->role === $role;
    }

    public function homeRoute(): string
    {
        return match ($this->role) {
            self::TEKNISI => route('technician.queue'),
            self::ADMIN => route('admin.stats'),
            default => route('scan'),
        };
    }

    public function roleLabel(): string
    {
        return match ($this->role) {
            self::TEKNISI => 'Teknisi Sarpras',
            self::ADMIN => 'Admin Kampus',
            default => 'Mahasiswa',
        };
    }

    public function initials(): string
    {
        return Format::initials($this->name);
    }

    public function tier(): ?array
    {
        return Tiers::current($this->xp);
    }

    public function nextTier(): ?array
    {
        return Tiers::next($this->xp);
    }

    /** Quest yang dilaporkan sendiri dan sudah diselesaikan teknisi. */
    public function validReportsCount(): int
    {
        return $this->reportedQuests()->where('status', Quest::RESOLVED)->count();
    }

    public function pendingXp(): int
    {
        $xp = config('kampusquest.xp');
        $asReporter = $this->reportedQuests()->open()->count() * $xp['discoverer_bonus'];
        $asParty = $this->confirmations()->whereHas('quest', fn ($q) => $q->open())->count() * $xp['party'];

        return $asReporter + $asParty;
    }
}
