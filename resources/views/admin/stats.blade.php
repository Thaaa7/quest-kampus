@php
    use App\Support\Format;
    $maxDuration = max(1, $durations->max('median') ?? 1);
    $maxCategory = max(1, $categories->max('total'));
    $maxWeek = max(1, $weeks->max('total'));

    // Grafik garis Raid Boss per minggu: SVG sederhana, satu seri, satu sumbu.
    $w = 1100; $h = 220; $padL = 32; $padR = 24; $padT = 28; $padB = 32;
    $stepX = ($w - $padL - $padR) / max(1, $weeks->count() - 1);
    $yFor = fn ($v) => $padT + ($h - $padT - $padB) * (1 - $v / $maxWeek);
    $points = $weeks->values()->map(fn ($wk, $i) => ['x' => $padL + $i * $stepX, 'y' => $yFor($wk['total'])] + $wk);
@endphp
<x-layouts.admin title="Statistik Fasilitas" active="stats">
    <header class="flex flex-wrap items-center justify-between gap-md">
        <h1 class="font-headline text-headline">Statistik fasilitas</h1>
        <form method="GET" action="{{ route('admin.stats') }}" class="flex items-center gap-sm">
            <label for="periode" class="meta">Periode</label>
            <select id="periode" name="periode" class="input w-auto py-sm pr-2xl font-label text-label">
                @foreach ($periods as $value => $label)
                    <option value="{{ $value }}" @selected($days === $value)>{{ $label }}</option>
                @endforeach
            </select>
            <button type="submit" class="btn-secondary btn-sm">Terapkan</button>
        </form>
    </header>

    @if (! $hasData)
        <section class="card px-lg py-3xl text-center">
            <p class="font-title text-title">Belum ada cukup quest di periode ini.</p>
            <p class="meta mt-sm">Statistik muncul setelah mahasiswa melapor dan teknisi menyelesaikan quest pertama.</p>
        </section>
    @else
        <section class="card flex flex-col gap-md p-lg" aria-labelledby="critical-title">
            <div class="flex flex-col gap-xs">
                <h2 id="critical-title" class="section-title">Ruangan yang perlu ditangani sekarang</h2>
                <p class="meta">Lima ruangan dengan HP terendah saat ini.</p>
            </div>
            @if ($critical->isEmpty())
                <p class="font-label text-label">Semua ruangan sehat. Tidak ada kerusakan yang terbuka.</p>
            @else
                <ul class="divide-y divide-outline-variant border-y border-outline-variant">
                    @foreach ($critical as $row)
                        <li class="grid items-center gap-sm py-md sm:grid-cols-[96px_1fr_260px_120px]">
                            <span class="font-code text-code">{{ $row['room']->code }}</span>
                            <span class="flex flex-wrap items-center gap-sm font-label text-label">
                                {{ $row['room']->name }}
                                @if ($row['raid'])<x-stamp status="raid" />@endif
                            </span>
                            <x-hp-meter :hp="$row['hp']" />
                            <span class="meta sm:text-right">{{ $row['open'] }} quest aktif</span>
                        </li>
                    @endforeach
                </ul>
            @endif
        </section>

        <div class="grid gap-xl lg:grid-cols-2">
            <section class="card flex flex-col gap-md p-lg" aria-labelledby="duration-title">
                <div class="flex flex-col gap-xs">
                    <h2 id="duration-title" class="section-title">Berapa lama kerusakan ditangani di tiap gedung?</h2>
                    <p class="meta">Median waktu dari laporan sampai selesai, {{ strtolower($periods[$days]) }}.</p>
                </div>
                @if ($durations->isEmpty())
                    <p class="meta">Belum ada quest yang selesai di periode ini.</p>
                @else
                    <ul class="flex flex-col gap-md">
                        @foreach ($durations as $row)
                            <li class="grid grid-cols-[120px_1fr] items-center gap-md" title="{{ $row['building']->name }}: median {{ Format::duration($row['median']) }} dari {{ $row['count'] }} quest">
                                <span class="truncate font-label text-label">{{ $row['building']->name }}</span>
                                <span class="flex items-center gap-sm">
                                    <span class="h-[16px] rounded-r-[4px] bg-primary" style="width: {{ max(2, $row['median'] / $maxDuration * 75) }}%"></span>
                                    <span class="whitespace-nowrap font-code text-code-sm">{{ Format::duration($row['median']) }}</span>
                                </span>
                            </li>
                        @endforeach
                    </ul>
                @endif
            </section>

            <section class="card flex flex-col gap-md p-lg" aria-labelledby="category-title">
                <div class="flex flex-col gap-xs">
                    <h2 id="category-title" class="section-title">Kategori apa yang paling sering rusak?</h2>
                    <p class="meta">Jumlah laporan per kategori, {{ strtolower($periods[$days]) }}, tanpa laporan yang ditolak.</p>
                </div>
                <ul class="flex flex-col gap-sm">
                    @foreach ($categories as $row)
                        <li class="grid grid-cols-[160px_1fr] items-center gap-md" title="{{ $row['label'] }}: {{ $row['total'] }} laporan">
                            <span class="flex items-center gap-sm truncate font-label text-label"><x-icon :name="$row['icon']" class="h-5 w-5 text-on-surface-variant" />{{ $row['label'] }}</span>
                            <span class="flex items-center gap-sm">
                                @if ($row['total'] > 0)
                                    <span class="h-[16px] rounded-r-[4px] bg-primary" style="width: {{ $row['total'] / $maxCategory * 80 }}%"></span>
                                @endif
                                <span class="font-code text-code-sm">{{ $row['total'] }}</span>
                            </span>
                        </li>
                    @endforeach
                </ul>
            </section>
        </div>

        <section class="card flex flex-col gap-md p-lg" aria-labelledby="raid-title">
            <div class="flex flex-col gap-xs">
                <h2 id="raid-title" class="section-title">Berapa Raid Boss muncul tiap minggu?</h2>
                <p class="meta">Jumlah quest yang naik jadi Raid Boss, 8 minggu terakhir. Arahkan kursor ke titik untuk melihat angkanya.</p>
            </div>
            <svg viewBox="0 0 {{ $w }} {{ $h }}" class="h-auto w-full max-h-[260px]" role="img" aria-labelledby="raid-title">
                @foreach ([0, $maxWeek] as $tick)
                    <line x1="{{ $padL }}" x2="{{ $w - $padR }}" y1="{{ $yFor($tick) }}" y2="{{ $yFor($tick) }}" stroke="#D9D6CC" stroke-width="1" />
                    <text x="{{ $padL - 8 }}" y="{{ $yFor($tick) + 4 }}" text-anchor="end" font-size="12" fill="#4F5A54" font-family="IBM Plex Mono, monospace">{{ $tick }}</text>
                @endforeach
                <polyline fill="none" stroke="#B23A22" stroke-width="2" stroke-linejoin="round" stroke-linecap="round"
                          points="{{ $points->map(fn ($p) => round($p['x'], 1).','.round($p['y'], 1))->implode(' ') }}" />
                @foreach ($points as $p)
                    <g>
                        <circle cx="{{ $p['x'] }}" cy="{{ $p['y'] }}" r="12" fill="transparent"><title>Minggu {{ $p['label'] }}: {{ $p['total'] }} Raid Boss</title></circle>
                        <circle cx="{{ $p['x'] }}" cy="{{ $p['y'] }}" r="4" fill="#B23A22" stroke="#FFFFFF" stroke-width="2" pointer-events="none" />
                        <text x="{{ $p['x'] }}" y="{{ $h - 8 }}" text-anchor="middle" font-size="12" fill="#4F5A54" font-family="Plus Jakarta Sans, sans-serif">{{ $p['label'] }}</text>
                    </g>
                @endforeach
                @php($last = $points->last())
                <text x="{{ $last['x'] }}" y="{{ $last['y'] - 12 }}" text-anchor="end" font-size="12" font-weight="600" fill="#1B2420" font-family="IBM Plex Mono, monospace">{{ $last['total'] }}</text>
            </svg>
            <table class="sr-only">
                <caption>Raid Boss per minggu</caption>
                <thead><tr><th scope="col">Minggu mulai</th><th scope="col">Jumlah</th></tr></thead>
                <tbody>
                    @foreach ($weeks as $wk)<tr><td>{{ $wk['label'] }}</td><td>{{ $wk['total'] }}</td></tr>@endforeach
                </tbody>
            </table>
        </section>
    @endif
</x-layouts.admin>
