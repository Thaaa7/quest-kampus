<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\Quest;
use App\Models\Room;
use Illuminate\Http\Request;

class RoomController extends Controller
{
    public function show(Request $request, string $token)
    {
        $room = Room::where('qr_token', $token)->with('building')->firstOrFail();

        // Tanda bahwa mahasiswa benar-benar memindai stiker di ruangan ini.
        $request->session()->put("scans.{$room->id}", now()->timestamp);

        $room->load(['openQuests' => fn ($q) => $q->withCount('confirmations')->with('reporter')->orderByDesc('is_raid_boss')->oldest()]);

        $history = $room->quests()
            ->where('status', Quest::RESOLVED)
            ->latest('resolved_at')
            ->take(3)
            ->get();

        $user = $request->user();
        $confirmedIds = $user->confirmations()->whereIn('quest_id', $room->openQuests->pluck('id'))->pluck('quest_id')->all();

        return view('student.room', [
            'room' => $room,
            'hp' => $room->hp(),
            'history' => $history,
            'confirmedIds' => $confirmedIds,
            'user' => $user,
        ]);
    }
}
