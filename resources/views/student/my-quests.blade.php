@php
    use App\Support\Format;
    $xp = config('kampusquest.xp');
    $tabs = ['aktif' => 'Aktif', 'selesai' => 'Selesai', 'ditolak' => 'Ditolak'];
@endphp
<x-layouts.mobile title="Quest Saya" heading="Quest Saya" nav="quest">
    <p class="font-label text-label text-on-surface-variant">{{ $counts['aktif'] }} quest aktif, {{ Format::xp($pendingXp) }} menunggu cair</p>

    <nav class="-mx-lg flex border-b border-outline-variant px-lg" aria-label="Filter quest">
        @foreach ($tabs as $key => $label)
            <a href="{{ route('my-quests', ['tab' => $key]) }}" @class(['tab', 'tab-active' => $tab === $key]) @if ($tab === $key) aria-current="page" @endif>
                {{ $label }} ({{ $counts[$key] }})
            </a>
        @endforeach
    </nav>

    @if ($quests->isEmpty())
        <section class="flex flex-col items-center gap-lg py-2xl text-center">
            @if ($tab === 'aktif' && $counts->sum() === 0)
                <p class="text-body text-on-surface-variant">Belum ada quest. Saat menemukan kerusakan, pindai QR di pintu ruangan untuk melapor.</p>
                <a href="{{ route('scan') }}" class="btn-primary w-full">Pindai QR Ruangan</a>
            @elseif ($tab === 'aktif')
                <p class="text-body text-on-surface-variant">Tidak ada quest yang sedang berjalan. Riwayatmu ada di tab Selesai.</p>
            @elseif ($tab === 'selesai')
                <p class="text-body text-on-surface-variant">Belum ada quest yang diselesaikan teknisi.</p>
            @else
                <p class="text-body text-on-surface-variant">Tidak ada laporan yang ditolak.</p>
            @endif
        </section>
    @else
        <ul class="-mx-lg divide-y divide-outline-variant border-y border-outline-variant bg-surface">
            @foreach ($quests as $quest)
                @php
                    $isReporter = $quest->reporter_id === $user->id;
                    $pending = $isReporter ? $xp['discoverer_bonus'] : $xp['party'];
                    $got = (int) ($earned[$quest->id] ?? 0);
                @endphp
                <li>
                    <a href="{{ route('quest.show', $quest) }}" class="flex items-start gap-md px-lg py-md hover:bg-background">
                        <div class="flex min-w-0 flex-1 flex-col gap-xs">
                            <div class="flex flex-wrap items-center gap-sm">
                                <span class="font-code text-code-sm text-on-surface-variant">{{ $quest->number }}</span>
                                <x-stamp :status="$quest->stamp()" />
                            </div>
                            <span class="font-label text-label">{{ $quest->description }}, {{ $quest->room->code }}</span>
                            <span class="meta">{{ $isReporter ? 'First Discoverer' : 'Party' }}</span>
                            @if ($quest->status === 'rejected')
                                <span class="meta">{{ $quest->reject_reason }}. Ditinjau admin {{ Format::date($quest->moderated_at) }}.</span>
                            @endif
                        </div>
                        <div class="shrink-0 text-right">
                            @if ($quest->isOpen())
                                <span class="block font-label text-label font-semibold text-primary">+{{ $pending }} XP</span>
                                <span class="meta">menunggu</span>
                            @else
                                <span @class(['block font-label text-label font-semibold', 'text-primary' => $got >= 0, 'text-accent' => $got < 0])>{{ Format::xp($got, true) }}</span>
                                <span class="meta">{{ $quest->status === 'resolved' ? 'sudah cair' : 'total' }}</span>
                            @endif
                        </div>
                    </a>
                </li>
            @endforeach
        </ul>
    @endif
</x-layouts.mobile>
