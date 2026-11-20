@php
    $threshold = config('kampusquest.raid_boss.threshold');
    $xp = config('kampusquest.xp');
    $count = $quest->confirmations_count;
@endphp
<x-layouts.mobile title="Kerusakan Sudah Dilaporkan" heading="Laporkan Kerusakan" :subtitle="$quest->room->code.', '.$quest->room->name" :back="route('room.show', $quest->room->qr_token)" back-label="Kembali ke profil ruangan">
    <section class="flex flex-col gap-sm">
        <h2 class="font-headline text-headline">{{ $quest->categoryShort() }} di ruangan ini sudah dilaporkan</h2>
        <p class="text-body text-on-surface-variant">Konfirmasi laporan yang ada agar teknisi tahu masalahnya nyata. Kamu tidak perlu membuat laporan baru.</p>
    </section>

    <article class="card overflow-hidden">
        <img src="{{ route('photo', [$quest, 'before']) }}" alt="Foto kerusakan dari pelapor" width="480" height="360" class="aspect-[4/3] w-full border-b border-outline-variant object-cover">
        <div class="flex flex-col gap-sm p-lg">
            <div class="flex items-center justify-between gap-sm">
                <span class="font-code text-code-sm text-on-surface-variant">No. {{ $quest->number }}</span>
                <x-stamp :status="$quest->stamp()" />
            </div>
            <span class="flex items-center gap-xs meta"><x-icon :name="$quest->categoryIcon()" class="h-4 w-4" />{{ $quest->categoryLabel() }}</span>
            <h3 class="font-title text-title">{{ $quest->description }}</h3>
            <p class="meta">Dilaporkan {{ \App\Support\Format::time($quest->created_at) }} oleh {{ $quest->reporter->name }} (First Discoverer)</p>
        </div>
    </article>

    <section class="flex flex-col gap-sm" aria-labelledby="party-title">
        <h3 id="party-title" class="sr-only">Party</h3>
        <div class="flex items-center gap-md">
            @if ($party->isNotEmpty())
                <div class="flex gap-[2px]">
                    @foreach ($party->take(7) as $member)
                        <x-avatar :user="$member" size="sm" />
                    @endforeach
                </div>
            @endif
            <p class="font-label text-label">{{ $count }} mahasiswa sudah konfirmasi</p>
        </div>
        @if ($quest->is_raid_boss)
            <p class="font-label text-label text-accent">Quest ini sudah jadi Raid Boss dan ada di urutan teratas antrean teknisi.</p>
        @else
            <x-segments :value="$recent" :max="$threshold" :label="$recent.' dari '.$threshold.' konfirmasi dalam satu jam menuju Raid Boss'" />
            <p class="meta">{{ $recent }}/{{ $threshold }} dalam 1 jam menuju Raid Boss</p>
        @endif
    </section>

    <section class="flex flex-col gap-sm">
        @if ($confirmed)
            <button type="button" class="btn-primary w-full" disabled>
                <x-icon name="check" class="h-5 w-5" /> Sudah Kamu Konfirmasi
            </button>
            <p class="meta text-center">Kamu masuk party ini. +{{ $xp['party'] }} XP menunggu quest selesai.</p>
            <a href="{{ route('quest.show', $quest) }}" class="btn-ghost w-full">Pantau Progres Quest</a>
        @elseif ($quest->isOpen())
            <form method="POST" action="{{ route('quest.confirm', $quest) }}" data-submitting="Mengonfirmasi...">
                @csrf
                <button type="submit" class="btn-primary w-full">Konfirmasi Masalah Ini (+1 Party)</button>
            </form>
            <p class="meta text-center">+{{ $xp['party'] }} XP cair saat quest ini selesai.</p>
        @else
            <p class="notice-info">Quest ini sudah ditutup, jadi tidak bisa dikonfirmasi lagi.</p>
        @endif
    </section>

    @if (! $confirmed && $quest->isOpen())
        <div class="flex flex-col gap-xs border-t border-outline-variant pt-lg text-center">
            <p class="meta">Masalahnya berbeda dari laporan ini?</p>
            <a href="{{ route('quest.create', ['token' => $quest->room->qr_token, 'beda' => $quest->category]) }}" class="link self-center">Buat laporan baru</a>
        </div>
    @endif
</x-layouts.mobile>
