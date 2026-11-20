@php
    $threshold = config('kampusquest.raid_boss.threshold');
    $open = $room->openQuests;
@endphp
<x-layouts.mobile :title="$room->name" heading="Profil Ruangan" :back="route('scan')" back-label="Kembali ke pemindai" nav="pindai">
    <section class="flex flex-col gap-xs">
        <span class="font-code text-code-sm text-on-surface-variant">{{ $room->code }}</span>
        <h2 class="font-headline text-headline">{{ $room->name }}</h2>
        <p class="font-label text-label text-on-surface-variant">{{ $room->location() }}</p>
    </section>

    <section class="card flex flex-col gap-sm p-lg" aria-labelledby="hp-title">
        <h3 id="hp-title" class="font-label text-label">Kesehatan ruangan</h3>
        <x-hp-meter :hp="$hp" />
        <p class="meta">
            @if ($open->isEmpty())
                Tidak ada kerusakan aktif.
            @else
                Turun {{ 100 - $hp }} HP karena {{ $open->count() }} kerusakan aktif{{ $open->where('is_raid_boss', true)->isNotEmpty() ? ', termasuk Raid Boss' : '' }}.
            @endif
        </p>
    </section>

    <section class="flex flex-col gap-md" aria-labelledby="active-title">
        <h3 id="active-title" class="section-title">Kerusakan aktif ({{ $open->count() }})</h3>
        @forelse ($open as $quest)
            <article @class(['card flex flex-col gap-md p-lg', 'border-[1.5px] border-accent' => $quest->is_raid_boss && $quest->status === 'active'])>
                <header class="flex items-center justify-between gap-sm">
                    <span class="font-code text-code-sm text-on-surface-variant">No. {{ $quest->number }}</span>
                    <x-stamp :status="$quest->stamp()" />
                </header>
                <div class="flex items-start justify-between gap-md">
                    <div class="flex min-w-0 flex-1 flex-col gap-xs">
                        <span class="flex items-center gap-xs meta"><x-icon :name="$quest->categoryIcon()" class="h-4 w-4" />{{ $quest->categoryLabel() }}</span>
                        <h4 class="font-title text-title">{{ $quest->description }}</h4>
                        <p class="meta">Dilaporkan {{ $quest->created_at->diffForHumans() }}</p>
                    </div>
                    <img src="{{ route('photo', [$quest, 'before']) }}" alt="Foto kerusakan {{ $quest->categoryShort() }} di {{ $room->name }}" width="48" height="48" loading="lazy" class="h-3xl w-3xl shrink-0 rounded-md border border-outline-variant object-cover">
                </div>

                @if ($quest->is_raid_boss)
                    <div class="flex items-end justify-between gap-md">
                        <p class="font-label text-label text-accent">{{ $quest->confirmations_count }} konfirmasi</p>
                        <div class="flex flex-col items-end">
                            <x-stopwatch :since="$quest->raid_boss_at" class="text-title text-accent" />
                            <span class="meta">durasi kerusakan</span>
                        </div>
                    </div>
                @else
                    @php($recent = $quest->recentConfirmations())
                    <div class="flex flex-col gap-xs">
                        <div class="flex items-center justify-between gap-sm">
                            <span class="meta">{{ $quest->confirmations_count }} mahasiswa sudah konfirmasi</span>
                            <span class="font-caption text-caption text-primary">{{ $recent }}/{{ $threshold }} menuju Raid Boss</span>
                        </div>
                        <x-segments :value="$recent" :max="$threshold" :label="$recent.' dari '.$threshold.' konfirmasi dalam satu jam menuju Raid Boss'" />
                    </div>
                @endif

                <footer>
                    @if ($quest->reporter_id === $user->id)
                        <a href="{{ route('quest.show', $quest) }}" class="btn-secondary w-full">Lihat Quest Kamu</a>
                    @elseif (in_array($quest->id, $confirmedIds))
                        <a href="{{ route('quest.show', $quest) }}" class="btn-secondary w-full"><x-icon name="check" class="h-5 w-5" /> Sudah Kamu Konfirmasi</a>
                    @elseif ($quest->is_raid_boss)
                        <a href="{{ route('quest.join', $quest) }}" class="btn-secondary w-full">Lihat Detail Quest</a>
                    @else
                        <a href="{{ route('quest.join', $quest) }}" class="btn-secondary w-full">Konfirmasi Masalah Ini (+1 Party)</a>
                    @endif
                </footer>
            </article>
        @empty
            <p class="notice-info">Belum ada kerusakan aktif di ruangan ini. Kalau kamu menemukan masalah, laporkan di sini.</p>
        @endforelse
    </section>

    <section class="flex flex-col gap-sm" aria-labelledby="history-title">
        <h3 id="history-title" class="section-title">Riwayat perbaikan</h3>
        @if ($history->isEmpty())
            <p class="meta">Belum ada perbaikan yang tercatat di ruangan ini.</p>
        @else
            <ul class="divide-y divide-outline-variant border-y border-outline-variant">
                @foreach ($history as $done)
                    <li class="flex items-center justify-between gap-md py-md">
                        <span class="flex min-w-0 items-center gap-sm">
                            <x-icon :name="$done->categoryIcon()" class="h-5 w-5 text-on-surface-variant" />
                            <span class="truncate font-label text-label">{{ $done->categoryShort() }}, selesai {{ \App\Support\Format::date($done->resolved_at) }}</span>
                        </span>
                        <span class="meta shrink-0">ditangani {{ \App\Support\Format::duration($done->ageSeconds()) }}</span>
                    </li>
                @endforeach
            </ul>
        @endif
    </section>

    <x-slot:sticky>
        <a href="{{ route('quest.create', $room->qr_token) }}" class="btn-primary w-full">Laporkan Kerusakan</a>
    </x-slot:sticky>
</x-layouts.mobile>
