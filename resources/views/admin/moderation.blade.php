@php
    use App\Support\Format;
    $penalty = abs(config('kampusquest.xp.penalty'));
@endphp
<x-layouts.admin title="Moderasi Laporan" active="moderation">
    <header class="flex flex-col gap-xs">
        <h1 class="font-headline text-headline">Moderasi laporan</h1>
        <p class="text-body text-on-surface-variant">
            @if ($queue->isEmpty())
                Tidak ada laporan yang perlu ditinjau.
            @else
                {{ $queue->count() }} laporan ditandai untuk ditinjau. Laporan yang ditolak memberi penalti -{{ $penalty }} XP ke pelapor.
            @endif
        </p>
    </header>

    @if ($queue->isEmpty())
        <section class="card px-lg py-3xl text-center">
            <x-icon name="check_circle" class="mx-auto h-10 w-10 text-primary" />
            <p class="mt-sm font-title text-title">Antrean moderasi kosong</p>
            <p class="meta mt-xs">Laporan yang ditandai teknisi atau sistem akan muncul di sini.</p>
        </section>
    @else
        <div class="grid items-start gap-xl lg:grid-cols-[380px_1fr]">
            <section class="card overflow-hidden" aria-label="Laporan yang ditandai">
                <ul class="divide-y divide-outline-variant">
                    @foreach ($queue as $item)
                        @php($isSelected = $selected->id === $item->id)
                        <li>
                            <a href="{{ route('admin.moderation', ['pilih' => $item->number]) }}" @class(['flex flex-col gap-xs px-lg py-md', 'bg-primary-container' => $isSelected, 'hover:bg-background' => ! $isSelected]) @if ($isSelected) aria-current="true" @endif>
                                <span class="flex items-center justify-between gap-sm">
                                    <span class="font-code text-code-sm">{{ $item->number }}</span>
                                    <span class="meta">{{ Format::date($item->flagged_at) }}, {{ Format::time($item->flagged_at) }}</span>
                                </span>
                                <span class="font-label text-label">{{ $item->room->code }}, {{ $item->room->name }}</span>
                                <span class="meta">{{ $item->flagged_reason }}</span>
                            </a>
                        </li>
                    @endforeach
                </ul>
            </section>

            <section class="card flex flex-col gap-lg p-lg" aria-labelledby="detail-title">
                <div class="flex flex-wrap items-center justify-between gap-sm">
                    <h2 id="detail-title" class="font-code text-code font-semibold">No. {{ $selected->number }}</h2>
                    <x-stamp :status="$selected->stamp()" />
                </div>
                <div class="grid gap-lg md:grid-cols-2">
                    <img src="{{ route('photo', [$selected, 'before']) }}" alt="Foto laporan {{ $selected->number }}" width="400" height="300" class="aspect-[4/3] w-full rounded-lg border border-outline-variant object-cover">
                    <div class="flex flex-col gap-md">
                        <p class="flex items-center gap-xs meta"><x-icon :name="$selected->categoryIcon()" class="h-4 w-4" />{{ $selected->categoryLabel() }}</p>
                        <p class="font-title text-title">{{ $selected->description }}</p>
                        <dl class="grid grid-cols-[120px_1fr] gap-x-md gap-y-sm font-label text-label">
                            <dt class="meta">Ruangan dari QR</dt><dd>{{ $selected->room->code }}, {{ $selected->room->name }}</dd>
                            <dt class="meta">Waktu lapor</dt><dd>{{ Format::date($selected->created_at) }}, {{ Format::time($selected->created_at) }}</dd>
                            <dt class="meta">Pelapor</dt><dd>{{ $selected->reporter->name }}, <span class="font-code">{{ $selected->reporter->identifier }}</span></dd>
                            <dt class="meta">Riwayat pelapor</dt><dd>{{ $history['valid'] }} laporan valid, {{ $history['rejected'] }} ditolak</dd>
                            <dt class="meta">Alasan ditandai</dt><dd>{{ $selected->flagged_reason }}</dd>
                        </dl>
                    </div>
                </div>
                @if ($selected->technician_note)
                    <div class="rounded-lg border border-outline-variant bg-background p-md">
                        <p class="meta">Catatan teknisi</p>
                        <p class="font-label text-label">{{ $selected->technician_note }}</p>
                    </div>
                @endif
                <div class="flex flex-wrap gap-sm border-t border-outline-variant pt-lg">
                    <form method="POST" action="{{ route('admin.moderation.approve', $selected) }}">
                        @csrf
                        <button type="submit" class="btn-secondary btn-sm">Loloskan Laporan</button>
                    </form>
                    <button type="button" class="btn-danger-outline btn-sm" data-dialog-open="reject-dialog" aria-haspopup="dialog">Tolak sebagai Spam (-{{ $penalty }} XP)</button>
                </div>
            </section>
        </div>

        <dialog id="reject-dialog" class="dialog" aria-labelledby="reject-title" @if ($errors->reject->any()) data-open-on-load @endif>
            <form method="POST" action="{{ route('admin.moderation.reject', $selected) }}" class="flex flex-col gap-lg p-xl">
                @csrf
                <div class="flex items-start justify-between gap-md">
                    <h2 id="reject-title" class="section-title">Tolak laporan {{ $selected->number }}?</h2>
                    <button type="button" class="btn-icon -mr-sm -mt-sm" data-dialog-close aria-label="Tutup"><x-icon name="close" /></button>
                </div>
                <fieldset class="flex flex-col gap-xs">
                    <legend class="field-label mb-sm">Alasan</legend>
                    @foreach ($reasons as $reason)
                        <label class="flex min-h-[44px] items-center gap-sm font-label text-label">
                            <input type="radio" name="reason" value="{{ $reason }}" @checked(old('reason') === $reason) class="h-5 w-5 border-outline text-primary focus:ring-primary"> {{ $reason }}
                        </label>
                    @endforeach
                    @if ($errors->reject->has('reason'))<p class="field-error" role="alert">{{ $errors->reject->first('reason') }}</p>@endif
                </fieldset>
                <div class="flex flex-col gap-xs">
                    <label for="note" class="field-label">Catatan untuk mahasiswa (opsional)</label>
                    <textarea id="note" name="note" rows="3" maxlength="500" class="input">{{ old('note') }}</textarea>
                </div>
                <p class="notice-error">-{{ $penalty }} XP akan dikurangi dari {{ $selected->reporter->name }}. Keputusan ini tercatat di riwayat moderasi.</p>
                <div class="flex flex-wrap justify-end gap-sm">
                    <button type="button" class="btn-secondary btn-sm" data-dialog-close>Batal</button>
                    <button type="submit" class="btn-danger btn-sm">Tolak Laporan</button>
                </div>
            </form>
        </dialog>
    @endif
</x-layouts.admin>
