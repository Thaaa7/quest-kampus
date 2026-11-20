<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Building;
use App\Models\Room;
use App\Services\QrCode;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class RoomController extends Controller
{
    public function index(Request $request)
    {
        $buildings = Building::withCount('rooms')->orderBy('name')->get();
        $building = $buildings->firstWhere('id', $request->integer('gedung')) ?? $buildings->first();

        $rooms = $building
            ? $building->rooms()->with(['openQuests', 'building'])->orderBy('floor')->orderBy('code')->get()
            : collect();

        $preview = $rooms->firstWhere('id', $request->integer('ruang')) ?? $rooms->first();

        return view('admin.rooms', [
            'buildings' => $buildings,
            'building' => $building,
            'rooms' => $rooms,
            'preview' => $preview,
            'previewQr' => $preview ? QrCode::svg($preview->scanUrl(), 200) : null,
        ]);
    }

    public function store(Request $request)
    {
        $request->merge(['code' => Str::upper(trim((string) $request->input('code')))]);
        $data = $request->validateWithBag('room', [
            'building_id' => ['required', 'exists:buildings,id'],
            'code' => ['required', 'string', 'max:20', 'unique:rooms,code'],
            'name' => ['required', 'string', 'max:100'],
            'floor' => ['required', 'integer', 'min:0', 'max:50'],
        ], ['code.unique' => 'Kode :input sudah dipakai ruangan lain.'], ['building_id' => 'gedung', 'code' => 'kode ruangan', 'name' => 'nama ruangan', 'floor' => 'lantai']);

        $room = Room::create($data);

        return redirect()->route('admin.rooms', ['gedung' => $room->building_id, 'ruang' => $room->id])
            ->with('status', "Ruangan {$room->code} ditambahkan. Cetak stiker QR-nya sebelum dipasang.");
    }

    public function storeBuilding(Request $request)
    {
        $request->merge(['code' => Str::upper(trim((string) $request->input('code')))]);
        $data = $request->validateWithBag('building', [
            'code' => ['required', 'string', 'max:10', 'unique:buildings,code'],
            'name' => ['required', 'string', 'max:100'],
        ], ['code.unique' => 'Kode gedung :input sudah dipakai.'], ['code' => 'kode gedung', 'name' => 'nama gedung']);

        $building = Building::create($data);

        return redirect()->route('admin.rooms', ['gedung' => $building->id])->with('status', "{$building->name} ditambahkan.");
    }

    public function edit(Room $room)
    {
        return view('admin.room-edit', ['room' => $room, 'buildings' => Building::orderBy('name')->get()]);
    }

    public function update(Request $request, Room $room)
    {
        $request->merge(['code' => Str::upper(trim((string) $request->input('code')))]);
        $data = $request->validate([
            'building_id' => ['required', 'exists:buildings,id'],
            'code' => ['required', 'string', 'max:20', Rule::unique('rooms', 'code')->ignore($room->id)],
            'name' => ['required', 'string', 'max:100'],
            'floor' => ['required', 'integer', 'min:0', 'max:50'],
        ], ['code.unique' => 'Kode :input sudah dipakai ruangan lain.'], ['building_id' => 'gedung', 'code' => 'kode ruangan', 'name' => 'nama ruangan', 'floor' => 'lantai']);

        $room->fill($data);
        $message = "Ruangan {$room->code} diperbarui.";
        if ($request->boolean('reset_token')) {
            $room->qr_token = Room::newToken();
            $room->qr_printed_at = null;
            $message .= ' Stiker QR lama tidak berlaku lagi, cetak stiker baru.';
        }
        $room->save();

        return redirect()->route('admin.rooms', ['gedung' => $room->building_id, 'ruang' => $room->id])->with('status', $message);
    }

    public function print(Request $request)
    {
        $request->validate(['rooms' => ['required', 'array', 'min:1'], 'rooms.*' => ['integer', 'exists:rooms,id']], ['rooms.required' => 'Pilih minimal satu ruangan untuk dicetak.']);

        $rooms = Room::with('building')->whereIn('id', $request->input('rooms'))->orderBy('code')->get();
        Room::whereIn('id', $rooms->pluck('id'))->update(['qr_printed_at' => now()]);

        return view('admin.print', [
            'rooms' => $rooms,
            'qrs' => $rooms->mapWithKeys(fn (Room $r) => [$r->id => QrCode::svg($r->scanUrl(), 220)]),
            'back' => route('admin.rooms', ['gedung' => $rooms->first()->building_id]),
        ]);
    }
}
