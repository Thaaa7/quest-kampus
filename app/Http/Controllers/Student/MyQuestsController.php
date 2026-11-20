<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\Quest;
use Illuminate\Http\Request;

class MyQuestsController extends Controller
{
    private const TABS = [
        'aktif' => [Quest::ACTIVE, Quest::IN_PROGRESS],
        'selesai' => [Quest::RESOLVED],
        'ditolak' => [Quest::REJECTED],
    ];

    public function __invoke(Request $request)
    {
        $user = $request->user();
        $tab = array_key_exists($request->query('tab'), self::TABS) ? $request->query('tab') : 'aktif';

        $mine = fn () => Quest::query()->where(function ($q) use ($user) {
            $q->where('reporter_id', $user->id)
                ->orWhereHas('confirmations', fn ($c) => $c->where('user_id', $user->id));
        });

        $counts = collect(self::TABS)->map(fn ($statuses) => $mine()->whereIn('status', $statuses)->count());

        $quests = $mine()
            ->whereIn('status', self::TABS[$tab])
            ->with('room')
            ->orderByDesc('is_raid_boss')
            ->latest()
            ->get();

        $earned = $user->xpTransactions()
            ->whereIn('quest_id', $quests->pluck('id'))
            ->get()
            ->groupBy('quest_id')
            ->map->sum('amount');

        return view('student.my-quests', [
            'tab' => $tab,
            'counts' => $counts,
            'quests' => $quests,
            'earned' => $earned,
            'user' => $user,
            'pendingXp' => $user->pendingXp(),
        ]);
    }
}
