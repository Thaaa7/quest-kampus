<?php

namespace App\Services;

use App\Models\Quest;
use App\Models\User;
use App\Models\XpTransaction;
use Illuminate\Support\Facades\DB;

class XpService
{
    /**
     * Catat XP di buku besar dan perbarui total di tabel users.
     * Jenis yang sama untuk quest yang sama tidak pernah tercatat dua kali.
     */
    public function award(User $user, int $amount, string $type, string $description, ?Quest $quest = null): ?XpTransaction
    {
        return DB::transaction(function () use ($user, $amount, $type, $description, $quest) {
            $exists = XpTransaction::where('user_id', $user->id)
                ->where('type', $type)
                ->where('quest_id', $quest?->id)
                ->lockForUpdate()
                ->exists();

            if ($exists) {
                return null;
            }

            $transaction = XpTransaction::create([
                'user_id' => $user->id,
                'quest_id' => $quest?->id,
                'type' => $type,
                'amount' => $amount,
                'description' => $description,
            ]);

            $user->increment('xp', $amount);

            return $transaction;
        });
    }
}
