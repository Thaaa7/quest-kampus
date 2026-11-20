@php use App\Support\Format; @endphp
<x-layouts.admin title="Gedung & Ruangan" active="rooms">
    <header class="flex flex-wrap items-center justify-between gap-md">
        <h1 class="font-headline text-headline">Gedung &amp; ruangan</h1>
        <div class="flex flex-wrap gap-sm">
            <button type="button" class="btn-secondary btn-sm" data-dialog-open="building-dialog" aria-haspopup="dialog">Tambah Gedung</button>
            <button type="button" class="btn-primary btn-sm" data-dialog-open="room-dialog" aria-haspopup="dialog" @disabled($buildings->isEmpty())>Tambah Ruangan</button>
        </div>
    </header>

    @if ($buildings->isEmpty())
        <section class="card px-lg py-3xl text-center">
            <p class="font-title text-title">Belum ada gedung.</p>
            <p class="meta mt-xs">Tambahkan gedung dulu, lalu ruangan di dalamnya.</p>
        </section>
    @else
        <div class="grid items-start gap-xl lg:grid-cols-[240px_1fr]">
            <nav class="card overflow-hidden" aria-label="Daftar gedung">
                <ul class="divide-y divide-outline-variant">
                    @foreach ($buildings as $b)
                        @php($isActive = $building && $building->id === $b->id)
                        <li>
                            <a href="{{ route('admin.rooms', ['gedung' => $b->id]) }}" @class(['flex min-h-[48px] items-center justify-between px-lg py-md font-label text-label', 'bg-primary-container font-semibold text-primary' => $isActive, 'hover:bg-background' => ! $isActive]) @if ($isActive) aria-current="page" @endif>
                                <span>{{ $b->name }}</span>
                                <span class="font-code text-code-sm">{{ $b->rooms_count }}</span>
                            </a>
                        </li>
                    @endforeach
                </ul>
            </nav>

            <div class="flex min-w-0 flex-col gap-xl">
            <form method="POST" action="{{ route('admin.rooms.print') }}" class="flex min-w-0 flex-col gap-md" target="_blank" data-require-checked>
                @csrf
                <section class="card overflow-x-auto" aria-label="Ruangan {{ $building->name }}">
                    @if ($rooms->isEmpty())
                        <p class="px-lg py-2xl text-center meta">{{ $building->name }} belum punya ruangan. Tekan Tambah Ruangan.</p>
                    @else
                        <table class="w-full min-w-[640px] border-collapse">
                            <thead>
                                <tr class="border-b border-outline-variant bg-background">
                                    <th class="th pl-lg" scope="col"><span class="sr-only">Pilih</span></th>
                                    <th class="th" scope="col">Kode</th>
                                    <th class="th" scope="col">Nama ruangan</th>
                                    <th class="th" scope="col">Lantai</th>
                                    <th class="th" scope="col">HP</th>
                                    <th class="th" scope="col">Quest aktif</th>
                                    <th class="th" scope="col">Stiker QR</th>
                                    <th class="th pr-lg text-right" scope="col">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-outline-variant">
                                @foreach ($rooms as $room)
                                    @php($isPreview = $preview && $preview->id === $room->id)
                                    <tr @class(['bg-primary-container' => $isPreview])>
                                        <td class="td pl-lg"><input type="checkbox" name="rooms[]" value="{{ $room->id }}" aria-label="Pilih {{ $room->code }}" class="h-5 w-5 rounded-sm border-outline text-primary focus:ring-primary"></td>
                                        <td class="td"><a href="{{ route('admin.rooms', ['gedung' => $building->id, 'ruang' => $room->id]) }}" class="whitespace-nowrap font-code text-code-sm underline-offset-2 hover:underline" @if ($isPreview) aria-current="true" @endif>{{ $room->code }}</a></td>
                                        <td class="td min-w-[160px] font-label text-label">{{ $room->name }}</td>
                                        <td class="td meta">{{ $room->floor }}</td>
                                        <td class="td"><x-hp-meter :hp="$room->hp()" size="sm" /></td>
                                        <td class="td font-code text-code-sm">{{ $room->openQuests->count() }}</td>
                                        <td class="td">
                                            @if ($room->qr_printed_at)
                                                <span class="meta">Dicetak {{ Format::date($room->qr_printed_at) }}</span>
                                            @else
                                                <x-stamp status="unprinted" />
                                            @endif
                                        </td>
                                        <td class="td pr-lg text-right">
                                            <a href="{{ route('admin.rooms.edit', $room) }}" class="btn-icon" aria-label="Ubah ruangan {{ $room->code }}" title="Ubah ruangan"><x-icon name="edit" class="h-5 w-5" /></a>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    @endif
                </section>
                @error('rooms')<p class="field-error" role="alert">{{ $message }}</p>@enderror
                @if ($rooms->isNotEmpty())
                    <div class="flex flex-wrap items-center justify-between gap-sm">
                        <p class="meta">Centang ruangan, lalu cetak stikernya. Halaman cetak terbuka di tab baru.</p>
                        <button type="submit" class="btn-primary btn-sm"><x-icon name="print" class="h-5 w-5" /> Cetak Stiker QR</button>
                    </div>
                @endif
            </form>
            @if ($preview)
                <aside class="card grid gap-lg p-lg md:grid-cols-[8cm_1fr] md:items-start" aria-labelledby="preview-title">
                    @include('admin.partials.sticker', ['room' => $preview, 'qr' => $previewQr])
                    <div class="flex flex-col gap-sm">
                        <h2 id="preview-title" class="section-title">Pratinjau stiker {{ $preview->code }}</h2>
                        <p class="meta">Ukuran asli 8 x 8 cm. Klik kode ruangan di tabel untuk melihat stiker ruangan lain.</p>
                        <p class="meta">Isi QR: alamat unik ruangan ini. Ganti token di halaman ubah ruangan bila stiker hilang atau difoto orang lain.</p>
                    </div>
                </aside>
            @endif
            </div>

        </div>
    @endif

    <dialog id="room-dialog" class="dialog" aria-labelledby="room-title" @if ($errors->room->any()) data-open-on-load @endif>
        <form method="POST" action="{{ route('admin.rooms.store') }}" class="flex flex-col gap-lg p-xl">
            @csrf
            <div class="flex items-start justify-between gap-md">
                <h2 id="room-title" class="section-title">Tambah ruangan</h2>
                <button type="button" class="btn-icon -mr-sm -mt-sm" data-dialog-close aria-label="Tutup"><x-icon name="close" /></button>
            </div>
            @include('admin.partials.room-fields', ['bag' => $errors->room, 'room' => null, 'selectedBuilding' => old('building_id', $building?->id)])
            <div class="flex justify-end gap-sm">
                <button type="button" class="btn-secondary btn-sm" data-dialog-close>Batal</button>
                <button type="submit" class="btn-primary btn-sm">Simpan Ruangan</button>
            </div>
        </form>
    </dialog>

    <dialog id="building-dialog" class="dialog" aria-labelledby="building-title" @if ($errors->building->any()) data-open-on-load @endif>
        <form method="POST" action="{{ route('admin.buildings.store') }}" class="flex flex-col gap-lg p-xl">
            @csrf
            <div class="flex items-start justify-between gap-md">
                <h2 id="building-title" class="section-title">Tambah gedung</h2>
                <button type="button" class="btn-icon -mr-sm -mt-sm" data-dialog-close aria-label="Tutup"><x-icon name="close" /></button>
            </div>
            <div class="flex flex-col gap-xs">
                <label for="building_code" class="field-label">Kode gedung</label>
                <input id="building_code" name="code" value="{{ $errors->building->any() ? old('code') : '' }}" maxlength="10" required placeholder="Contoh: D" @class(['input font-code uppercase', 'input-error' => $errors->building->has('code')])>
                @if ($errors->building->has('code'))<p class="field-error">{{ $errors->building->first('code') }}</p>@endif
            </div>
            <div class="flex flex-col gap-xs">
                <label for="building_name" class="field-label">Nama gedung</label>
                <input id="building_name" name="name" value="{{ $errors->building->any() ? old('name') : '' }}" maxlength="100" required placeholder="Contoh: Gedung D" @class(['input', 'input-error' => $errors->building->has('name')])>
                @if ($errors->building->has('name'))<p class="field-error">{{ $errors->building->first('name') }}</p>@endif
            </div>
            <div class="flex justify-end gap-sm">
                <button type="button" class="btn-secondary btn-sm" data-dialog-close>Batal</button>
                <button type="submit" class="btn-primary btn-sm">Simpan Gedung</button>
            </div>
        </form>
    </dialog>
</x-layouts.admin>
