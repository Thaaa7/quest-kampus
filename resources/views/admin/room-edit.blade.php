<x-layouts.admin :title="'Ubah '.$room->code" active="rooms">
    <a href="{{ route('admin.rooms', ['gedung' => $room->building_id, 'ruang' => $room->id]) }}" class="btn-ghost btn-sm self-start px-sm"><x-icon name="arrow_back" class="h-5 w-5" /> Kembali ke daftar ruangan</a>
    <h1 class="font-headline text-headline">Ubah ruangan {{ $room->code }}</h1>
    <form method="POST" action="{{ route('admin.rooms.update', $room) }}" class="card flex max-w-[560px] flex-col gap-lg p-xl">
        @csrf
        @method('PUT')
        @include('admin.partials.room-fields', ['bag' => $errors, 'room' => $room, 'selectedBuilding' => $room->building_id])
        <label class="flex items-start gap-sm rounded-lg border border-outline-variant p-md font-label text-label">
            <input type="checkbox" name="reset_token" value="1" class="mt-[2px] h-5 w-5 rounded-sm border-outline text-accent focus:ring-accent">
            <span class="flex flex-col gap-xs">
                Ganti token QR
                <span class="meta">Stiker lama langsung tidak berlaku. Pakai bila stiker hilang, rusak, atau fotonya tersebar di luar ruangan.</span>
            </span>
        </label>
        <div class="flex justify-end gap-sm">
            <a href="{{ route('admin.rooms', ['gedung' => $room->building_id]) }}" class="btn-secondary btn-sm">Batal</a>
            <button type="submit" class="btn-primary btn-sm">Simpan Perubahan</button>
        </div>
    </form>
</x-layouts.admin>
