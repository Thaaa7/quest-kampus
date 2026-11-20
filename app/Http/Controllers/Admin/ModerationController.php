<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Quest;
use App\Services\QuestService;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use InvalidArgumentException;

class ModerationController extends Controller
{
    public const REASONS = [
        'Foto tidak sesuai ruangan',
        'Kerusakan tidak ditemukan teknisi',
        'Laporan sengaja diduplikasi',
        'Konten tidak relevan',
    ];

    public function __construct(private QuestService $quests) {}

    public function index(Request $request)
    {
        $queue = Quest::open()
            ->whereNotNull('flagged_at')
            ->with('room.building', 'reporter')
            ->oldest('flagged_at')
            ->get();

        $selected = $queue->firstWhere('number', $request->query('pilih')) ?? $queue->first();
        $history = null;
        if ($selected) {
            $reporter = $selected->reporter;
            $history = [
                'valid' => $reporter->reportedQuests()->where('status', Quest::RESOLVED)->count(),
                'rejected' => $reporter->reportedQuests()->where('status', Quest::REJECTED)->count(),
            ];
        }

        return view('admin.moderation', [
            'queue' => $queue,
            'selected' => $selected,
            'history' => $history,
            'reasons' => self::REASONS,
        ]);
    }

    public function approve(Request $request, Quest $quest)
    {
        $this->quests->approve($request->user(), $quest);

        return redirect()->route('admin.moderation')->with('status', "Laporan {$quest->number} diloloskan dan tetap ada di antrean teknisi.");
    }

    public function reject(Request $request, Quest $quest)
    {
        $data = $request->validateWithBag('reject', [
            'reason' => ['required', Rule::in(self::REASONS)],
            'note' => ['nullable', 'string', 'max:500'],
        ], ['reason.required' => 'Pilih alasan penolakan.']);

        try {
            $this->quests->reject($request->user(), $quest, $data['reason'], $data['note'] ?? null);
        } catch (InvalidArgumentException $e) {
            return redirect()->route('admin.moderation')->with('error', $e->getMessage());
        }

        return redirect()->route('admin.moderation')->with('status', "Laporan {$quest->number} ditolak. ".abs(config('kampusquest.xp.penalty'))." XP dikurangi dari pelapor.");
    }
}
