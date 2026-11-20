@php use App\Support\Format; @endphp
<x-layouts.mobile title="Peringkat Fakultas" heading="Peringkat Fakultas" :subtitle="$user->faculty" nav="peringkat">
    <div class="grid grid-cols-2 rounded-md border border-outline-variant bg-surface p-xs" role="group" aria-label="Periode">
        <a href="{{ route('leaderboard', ['periode' => 'bulan']) }}" @class(['flex min-h-[40px] items-center justify-center rounded-md font-label text-label', 'bg-primary text-on-primary' => $period === 'bulan', 'text-on-surface-variant hover:text-on-surface' => $period !== 'bulan']) @if ($period === 'bulan') aria-current="page" @endif>Bulan ini</a>
        <a href="{{ route('leaderboard', ['periode' => 'semua']) }}" @class(['flex min-h-[40px] items-center justify-center rounded-md font-label text-label', 'bg-primary text-on-primary' => $period === 'semua', 'text-on-surface-variant hover:text-on-surface' => $period !== 'semua']) @if ($period === 'semua') aria-current="page" @endif>Semua waktu</a>
    </div>

    <ol class="-mx-lg divide-y divide-outline-variant border-y border-outline-variant bg-surface">
        @foreach ($top as $i => $row)
            @php($me = $row->id === $user->id)
            <li @class(['flex items-center gap-md px-lg py-md', 'bg-primary-container' => $me])>
                <span @class(['w-[28px] font-code text-code', 'font-semibold' => $i < 3, 'text-on-surface-variant' => $i >= 3])>{{ $i + 1 }}</span>
                <x-avatar :user="$row" />
                <div class="flex min-w-0 flex-1 flex-col">
                    <span class="truncate font-label text-label">{{ $me ? 'Kamu' : $row->name }}</span>
                    <span class="meta">{{ \App\Support\Tiers::current($row->xp)['name'] ?? 'Belum ada tier' }}</span>
                </div>
                <span class="shrink-0 font-label text-label font-semibold">{{ Format::xp((int) $row->score) }}</span>
            </li>
        @endforeach
    </ol>

    @if ($rank && $rank > 10)
        <x-slot:sticky>
            <div class="flex items-center gap-md rounded-md bg-primary-container px-md py-sm">
                <span class="font-code text-code font-semibold">#{{ $rank }}</span>
                <span class="flex-1 font-label text-label">Kamu</span>
                <span class="font-label text-label font-semibold">{{ Format::xp($myScore) }}</span>
            </div>
        </x-slot:sticky>
    @endif
</x-layouts.mobile>
