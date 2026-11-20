@php
    use App\Support\Format;
    $max = end($tiers)['xp'];
    $progress = min(100, max(0, $user->xp / $max * 100));
@endphp
<x-layouts.mobile title="Profil" heading="Profil" nav="profil">
    <x-slot:actions>
        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button type="submit" class="btn-ghost btn-sm">Keluar</button>
        </form>
    </x-slot:actions>

    <section class="flex items-center gap-md">
        <x-avatar :user="$user" size="lg" />
        <div class="flex min-w-0 flex-col">
            <h2 class="font-title text-title">{{ $user->name }}</h2>
            <p class="meta"><span class="font-code">{{ $user->identifier }}</span>, {{ $user->faculty }}</p>
        </div>
    </section>

    <section class="flex flex-col gap-sm" aria-labelledby="xp-total">
        <h2 id="xp-total" class="sr-only">Total XP</h2>
        <p class="font-headline text-[32px] font-bold leading-[38px]">{{ Format::xp($user->xp) }}</p>
        @if ($tier)
            <p class="flex items-center gap-xs font-label text-label text-primary">
                <x-icon name="shield" :fill="true" class="h-5 w-5" /> Tier {{ $tier['level'] }}: {{ $tier['name'] }}
            </p>
        @else
            <p class="font-label text-label text-on-surface-variant">Belum ada tier. Tier pertama terbuka di {{ Format::xp($tiers[0]['xp']) }}.</p>
        @endif
    </section>

    <section class="flex flex-col gap-md" aria-labelledby="tier-title">
        <h2 id="tier-title" class="sr-only">Jalur tier</h2>
        <div class="relative h-sm rounded-sm bg-meter-empty" role="img" aria-label="{{ Format::xp($user->xp) }} dari {{ Format::xp($max) }}">
            <div class="absolute inset-y-0 left-0 rounded-sm bg-primary" style="width: {{ $progress }}%"></div>
            @foreach ($tiers as $t)
                <span @class(['absolute top-1/2 h-[16px] w-[3px] -translate-y-1/2', 'bg-primary' => $user->xp >= $t['xp'], 'bg-outline' => $user->xp < $t['xp']]) style="left: calc({{ $t['xp'] / $max * 100 }}% - 2px)"></span>
            @endforeach
        </div>
        <ol class="grid grid-cols-3 gap-sm">
            @foreach ($tiers as $t)
                <li @class(['flex flex-col', 'text-right' => $loop->last, 'text-center' => ! $loop->first && ! $loop->last])>
                    <span @class(['font-code text-code-sm', 'text-primary' => $user->xp >= $t['xp'], 'text-on-surface-variant' => $user->xp < $t['xp']])>{{ Format::number($t['xp']) }}</span>
                    <span class="meta">{{ $t['name'] }}</span>
                </li>
            @endforeach
        </ol>
        @if ($next)
            <p class="font-label text-label">{{ Format::xp($next['xp'] - $user->xp) }} lagi menuju {{ $next['name'] }}.</p>
        @else
            <p class="font-label text-label">Semua tier sudah tercapai.</p>
        @endif
    </section>

    <section class="flex flex-col gap-md" aria-labelledby="cert-title">
        <h2 id="cert-title" class="section-title">E-Sertifikat</h2>
        @foreach ($certificateTypes as $type)
            @php($issued = $certificates[$type['key']] ?? null)
            <article class="card flex flex-col gap-md p-lg">
                <div class="flex flex-col gap-xs">
                    @if ($issued)<x-stamp status="resolved" class="self-start" />@endif
                    <h3 class="font-title text-title">{{ $type['name'] }}</h3>
                    <p class="meta">Rekomendasi setara {{ $type['skp'] }} SKP</p>
                </div>
                @if ($issued)
                    <p class="font-label text-label">Sertifikat diterbitkan {{ Format::date($issued->issued_at) }}</p>
                    <p class="font-code text-code">{{ $issued->serial }}</p>
                    <div class="flex flex-wrap gap-sm">
                        <a href="{{ route('certificate.pdf', $issued) }}" class="btn-primary btn-sm"><x-icon name="download" class="h-5 w-5" /> Unduh PDF</a>
                        <button type="button" class="btn-secondary btn-sm" data-copy="{{ $issued->serial }}"><x-icon name="content_copy" class="h-5 w-5" /><span data-copy-label>Salin Nomor Seri</span></button>
                    </div>
                @elseif ($user->xp >= $type['xp'])
                    <p class="meta">PDF berisi nama, NIM, total XP, jumlah laporan valid, nomor seri, dan QR verifikasi.</p>
                    <form method="POST" action="{{ route('certificate.claim', $type['key']) }}" data-submitting="Menerbitkan...">
                        @csrf
                        <button type="submit" class="btn-primary w-full">Klaim E-Sertifikat</button>
                    </form>
                @else
                    <button type="button" class="btn-primary w-full" disabled><x-icon name="lock" class="h-5 w-5" /> Klaim E-Sertifikat</button>
                    <p class="meta">Terbuka di {{ Format::xp($type['xp']) }} ({{ $type['tier_name'] }}).</p>
                @endif
            </article>
        @endforeach
    </section>

    <section class="grid grid-cols-3 gap-sm border-y border-outline-variant py-lg text-center">
        <div><p class="font-title text-title">{{ $validReports }}</p><p class="meta">laporan valid</p></div>
        <div><p class="font-title text-title">{{ $partyCount }}</p><p class="meta">konfirmasi party</p></div>
        <div><p class="font-title text-title">{{ Format::number($pendingXp) }}</p><p class="meta">XP menunggu</p></div>
    </section>

    <section class="flex flex-col gap-sm" aria-labelledby="ledger-title">
        <h2 id="ledger-title" class="section-title">Riwayat XP</h2>
        @if ($ledger->isEmpty())
            <p class="meta">Belum ada XP. Laporkan kerusakan atau konfirmasi laporan teman untuk mulai mengumpulkan XP.</p>
        @else
            <ul class="divide-y divide-outline-variant border-y border-outline-variant">
                @foreach ($ledger as $row)
                    <li class="flex items-start justify-between gap-md py-md">
                        <div class="flex min-w-0 flex-col">
                            <span class="font-label text-label">{{ $row->description }}</span>
                            <span class="meta">{{ Format::date($row->created_at) }}</span>
                        </div>
                        <span @class(['shrink-0 font-label text-label font-semibold', 'text-primary' => $row->amount >= 0, 'text-accent' => $row->amount < 0])>{{ Format::xp($row->amount, true) }}</span>
                    </li>
                @endforeach
            </ul>
        @endif
    </section>
</x-layouts.mobile>
