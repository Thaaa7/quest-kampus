@php
    use App\Support\Format;
    $tabs = ['antrean' => 'Antrean', 'dikerjakan' => 'Dikerjakan', 'selesai' => 'Selesai hari ini'];
    $url = fn (array $extra = []) => route('technician.queue', array_filter(array_merge(['tab' => $tab === 'antrean' ? null : $tab], $filters, $extra)));
@endphp
<x-layouts.technician title="Antrean Tiket">
    <div class="flex flex-col gap-md">
        <h1 class="font-headline text-headline">Antrean tiket</h1>
        <nav class="flex flex-wrap border-b border-outline-variant" aria-label="Status tiket">
            @foreach ($tabs as $key => $label)
                <a href="{{ route('technician.queue', array_filter(array_merge(['tab' => $key === 'antrean' ? null : $key], $filters))) }}" @class(['tab', 'tab-active' => $tab === $key]) @if ($tab === $key) aria-current="page" @endif>
                    {{ $label }} ({{ $counts[$key] }})
                </a>
            @endforeach
        </nav>
    </div>

    <nav class="flex flex-wrap items-center gap-sm" aria-label="Filter gedung">
        <a href="{{ route('technician.queue', array_filter(['tab' => $tab === 'antrean' ? null : $tab, 'kategori' => $category])) }}" @class(['chip', 'chip-active' => ! $building]) @if (! $building) aria-current="true" @endif>Semua gedung</a>
        @foreach ($buildings as $b)
            <a href="{{ route('technician.queue', array_filter(['tab' => $tab === 'antrean' ? null : $tab, 'kategori' => $category, 'gedung' => $b->id])) }}" @class(['chip', 'chip-active' => $building === $b->id]) @if ($building === $b->id) aria-current="true" @endif>{{ $b->name }}</a>
        @endforeach
    </nav>
    <form method="GET" action="{{ route('technician.queue') }}" class="-mt-md flex items-center gap-sm">
        @if ($tab !== 'antrean')<input type="hidden" name="tab" value="{{ $tab }}">@endif
        @if ($building)<input type="hidden" name="gedung" value="{{ $building }}">@endif
        <label for="kategori" class="meta">Kategori</label>
        <select id="kategori" name="kategori" class="input w-auto py-sm pr-2xl font-label text-label">
            <option value="">Semua kategori</option>
            @foreach ($categories as $key => $cat)
                <option value="{{ $key }}" @selected($category === $key)>{{ $cat['label'] }}</option>
            @endforeach
        </select>
        <button type="submit" class="btn-secondary btn-sm">Terapkan</button>
    </form>

    @foreach ($raids as $raid)
        <section class="card grid gap-lg border-[1.5px] border-accent p-lg md:grid-cols-[1fr_auto_auto] md:items-center" aria-label="Raid Boss {{ $raid->number }}">
            <div class="flex flex-col gap-xs">
                <div class="flex flex-wrap items-center gap-sm">
                    <x-stamp status="raid" />
                    <span class="font-code text-code-sm text-on-surface-variant">No. {{ $raid->number }}</span>
                </div>
                <h2 class="font-title text-title">{{ $raid->description }}</h2>
                <p class="font-label text-label text-on-surface-variant">{{ $raid->room->code }}, {{ $raid->room->name }}</p>
                <p class="font-label text-label text-accent">{{ $raid->confirmations_count }} konfirmasi, Raid Boss sejak {{ Format::time($raid->raid_boss_at) }}</p>
            </div>
            <div class="flex flex-col md:items-center">
                <x-stopwatch :since="$raid->raid_boss_at" class="text-[40px] font-semibold leading-[44px] text-accent md:text-[48px] md:leading-[52px]" />
                <span class="meta">durasi kerusakan</span>
            </div>
            <div class="flex flex-col gap-md md:items-end">
                <x-hp-meter :hp="$raid->room->hp()" size="sm" />
                <form method="POST" action="{{ route('technician.start', $raid) }}" data-submitting="Memulai...">
                    @csrf
                    <button type="submit" class="btn-primary btn-sm w-full md:w-auto">Mulai Kerjakan</button>
                </form>
            </div>
        </section>
    @endforeach

    @if ($rows->isEmpty() && $raids->isEmpty())
        <section class="card flex flex-col items-center gap-sm px-lg py-3xl text-center">
            <x-icon name="check_circle" class="h-10 w-10 text-primary" />
            <p class="font-title text-title">
                @if ($tab === 'antrean') Tidak ada tiket menunggu.
                @elseif ($tab === 'dikerjakan') Tidak ada tiket yang sedang dikerjakan.
                @else Belum ada tiket yang selesai hari ini.
                @endif
            </p>
            @if ($tab === 'antrean')<p class="meta">Tiket baru muncul di sini begitu mahasiswa melapor.</p>@endif
        </section>
    @elseif ($rows->isNotEmpty())
        <div class="grid items-start gap-xl xl:grid-cols-[1fr_360px]">
            <section class="card overflow-x-auto" aria-label="Daftar tiket">
                <table class="w-full min-w-[720px] border-collapse">
                    <thead>
                        <tr class="border-b border-outline-variant bg-background">
                            <th class="th pl-lg" scope="col">Ruangan</th>
                            <th class="th" scope="col">Kerusakan</th>
                            <th class="th" scope="col">Konfirmasi</th>
                            <th class="th" scope="col">{{ $tab === 'selesai' ? 'Ditangani' : 'Menunggu' }}</th>
                            <th class="th" scope="col">HP ruangan</th>
                            <th class="th pr-lg text-right" scope="col">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-outline-variant">
                        @foreach ($rows as $row)
                            @php($isSelected = $selected && $selected->id === $row->id)
                            <tr @class(['bg-primary-container' => $isSelected])>
                                <td class="td pl-lg">
                                    <a href="{{ $url(['pilih' => $row->number]) }}" class="flex flex-col hover:underline" @if ($isSelected) aria-current="true" @endif>
                                        <span class="font-code text-code-sm">{{ $row->room->code }}</span>
                                        <span class="meta">{{ $row->room->name }}</span>
                                    </a>
                                </td>
                                <td class="td">
                                    <span class="flex items-center gap-sm font-label text-label">
                                        <x-icon :name="$row->categoryIcon()" class="h-5 w-5 text-on-surface-variant" />
                                        <span>{{ $row->description }}</span>
                                    </span>
                                    @if ($row->is_raid_boss)<x-stamp status="raid" class="mt-xs" />@endif
                                </td>
                                <td class="td font-code text-code">{{ $row->confirmations_count }}</td>
                                <td class="td meta">{{ Format::duration($row->ageSeconds()) }}</td>
                                <td class="td"><x-hp-meter :hp="$row->room->hp()" size="sm" /></td>
                                <td class="td pr-lg text-right">
                                    @if ($row->status === 'active')
                                        <form method="POST" action="{{ route('technician.start', $row) }}" data-submitting="Memulai...">
                                            @csrf
                                            <button type="submit" class="btn-secondary btn-sm">Mulai Kerjakan</button>
                                        </form>
                                    @elseif ($row->status === 'in_progress')
                                        <a href="{{ route('technician.ticket', $row) }}" class="btn-primary btn-sm">Selesaikan Quest</a>
                                    @else
                                        <a href="{{ route('technician.ticket', $row) }}" class="btn-ghost btn-sm">Lihat</a>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </section>

            @if ($selected)
                <aside class="card flex flex-col gap-md p-lg" aria-label="Detail tiket {{ $selected->number }}">
                    <div class="flex items-center justify-between gap-sm">
                        <span class="font-code text-code-sm text-on-surface-variant">No. {{ $selected->number }}</span>
                        <x-stamp :status="$selected->stamp()" />
                    </div>
                    <img src="{{ route('photo', [$selected, $selected->photo_after ? 'after' : 'before']) }}" alt="Foto {{ $selected->photo_after ? 'setelah perbaikan' : 'kerusakan' }} {{ $selected->room->name }}" width="328" height="246" loading="lazy" class="aspect-[4/3] w-full rounded-md border border-outline-variant object-cover">
                    <h2 class="font-title text-title">{{ $selected->description }}</h2>
                    <p class="font-label text-label text-on-surface-variant">{{ $selected->room->code }}, {{ $selected->room->name }}, {{ $selected->room->location() }}</p>
                    <p class="meta">Dilaporkan oleh {{ $selected->reporter->name }}, {{ $selected->created_at->diffForHumans() }}</p>
                    @if ($selected->flagged_at)
                        <p class="notice-info">Sedang ditinjau admin: {{ $selected->flagged_reason }}</p>
                    @endif
                    @if ($selected->status === 'active')
                        <form method="POST" action="{{ route('technician.start', $selected) }}" data-submitting="Memulai...">
                            @csrf
                            <button type="submit" class="btn-primary w-full">Mulai Kerjakan</button>
                        </form>
                    @endif
                    <a href="{{ route('technician.ticket', $selected) }}" class="btn-ghost w-full">Buka Tiket Lapangan</a>
                </aside>
            @endif
        </div>
    @endif
</x-layouts.technician>
