<?php

namespace App\Http\Controllers;

use App\Models\Quest;
use App\Models\Room;
use App\Models\User;
use App\Services\QuestService;
use App\Support\Categories;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use InvalidArgumentException;

class QuestController extends Controller
{
    public function __construct(private QuestService $quests) {}

    public function create(Request $request, string $token)
    {
        $room = $this->room($token);
        if (! $this->recentlyScanned($request, $room)) {
            return redirect()->route('scan')->with('status', 'Pindai ulang QR ruangan untuk membuat laporan.');
        }

        // "Masalahnya berbeda?" dari halaman Join Party: kategori ini boleh dilaporkan terpisah.
        $forced = in_array($request->query('beda'), Categories::keys(), true) ? $request->query('beda') : null;

        $duplicates = $room->openQuests()->get()
            ->reject(fn (Quest $q) => $q->category === $forced)
            ->mapWithKeys(fn (Quest $q) => [$q->category => route('quest.join', $q)]);

        return view('student.report', [
            'room' => $room,
            'categories' => Categories::ALL,
            'duplicates' => $duplicates,
            'forced' => $forced,
        ]);
    }

    public function store(Request $request, string $token)
    {
        $room = $this->room($token);
        if (! $this->recentlyScanned($request, $room)) {
            return redirect()->route('scan')->with('status', 'Waktu laporan habis. Pindai ulang QR ruangan untuk melapor.');
        }

        $data = $request->validate([
            'category' => ['required', Rule::in(Categories::keys())],
            'description' => ['required', 'string', 'max:200'],
            'photo' => ['required', 'image', 'mimes:jpg,jpeg,png,webp', 'max:'.config('kampusquest.photo_max_kb')],
        ], [
            'category.required' => 'Pilih jenis kerusakan dulu.',
            'photo.required' => 'Ambil foto kerusakan dulu.',
            'photo.image' => 'File foto tidak terbaca. Ambil ulang fotonya.',
            'photo.max' => 'Foto terlalu besar. Ambil ulang dengan kamera.',
            'description.required' => 'Tulis keterangan singkat kerusakannya.',
            'description.max' => 'Keterangan maksimal 200 karakter.',
        ]);

        $forced = $request->input('force_new') === $data['category'];
        if (! $forced && $duplicate = $this->quests->findDuplicate($room, $data['category'])) {
            return redirect()->route('quest.join', $duplicate);
        }

        $quest = $this->quests->create($request->user(), $room, $data['category'], $data['description'], $request->file('photo'));

        return redirect()->route('quest.show', $quest)->with('created', true);
    }

    public function join(Request $request, Quest $quest)
    {
        if ($quest->reporter_id === $request->user()->id) {
            return redirect()->route('quest.show', $quest);
        }

        $quest->load('room.building', 'reporter')->loadCount('confirmations');

        return view('student.join', [
            'quest' => $quest,
            'confirmed' => $quest->isConfirmedBy($request->user()),
            'recent' => $quest->recentConfirmations(),
            'party' => $quest->confirmations()->with('user')->latest()->take(8)->get()->pluck('user'),
        ]);
    }

    public function confirm(Request $request, Quest $quest)
    {
        try {
            $raid = $this->quests->confirm($request->user(), $quest);
        } catch (InvalidArgumentException $e) {
            return redirect()->route('quest.show', $quest)->with('error', $e->getMessage());
        }

        $redirect = redirect()->route('quest.join', $quest);

        return $raid
            ? $redirect->with('status', 'Konfirmasimu membuat quest ini naik jadi Raid Boss. Tiket sekarang ada di urutan teratas antrean teknisi.')
            : $redirect;
    }

    public function show(Request $request, Quest $quest)
    {
        $quest->load('room.building', 'room.openQuests', 'reporter', 'technician')->loadCount('confirmations');
        $user = $request->user();

        return view('quest.show', [
            'quest' => $quest,
            'user' => $user,
            'role' => $user->hasRole(User::MAHASISWA) ? $quest->roleOf($user) : null,
            'recent' => $quest->isOpen() ? $quest->recentConfirmations() : 0,
            'xpForUser' => $user->xpTransactions()->where('quest_id', $quest->id)->sum('amount'),
        ]);
    }

    private function room(string $token): Room
    {
        return Room::where('qr_token', $token)->with('building')->firstOrFail();
    }

    private function recentlyScanned(Request $request, Room $room): bool
    {
        $at = $request->session()->get("scans.{$room->id}");

        return $at && now()->timestamp - $at <= config('kampusquest.scan_ttl_minutes') * 60;
    }
}
