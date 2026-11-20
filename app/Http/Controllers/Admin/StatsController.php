<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Building;
use App\Models\Quest;
use App\Models\Room;
use App\Support\Categories;
use Illuminate\Http\Request;

class StatsController extends Controller
{
    public const PERIODS = [7 => '7 hari terakhir', 30 => '30 hari terakhir', 90 => '90 hari terakhir'];

    public function __invoke(Request $request)
    {
        $days = array_key_exists($request->integer('periode'), self::PERIODS) ? $request->integer('periode') : 30;
        $since = now()->subDays($days)->startOfDay();

        // Ruangan dengan HP terendah sekarang.
        $critical = Room::with(['building', 'openQuests' => fn ($q) => $q->withCount('confirmations')])
            ->whereHas('openQuests')
            ->get()
            ->map(fn (Room $room) => ['room' => $room, 'hp' => $room->hp(), 'open' => $room->openQuests->count(), 'raid' => $room->openQuests->firstWhere('is_raid_boss', true)])
            ->sortBy('hp')
            ->take(5)
            ->values();

        // Median waktu dari laporan sampai selesai, per gedung.
        $resolved = Quest::where('status', Quest::RESOLVED)->where('resolved_at', '>=', $since)->with('room')->get();
        $durations = Building::orderBy('name')->get()->map(function (Building $b) use ($resolved) {
            $seconds = $resolved->filter(fn (Quest $q) => $q->room->building_id === $b->id)
                ->map(fn (Quest $q) => $q->ageSeconds())
                ->sort()->values();

            return ['building' => $b, 'count' => $seconds->count(), 'median' => $seconds->isEmpty() ? null : (int) $seconds->median()];
        })->filter(fn ($row) => $row['count'] > 0)->sortByDesc('median')->values();

        // Kategori yang paling sering dilaporkan.
        $categoryCounts = Quest::where('created_at', '>=', $since)
            ->where('status', '!=', Quest::REJECTED)
            ->selectRaw('category, COUNT(*) as total')
            ->groupBy('category')
            ->pluck('total', 'category');
        $categories = collect(Categories::ALL)->map(fn ($c, $key) => ['label' => $c['label'], 'icon' => $c['icon'], 'total' => (int) ($categoryCounts[$key] ?? 0)])
            ->sortByDesc('total')->values();

        // Raid Boss per minggu, 8 minggu terakhir.
        $raidDates = Quest::whereNotNull('raid_boss_at')->where('raid_boss_at', '>=', now()->startOfWeek()->subWeeks(7))->pluck('raid_boss_at');
        $weeks = collect(range(7, 0))->map(function ($ago) use ($raidDates) {
            $start = now()->startOfWeek()->subWeeks($ago);
            $end = $start->copy()->endOfWeek();

            return ['label' => $start->translatedFormat('j M'), 'total' => $raidDates->filter(fn ($d) => $d->between($start, $end))->count()];
        });

        return view('admin.stats', [
            'days' => $days,
            'periods' => self::PERIODS,
            'critical' => $critical,
            'durations' => $durations,
            'categories' => $categories,
            'weeks' => $weeks,
            'hasData' => $resolved->isNotEmpty() || $categoryCounts->isNotEmpty() || $critical->isNotEmpty(),
        ]);
    }
}
