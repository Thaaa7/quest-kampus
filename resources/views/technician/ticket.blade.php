@php
    use App\Support\Format;
    $room = $quest->room;
    $resolvedNow = session('resolved');
    $people = $quest->confirmations_count + 1;
@endphp
<x-layouts.mobile :title="'Tiket '.$quest->number" :heading="'No. '.$quest->number" code-heading :back="route('technician.queue', $quest->status === 'in_progress' ? ['tab' => 'dikerjakan'] : [])" back-label="Kembali ke antrean">
    @if ($resolvedNow)
        <section class="card flex flex-col gap-md border-primary p-lg" role="status">
            <h2 class="font-headline text-headline">Quest selesai</h2>
            @if ($quest->raid_boss_at)
                <p class="font-label text-label">Ditangani dalam <span class="font-code">{{ Format::clock($quest->raidSeconds()) }}</span> sejak jadi Raid Boss.</p>
            @endif
            <div class="flex flex-col gap-xs">
                <p class="font-label text-label">HP {{ $room->name }} sekarang {{ $room->hp() }}/100.</p>
                <x-hp-meter :hp="$room->hp()" />
            </div>
            <p class="font-label text-label">XP dicairkan ke {{ $resolvedNow }} mahasiswa.</p>
            <a href="{{ route('technician.queue') }}" class="btn-primary w-full">Kembali ke Antrean</a>
        </section>
    @endif

    <section class="flex flex-wrap items-center justify-between gap-md">
        <x-stamp :status="$quest->stamp()" size="lg" />
        @if ($quest->raid_boss_at)
            <div class="flex flex-col items-end">
                <x-stopwatch :since="$quest->raid_boss_at" :until="$quest->resolved_at" class="text-[28px] font-semibold leading-[32px] {{ $quest->resolved_at ? 'text-on-surface' : 'text-accent' }}" />
                <span class="meta">{{ $quest->resolved_at ? 'durasi sampai selesai' : 'durasi kerusakan' }}</span>
            </div>
        @endif
    </section>

    <section class="flex flex-col gap-xs">
        <h2 class="font-headline text-headline">{{ $quest->description }}</h2>
        <p class="font-label text-label text-on-surface-variant">{{ $room->code }}, {{ $room->name }}, {{ $room->location() }}</p>
        <p class="flex items-center gap-xs meta"><x-icon :name="$quest->categoryIcon()" class="h-4 w-4" />{{ $quest->categoryLabel() }}</p>
        <p class="font-label text-label">{{ $people }} mahasiswa terdampak (1 pelapor, {{ $quest->confirmations_count }} party)</p>
    </section>

    @if ($quest->photo_after)
        <section class="grid grid-cols-2 gap-sm">
            <figure class="flex flex-col gap-xs">
                <img src="{{ route('photo', [$quest, 'before']) }}" alt="Foto sebelum perbaikan" width="220" height="165" class="aspect-[4/3] w-full rounded-lg border border-outline-variant object-cover">
                <figcaption class="meta">Before</figcaption>
            </figure>
            <figure class="flex flex-col gap-xs">
                <img src="{{ route('photo', [$quest, 'after']) }}" alt="Foto bukti setelah perbaikan" width="220" height="165" class="aspect-[4/3] w-full rounded-lg border border-outline-variant object-cover">
                <figcaption class="meta">After</figcaption>
            </figure>
        </section>
        @if ($quest->technician_note)
            <p class="notice-info">Catatan perbaikan: {{ $quest->technician_note }}</p>
        @endif
    @else
        <figure class="flex flex-col gap-xs">
            <img src="{{ route('photo', [$quest, 'before']) }}" alt="Foto kerusakan dari pelapor" width="480" height="360" class="aspect-[4/3] w-full rounded-lg border border-outline-variant object-cover">
            <figcaption class="meta">Foto dari pelapor ({{ $quest->reporter->name }}, {{ Format::time($quest->created_at) }})</figcaption>
        </figure>
    @endif

    @if ($quest->status === 'active')
        <form method="POST" action="{{ route('technician.start', $quest) }}" data-submitting="Memulai...">
            @csrf
            <button type="submit" class="btn-primary w-full">Mulai Kerjakan</button>
        </form>
    @elseif ($quest->status === 'in_progress')
        <form id="resolve-form" method="POST" action="{{ route('technician.resolve', $quest) }}" enctype="multipart/form-data" class="flex flex-col gap-lg" data-submitting="Mengunggah...">
            @csrf
            <x-camera-field name="photo_after" label="Foto bukti perbaikan (After)" button="Ambil Foto After" />
            <div class="flex flex-col gap-xs">
                <label for="technician_note" class="field-label">Catatan perbaikan (opsional)</label>
                <textarea id="technician_note" name="technician_note" rows="2" maxlength="500" class="input" placeholder="Contoh: kapasitor AC diganti">{{ old('technician_note') }}</textarea>
            </div>
            <p class="meta" id="resolve-help">Ambil foto bukti perbaikan dulu untuk menyelesaikan quest. HP ruangan pulih dan XP dicairkan setelah kamu menekan Selesaikan Quest.</p>
            <button type="submit" class="btn-primary w-full" disabled data-enable-on-photo aria-describedby="resolve-help">Selesaikan Quest</button>
        </form>
    @endif

    @if ($quest->isOpen() && ! $quest->flagged_at)
        <details class="border-t border-outline-variant pt-lg">
            <summary class="font-label text-label text-on-surface-variant">Kerusakan tidak ditemukan di lokasi?</summary>
            <form method="POST" action="{{ route('technician.flag', $quest) }}" class="mt-md flex flex-col gap-md">
                @csrf
                <fieldset class="flex flex-col gap-sm">
                    <legend class="field-label mb-sm">Alasan</legend>
                    <label class="flex min-h-[44px] items-center gap-sm font-label text-label">
                        <input type="radio" name="reason" value="Ditandai teknisi: kerusakan tidak ditemukan" checked class="h-5 w-5 border-outline text-primary focus:ring-primary"> Kerusakan tidak ditemukan
                    </label>
                    <label class="flex min-h-[44px] items-center gap-sm font-label text-label">
                        <input type="radio" name="reason" value="Ditandai teknisi: foto tidak sesuai ruangan" class="h-5 w-5 border-outline text-primary focus:ring-primary"> Foto tidak sesuai ruangan
                    </label>
                </fieldset>
                <div class="flex flex-col gap-xs">
                    <label for="flag_note" class="field-label">Catatan untuk admin</label>
                    <textarea id="flag_note" name="technician_note" rows="2" maxlength="500" class="input" placeholder="Contoh: dicek pukul 13.20, semua kursi baik"></textarea>
                </div>
                <button type="submit" class="btn-danger-outline w-full">Kirim ke Admin untuk Ditinjau</button>
            </form>
        </details>
    @elseif ($quest->flagged_at)
        <p class="notice-info">Laporan ini sedang ditinjau admin: {{ $quest->flagged_reason }}.</p>
    @endif

    <x-slot:after>
        <x-camera-dialog />
    </x-slot:after>
</x-layouts.mobile>
