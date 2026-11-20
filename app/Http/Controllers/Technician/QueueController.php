<?php

namespace App\Http\Controllers\Technician;

use App\Http\Controllers\Controller;
use App\Models\Building;
use App\Models\Quest;
use App\Services\QuestService;
use App\Support\Categories;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use InvalidArgumentException;

class QueueController extends Controller
{
    public function __construct(private QuestService $quests) {}

    public function index(Request $request)
    {
        $tab = in_array($request->query('tab'), ['dikerjakan', 'selesai'], true) ? $request->query('tab') : 'antrean';
        $building = $request->integer('gedung') ?: null;
        $category = in_array($request->query('kategori'), Categories::keys(), true) ? $request->query('kategori') : null;

        $base = fn () => Quest::query()
            ->when($building, fn ($q) => $q->whereHas('room', fn ($r) => $r->where('building_id', $building)))
            ->when($category, fn ($q) => $q->where('category', $category));

        $counts = [
            'antrean' => $base()->where('status', Quest::ACTIVE)->count(),
            'dikerjakan' => $base()->where('status', Quest::IN_PROGRESS)->count(),
            'selesai' => $base()->where('status', Quest::RESOLVED)->where('resolved_at', '>=', today())->count(),
        ];

        $query = match ($tab) {
            'dikerjakan' => $base()->where('status', Quest::IN_PROGRESS)->orderByDesc('is_raid_boss')->oldest('started_at'),
            'selesai' => $base()->where('status', Quest::RESOLVED)->where('resolved_at', '>=', today())->latest('resolved_at'),
            default => $base()->where('status', Quest::ACTIVE)->orderByDesc('confirmations_count')->oldest(),
        };

        $rows = $query->with(['room.building', 'room.openQuests', 'reporter'])->withCount('confirmations')->get();

        // Raid Boss di tab antrean tampil sebagai panel di atas tabel.
        $raids = $tab === 'antrean' ? $rows->where('is_raid_boss', true)->values() : collect();
        $rows = $tab === 'antrean' ? $rows->where('is_raid_boss', false)->values() : $rows;

        $selected = $rows->firstWhere('number', $request->query('pilih')) ?? $rows->first();

        return view('technician.queue', [
            'tab' => $tab,
            'counts' => $counts,
            'raids' => $raids,
            'rows' => $rows,
            'selected' => $selected,
            'buildings' => Building::orderBy('name')->get(),
            'building' => $building,
            'category' => $category,
            'categories' => Categories::ALL,
            'filters' => array_filter(['gedung' => $building, 'kategori' => $category]),
        ]);
    }

    public function ticket(Quest $quest)
    {
        $quest->load('room.building', 'room.openQuests', 'reporter', 'technician')->loadCount('confirmations');

        return view('technician.ticket', ['quest' => $quest]);
    }

    public function start(Request $request, Quest $quest)
    {
        try {
            $this->quests->start($request->user(), $quest);
        } catch (InvalidArgumentException $e) {
            return back()->with('error', $e->getMessage());
        }

        return redirect()->route('technician.ticket', $quest)->with('status', "Quest {$quest->number} sedang kamu kerjakan.");
    }

    public function resolve(Request $request, Quest $quest)
    {
        $data = $request->validate([
            'photo_after' => ['required', 'image', 'mimes:jpg,jpeg,png,webp', 'max:'.config('kampusquest.photo_max_kb')],
            'technician_note' => ['nullable', 'string', 'max:500'],
        ], [
            'photo_after.required' => 'Ambil foto bukti perbaikan dulu untuk menyelesaikan quest.',
            'photo_after.image' => 'Foto belum terunggah. Periksa sinyal lalu coba lagi.',
            'photo_after.max' => 'Foto terlalu besar. Ambil ulang dengan kamera.',
        ]);

        try {
            $recipients = $this->quests->resolve($request->user(), $quest, $request->file('photo_after'), $data['technician_note'] ?? null);
        } catch (InvalidArgumentException $e) {
            return back()->with('error', $e->getMessage());
        }

        return redirect()->route('technician.ticket', $quest)->with('resolved', $recipients);
    }

    public function flag(Request $request, Quest $quest)
    {
        $data = $request->validate([
            'reason' => ['required', Rule::in(['Ditandai teknisi: kerusakan tidak ditemukan', 'Ditandai teknisi: foto tidak sesuai ruangan'])],
            'technician_note' => ['nullable', 'string', 'max:500'],
        ]);

        $this->quests->flag($quest, $data['reason'], $data['technician_note'] ?? null);

        return redirect()->route('technician.queue')->with('status', "Quest {$quest->number} dikirim ke admin untuk ditinjau.");
    }
}
