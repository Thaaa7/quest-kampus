@php
    use App\Support\Format;
    $info = $certificate?->info();
@endphp
<x-layouts.mobile title="Verifikasi E-Sertifikat" wordmark heading="Verifikasi E-Sertifikat">
    <form method="GET" action="{{ route('verify') }}" class="flex flex-col gap-sm" data-submitting="Memeriksa nomor seri...">
        <label for="seri" class="field-label">Nomor seri sertifikat</label>
        <input id="seri" name="seri" value="{{ $serial }}" required maxlength="30" autocomplete="off" autocapitalize="characters" spellcheck="false" placeholder="Contoh: KQ-M-2026-000418" class="input font-code uppercase">
        <button type="submit" class="btn-primary w-full">Cek Sertifikat</button>
        <p class="meta">Atau pindai QR di sertifikat dengan kamera ponsel. Halaman ini terbuka tanpa login.</p>
    </form>

    @if ($serial && $certificate)
        <section class="card flex flex-col gap-lg p-lg" aria-labelledby="result-title">
            <h2 id="result-title" class="sr-only">Hasil verifikasi</h2>
            <x-stamp status="valid" size="lg" class="self-start" />
            <dl class="grid grid-cols-[136px_1fr] gap-x-md gap-y-sm font-label text-label">
                <dt class="meta">Nama</dt><dd>{{ $certificate->user->name }}</dd>
                <dt class="meta">NIM</dt><dd class="font-code">{{ $certificate->user->identifier }}</dd>
                <dt class="meta">Fakultas</dt><dd>{{ $certificate->user->faculty }}</dd>
                <dt class="meta">Jenis</dt><dd>{{ $info['name'] }}</dd>
                <dt class="meta">Tier</dt><dd>{{ $info['tier_name'] }}</dd>
                <dt class="meta">Total XP</dt><dd>{{ Format::number($certificate->xp_at_issue) }} <span class="meta">(saat diterbitkan)</span></dd>
                <dt class="meta">Laporan valid</dt><dd>{{ $certificate->valid_reports }}</dd>
                <dt class="meta">Diterbitkan</dt><dd>{{ Format::longDate($certificate->issued_at) }}</dd>
                <dt class="meta">Nomor seri</dt><dd class="font-code">{{ $certificate->serial }}</dd>
                <dt class="meta">Rekomendasi</dt><dd class="font-semibold text-primary">{{ $info['skp'] }} SKP</dd>
            </dl>
        </section>
        <p class="meta">Data diambil langsung dari sistem Kampus Quest saat halaman ini dibuka, {{ Format::longDate(now()) }} pukul {{ Format::time(now()) }}.</p>
    @elseif ($serial)
        <section class="card flex flex-col gap-md p-lg" role="alert">
            <x-stamp status="notfound" size="lg" class="self-start" />
            <p class="text-body">Nomor seri <span class="font-code">{{ $serial }}</span> tidak ada di sistem. Periksa lagi nomornya atau minta mahasiswa mengirim ulang PDF aslinya.</p>
        </section>
    @endif
</x-layouts.mobile>
