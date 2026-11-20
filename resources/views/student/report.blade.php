@php($xp = config('kampusquest.xp'))
<x-layouts.mobile title="Laporkan Kerusakan" heading="Laporkan Kerusakan" :subtitle="$room->code.', '.$room->name" :back="route('room.show', $room->qr_token)" back-label="Kembali ke profil ruangan">
    <form id="report-form" method="POST" action="{{ route('quest.store', $room->qr_token) }}" enctype="multipart/form-data" class="flex flex-col gap-xl" data-report-form data-submitting="Menerbitkan...">
        @csrf
        @php($forced = old('force_new', $forced))
        @if ($forced)
            <input type="hidden" name="force_new" value="{{ $forced }}">
        @endif
        @if ($errors->any())
            <div class="notice-error" role="alert">Quest belum terkirim. Periksa bagian yang ditandai merah, lalu ambil ulang fotonya bila diminta.</div>
        @endif

        <fieldset class="flex flex-col gap-md">
            <legend class="section-title mb-md">1. Apa yang rusak?</legend>
            <div class="grid grid-cols-2 gap-sm">
                @foreach ($categories as $key => $cat)
                    <label class="group relative flex min-h-[72px] items-center gap-sm rounded-lg border border-outline-variant bg-surface p-md has-[:checked]:border-2 has-[:checked]:border-primary has-[:checked]:bg-primary-container has-[:focus-visible]:outline has-[:focus-visible]:outline-2 has-[:focus-visible]:outline-offset-2 has-[:focus-visible]:outline-primary">
                        <input type="radio" name="category" value="{{ $key }}" class="sr-only" @checked(old('category', $forced) === $key)
                               @if (isset($duplicates[$key])) data-join-url="{{ $duplicates[$key] }}" @endif data-short="{{ $cat['short'] }}">
                        <x-icon :name="$cat['icon']" class="h-6 w-6 text-on-surface-variant group-has-[:checked]:text-primary" />
                        <span class="font-label text-label">{{ $cat['label'] }}</span>
                        <span class="absolute right-sm top-sm hidden text-primary group-has-[:checked]:block"><x-icon name="check_circle" :fill="true" class="h-5 w-5" /></span>
                    </label>
                @endforeach
            </div>
            @error('category')<p class="field-error" role="alert">{{ $message }}</p>@enderror

            <div id="duplicate-hint" hidden class="notice-info flex flex-col gap-sm" role="status">
                <p><span id="duplicate-label">Kerusakan ini</span> di ruangan ini sudah dilaporkan. Konfirmasi laporan yang ada saja, tidak perlu membuat laporan baru.</p>
                <a id="duplicate-link" href="#" class="btn-secondary btn-sm self-start">Lihat Laporan yang Ada</a>
            </div>
        </fieldset>

        <section class="flex flex-col gap-sm">
            <h2 class="section-title">2. Foto kerusakan</h2>
            <x-camera-field name="photo" label="Foto kerusakan" hint="Foto diambil langsung dari kamera, tidak bisa dari galeri." />
        </section>

        <section class="flex flex-col gap-sm">
            <h2 class="section-title">3. Keterangan singkat</h2>
            <label for="description" class="field-label">Keterangan</label>
            <textarea id="description" name="description" rows="3" maxlength="200" required data-counter="description-count" placeholder="Contoh: AC tidak menyala sejak pagi"
                      @class(['input', 'input-error' => $errors->has('description')])>{{ old('description') }}</textarea>
            <div class="flex justify-between gap-sm">
                @error('description')<p class="field-error" role="alert">{{ $message }}</p>@else<span></span>@enderror
                <span id="description-count" class="meta font-code" aria-live="polite">0/200</span>
            </div>
        </section>

        <div class="flex flex-col gap-xs">
            <p class="notice-success">+{{ $xp['create'] }} XP saat quest terbit. +{{ $xp['discoverer_bonus'] }} XP lagi saat teknisi menyelesaikannya.</p>
            <p class="meta">Laporan palsu dikenai {{ $xp['penalty'] }} XP.</p>
        </div>
    </form>

    <x-slot:sticky>
        <button type="submit" form="report-form" class="btn-primary w-full">Terbitkan Quest</button>
    </x-slot:sticky>
    <x-slot:after>
        <x-camera-dialog />
    </x-slot:after>
</x-layouts.mobile>
