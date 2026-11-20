<?php

namespace App\Services;

use App\Models\Quest;
use App\Models\QuestConfirmation;
use App\Models\Room;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class QuestService
{
    public function __construct(private XpService $xp) {}

    /** Quest terbuka dengan kategori yang sama di ruangan yang sama. */
    public function findDuplicate(Room $room, string $category): ?Quest
    {
        return $room->quests()->open()->where('category', $category)->oldest()->first();
    }

    public function create(User $reporter, Room $room, string $category, string $description, UploadedFile $photo): Quest
    {
        $path = $photo->store('quests', 'local');

        return DB::transaction(function () use ($reporter, $room, $category, $description, $path) {
            $quest = Quest::create([
                'room_id' => $room->id,
                'reporter_id' => $reporter->id,
                'category' => $category,
                'description' => $description,
                'photo_before' => $path,
            ]);
            $quest->number = sprintf('KQ-%s-%04d', $quest->created_at->format('ym'), $quest->id);
            $quest->save();

            $this->xp->award($reporter, config('kampusquest.xp.create'), 'quest_created', "Quest {$quest->number} terbit", $quest);
            $this->autoFlag($reporter, $quest);

            return $quest;
        });
    }

    /** Konfirmasi (Join Party). Mengembalikan true bila quest baru saja naik jadi Raid Boss. */
    public function confirm(User $user, Quest $quest): bool
    {
        if (! $quest->isOpen()) {
            throw new InvalidArgumentException('Quest ini sudah ditutup.');
        }
        if ($quest->reporter_id === $user->id) {
            throw new InvalidArgumentException('Kamu pelapor quest ini, jadi tidak perlu mengonfirmasi.');
        }

        QuestConfirmation::firstOrCreate(['quest_id' => $quest->id, 'user_id' => $user->id]);

        return $this->checkRaidBoss($quest);
    }

    public function checkRaidBoss(Quest $quest): bool
    {
        if ($quest->is_raid_boss || ! $quest->isOpen()) {
            return false;
        }
        if ($quest->recentConfirmations() < config('kampusquest.raid_boss.threshold')) {
            return false;
        }

        $quest->forceFill(['is_raid_boss' => true, 'raid_boss_at' => now()])->save();

        return true;
    }

    public function start(User $technician, Quest $quest): void
    {
        if ($quest->status !== Quest::ACTIVE) {
            throw new InvalidArgumentException('Quest ini sudah dikerjakan atau sudah ditutup.');
        }

        $quest->forceFill([
            'status' => Quest::IN_PROGRESS,
            'technician_id' => $technician->id,
            'started_at' => now(),
        ])->save();
    }

    /** Selesaikan quest: wajib foto After, HP pulih, XP dicairkan. Mengembalikan jumlah penerima XP. */
    public function resolve(User $technician, Quest $quest, UploadedFile $photoAfter, ?string $note): int
    {
        if ($quest->status !== Quest::IN_PROGRESS) {
            throw new InvalidArgumentException('Tekan Mulai Kerjakan dulu sebelum menyelesaikan quest.');
        }

        $path = $photoAfter->store('quests', 'local');
        $xp = config('kampusquest.xp');

        return DB::transaction(function () use ($technician, $quest, $path, $note, $xp) {
            $quest->forceFill([
                'status' => Quest::RESOLVED,
                'photo_after' => $path,
                'resolved_at' => now(),
                'technician_id' => $quest->technician_id ?? $technician->id,
                'technician_note' => $note,
                'flagged_at' => null,
            ])->save();

            $this->xp->award($quest->reporter, $xp['discoverer_bonus'], 'discoverer_bonus', "Quest {$quest->number} selesai (First Discoverer)", $quest);

            $recipients = 1;
            $quest->confirmations()->with('user')->get()->each(function (QuestConfirmation $c) use ($quest, $xp, &$recipients) {
                $this->xp->award($c->user, $xp['party'], 'party', "Party {$quest->number} selesai", $quest);
                $recipients++;
            });

            return $recipients;
        });
    }

    /** Teknisi menandai laporan untuk ditinjau admin, misalnya karena kerusakan tidak ditemukan. */
    public function flag(Quest $quest, string $reason, ?string $note = null): void
    {
        $quest->forceFill([
            'flagged_reason' => $reason,
            'flagged_at' => now(),
            'technician_note' => $note ?? $quest->technician_note,
        ])->save();
    }

    public function approve(User $admin, Quest $quest): void
    {
        $quest->forceFill([
            'flagged_at' => null,
            'moderated_at' => now(),
            'moderator_id' => $admin->id,
        ])->save();
    }

    public function reject(User $admin, Quest $quest, string $reason, ?string $note): void
    {
        if (! $quest->isOpen()) {
            throw new InvalidArgumentException('Quest yang sudah selesai atau ditolak tidak bisa ditolak lagi.');
        }

        DB::transaction(function () use ($admin, $quest, $reason, $note) {
            $quest->forceFill([
                'status' => Quest::REJECTED,
                'reject_reason' => $reason,
                'moderation_note' => $note,
                'moderated_at' => now(),
                'moderator_id' => $admin->id,
                'flagged_at' => null,
            ])->save();

            $this->xp->award($quest->reporter, config('kampusquest.xp.penalty'), 'rejected_penalty', "Laporan {$quest->number} ditolak admin", $quest);
        });
    }

    private function autoFlag(User $reporter, Quest $quest): void
    {
        $rule = config('kampusquest.auto_flag');
        $recent = $reporter->reportedQuests()
            ->where('created_at', '>=', now()->subMinutes($rule['window_minutes']))
            ->count();

        if ($recent >= $rule['max_reports']) {
            $this->flag($quest, "{$recent} laporan dari akun yang sama dalam {$rule['window_minutes']} menit");
        }
    }
}
