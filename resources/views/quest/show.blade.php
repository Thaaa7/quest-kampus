@php
    use App\Support\Format;
    $xp = config('kampusquest.xp');
    $threshold = config('kampusquest.raid_boss.threshold');
    $room = $quest->room;
    $hp = $room->hp();
    $isStudent = $user->hasRole(\App\Models\User::MAHASISWA);
    $back = $isStudent ? route('my-quests') : $user->homeRoute();
    $count = $quest->confirmations_count;

    $steps = [
        ['done' => true, 'title' => 'Dilaporkan, '.Format::time($quest->created_at), 'meta' => 'oleh '.$quest->reporter->name],
        $quest->raid_boss_at
            ? ['done' => true, 'title' => 'Naik jadi Raid Boss, '.Format::time($quest->raid_boss_at), 'meta' => $count.' konfirmasi']
            : ['done' => $count > 0, 'title' => 'Dikonfirmasi party', 'meta' => $count.' mahasiswa'],
        ['done' => (bool) $quest->started_at, 'title' => $quest->started_at ? 'Dikerjakan teknisi Sarpras, '.Format::time($quest->started_at) : 'Dikerjakan teknisi', 'meta' => $quest->started_at ? null : 'menunggu'],
        ['done' => (bool) $quest->resolved_at, 'title' => $quest->resolved_at ? 'Selesai, '.Format::time($quest->resolved_at) : 'Selesai, foto bukti diunggah', 'meta' => $quest->resolved_at ? 'foto bukti diunggah' : 'menunggu'],
    ];
@endphp
<x-layouts.mobile :title="'Quest '.$quest->number" :heading="'No. '.$quest->number" code-heading :back="$back" :nav="$isStudent ? 'quest' : null">
    @if (session('created'))
        <section class="card flex flex-col gap-sm border-primary p-lg" role="status">
            <h2 class="font-headline text-headline">Quest terbit</h2>
            <p class="text-body">+{{ $xp['create'] }} XP masuk ke akunmu. +{{ $xp['discoverer_bonus'] }} XP menyusul saat quest selesai.</p>
            <p class="meta">HP {{ $room->name }} sekarang {{ $hp }}/100.</p>
            <a href="{{ route('room.show', $room->qr_token) }}" class="btn-secondary btn-sm self-start">Kembali ke Ruangan</a>
        </section>
    @endif

    @if ($quest->status === 'rejected')
        <section class="notice-error flex flex-col gap-xs" role="status">
            <x-stamp status="rejected" class="self-start" />
            <p>Laporan ini ditolak admin: {{ $quest->reject_reason }}.</p>
            @if ($quest->moderation_note)<p class="font-body">{{ $quest->moderation_note }}</p>@endif
        </section>
    @elseif ($quest->stamp() === 'raid')
        <section class="flex flex-col gap-sm rounded-lg border border-accent bg-accent-container p-lg">
            <x-stamp status="raid" class="self-start" />
            <p class="font-label text-label text-on-accent-container">{{ $count }} mahasiswa mengonfirmasi kerusakan ini. Tiket ada di urutan teratas antrean teknisi.</p>
        </section>
    @else
        <x-stamp :status="$quest->stamp()" size="lg" class="self-start" />
    @endif

    @if ($quest->raid_boss_at)
        <section class="flex flex-col gap-xs" aria-labelledby="stopwatch-label">
            <h2 id="stopwatch-label" class="meta">{{ $quest->resolved_at ? 'Ditangani dalam' : 'Durasi kerusakan' }}</h2>
            <x-stopwatch :since="$quest->raid_boss_at" :until="$quest->resolved_at" :class="\Illuminate\Support\Arr::toCssClasses(['text-display-stopwatch', 'text-accent' => ! $quest->resolved_at, 'text-on-surface' => (bool) $quest->resolved_at])" />
            <p class="meta">
                @if ($quest->resolved_at)
                    Dihitung sejak {{ Format::time($quest->raid_boss_at) }} sampai teknisi menyelesaikan quest.
                @else
                    Dihitung sejak {{ Format::time($quest->raid_boss_at) }}. Berhenti saat teknisi menyelesaikan quest.
                @endif
            </p>
        </section>
    @endif

    <section class="flex flex-col gap-sm">
        <span class="flex items-center gap-xs meta"><x-icon :name="$quest->categoryIcon()" class="h-4 w-4" />{{ $quest->categoryLabel() }}</span>
        <h2 class="font-headline text-headline">{{ $quest->description }}</h2>
        <p class="font-label text-label text-on-surface-variant">{{ $room->code }}, {{ $room->name }}</p>
        <x-hp-meter :hp="$hp" />
    </section>

    @if (! $quest->raid_boss_at && $quest->isOpen())
        <section class="flex flex-col gap-xs">
            <p class="meta">Dilaporkan {{ $quest->created_at->diffForHumans() }}</p>
            <x-segments :value="$recent" :max="$threshold" :label="$recent.' dari '.$threshold.' konfirmasi dalam satu jam menuju Raid Boss'" />
            <p class="font-caption text-caption text-primary">{{ $recent }}/{{ $threshold }} dalam 1 jam menuju Raid Boss</p>
        </section>
    @endif

    @if ($quest->photo_after)
        <section class="grid grid-cols-2 gap-sm">
            <figure class="flex flex-col gap-xs">
                <img src="{{ route('photo', [$quest, 'before']) }}" alt="Foto sebelum perbaikan" width="240" height="180" loading="lazy" class="aspect-[4/3] w-full rounded-lg border border-outline-variant object-cover">
                <figcaption class="meta">Before</figcaption>
            </figure>
            <figure class="flex flex-col gap-xs">
                <img src="{{ route('photo', [$quest, 'after']) }}" alt="Foto bukti setelah perbaikan" width="240" height="180" loading="lazy" class="aspect-[4/3] w-full rounded-lg border border-outline-variant object-cover">
                <figcaption class="meta">After</figcaption>
            </figure>
        </section>
    @else
        <figure class="flex flex-col gap-xs">
            <img src="{{ route('photo', [$quest, 'before']) }}" alt="Foto kerusakan dari pelapor" width="480" height="360" class="aspect-[4/3] w-full rounded-lg border border-outline-variant object-cover">
            <figcaption class="meta">Foto kerusakan (Before)</figcaption>
        </figure>
    @endif

    @if ($quest->status !== 'rejected')
        <section aria-labelledby="timeline-title" class="flex flex-col gap-md">
            <h2 id="timeline-title" class="section-title">Status</h2>
            <ol class="flex flex-col">
                @foreach ($steps as $i => $step)
                    <li class="relative flex gap-md pb-lg last:pb-0">
                        @if (! $loop->last)
                            <span @class(['absolute left-[9px] top-[20px] h-[calc(100%-20px)] w-[2px]', 'bg-primary' => $steps[$i + 1]['done'], 'bg-outline-variant' => ! $steps[$i + 1]['done']]) aria-hidden="true"></span>
                        @endif
                        <span @class(['relative z-10 mt-[2px] flex h-[20px] w-[20px] shrink-0 items-center justify-center rounded-full border-2', 'border-primary bg-primary text-on-primary' => $step['done'], 'border-outline bg-surface' => ! $step['done']])>
                            @if ($step['done'])<x-icon name="check" class="h-3 w-3" />@endif
                        </span>
                        <div class="flex flex-col">
                            <span @class(['font-label text-label', 'text-on-surface' => $step['done'], 'text-on-surface-variant' => ! $step['done']])>{{ $step['title'] }}</span>
                            @if ($step['meta'])<span class="meta">{{ $step['meta'] }}</span>@endif
                            <span class="sr-only">{{ $step['done'] ? 'selesai' : 'belum' }}</span>
                        </div>
                    </li>
                @endforeach
            </ol>
        </section>
    @endif

    @if ($isStudent && $role)
        <section class="flex items-start justify-between gap-md border-t border-outline-variant pt-lg">
            <div class="flex flex-col">
                <span class="meta">Peranmu</span>
                <span class="font-label text-label">{{ $role === 'First Discoverer' ? 'First Discoverer (pelapor pertama)' : 'Anggota party' }}</span>
            </div>
            <div class="text-right">
                @if ($quest->status === 'resolved')
                    <span class="font-label text-label font-semibold text-primary">{{ Format::xp($xpForUser, true) }}</span>
                    <span class="block meta">sudah masuk ke akunmu</span>
                @elseif ($quest->status === 'rejected')
                    <span class="font-label text-label font-semibold text-accent">{{ Format::xp($xpForUser, true) }}</span>
                    <span class="block meta">total dari quest ini</span>
                @else
                    <span class="font-label text-label font-semibold text-primary">+{{ $role === 'Party' ? $xp['party'] : $xp['discoverer_bonus'] }} XP</span>
                    <span class="block meta">cair saat quest selesai</span>
                @endif
            </div>
        </section>
    @elseif ($isStudent && $quest->isOpen() && $quest->reporter_id !== $user->id)
        <a href="{{ route('quest.join', $quest) }}" class="btn-secondary w-full">Konfirmasi Masalah Ini (+1 Party)</a>
    @endif
</x-layouts.mobile>
